<?php
ob_start();
session_start();
/*
Plugin Name: Twshot for WordPress
Plugin URI: http://www.gurkanoluc.com/twshot-for-wordpress
Description:  This plugin automatically updates your twitter status with title of post that you published and link to post that you published by using <a href="http://www.twshot.com" target="_blank">twshot</a> service.
Version: 0.2
Author: Gürkan OLUÇ
Author URI: http://www.gurkanoluc.com
*/

/*  Copyright 2008   Gürkan OLUÇ  (email : me@gurkanoluc.com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpdb;
define('TWSHOT_PLUGIN_DIR',dirname(__FILE__));
define('TWSHOT_URL',get_option('siteurl').'/wp-content/plugins/twshot-for-wordpress/');

if( WPLANG == '' ) {
	include_once TWSHOT_PLUGIN_DIR.'/en_EN.php';
} else { 
	if( file_exists(TWSHOT_PLUGIN_DIR.'/'.WPLANG.'.php') ) {
		include_once TWSHOT_PLUGIN_DIR.'/'.WPLANG.'.php';
	} else {
		include_once TWSHOT_PLUGIN_DIR.'/en_EN.php';
	}
}

if( !class_exists('Snoopy') ) {

    include_once dirname(dirname(dirname(dirname(__FILE__)))).'/wp-includes/class-snoopy.php';

}

$twshot_options = get_option('twshot_options');
$twshot_options = unserialize($twshot_options);
$twshot_options["twitter_password"] = stripslashes($twshot_options["twitter_password"]);

 
 


// --------------------------------------------------

/**
 * Install function
 * 
 * 
 * This function creates option if it doesn't exists
 * 
 */

register_activation_hook(__FILE__,'twshot_for_wp_install');

function twshot_for_wp_install() {

	add_option('twshot_options',serialize(array('twitter_username' => '','twitter_password' => '')));
		
}

/**
 * Add to Twshot Functions
 *
 * This function adds link to twshot
 *
 * @param	array 	$twitter	Twitter username and password
 * @param	string	$title		Title of post which will be added as a link to twshot
 * @param	string	$uri		twshot url of post which will be added as a link to twshot
 *
 */

function add_to_twshot($twitter,$title,$uri) {

	$snoopy = new Snoopy();
	$result = $snoopy->fetch('http://twshot.com/api/?title='. urlencode($title) .'&redirectUrl='. urlencode($uri) .'&username='. urlencode($twitter['twitter_username']) .'&password='. urlencode($twitter['twitter_password']) .'&sourceid=6&key=word8704press');
	
	return $result->results;
}


/**
 * Adds CSS files quickly
 * 
 * This function takes css file name
 * and includes it to page
 * 
 * @param string
 * 
 * @return string
 * 
 */
function twshot_for_wp_css_link($css_file) {
	return "<link rel=\"stylesheet\" href=\"".TWSHOT_URL. 'css/'. $css_file.".css\" media=\"screen\"/>\n";
}

// --------------------------------------------------

/**
 * Adds JS files quickly
 * 
 * This function takes js file name
 * and includes it to page
 * 
 * @param string
 * 
 * @return string
 * 
 */
function twshot_for_wp_js_link($js_file) {
	return "<script src=\"". TWSHOT_URL.'js/'.$js_file .".js\" type=\"text/javascript\"></script>\n";
}

// --------------------------------------------------

/**
 * Generate link for plugin page
 * 
 * This functiong gets query string then adds it to 
 * options-general.php?page=twshot-for-wordpress/twshot-for-wordpress.php
 * 
 * @param string
 * 
 * return string
 * 
 */
function twshot_for_wp_make_link($query_string = '') {
		if( empty($query_string)) {
			return 'options-general.php?page=twshot-for-wordpress/twshot-for-wordpress.php';
		} else {
			return 'options-general.php?page=twshot-for-wordpress/twshot-for-wordpress.php&'.$query_string;
		}
		
}

