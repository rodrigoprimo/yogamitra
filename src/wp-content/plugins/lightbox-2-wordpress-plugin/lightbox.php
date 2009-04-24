<?php
/*
Plugin Name: Lightbox 2
Plugin URI: http://www.4mj.it/lightbox-js-v20-wordpress/
Description: Used to overlay images on the current page. Lightbox JS v2.04 by <cite><a href="http://www.huddletogether.com/projects/lightbox2/" title="Lightbox JS v2.0 ">Lokesh Dhakar</a>.</cite>
Version: 2.0.2
Author: Giuseppe Argento
Author URI: http://www.4mj.it

*/

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
 
// Guess the location
$lightboxpluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

function lightbox_init_locale(){
	load_plugin_textdomain('lightbox', $lightboxpluginpath);
}
add_filter('init', 'lightbox_init_locale');

$lightbox_files = Array(
	'lightbox/css/lightbox.css',
	'lightbox/images/bullet.gif',
	'lightbox/images/close.gif',
	'lightbox/images/closelabel.gif',
	'lightbox/images/loading.gif',
	'lightbox/images/nextlabel.gif',
	'lightbox/images/prevlabel.gif',
	'lightbox/js/builder.js',
	'lightbox/js/effects.js',
	'lightbox/js/lightbox.js',
	'lightbox/js/prototype.js',
	'lightbox/js/scriptaculous.js',
	'lightbox.php',
);

function lightbox_wp_head() {
	global $lightboxpluginpath, $post;
	$lightboxcolor1 = get_option("lightbox_color1");
	$lightboxlb_opacity = get_option("lightbox_lb_opacity");
	$lightboxlb_resize = get_option("lightbox_lb_resize");
	$lightboxoff = false;
	$lightboxoffmeta = get_post_meta($post->ID,'lightboxoff',true);
	if ($lightboxoffmeta == "false") {
		echo '<script type="text/javascript"> lb_path = "' . $lightboxpluginpath . 'lightbox/"; lb_opacity= "' . $lightboxlb_opacity . '"; lb_resize= "' . $lightboxlb_resize . '";</script>'."\n";
		echo '<link rel="stylesheet" type="text/css" media="screen" href="' . $lightboxpluginpath . 'lightbox/css/lightbox.css" />'."\n";
		echo '<script type="text/javascript" src="' . $lightboxpluginpath . 'lightbox/js/prototype.js" ></script>'."\n";
		echo '<script type="text/javascript" src="' . $lightboxpluginpath . 'lightbox/js/scriptaculous.js?load=effects,builder"></script>'."\n";
		echo '<script type="text/javascript" src="' . $lightboxpluginpath . 'lightbox/js/lightbox.js"></script>'."\n";
		echo '<style type="text/css">#overlay {background-color:' . $lightboxcolor1 . ';}</style>'."\n";
	}
}

function lightbox_auto ($content) {
	global $post;
	$pattern[0] = "/<a(.*?)href=('|\")([A-Za-z0-9\/_\.\~\:-]*?)(\.bmp|\.gif|\.jpg|\.jpeg|\.png)('|\")([^\>]*?)>/i";
	$pattern[1] = "/<a(.*?)href=('|\")([A-Za-z0-9\/_\.\~\:-]*?)(\.bmp|\.gif|\.jpg|\.jpeg|\.png)('|\")(.*?)(rel=('|\")lightbox(.*?)('|\"))([ \t\r\n\v\f]*?)((rel=('|\")lightbox(.*?)('|\"))?)([ \t\r\n\v\f]?)([^\>]*?)>/i";
	$replacement[0] = '<a$1href=$2$3$4$5$6 rel="lightbox['.$post->ID.']">';
	$replacement[1] = '<a$1href=$2$3$4$5$6$7>';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}

