<?php
/*
Plugin Name: Recently Viewed Posts
Plugin URI: http://www.ostext.org
Description: Show the posts (and pages) that have been recently viewed on your wordpress blog
Author: Michael Jentsch
Version: 0.1
Author URI: http://www.ostext.org
*/

/* 
    Copyright 2013 Michael Jentsch (http://ostext.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define('RECENTLY_VIEWED_DATA', 'recently_viewed' );
define('RECENTLY_VIEWED_POST', 0 );
define('RECENTLY_VIEWED_PAGE', 1 );
define('RECENTLY_VIEWED_MAX_ANZ', 100 ); // Recently viewed pages in database
define('RECENTLY_VIEWED_BASENAME', plugin_basename(__FILE__));

// Admin Menu
require_once ("inc/recently-viewed-posts-admin.php");

function recently_viewed_header() 
{
	if (is_single()) {
		recently_viewed_save_post();
		return;
	}
	if (is_page()) {
		recently_viewed_save_page();
		return;
	}
}

function recently_viewed_save_post() 
{
	recently_viewed_save(RECENTLY_VIEWED_POST);
}

function recently_viewed_save_page() 
{
	recently_viewed_save(RECENTLY_VIEWED_PAGE);
}

function recently_viewed_save($type) {
	global $wp_query;
	$ID = $wp_query->post->ID;
	$recently_viewed = get_option(RECENTLY_VIEWED_DATA . "_" . $type);
	if (!is_array ($recently_viewed))
	{
		$recently_viewed = array ();
	}
	// Alte Einträge löschen und die ID der Seite eintragen
	$recently_viewed = recently_viewed_refresh ($recently_viewed, $ID);

	update_option (RECENTLY_VIEWED_DATA . "_" . $type, $recently_viewed);
}

function recently_viewed_get_blackist ()
{
	$recently_viewed_options = get_option(RECENTLY_VIEWED_OPTIONS);
        $exclude = recently_viewed_get_option ($recently_viewed_options, 'recently_exclude', '');
	$exclude = str_replace ("\r","\n", $exclude);
	$exclude = str_replace ("\n\n","\n", $exclude);
	$exclude = explode ("\n", $exclude);
	foreach ($exclude as $key => $value)
	{
		$exclude[intval ($value)] = 1;
	}
	return $exclude;
}

function recently_viewed_in_blackist ($id)
{
	// Blacklisted Ids // TODO: Admin Interface
	$recently_viewed_blacklist = recently_viewed_get_blackist ();
	if (isset ($recently_viewed_blacklist[$id])) 
		return true;
	return false;
}

function recently_viewed_refresh ($recently_viewed, $id)
{
	$time = time();
	if (!recently_viewed_in_blackist ($id))
	{
		$recently_viewed[$id] = $time;
	}
	arsort ($recently_viewed);
	$anz = count ($recently_viewed);
	if ($anz >= RECENTLY_VIEWED_MAX_ANZ)
	{
		// Alte Einträge entfernen
		$ret = array ();
		$count = 0;
		foreach ($recently_viewed as $key => $value)
		{
			$ret[$key] = $value;
			$count ++;
			if ($count >= RECENTLY_VIEWED_MAX_ANZ) 
				return $ret;
		}	
	}
	return $recently_viewed;
}

function recently_viewed_combine ($a1, $a2)
{
	$ret = array ();
	foreach ($a1 as $key => $val) { $ret[$key] = $val; }
	foreach ($a2 as $key => $val) { $ret[$key] = $val; }
	return $ret;
}

function recently_viewed_shortcode ($attr)
{
	$anz = recently_viewed_get_attr ($attr, 'anz');
	$type = recently_viewed_get_attr ($attr, 'type');
	if ($type == 'posts') {
		$recently_viewed = get_option(RECENTLY_VIEWED_DATA . "_" . RECENTLY_VIEWED_POST);
	} else if ($type == 'pages') {
		$recently_viewed = get_option(RECENTLY_VIEWED_DATA . "_" . RECENTLY_VIEWED_PAGE);
	} else {
		$recently_viewed1 = get_option(RECENTLY_VIEWED_DATA . "_" . RECENTLY_VIEWED_POST);
		$recently_viewed2 = get_option(RECENTLY_VIEWED_DATA . "_" . RECENTLY_VIEWED_PAGE);
		$recently_viewed = recently_viewed_combine ($recently_viewed1, $recently_viewed2);
	}
	arsort ($recently_viewed);

	// Ausgaben erzeugen
	$ret = "";
	$count = 0;
	foreach ($recently_viewed as $id => $timestamp)
	{
		if (!recently_viewed_in_blackist ($id))
		{
			if ($count < $anz)
			{
				$post = get_post ($id, ARRAY_A);
				$post['post_permalink'] = get_permalink ($id);
				$title = $post['post_title'];
				$permalink = $post['post_permalink'];
				$post['post_content'] = str_replace ("<", " <", $post['post_content']);
				$post['post_content'] = str_replace ("\r\n", "\n", $post['post_content']);
				$post['post_content'] = str_replace ("\n", " ", $post['post_content']);
				$excerpt = recently_viewed_trim_excerpt (strip_tags ($post['post_content']));
				$ret .= "<strong><a href='" . $permalink . "'>" . $title . "</a></strong><br>" . $excerpt . 
					"<br><a href='" . $permalink . "'>" . $permalink . "</a><br><br>";
			}
			$count ++;
		}
	}
	return $ret;
}

function recently_viewed_trim_excerpt ($str)
{
	$max = 350;
	$maxlspace = 50;
	if (strlen ($str) > $max)
	{
		$pos = $max;
		$str = substr ($str, 0,$pos);
		if (strrpos ($str, " ") > $max - $maxlspace)
		{
			$str = substr ($str, 0,strrpos ($str, " "));
		}
		$str .= "...";
	}
	return $str;
}

function recently_viewed_get_attr ($attr, $name)
{
	$recently_viewed_options = get_option(RECENTLY_VIEWED_OPTIONS);
        $anz  = recently_viewed_get_option ($recently_viewed_options, 'recently_anz',     '10');
        $type = recently_viewed_get_option ($recently_viewed_options, 'recently_type',    'posts');
	$recently_viewed_default = array ('anz' => $anz, 'type' => $type);
	if (isset ($attr[$name]))
	{
		return $attr[$name];
	} else {
		if (isset ($recently_viewed_default[$name]))
		{
			return $recently_viewed_default[$name];
		} else {
			return;
		}
	}
}

add_action('get_header','recently_viewed_header');
add_shortcode ('recently', 'recently_viewed_shortcode');
?>