// --------------------------------------------------

/**
 * Look for flash message
 * 
 * This function looks to ff_flash_message session
 * If it's not empty function returns true, 
 * else function returns false
 * 
 * @return bool
 **/
function twshot_for_wp_is_there_flash_notice() {
	return ( !empty($_SESSION['twshot_flash_message']) ) ? true : false;
}

// --------------------------------------------------

/**
 * Set or Show Flash Notice
 * 
 * This function shows flash notice if $message variable is empty
 * If $message variable is not empty sets ff_flash_message session
 * value with given message
 * 
 * @param string
 * 
 * @return string|nothing
 * 
 **/
function twshot_for_wp_flash_notice($message = '') {
	if( empty($message) ) {
		$msg = $_SESSION['twshot_flash_message'];
		$_SESSION['twshot_flash_message'] = '';
		return $msg;
	} else {
		$_SESSION['twshot_flash_message'] = $message;
	}
}

// --------------------------------------------------

/**
 * twshot_for_wp_redirect Function
 * 
 * This function twshot_for_wp_redirects page to given querystring
 * 
 * @param string
 * 
 */
function twshot_for_wp_redirect($query_string='') {
	header('Location: '.twshot_for_wp_make_link($query_string).'');
}

// --------------------------------------------------
/**
 * This function cuts string after charecter count
 */
function twshot_for_wp_character_limiter($str, $n = 500, $end_char = '&#8230;') {
	if (strlen($str) < $n)
	{
		return $str;
	}
	
	$str = preg_replace("/\s+/", ' ', preg_replace("/(\r\n|\r|\n)/", " ", $str));

	if (strlen($str) <= $n)
	{
		return $str;
	}
								
	$out = "";
	foreach (explode(' ', trim($str)) as $val)
	{
		$out .= $val.' ';			
		if (strlen($out) >= $n)
		{
			return trim($out).$end_char;
		}		
	}
}

// --------------------------------------------------

add_action('admin_menu', 'twshot_for_wp_ekle');
/**
 * Add link to options panel
 * 
 * This function adds link to options-general.php page in admin panel
 * 
 * @return nothing
 * 
 */
function twshot_for_wp_ekle() {
	add_submenu_page('options-general.php', 'Twshot for Wordpress Options', 'Twshot', 10, __FILE__, 'twshot_for_wp_menu');
}

// --------------------------------------------------

// add_action('admin_head','twshot_for_wp_head');
/**
 * Add extra head content to WP Admin head
 * 
 * @return nothing
 * 
 */
function twshot_for_wp_head() {
	// there is nothing to add :)
}


add_action('publish_post','twshot_for_wp_save');
/**
 * Save function
 * 
 * This function updates user's twitter status with post title + twshot url
 * 
 * @return bool
 * 
 */
function twshot_for_wp_save($post_ID) {

	global $wpdb, $twshot_options;
	
	$post_title = $_POST['post_title'];
	$permalink = get_permalink($post_ID);
	
	// Eğer bir yazı düzenleniyor ise yeni kayıt yapma
	// False döndür
	if( !isset($_POST['publish'])) {
		return false;
	} else { 
		if( empty($twshot_options['twitter_username']) OR empty($twshot_options['twitter_password']) OR $_POST['post_type'] != 'post' ) {
			return false;
		} else {
			$twshot_post_title = $_POST['twshot_for_wp_title'];
			
			$post_title = (empty($twshot_post_title)) ? twshot_for_wp_character_limiter($post_title,105,'... ') : $twshot_post_title;
			$post_title = stripslashes($post_title);
				
			if( !ini_get('allow_url_fopen') ) {
				return false;
			} else {
				$result = add_to_twshot($twshot_options,$post_title,$permalink);
				// echo $twshot_options['twitter_password'];
				// echo $result;
				// die;
				return ( $result ) ? true : false;				
			}
		}
	}
	
}