$lightbox_contitionals = get_option('lightbox_conditionals');
if (is_array($lightbox_contitionals)) {
	add_action('wp_head', 'lightbox_display_hook');
		function lightbox_display_hook($content='') {
		$conditionals = get_option('lightbox_conditionals');
		if ((is_home()     and $conditionals['is_home']) or
		    (is_single()   and $conditionals['is_single']) or
		    (is_page()     and $conditionals['is_page']) or
		    (is_category() and $conditionals['is_category']) or
			(is_tag() 	   and $conditionals['is_tag']) or
		    (is_date()     and $conditionals['is_date']) or
		    (is_search()   and $conditionals['is_search'])) {
		$content .=lightbox_wp_head();
		}
		if ($conditionals['is_automatic']){
		add_filter('the_content', 'lightbox_auto');
		add_filter('the_excerpt', 'lightbox_auto');
		}
		return $content;
	}
}


// Plugin config
register_activation_hook(__FILE__, 'lightbox_activation_hook');

function lightbox_activation_hook() {
	return lightbox_restore_config(False);
}

// restore defaults
function lightbox_restore_config($force=False) {

	// color1
	if ($force or !is_string(get_option('lightbox_color1')))
		update_option('lightbox_color1', "" . __("#000000", 'lightbox') . "");

	// lb_opacity
	if ($force or !is_string(get_option('lightbox_lb_opacity')))
		update_option('lightbox_lb_opacity', "" . __("0.8", 'lightbox') . "");

	// lb_opacity
	if ($force or !is_string(get_option('lightbox_lb_resize')))
		update_option('lightbox_lb_resize', "" . __("7", 'resize') . "");

	// only display on single posts and pages by default
	if ($force or !is_array(get_option('lightbox_conditionals')))
		update_option('lightbox_conditionals', array(
			'is_home' => True,
			'is_single' => True,
			'is_page' => True,
			'is_category' => True,
			'is_tag' => True,
			'is_date' => True,
			'is_search' => True,
			'is_automatic' => False,
		));

}

add_action('admin_menu', 'lightbox_admin_menu');
function lightbox_admin_menu() {
	add_submenu_page('options-general.php', 'lightbox', 'Lightbox', 8, 'lightbox', 'lightbox_submenu');
}

function lightbox_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

function lightbox_upload_errors() {
	global $lightbox_files;

	$cwd = getcwd(); // store current dir for restoration
	if (!@chdir('../wp-content/plugins'))
		return __("Couldn't find wp-content/lightbox-2-wordpress-plugin folder. Please make sure WordPress is installed correctly.", 'lightbox');
	if (!is_dir('lightbox-2-wordpress-plugin'))
		return __("Can't find lightbox-2-wordpress-plugin folder.", 'lightbox');
	chdir('lightbox-2-wordpress-plugin');

	foreach($lightbox_files as $file) {
		if (substr($file, -1) == '/') {
			if (!is_dir(substr($file, 0, strlen($file) - 1)))
				return __("Can't find folder:", 'lightbox') . " <kbd>$file</kbd>";
		} else if (!is_file($file))
		return __("Can't find file:", 'lightbox') . " <kbd>$file</kbd>";
	}


	$header_filename = '../../themes/' . get_option('template') . '/header.php';
	if (!file_exists($header_filename) or strpos(@file_get_contents($header_filename), 'wp_head()') === false)
		return __("Your theme isn't set up for lightbox to load its style. Please edit <kbd>header.php</kbd> and add a line reading <kbd>&lt?php wp_head(); ?&gt;</kbd> before <kbd>&lt;/head&gt;</kbd> to fix this.", 'lightbox');

	chdir($cwd); // restore cwd

	return false;
}

function lightbox_meta() {
	global $post;
	$lightboxoff = false;
	$lightboxoffmeta = get_post_meta($post->ID,'lightboxoff',true);
	if ($lightboxoffmeta == "true") {
		$lightboxoff = true;
	}
	?>
	<input type="checkbox" name="lightboxoff" <?php if ($lightboxoff) { echo 'checked="checked"'; } ?>/> Disable Lightbox 
	<?php
}

