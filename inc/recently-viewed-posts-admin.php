<?php

define (RECENTLY_VIEWED_OPTIONS, "recently_viewed_options");

function recently_viewed_get_option ($array, $key, $default)
{
	if (isset ($array[$key]))
	{
		return $array[$key];
	} else {
		return $default;
	}
}

function recently_viewed_config() 
{
	$msg = "";
	$saved = false;
	$recently_viewed_options = get_option(RECENTLY_VIEWED_OPTIONS);
	$anz     = recently_viewed_get_option ($recently_viewed_options, 'recently_anz',     '10');
	$type    = recently_viewed_get_option ($recently_viewed_options, 'recently_type',    'posts');
	$exclude = recently_viewed_get_option ($recently_viewed_options, 'recently_exclude', '');
	// TODO
	if ( isset($_POST['submit']) ) {
		$saved = true;
		$anz     = $_POST['recently_anz'];
		if (!is_numeric ($anz))
		{
			$msg .= "Default anz. must be numeric<br>";
			$saved = false;
		}
		$type    = $_POST['recently_type'];
		$exclude = $_POST['recently_exclude'];
		$recently_viewed_options['recently_anz'] = $anz;
		$recently_viewed_options['recently_type'] = $type;
		$recently_viewed_options['recently_exclude'] = $exclude;
		if ($saved)
			update_option (RECENTLY_VIEWED_OPTIONS, $recently_viewed_options);
	}
	recently_viewed_show_form ($saved, $msg, $anz, $type, $exclude);
}

function recently_viewed_show_form ($saved, $msg, $anz, $type, $exclude)
{
	if ($saved) 
	{
		echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';
	}

	if (strlen ($msg) > 0) 
	{
		echo '<div id="message" class="error fade"><p><strong>' . $msg . '</strong></p></div>';
	}

?>

<div class="wrap"><?php screen_icon(); ?>
<h2><?php _e('Recently Views Configuration', 'wp-recently-viewed'); ?></h2>
<div class="metabox-holder" id="poststuff">
<div class="postbox">
    <h3 class="hndle"><span><?php _e("Options", "wp-recently-viewed"); ?></span></h3>
    <div class="inside" style="display: block;">
	<form method="post">
        <table class="form-table">
            <tr>
                <th><?php _e("Default type", "wp-recently-viewed") ?></th>
                <td>
                    <select name="recently_type">
                        <option value="any"   <?php if ($type == 'any')   echo "selected='selected'" ?>>Any</option>
                        <option value="posts" <?php if ($type == 'posts') echo "selected='selected'" ?>>Posts</option>
                        <option value="pages" <?php if ($type == 'pages') echo "selected='selected'" ?>>Pages</option>
                    </select>
			<br>Override with shortcode <b>[recently type="posts"]</b>
                </td>
            </tr>

            <tr>
                <th><?php _e("Default anz.", "wp-recently-viewed") ?></th>
                <td>
                    <input type="text" name="recently_anz" value="<?php echo $anz; ?>" /><br>
			Override with shortcode <b>[recently anz="5"]</b>
                </td>
            </tr>

            <tr>
                <th><?php _e("Exclude from 'Recently viewed list'", "wp-recently-viewed") ?></th>
                <td>
                    <textarea name="recently_exclude" cols="10" rows="5" style="font-size: 12px;" 
			class="code"><?php echo $exclude; ?></textarea><br>
		    <b>One Page/Post Id per line.</b>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" class="button" value="<?php _e('Update options &raquo;'); ?>" />
                </td>
            </tr>
       </table>
</form></div></div></div>

<?php screen_icon(); ?>
<h2><?php _e('Recently Views Usage', 'wp-recently-viewed'); ?></h2>
<div class="metabox-holder" id="poststuff">
<div class="postbox">
    <h3 class="hndle"><span><?php _e("Usage Info", "wp-recently-viewed"); ?></span></h3>
    <div class="inside" style="display: block;">

The <b>Recently Viewd Posts</b> feature allows you to add one or more lists of recently viewed lists to your posts and pages.
<pre><span style="font-family: Helvetica;"><tt>[recently]</tt></span></pre>
Output Options

There are some options that may be specified using this syntax:
<pre>[recently option1="value1" option2="value2"]</pre>
Options

The following basic options are supported:
<ul>
	<li>anz - you can define the anz of pages/posts the list will include. (Max. 100)</li>
	<li>type - you can specify the post types that will be shown. Possible options are posts, pages, any.</li>
</ul>
<div><b>Sample</b></div>
<pre><span style="font-family: Helvetica;">[</span>recently  anz="10" type="posts"]</pre>

</div></div>

</div>
<?php
}


function recently_viewed_plugin_actions ($links, $file) {
	$settings_link = '<a href="options-general.php?page=wp-recently-viewed">' . __('Settings') . '</a>';
	array_unshift($links, $settings_link); // before other links
	return $links;
}

function recently_viewed_config_page() {
    if ( function_exists('add_submenu_page') )
        add_options_page(__('Recently Viewed'), __('Recently Viewed'), 'manage_options', 'wp-recently-viewed', 'recently_viewed_config');

    add_filter('plugin_action_links_' . RECENTLY_VIEWED_BASENAME , 'recently_viewed_plugin_actions', 10, 2 );
}

add_action('admin_menu', 'recently_viewed_config_page');