// --------------------------------------------------

/**
 * Main Function
 * 
 * @return string
 * 
 */
function twshot_for_wp_menu() {
	
	global $wpdb, $twshot_options;
	
	$action = $_GET['action'];
	
	echo "<div id=\"twshot_for_wp\">";
	
	if( twshot_for_wp_is_there_flash_notice() ) {  
		echo '<div class="updated fade" id="message"><p><strong>'. twshot_for_wp_flash_notice() .'</strong></p></div>';
	} 
	
	if( !ini_get('allow_url_fopen') ) {
		echo '<div class="error" id="message"><p><strong>'. TWSHOT_URL_FOPEN_REQUIRED .'</strong></p></div>';
	}
	
	
	if( empty($action) ) {
		echo "<div class=\"wrap\">\n";
		echo '<div style="width:800px; margin: 20px 0px 20px 0px; text-align:center;">';
		echo '<img src="'. TWSHOT_URL .'twshot_for_wp.png" alt="logo">';
		echo '</div>';
		echo '<h2>'. TWSHOT_OPTIONS .'</h2>';
		echo '<div id="options">';
		echo '<form method="POST" name="ff_form" action="'. twshot_for_wp_make_link('action=save_options'). '">';
		echo '<table class="form-table">';
		echo '<tr valign="top">';
		echo '<th scope="row">'. TWSHOT_TWITTER_USERNAME .'</th>';
		echo '<td><input name="twitter_username" type="text" id="twitter_username" size="40" value="'. $twshot_options['twitter_username'].'" /></td>';
		echo '</tr>';
		echo '<tr valign="top">';
		echo '<th scope="row">'. TWSHOT_TWITTER_PASSWORD .'</th>';
		echo "<td><input name='twitter_password' type='password' id='twitter_password' size='40' value='". stripslashes($twshot_options['twitter_password']) ."' /></td>";
		echo '</tr>';		
		echo '</table>';
		echo '<p class="submit"><input type="submit" name="btnSave" value="'. TWSHOT_SAVE .'" /></p>';	
		echo '</form>';
		echo '</div>';
		echo "</div>";
		
	}
	else if ( $action == 'save_options' ) {
		twshot_for_wp_save_options();
	} 

	echo "</div>";
}

/**
 * Save FF Options
 * 
 * This function saves author's twitter nickname and password
 * 
 * @return nothing
 * 
 */
function twshot_for_wp_save_options() {

	$options = array();
	
	$options['twitter_username'] = ( !empty($_POST['twitter_username']) ) ? $_POST['twitter_username'] : '';
	$options['twitter_password'] = ( !empty($_POST['twitter_password']) ) ? $_POST['twitter_password'] : '';
	
	$options = serialize($options);
	update_option('twshot_options',$options);
	
	twshot_for_wp_flash_notice(TWSHOT_DATA_SAVED_MSG);
	
	twshot_for_wp_redirect();	
}

/**
 * Add Meta Box to post and page create pages
 * To set twshot title
 */
 if(!function_exists('add_meta_box')) {
   include_once ABSPATH.'wp-admin/includes/template.php';
 }
 add_meta_box('twshot_for_wp_title','Twshot Title','twshot_for_wp_add_meta_box','post','advanced','high');
 add_meta_box('twshot_for_wp_title','Twshot Title','twshot_for_wp_add_meta_box','page','advanced','high'); 

/**
 * Adds meta box to post page
 */
function twshot_for_wp_add_meta_box() {
  echo "<p>\n";
  echo "<label for=\"twshot_for_wp_title\">Twshot Title</label>\n";
  echo "<br/>\n";
  echo "<input type=\"text\" name=\"twshot_for_wp_title\" id=\"twshot_for_wp_title\"  style=\"width:500px;\" maxlength=\"120\" />";
  echo "</p>\n";
}


?>
