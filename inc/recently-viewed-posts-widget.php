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

function zg_recently_viewed_widget() { // Output
	echo '<ul class="viewed_posts">';
	if (isset($_COOKIE["WP-LastViewedPosts"])) {
		//echo "Cookie was set.<br/>";  // For bugfixing - uncomment to see if cookie was set
		//echo $_COOKIE["WP-LastViewedPosts"]; // For bugfixing (cookie content)
		$zg_post_IDs = unserialize(preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", stripslashes($_COOKIE["WP-LastViewedPosts"]))); // Read serialized array from cooke and unserialize it
		foreach ($zg_post_IDs as $value) { // Do output as long there are posts
			global $wpdb;
			$zg_get_title = $wpdb->get_results("SELECT post_title FROM $wpdb->posts WHERE ID = '$value+0' LIMIT 1");
			foreach($zg_get_title as $zg_title_out) {
				echo "<li><a href=\"". get_permalink($value+0) . "\" title=\"". $zg_title_out->post_title . "\">". $zg_title_out->post_title . "</a></li>\n"; // Output link and title
			}
		}
	} else {
		//echo "No cookie found.";  // For bugfixing - uncomment to see if cookie was not set
	}
	echo '</ul>';
}

function zg_lwp_widget($args) { // Widget output
	extract($args);
	$options = get_option('zg_lwp_widget');
	$title = htmlspecialchars(stripcslashes($options['title']), ENT_QUOTES);
	$title = empty($options['title']) ? 'Last viewed posts' : $options['title'];
	if (isset($_COOKIE["WP-LastViewedPosts"])) {
		echo $before_widget . $before_title . $title . $after_title;
		zg_recently_viewed_widget();
		echo $after_widget;
	}
}

function zg_lwp_widget_control() { // Widget control
	$options = $newoptions = get_option('zg_lwp_widget');
	if ( $_POST['lwp-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['lwp-title']));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('zg_lwp_widget', $options);
	}
	$title = attribute_escape( $options['title'] );
	?>
	<p><label for="lwp-title">
	<?php _e('Title:') ?> <input type="text" style="width:250px" id="lwp-title" name="lwp-title" value="<?php echo $title ?>" /></label>
	</p>
	<input type="hidden" name="lwp-submit" id="lwp-submit" value="1" />
	<?php
}

function zg_lwp_init() { // Widget init
  	if ( !function_exists('register_sidebar_widget') )
  		return;
	register_sidebar_widget('Last Viewed Posts','zg_lwp_widget');
  	register_widget_control('Last Viewed Posts','zg_lwp_widget_control', 250, 100);
}

add_action('widgets_init', 'zg_lwp_init');
?>