function lightbox_option() {
	global $post;
	$lightboxoff = false;
	$lightboxoffmeta = get_post_meta($post->ID,'lightboxoff',true);
	if ($lightboxoffmeta == "true") {
		$lightboxoff = true;
	}
	if ( current_user_can('edit_posts') ) { ?>
	<fieldset id="lightboxoption" class="dbx-box">
	<h3 class="dbx-handle">lightbox</h3>
	<div class="dbx-content">
		<input type="checkbox" name="lightboxon" <?php if ($lightboxoff) { echo 'checked="checked"'; } ?>/> lightbox disabled?
	</div>
	</fieldset>
	<?php 
	}
}

function lightbox_meta_box() {
	// Check whether the 2.5 function add_meta_box exists, and if it doesn't use 2.3 functions.
	if ( function_exists('add_meta_box') ) {
		add_meta_box('lightbox','Lightbox','lightbox_meta','post');
		add_meta_box('lightbox','Lightbox','lightbox_meta','page');
	} else {
		add_action('dbx_post_sidebar', 'lightbox_option');
		add_action('dbx_page_sidebar', 'lightbox_option');
	}
}
add_action('admin_menu', 'lightbox_meta_box');

function lightbox_insert_post($pID) {
	if (isset($_POST['lightboxoff'])) {
		add_post_meta($pID,'lightboxoff',"true", true) or update_post_meta($pID, 'lightboxoff', "true");
	} else {
		add_post_meta($pID,'lightboxoff',"false", true) or update_post_meta($pID, 'lightboxoff', "false");
	}
}
add_action('wp_insert_post', 'lightbox_insert_post');

// The admin page
function lightbox_submenu() {
	global $lightbox_known_sites, $lightbox_date, $lightbox_files, $lightboxpluginpath;

	// update options in db if requested
	if ($_REQUEST['restore']) {
		check_admin_referer('lightbox-config');
		lightbox_restore_config(True);
		lightbox_message(__("Restored all settings to defaults.", 'lightbox'));
	} else if ($_REQUEST['save']) {
		check_admin_referer('lightbox-config');

		if ($_POST['usetargetblank']) {
			update_option('lightbox_usetargetblank',true);
		} else {
			update_option('lightbox_usetargetblank',false);
		}
		
		// update conditional displays
		$conditionals = Array();
		if (!$_POST['conditionals'])
			$_POST['conditionals'] = Array();
		
		$curconditionals = get_option('lightbox_conditionals');
		foreach($curconditionals as $condition=>$toggled)
			$conditionals[$condition] = array_key_exists($condition, $_POST['conditionals']);
			
		update_option('lightbox_conditionals', $conditionals);

		// update color1
		if (!$_REQUEST['color1'])
			$_REQUEST['color1'] = "";
		update_option('lightbox_color1', $_REQUEST['color1']);

		// update lb_opacity
		if (!$_REQUEST['lb_opacity'])
			$_REQUEST['lb_opacity'] = "";
		update_option('lightbox_lb_opacity', $_REQUEST['lb_opacity']);

		// update lb_resize
		if (!$_REQUEST['lb_resize'])
			$_REQUEST['lb_resize'] = "";
		update_option('lightbox_lb_resize', $_REQUEST['lb_resize']);
		
		lightbox_message(__("Saved changes.", 'lightbox'));
	}

	if ($str = lightbox_upload_errors())
		lightbox_message("$str</p><p>" . __("In your plugins/lightbox-2-wordpress-plugin folder, you must have these files:", 'lightbox') . ' <pre>' . implode("\n", $lightbox_files) ); 

	// load options from db to display
	$color1 		= stripslashes(get_option('lightbox_color1'));
	$lb_opacity 	= stripslashes(get_option('lightbox_lb_opacity'));
	$lb_resize 	= stripslashes(get_option('lightbox_lb_resize'));
	$conditionals 	= get_option('lightbox_conditionals');
	$updated 		= get_option('lightbox_updated');
	$usetargetblank = get_option('lightbox_usetargetblank');
	// display options
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('lightbox-config');
?>

<div class="wrap">
	<h2><?php _e("Lightbox options", 'lightbox'); ?></h2>
	<table class="form-table">
	<tr>
		<th scope="row" valign="top">
			Background-color:
		</th>
		<td>
			<?php _e("Choose the lightbox hexadecimal background-color.", 'lightbox'); ?><br/>
			<div class="controlset"><input id="color1" type="text" name="color1" value="<?php echo $color1; ?>" /></div>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			Overlay opacity:
		</th>
		<td>
			<?php _e("Choose the lightbox overlay opacity, from 0 to 1 (default 0.8).", 'lightbox'); ?><br/>
			<div class="controlset"><input id="lb_opacity" type="text" name="lb_opacity" value="<?php echo $lb_opacity; ?>" /></div>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			Resize speed:
		</th>
		<td>
			<?php _e("Choose the lightbox resize speed, from 0 to 10 (default 7).", 'lightbox'); ?><br/>
			<div class="controlset"><input id="lb_resize" type="text" name="lb_resize" value="<?php echo $lb_resize; ?>" /></div>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("Sections:", "lightbox"); ?>
		</th>
		<td>
			<?php _e("Choose which sections you want to enable lightbox on your site:", 'lightbox'); ?>
			<br/>
			<input type="checkbox" name="conditionals[is_home]"<?php echo ($conditionals['is_home']) ? ' checked="checked"' : ''; ?> /> <?php _e("Homepage", 'lightbox'); ?><br/>
			<input type="checkbox" name="conditionals[is_single]"<?php echo ($conditionals['is_single']) ? ' checked="checked"' : ''; ?> /> <?php _e("Individual blog posts", 'lightbox'); ?><br/>
			<input type="checkbox" name="conditionals[is_page]"<?php echo ($conditionals['is_page']) ? ' checked="checked"' : ''; ?> /> <?php _e('Individual Pages', 'lightbox'); ?><br/>
			<input type="checkbox" name="conditionals[is_category]"<?php echo ($conditionals['is_category']) ? ' checked="checked"' : ''; ?> /> <?php _e("Category archives", 'lightbox'); ?><br/>
			<input type="checkbox" name="conditionals[is_tag]"<?php echo ($conditionals['is_tag']) ? ' checked="checked"' : ''; ?> /> <?php _e("Tag listings", 'lightbox'); ?><br/>
			<input type="checkbox" name="conditionals[is_date]"<?php echo ($conditionals['is_date']) ? ' checked="checked"' : ''; ?> /> <?php _e("Date-based archives", 'lightbox'); ?><br/>
			<input type="checkbox" name="conditionals[is_search]"<?php echo ($conditionals['is_search']) ? ' checked="checked"' : ''; ?> /> <?php _e("Search results", 'lightbox'); ?><br/>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("Auto-lightbox:", "lightbox"); ?>
		</th>
		<td>
			<?php _e("Automatically add rel='lightbox[post-ID]' to images in posts. All images in a post are grouped into a lightbox set.", 'lightbox'); ?>
			<br/>
			<input type="checkbox" name="conditionals[is_automatic]"<?php echo ($conditionals['is_automatic']) ? ' checked="checked"' : ''; ?> /> <?php _e("Enable Automatic", 'lightbox'); ?><br/>
			<?php _e("You can disable the lightbox effect from the Wordpress editor.", 'lightbox'); ?>
		</td>
	</tr>
		<td>&nbsp;</td>
		<td>
			<span class="submit"><input name="save" value="<?php _e("Save", 'lightbox'); ?>" type="submit" /></span>
			<span class="submit"><input name="restore" value="<?php _e("Restore Defaults", 'lightbox'); ?>" type="submit"/></span>
<br/><strong><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=peppolone%40hotmail%2ecom&amp;item_name=4MJ%20%2d%20Internet%20News&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8">If you appreciated this plugin or my support, please submit a donation, thank you!</a></strong>

		</td>
	</tr>
</table>
</div>

</form>

<?php
}

?>