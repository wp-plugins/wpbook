<?php
/*
Plugin Name: WPBook
Plugin URI: http://www.openparenthesis.org/code/wp
Date: 2010, July 7th
Description: Plugin to embed Wordpress Blog into Facebook Canvas using the Facebook Platform. 
Author: John Eckman
Author URI: http://johneckman.com
Version: 2.0.1
*/
  

/*
Note: This plugin draws inspiration (and sometimes code) from: 
   Alex King's WP-Mobile plugin (http://alexking.org/projects/wordpress ) 
   and BraveNewCode's WPTouch (http://www.bravenewcode.com/wptouch/
   as well as Devbit's List Pages Plus (http://skullbit.com/wordpress-plugin/list-pages-plus/) 
   and Steve Atty's Wordbooker (http://wordpress.org/extend/plugins/wordbooker/ )
*/

/*  
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

// Pre-2.6 compatibility - which may be unnecessary if we require 2.7
  
if ( ! defined( 'WP_CONTENT_URL' ) )
  define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
  define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
  define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
  
// this function checks for admin pages
if (!function_exists('is_admin_page')) {
  function is_admin_page() {
    if (function_exists('is_admin')) {
      return is_admin();
    }
		if (function_exists('check_admin_referer')) {
			return true;
		}
		else {
			return false;
		}
  }
}

$_SERVER['REQUEST_URI'] = ( isset($_SERVER['REQUEST_URI']) ? 
  $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] 
  . (( isset($_SERVER['QUERY_STRING']) ? '?' 
  . $_SERVER['QUERY_STRING'] : '')));


// activation, install, uninstall need work  
function wpbook_activate() {
  wpbook_activation_check();
  $dummy=wp_clear_scheduled_hook('wpbook_cron_job');
	$dummy=wp_schedule_event(time(), 'hourly', 'wpbook_cron_job');
}

function wpbook_deactivate() {
  wp_clear_scheduled_hook('wpbook_cron_job');
}

function is_authorized() {
  global $user_level;
	if (function_exists("current_user_can")) {
		return current_user_can('activate_plugins');
	} else {
		return $user_level > 5;
	}
}

function wpbook_getAdminOptions() {
	$wpbookOptions = get_option('wpbookAdminOptions');
	if (!empty($wpbookOptions)) {
		foreach ($wpbookOptions as $key => $option)
			$wpbookAdminOptions[$key] = $option;
		}
	return $wpbookAdminOptions;
}
  
function setAdminOptions($wpbook_installation, $fb_api_key, $fb_secret, 
                         $fb_app_url,$fb_admin_target,$fb_page_target,$invite_friends,$require_email,
                         $give_credit,$enable_share, $allow_comments,
                         $links_position,$enable_external_link,
                         $enable_profile_link,$timestamp_date_format,
                         $timestamp_time_format, $show_date_title,
                         $show_advanced_options,$custom_header,$custom_footer,
                         $show_custom_header_footer,$use_gravatar,
                         $gravatar_rating,$gravatar_default,$show_pages,
                         $exclude_page_list,$exclude_true,$show_pages_menu,
                         $show_pages_list, $show_recent_post_list, 
                         $recent_post_amount,$stream_publish,$stream_publish_pages,
                         $show_errors,$promote_external,$import_comments,
                         $approve_imported_comments,$num_days_import,
                         $imported_comments_email,$infinite_session_key,
                         $attribution_line,$wpbook_enable_debug) {
  $wpbookAdminOptions = array('wpbook_installation' => $wpbook_installation,
                              'fb_api_key' => $fb_api_key,
                              'fb_secret'  => $fb_secret,
                              'fb_app_url' => $fb_app_url,
                              'fb_admin_target' => $fb_admin_target,
                              'fb_page_target' => $fb_page_target,
                              'invite_friends' => $invite_friends,
                              'require_email' => $require_email,
                              'give_credit' => $give_credit,
                              'enable_share' => $enable_share,
                              'allow_comments' => $allow_comments,
                              'links_position' => $links_position,
                              'enable_external_link' => $enable_external_link,
                              'enable_profile_link' => $enable_profile_link,
                              'timestamp_date_format' => $timestamp_date_format,
                              'timestamp_time_format' => $timestamp_time_format,
                              'show_date_title' => $show_date_title,
                              'show_advanced_options' => $show_advanced_options,
                              'custom_header' => $custom_header,
                              'custom_footer' => $custom_footer,
                              'show_custom_header_footer'=> $show_custom_header_footer,
                              'use_gravatar'=> $use_gravatar,
                              'gravatar_rating'=> $gravatar_rating,
                              'gravatar_default'=> $gravatar_default,
                              'show_pages'=> $show_pages,
                              'exclude_pages'=>$exclude_page_list,
                              'exclude_true'=>$exclude_true,
                              'show_pages_menu'=>$show_pages_menu,
                              'show_pages_list'=>$show_pages_list,
                              'show_recent_post_list'=>$show_recent_post_list,
                              'recent_post_amount'=>$recent_post_amount,
                              'stream_publish' => $stream_publish,
                              'stream_publish_pages' => $stream_publish_pages,
                              'show_errors' => $show_errors,
                              'promote_external' => $promote_external,
                              'import_comments' => $import_comments,
                              'approve_imported_comments' => $approve_imported_comments,
                              'num_days_import' => $num_days_import,
                              'imported_comments_email' => $imported_comments_email,
                              'infinite_session_key' => $infinite_session_key,
                              'attribution_line' => $attribution_line,
                              'wpbook_enable_debug' => $wpbook_enable_debug
                              );
  update_option('wpbookAdminOptions', $wpbookAdminOptions);
}
  
add_action('admin_menu', 'wpbook_options_page');						   
function wpbook_options_page() {
	if (function_exists('add_options_page')) {
		$wpbook_plugin_page = add_options_page('WPBook', 'WPBook', 8, 
		  basename(__FILE__), 'wpbook_subpanel');
	   add_action( 'admin_head-'. $wpbook_plugin_page, 'wpbook_admin_head' );
	}
} 

//function to add css and java to the header of the admin page 
function wpbook_admin_head() {
  $wpbook_admin_styles_path=  WP_PLUGIN_URL . "/wpbook/admin_includes/wpbook_admin_styles.css";
  $wpbook_admin_tooltip_path = WP_PLUGIN_URL . "/wpbook/admin_includes/jquery.simpletip-2.0.0-beta4.js";
  $wpbook_admin_javascript_path = WP_PLUGIN_URL . "/wpbook/admin_includes/wpbook_admin_javascript.js";
  $wpbook_admin_head = "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"".$wpbook_admin_styles_path."\" media=\"screen\" />\n
\n<script src=\"".$wpbook_admin_tooltip_path."\" type=\"text/javascript\"></script>
\n<script src=\"".$wpbook_admin_javascript_path."\" type=\"text/javascript\"></script>";
	echo $wpbook_admin_head; 
}
  
//function to list pages to exclude taken from List Pages Plus 
function wpbook_exclude_Page(){
  global $wpdb;
  $wpbookAdminOptions = wpbook_getAdminOptions();
  $pages = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type='page' ORDER BY post_parent, menu_order, post_title ASC" );
  $select = $wpbookAdminOptions['exclude_pages'];
  $select = explode(",", $select);
  $out = "<ul>";
  if(!is_array($select)) {
    $select = array($select);
  }
  foreach( $pages as $pg ) {
    $out .= "<li class='options'><input type='checkbox'  name='exclude_pages[]' value='".$pg->ID ."' id='$pg->ID'";
    if( in_array($pg->ID, $select)) {
      $out .= " checked";
    }
    $out .= "> ". $pg->post_title."</li>";
  } // end foreach
  $out .= "</ul>";
  echo $out;
}

function wpbook_subpanel() {
  if (is_authorized()) {
    $wpbookAdminOptions = wpbook_getAdminOptions();
    if (isset($_POST['fb_api_key']) && isset($_POST['fb_secret']) && isset($_POST['fb_app_url']) 
      && (!empty($_POST['fb_api_key']))  && (!empty($_POST['fb_secret'])) && (!empty($_POST['fb_app_url']))) { 
      $fb_api_key = $_POST['fb_api_key'];
      $fb_secret = $_POST['fb_secret'];
      $fb_app_url = $_POST['fb_app_url'];
      $fb_admin_target = $_POST['fb_admin_target'];
      $fb_page_target = $_POST['fb_page_target'];
      $invite_friends = $_POST['invite_friends'];
      $require_email = $_POST['require_email'];
      $give_credit = $_POST['give_credit'];
      $enable_share = $_POST['enable_share'];
      $allow_comments = $_POST['allow_comments'];
      $links_position = $_POST['links_position'];
      $enable_external_link = $_POST['enable_external_link'];
      $enable_profile_link = $_POST['enable_profile_link'];
	  
      // Handle custom date/time formats code modified from wp-admin/options.php
      if ( !empty($_POST['timestamp_date_format']) && isset($_POST['timestamp_date_format_custom']) && '\c\u\s\t\o\m' == stripslashes( $_POST['timestamp_date_format'] ) )
        $_POST['timestamp_date_format'] = $_POST['timestamp_date_format_custom'];
      if ( !empty($_POST['timestamp_time_format']) && isset($_POST['timestamp_time_format_custom']) && '\c\u\s\t\o\m' == stripslashes( $_POST['timestamp_time_format'] ) )
        $_POST['timestamp_time_format'] = $_POST['timestamp_time_format_custom'];
      //end custom date/time code
			
      $timestamp_date_format = $_POST['timestamp_date_format'];
      $timestamp_time_format = $_POST['timestamp_time_format'];
      $show_date_title = $_POST['show_date_title'];
      $show_advanced_options = $_POST['show_advanced_options'];
      $custom_header = $_POST['custom_header'];
      $custom_footer = $_POST['custom_footer'];
      $show_custom_header_footer = $_POST['show_custom_header_footer'];
      $use_gravatar = $_POST['use_gravatar'];
      $gravatar_rating = $_POST['gravatar_rating'];
      $show_pages = $_POST['show_pages'];
      $exclude_true = $_POST['exclude_true'];
      $show_pages_menu = $_POST['show_pages_menu'];
      $show_pages_list = $_POST['show_pages_list'];
      $show_recent_post_list = $_POST['show_recent_post_list'];
      $recent_post_amount = ereg_replace("[^0-9]", "",$_POST['recent_post_amount_input']); 
      $stream_publish = $_POST['stream_publish'];  
      $stream_publish_pages = $_POST['stream_publish_pages'];
      $show_errors = $_POST['show_errors'];  
      $promote_external = $_POST['promote_external'];
      $import_comments = $_POST['import_comments'];
      $approve_imported_comments = $_POST['approve_imported_comments'];
      $num_days_import = $_POST['num_days_import'];  
      $imported_comments_email = $_POST['imported_comments_email'];  
      $infinite_session_key = $_POST['infinite_session_key']; 
      $attribution_line = $_POST['attribution_line'];
      $wpbook_enable_debug = $_POST['wpbook_enable_debug'];
      // Handle custom gravatar_deault code modified from wp-admin/options.php
      if ( !empty($_POST['gravatar_default']) && isset($_POST['gravatar_rating_custom']) && '\c\u\s\t\o\m' == stripslashes( $_POST['gravatar_default'] ) )
        $_POST['gravatar_default'] = urlencode($_POST['gravatar_rating_custom']);
      //end custom gravatar_deafult code
      			
      $gravatar_default = $_POST['gravatar_default'];
      $exclude_pages = $_POST['exclude_pages'];
      //write a comma seperated list of pages to exclude
      $exclude_pages_count = count($exclude_pages);
      $i = 0;
      if (!empty($exclude_pages)) {
        foreach($exclude_pages as $page_id) {
          $i++;
          $exclude_page_list .= $page_id ;
          if($i<$exclude_pages_count){
            $exclude_page_list .= ',';
          }
        }
      }
      setAdminOptions(1, $fb_api_key, $fb_secret, $fb_app_url,$fb_admin_target,$fb_page_target,
                    $invite_friends,$require_email,$give_credit,$enable_share,
                    $allow_comments,$links_position,$enable_external_link,
                    $enable_profile_link,$timestamp_date_format,
                    $timestamp_time_format,$show_date_title,
                    $show_advanced_options,$custom_header,$custom_footer,
                    $show_custom_header_footer,$use_gravatar,$gravatar_rating,
                    $gravatar_default,$show_pages,$exclude_page_list,
                    $exclude_true,$show_pages_menu,$show_pages_list,
                    $show_recent_post_list, $recent_post_amount,$stream_publish,
                    $stream_publish_pages,$show_errors,$promote_external,
                    $import_comments,$approve_imported_comments,$num_days_import,
                    $imported_comments_email,$infinite_session_key,
                    $attribution_line,$wpbook_enable_debug);
      $flash = "Your settings have been saved. ";
    } elseif (($wpbookAdminOptions['fb_api_key'] != "") || ($wpbookAdminOptions['fb_secret'] != "") || ($wpbookAdminOptions['fb_app_url'] != "")
            || (!empty($_POST['fb_api_key']))  || (!empty($_POST['fb_secret'])) || (!empty($_POST['fb_app_url']))){
      $flash = "";
    } else {
      $flash = "Please complete all necessary fields";}
    } else {
      $flash = "You don't have enough access rights.";
    }   
  
    if (is_authorized()) {
      $wpbookAdminOptions = wpbook_getAdminOptions();
      //set the "smart" defaults on install this only works once the page has been refeshed
      if ($wpbookAdminOptions['wpbook_installation'] != 1) {  
        $gravatar_default = WP_PLUGIN_URL .'/wpbook/theme/default/gravatar_default.gif';
        setAdminOptions(1, null,null,null,null,null,true,true,true,true,true,top,null,null,"F j, Y","g:i a",
                        true,null,null,null,disabled,null,"g",$gravatar_default,null,null,null,null,true,true,10,
                        false,false,false,false,false,false,7,"facebook@openparenthesis.org",null,null,null);
      }
      if ($flash != '') echo '<div id="message"class="updated fade">'
        . '<p>' . $flash . '</p></div>'; 
      ?>
      <div class="wrap">
      <h2>WPBook Setup</h2>
      <p>This plugin allows you to embed your blog into the Facebook canvas, 
allows Facebook users to comment on or share your blog posts, and puts your 5 
most recent posts in users profiles (with their permission).</p>
      <p><a href="<?php echo WP_PLUGIN_URL .'/wpbook/instructions/index.html' ?>" 
      target="_blank">Detailed instructions</a></p>
      <?php
        echo '<form action="'. $_SERVER["REQUEST_URI"] .'" method="post">';
      ?>

      <?php /* required options */
      ?>
      <div id ="required_options"><h3> Required Options:</h3>
      <p>To use this app, you must register for an API key at <a href="http://www.facebook.com/developers/">Facebook</a>.
Follow the link and click "set up a new application."  After you've obtained the 
necessary info, fill in both your application's API and Secret keys as well as 
your application's url.</p>
      <p>Note: Your "Canvas Callback URL" setting in Facebook should be: 
      <?php
      echo '<code>' . get_bloginfo('url') . '</code></p>';
      echo '<p>Facebook API Key: <input type="text" name="fb_api_key" value="';
      echo htmlentities($wpbookAdminOptions['fb_api_key']) .'" size="35" /></p>';
      echo '<p>Facebook Secret: ';
      echo '<input type="text" name="fb_secret" value="';
      echo htmlentities($wpbookAdminOptions['fb_secret']) .'" size="35" /></p>';
      echo '<p>Facebook Canvas Page URL, ';
      echo '<strong>NOT</strong> INCLUDING "http://apps.facebook.com/" ';
      echo '<input type="text" name="fb_app_url" value="';
      echo htmlentities($wpbookAdminOptions['fb_app_url']) .'" size="20" /></p>';
      echo '</div>'; /* end of required options */

      // stream publishing
      echo '<div id="stream_publishing_options"><h3> Stream Publishing Options: </h3>';
      if(empty($wpbookAdminOptions['fb_app_url']) || empty($wpbookAdminOptions['fb_secret'])
        || empty($wpbookAdminOptions['fb_api_key'])) {  
        echo '<p>Once your Facebook application is established, return and enter data below.</p>';
      } else {
        echo '<p>These settings all impact how WPBook publishes to Facebook walls, and depend on appropriate permissions being set in Facebook.</p>';
        echo '<a href="http://apps.facebook.com/' . htmlentities($wpbookAdminOptions['fb_app_url']) .'/?is_permissions=true">Check '
          . 'permissions</a> for stream publishing, reading, and offline access.';
      }
      echo '<p class="options"><input type="checkbox" name="stream_publish" value="true" ';
      if( htmlentities($wpbookAdminOptions['stream_publish']) == "true") {
        echo("checked");
      }
      echo ' id="stream_publish" > Publish new posts to YOUR Facebook Wall ';
      echo 'Profile ID: <input type="text" name="fb_admin_target" value="';
      echo htmlentities($wpbookAdminOptions['fb_admin_target']) .'" size="15" />';
      echo '<img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="stream_publish" /></p>';  
      echo '<p class="options"><input type="checkbox" name="stream_publish_pages" value="true" ';
      if( htmlentities($wpbookAdminOptions['stream_publish_pages']) == "true") {
        echo("checked");
      }
      echo ' id="stream_publish_pages" > Publish new posts to the Wall of Fan Page ';
      echo 'PageID: <input type="text" name="fb_page_target" value="';
      echo htmlentities($wpbookAdminOptions['fb_page_target']) .'" size="15" />'; 
      echo '<img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="stream_publish_pages" /></p>';
  

      echo '<p>Infinite Session Key: ';
      echo '<input type="text" name="infinite_session_key" value ="';
      echo htmlentities($wpbookAdminOptions['infinite_session_key']) .'" size="45" /></p>';      
      echo '<p>(This key is used for posting to your personal wall, and retrieving comments from your personal wall. ';
      echo 'If you are not importing comments from a personal wall, it is not necessary. If you are importing comments ';
      echo ' from a personal wall, and no Infinite Session Key is set, visit the check permissions link above)</p>';

      echo '<p class="options"><input type="checkbox" name="promote_external" value="true" ';
      if( htmlentities($wpbookAdminOptions['promote_external']) == "true") {
        echo("checked");
      }
      echo ' id="promote_external" > Use external permalinks on Walls <img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="promote_external" /></p>';

      echo '<p class="options"><input type="checkbox" name="wpbook_enable_debug" value="true" ';
      if( htmlentities($wpbookAdminOptions['wpbook_enable_debug']) == "true") {
        echo("checked");
      }
      echo ' id="wpbook_enable_debug" > Enable WPBook to create a debug file <img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="wpbook_enable_debug" /> ';
      echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="show_errors" value="true" ';
      if( htmlentities($wpbookAdminOptions['show_errors']) == "true") {
        echo("checked");
      }
      echo ' id="show_errors" > Show errors posting to Facebook Stream <img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="show_errors" /></p>';

      echo '<p>Attribution line: <input type="text" name="attribution_line" size="40" value="'. $wpbookAdminOptions['attribution_line'] .'" />';
      echo '<img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="attribution_line"/><br />';
      echo 'Predefined Strings (you can use these in your attribution line): %author%, %blogname%.</p>';

      echo '<p class="options"><input type="checkbox" name="import_comments" value="1" ';
      if( htmlentities($wpbookAdminOptions['import_comments']) == "1") {
        echo("checked");
      }
      echo ' id="import_comments" > Import comments from Facebook Walls <img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="import_comments" /> ';
      echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="approve_imported_comments" value="1" ';
      if( htmlentities($wpbookAdminOptions['approve_imported_comments']) == "1") {
        echo("checked");
      }
      echo ' id="approve_imported_comments" > Automatically approve imported Wall comments <img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="approve_imported_comments" /></p>';

      echo '<p>For how many days should WPBook look for comments on Facebook Walls?: ';
      echo '&nbsp;<input type="text" name="num_days_import" value="';
      echo htmlentities($wpbookAdminOptions['num_days_import']) .'" size="2" /></p>';      

      echo '<p>What email address should WPBook associate with imported comments? ';
      echo '&nbsp;<input type="text" name="imported_comments_email" value="';
      echo htmlentities($wpbookAdminOptions['imported_comments_email']) .'" size="40" /></p>';      
      echo '</div>'; 
      /* end of stream_publish_options */

      /* customization options */
      echo '<div id="customization_options"><h3> Customize Facebook Application View: </h3>';
      echo '<p>These options will allow you to customize the behavior and display ';
      echo 'of blog posts in the <a href="http://apps.facebook.com/'. $wpbookAdminOptions['fb_app_url'] .'/';
      echo '">Facebook application view</a>.</p>';
      /* Now let's handle commenting - only show require_email if comments on */
      echo'<p><strong> Commenting Options:</strong></p>';
      echo '<p class="options"><input type="checkbox" name="allow_comments" value="true" ';
      if( htmlentities($wpbookAdminOptions['allow_comments']) == "true") {
        echo("checked");
      }
      echo ' id="allow_comments" > Allow comments inside Facebook <img src="'. WP_PLUGIN_URL . '/wpbook/admin_includes/images/help.png" class="allow_comments" /></p>';
      echo '<div id="comments_options">';
      echo '<p class="options"><input type="checkbox" name="require_email" value = "true"';
      if( htmlentities($wpbookAdminOptions['require_email']) == "true"){ 
        echo("checked");
      }
      echo '> Require Comment Authors E-mail Address <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="require_email" /> ';
      //gravatar options
echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="use_gravatar" value="true" ';
      if( htmlentities($wpbookAdminOptions['use_gravatar']) == "true") {
        echo("checked");
      }
      echo ' id="use_gravatar" > Show Gravatar Images Inside Facebook <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="use_gravatar" /></p>';
      echo '<div id="gravatar_options" class="child_options">';
      //gravatar rating
echo '<p> Gravatar Rating: ';
      $gravatar_ratings = array('G','PG','R','X');
      foreach ( $gravatar_ratings as $gravatar_rating ) {
        echo "<input type='radio' name='gravatar_rating' value='" . attribute_escape($gravatar_rating) . "'";
        if ( htmlentities($wpbookAdminOptions['gravatar_rating']) === $gravatar_rating ) { // checked() uses "==" rather than "==="
          echo " checked='checked'";
        }
        echo ' /> ' . $gravatar_rating;
      }
      echo ' <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="gravatar_rating" /></p>';
      //gravatar default
echo '<p> Gravatar Default: <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="gravatar_default" /></p>';
      echo '<p class="options"><input type="radio" name="gravatar_default" value = "'. WP_PLUGIN_URL .'/wpbook/theme/default/gravatar_default.gif"';
      $gravatar_defaults_custom = TRUE;
      if( htmlentities($wpbookAdminOptions['gravatar_default']) == WP_PLUGIN_URL. '/wpbook/theme/default/gravatar_default.gif'){ 
        echo("checked");
        $gravatar_defaults_custom = FALSE;
      }
      echo (' ><span class="gravatar_facebook_default"> Facebook Default <img src="');
      echo (WP_PLUGIN_URL.'/wpbook/admin_includes/images/gravatar_default.gif"  width="40" height="40" /></span> ');

      $gravatar_defaults = array('identicon','monsterid','wavatar');

      foreach ( $gravatar_defaults as $gravatar_default ) {
        echo "<input type='radio' name='gravatar_default' value='" . attribute_escape($gravatar_default) . "'";
        if ( htmlentities($wpbookAdminOptions['gravatar_default']) === $gravatar_default ) { // checked() uses "==" rather than "==="
          echo " checked='checked'";
          $gravatar_defaults_custom = FALSE;
        }
        echo ' /> <span class="gravatar_'.$gravatar_default .'_default">' . $gravatar_default;
        echo '   <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/'. $gravatar_default .'_default.gif" width="40" height="40"> </span> ';
      }
      
      //Custom Gravatar
      echo '<input type="radio" name="gravatar_default" class="gravatar_rating_custom_radio" value="\c\u\s\t\o\m"';
      checked( $gravatar_defaults_custom, TRUE );
      echo '/> Custom: 
            <p class="gravatar_rating_custom options">  <input type="text" size="70" name="gravatar_rating_custom"'; 
      if($gravatar_defaults_custom === TRUE){echo 'value= '. urldecode($wpbookAdminOptions['gravatar_default']);}
      echo'  /> ';
      echo' </p></div></div> '; /* end of customization options div */ 
    
      echo '<div id="socialize_options"><p><strong>Socialize Options: </strong></p>';
      // Here starts the "invite friends" section
      echo '<p class="options"> <input type="checkbox" name="invite_friends" value = "true"';
      if( htmlentities($wpbookAdminOptions['invite_friends']) == "true"){ 
        echo("checked");
      }
      echo '> Show Invite Friends Link <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="show_invite" /> ';
  
      //enable profile option
echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="enable_profile_link" value="true"';
      if(htmlentities($wpbookAdminOptions['enable_profile_link']) == "true") {
        echo("checked");
      }
      echo '> Enable Facebook users to add your app to their profile <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="enable_profile" />';
      echo '</p>';
  
      // show share option 
      echo '<p class="options"><input type="checkbox" name="enable_share" value="true"';
      if( htmlentities($wpbookAdminOptions['enable_share']) == "true"){
        echo("checked");
      }
      echo ' id="enable_share"> Enable "Share This Post" (within Facebook) <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="show_share" /> ';
  
      // show external link option 
      echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="enable_external_link" value="true"';
      if( htmlentities($wpbookAdminOptions['enable_external_link']) == "true"){
        echo("checked");
      }
      echo ' id="enable_external_link"> Enable "view post at external site" link <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="show_external" /></p>';
  
      //links button position for external and share button 
      //see if share button or external link is enabled first
      echo '<div id="position_option">';
      echo '<p class="options">Link(s) position for share button and external link button: ';
      //top
      echo '<input type="radio" name="links_position" value = "top"';
      if( htmlentities($wpbookAdminOptions['links_position']) == "top"){ 
        echo("checked");
      }
      echo '>Top ';
      echo '<input type="radio" name="links_position" value = "bottom"';
      if( htmlentities($wpbookAdminOptions['links_position']) == "bottom"){ 
        echo("checked");
      }
      //bottom
      echo '> Bottom <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="link_position" /></p>';
      echo'</div>';
      echo'<p><strong> Page Options:</strong></p>';

      //start show pages option 
      echo '<p class="options"><input type="checkbox" id="show_pages" name="show_pages" value="true"';
      if( htmlentities($wpbookAdminOptions['show_pages']) == "true"){
        echo("checked");
      }
      echo '> Enable pages <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="enable_pages" /></p>';

      echo ' <div id="page_options" class="child_options"> ';
      //show top menu of parent pages 
      echo'<p class="options"><input type="checkbox" id="show_pages_menu" name="show_pages_menu" value="true"';
      if( htmlentities($wpbookAdminOptions['show_pages_menu']) == "true"){
        echo("checked");
      }
      echo '> Display menu of parent pages at top of application <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="enable_pages_menu" /> '
        .'&nbsp;&nbsp;&nbsp;<input type="checkbox" id="show_pages_list" name="show_pages_list" value="true"';
      if( htmlentities($wpbookAdminOptions['show_pages_list']) == "true"){
        echo("checked");
      }
      echo '> Show a list of pages below content <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="enable_pages_below" /></p>';

      echo '<p class="options"><input type="checkbox" id="exclude_true" name="exclude_true" value="true"';
      if( htmlentities($wpbookAdminOptions['exclude_true']) == "true"){
        echo("checked");
      }
      echo '>';
      echo 'Exclude some pages <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="exclude_pages" />'
          .'<div id="exclude_true_div" class="grandchild_options">Pages to exclude:<br/>';
      echo(wpbook_exclude_Page());
      echo' </div>'; //end exclude pages 
      echo '</div>'; //end page options
      
      echo'<p><strong> General Options:</strong></p>';

      //start show date in title
      echo '<p class="options"><input type="checkbox" name="show_date_title" value="true"';
      if( htmlentities($wpbookAdminOptions['show_date_title']) == "true"){
        echo("checked");
      }
      echo '> Include post date with title (you can customize the date format by using the advanced options) <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="show_date_title" /></p>';
  
      //start give credit option 
      echo '<p class="options"><input type="checkbox" name="give_credit" value="true"';
      if( htmlentities($wpbookAdminOptions['give_credit']) == "true"){
        echo("checked");
      }
      echo '> Give WPBook Credit (in Facebook) <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="give_credit" /> '
        .'&nbsp;&nbsp;&nbsp;<input type="checkbox" id="show_recent_post_list" name="show_recent_post_list" value="true"';
      if( htmlentities($wpbookAdminOptions['show_recent_post_list']) == "true"){
        echo("checked");
      }
      echo '> Show a list of recent post below content <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="enable_recent_post_list" /></p>'
            .'<p class="recent_post_amount child_options">How many? <input type="text" size="20" name="recent_post_amount_input"'; 
      echo 'value= '. ereg_replace("[^0-9]","", $wpbookAdminOptions['recent_post_amount']);
      echo '  /></p> '; 
      echo '</div>'; /* end of socialize_options div */

      // Advanced options
      echo '<p><input type="checkbox" name="show_advanced_options" value="true"';
      if( htmlentities($wpbookAdminOptions['show_advanced_options']) == "true"){
        echo("checked");
      }
      echo ' id="advanced_options" > <strong> Show Advanced Options</strong> <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="advanced_options" /></p>';

      //start advanced options div
      echo'<div id="wpbook_advanced_options"> <h3> Advanced Options:</h3>';
  
      echo'<p><strong> Date/Time Options:</strong></p>';
      echo '<p> Date format <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="date_format" /> </p><p class="options">';

      // date code copied from wp-admin/options-general.php
      $date_formats = apply_filters( 'date_formats', array(
                                                           __('F j, Y'),
                                                          'Y/m/d',
                                                          'm/d/Y',
                                                           'd/m/Y',
                                                          ) 
                                    );
      $custom = TRUE;
      foreach ( $date_formats as $format ) {
        echo "\t<label title='" . attribute_escape($format) . "'><input type='radio' name='timestamp_date_format' value='" . attribute_escape($format) . "'";
        if ( htmlentities($wpbookAdminOptions['timestamp_date_format']) === $format ) { // checked() uses "==" rather than "==="
          echo " checked='checked'";
          $custom = FALSE;
        }
        echo ' /> ' . date_i18n($format,time(),FALSE) . "</label><br />\n";
      }
      echo '	<label><input type="radio" name="timestamp_date_format" id="date_format_custom_radio" value="\c\u\s\t\o\m"';
      checked( $custom, TRUE );
      echo '/> ' . __('Custom:') . ' </label><input type="text" name="timestamp_date_format_custom" value="' . attribute_escape($wpbookAdminOptions['timestamp_date_format'] ) . '" class="small-text" /> ' . date_i18n($wpbookAdminOptions['timestamp_date_format'], time(),FALSE);
      echo'</p>';
      //end date code 
	
      //start time code, copied from wp-admin/options-general.php
      echo '<p> Time format <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="time_format" /> </p> <p class="options">';
      $time_formats = apply_filters( 'time_formats', array(
                                                           __('g:i a'),
                                                           'g:i A',
                                                           'H:i',
                                                           ) 
                                    );
      $custom = TRUE;
      foreach ( $time_formats as $format ) {
        echo "\t<label title='" . attribute_escape($format) . "'><input type='radio' name='timestamp_time_format' value='" . attribute_escape($format) . "'";
        if ( htmlentities($wpbookAdminOptions['timestamp_time_format'])  === $format) { // checked() uses "==" rather than "==="
          echo " checked='checked'";
          $custom = FALSE;
        }
        echo ' /> ' . date_i18n($format,time(),FALSE) . "</label><br />\n";
      }
      echo '	<label><input type="radio" name="timestamp_time_format" id="time_format_custom_radio" value="\c\u\s\t\o\m"';
      checked( $custom, TRUE );
      echo '/> ' . __('Custom:') . ' </label><input type="text" name="timestamp_time_format_custom" value="' . attribute_escape(($wpbookAdminOptions['timestamp_time_format'] ) ) . '" class="small-text" /> ' . date_i18n(($wpbookAdminOptions['timestamp_time_format']), time(),FALSE ) . "\n";

      echo "\t<p class='options'>" . __('<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">Documentation on date/time formatting</a>. Click "Save" to update sample output.'). "</p>\n";
	
      //begin custom header and footer code
      echo'<p><strong>Custom Header and Footer</strong><br/> This is where you can'
          .' set custom headers and footers for your post. For example if you wanted'
          .' to show the post author at the bottom of each post here is where you would set that option.'
          .'<div id="custom_header_footer_options"> <strong>Predefined Options:</strong><br/> '
          .'%author% - The Post Author<br/>  '
          .'%time% - The Post Time (in format above) <br/>'
          .'%date% - The Post Date (in format above) <br/>'
          .'%tags% - The Post\'s tags <br/>'
          .'%tag_link% - The Post\'s tags with link to archive page <br/>'
          .'%category% - The Post Category <br/> '
          .'%category_link% - The Post Category with link to archive page <br/>'
          .'%permalink% - The Post Permalink<br><br/>'
          .'<strong>Example Usage</strong><br/> Written by %author% and posted to %category% on %date% at %time%.</div> </p><br/>';
      echo'<div class="options">';

      //custom header
      echo(' Custom Header: <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="custom_header"/><br/><textarea rows="2" cols="100" name="custom_header">'.$wpbookAdminOptions['custom_header'].'</textarea>');

      //custom footer
      echo(' <br/><br/>Custom Footer: <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="custom_footer"/><br/><textarea rows="2" cols="100" name="custom_footer">'.$wpbookAdminOptions['custom_footer'].'</textarea>');
 
      //enable custom footer/header
      echo '<br/><br/>Show Custom Header/Footer: <img src=".'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="show_header_footer"/><br/>';
  
      //disabled
      echo '<input type="radio" name="show_custom_header_footer" value = "disabled"';
      if( htmlentities($wpbookAdminOptions['show_custom_header_footer']) == "disabled"){ 
        echo("checked");
      }
      echo '>Disabled ';
  
      //Both 
      echo '<input type="radio" name="show_custom_header_footer" value = "both"';
      if( htmlentities($wpbookAdminOptions['show_custom_header_footer']) == "both"){ 
        echo("checked");
      }
      echo '> Both ';
  
      //header
      echo '<input type="radio" name="show_custom_header_footer" value = "header"';
      if( htmlentities($wpbookAdminOptions['show_custom_header_footer']) == "header"){ 
        echo("checked");
      }
      echo '> Header ';
	
      //footer
      echo '<input type="radio" name="show_custom_header_footer" value = "footer"';
      if( htmlentities($wpbookAdminOptions['show_custom_header_footer']) == "footer"){ 
        echo("checked");
      }
      echo '> Footer ';
      echo'</div>';
      echo'</div>';

      //end advanced options
      echo '<p><input type="submit" value="Save" class="button-primary"';
      echo 'name="wpbook_save_button" /></p></form>';
      echo'<div id="help">';
      echo '<h2>Need Help?</h2>';
      echo '<p>If you need help setting up this application first read the <a href="'. WP_PLUGIN_URL .'/wpbook/instructions/index.html" target="_blank"> install instructions</a>. If you need help about an option mouse-over the <img src="'. WP_PLUGIN_URL .'/wpbook/admin_includes/images/help.png" class="need_help" /> for the a tooltip that we hope you\'ll find useful.';
      echo 'Support can also be found on <a href="http://wordpress.org/extend/plugins/wpbook/" target="_blank">the plugin page</a> </p><h3>Thanks for using WPBook!</h3>';
      echo'</div>';
  } else {
    echo '<div class="wrap"><p>Sorry, you are not allowed to access ';
    echo 'this page.</p></div>';
  }
}
  
  if (!function_exists('wp_recent_posts')) {
// this is based almost entirely on: Recent Posts
// http://mtdewvirus.com/code/wordpress-plugins/ v. 1.07
// by Nick Momrik, http://mtdewvirus.com/
	function wp_recent_posts($count = 5, $before = '<li>', $after = '</li>',
      $hide_pass_post = true, $skip_posts = 0, $show_excerpts = false, 
      $where = '', $join = '', $groupby = '') {
		global $wpdb;
		$time_difference = get_settings('gmt_offset');
		$now = gmdate("Y-m-d H:i:s",time());
	
		$join = apply_filters('posts_join', $join);
		$where = apply_filters('posts_where', $where);
		$groupby = apply_filters('posts_groupby', $groupby);
		if (!empty($groupby)) { $groupby = ' GROUP BY '.$groupby; }
	
		$request = "SELECT ID, post_title, post_excerpt FROM $wpdb->posts "
      . "$join WHERE post_status = 'publish' AND post_type = 'post' ";
		if ($hide_pass_post) $request .= "AND post_password ='' ";
		$request .= "AND post_date_gmt < '$now' $where $groupby ORDER BY "
      . "post_date DESC LIMIT $skip_posts, $count";
		$posts = $wpdb->get_results($request);
		$output = '';
		if ($posts) {
			foreach ($posts as $post) {
				$post_title = stripslashes($post->post_title);
				$permalink = get_permalink($post->ID);
				$output .= $before . '<a href="' . $permalink . '" rel="bookmark" '
          . 'title="Permanent Link: ' 
          . htmlspecialchars($post_title, ENT_COMPAT) . '">'
          . htmlspecialchars($post_title) . '</a>';
				if($show_excerpts) {
					$post_excerpt = stripslashes($post->post_excerpt);
					$output.= '<br />' . $post_excerpt;
				}
				$output .= $after;
			}
		} else {
			$output .= $before . "None found" . $after;
		}
		echo $output;
	}
}

// this is a copy of the wp_recent_posts function
// necessary because we don't want to echo output (for profile)
function wpbook_profile_recent_posts($count = 5, $before = '<li>', $after = '</li>',
                        $hide_pass_post = true, $skip_posts = 0, $show_excerpts = false, 
                        $where = '', $join = '', $groupby = '') {
  global $wpdb;
  $my_options = wpbook_getAdminOptions();
  
  $time_difference = get_settings('gmt_offset');
  $now = gmdate("Y-m-d H:i:s",time());
  $join = apply_filters('posts_join', $join);
  $where = apply_filters('posts_where', $where);
  $groupby = apply_filters('posts_groupby', $groupby);
  if (!empty($groupby)) { $groupby = ' GROUP BY '.$groupby; }
  
  $request = "SELECT ID, post_title, post_excerpt FROM $wpdb->posts "
    . "$join WHERE post_status = 'publish' AND post_type = 'post' ";
  if ($hide_pass_post) $request .= "AND post_password ='' ";
  $request .= "AND post_date_gmt < '$now' $where $groupby ORDER BY "
    . "post_date DESC LIMIT $skip_posts, $count";
  $posts = $wpdb->get_results($request);
  $output = '';
  if ($posts) {
    foreach ($posts as $post) {
      $post_title = stripslashes($post->post_title);
      if($my_options['promote_external']) {
        if(check_facebook()) {
          $permalink = get_external_post_url(get_permalink($post->ID));  // external permalink
        } else {
          $permalink = get_permalink($post->ID);
        }
      } else {
        $permalink = get_permalink($post->ID);  // permalink is un-filtered
        $my_offset = strlen(get_option('home'));
        $app_url = $my_options['fb_app_url'];
        $my_link = 'http://apps.facebook.com/' . $app_url 
          . substr($permalink,$my_offset); 
        $permalink = $my_link;
      }
      $output .= $before . '<a href="' . $permalink . '" rel="bookmark" '
        . 'title="Permanent Link: ' 
        . htmlspecialchars($post_title, ENT_COMPAT) . '">'
        . htmlspecialchars($post_title) . '</a>';
      if($show_excerpts) {
        $post_excerpt = stripslashes($post->post_excerpt);
        $output.= '<br />' . $post_excerpt;
      }
      $output .= $after;
    }
  } else {
    $output .= $before . "None found" . $after;
  }
  return $output;
}  

// this checks to see if we are in facebook
function check_facebook() {
	if (!isset($_SERVER["HTTP_USER_AGENT"])) {
		return false;
	}
	if (isset($_GET['fb_sig_in_iframe']) || isset($_GET['fb_force_mode'])) {  
		return true;
	}
	return false;
}

function wpbook_theme_root($path) {
	$theme_root = dirname(__FILE__);
	if (check_facebook()) {
		return $theme_root . '/theme'; 
	} else {
		return $path;
	}
}	

function wpbook_theme_root_uri($url) {
	if (check_facebook()) {
		$dir = WP_PLUGIN_DIR . "/wpbook/theme";
		return $dir;
	} else {
		return $url;
	}
}

// this function seems to be required by WP 2.6
function wpbook_template_directory($value) {
  if (check_facebook())  {
    $theme_root = dirname(__FILE__);
      return $theme_root . '/theme';
    } else {
      return $value;
    }
}
 
  
// this is the function which adds to the template and stylesheet hooks
// the call to wpbook_template
if (check_facebook()) {
  add_filter('template_directory', 'wpbook_template_directory');
	add_filter('theme_root', 'wpbook_theme_root');
  add_filter('theme_root_uri', 'wpbook_theme_root_uri');
}
             
// also have to change permalinks, next/prev links , page links, and archive links
function fb_filter_postlink($postlink) {
	if (check_facebook()) {
		$my_offset = strlen(get_option('home'));
		$my_options = wpbook_getAdminOptions();
		$app_url = $my_options['fb_app_url'];
		$my_link = 'http://apps.facebook.com/' . $app_url 
      . substr($postlink,$my_offset); 
		return $my_link;
	} else {
		return $postlink; 
	}
}

// this version to be called when we're outside facebook too  
function wpbook_always_filter_postlink($postlink) {
  $my_offset = strlen(get_option('home'));
  $my_options = wpbook_getAdminOptions();
  $app_url = $my_options['fb_app_url'];
  $my_link = 'http://apps.facebook.com/' . $app_url 
  . substr($postlink,$my_offset); 
  return $my_link;
  }

// change links to pages as well	
function fb_filter_pagelink($pagelink) {
  if(check_facebook()) {
    $my_offset = strlen(get_option('home'));
		$my_options = wpbook_getAdminOptions();
		$app_url = $my_options['fb_app_url'];
		$my_link = 'http://apps.facebook.com/' . $app_url 
    . substr($pagelink,$my_offset); 
		return $my_link;
	} else {
		return $pagelink; 
  }
}
// Can't forget tags 	
function fb_filter_taglink($taglink) {
  if(check_facebook()) {
    $my_offset = strlen(get_option('home'));
		$my_options = wpbook_getAdminOptions();
		$app_url = $my_options['fb_app_url'];
		$my_link = 'http://apps.facebook.com/' . $app_url 
    . substr($taglink,$my_offset); 
		return $my_link;
	} else {
		return $taglink; 
  }
}
// and categories 	
function fb_filter_catlink($catlink) {
  if(check_facebook()) {
    $my_offset = strlen(get_option('home'));
		$my_options = wpbook_getAdminOptions();
		$app_url = $my_options['fb_app_url'];
		$my_link = 'http://apps.facebook.com/' . $app_url 
    . substr($catlink,$my_offset); 
		return $my_link;
	} else {
		return $catlink; 
  }
}  
function wpbook_publish_to_facebook($post_ID) {
  if((!class_exists('FacebookRestClient')) && (!version_compare(PHP_VERSION, '5.0.0', '<'))) {
    include_once(WP_PLUGIN_DIR.'/wpbook/client/facebook.php');
  }           
	$wpbookOptions = get_option('wpbookAdminOptions');
	
	if (!empty($wpbookOptions)) {
		foreach ($wpbookOptions as $key => $option)
		$wpbookAdminOptions[$key] = $option;
	}
	
	$api_key = $wpbookAdminOptions['fb_api_key'];
	$secret  = $wpbookAdminOptions['fb_secret'];
  $target_admin = $wpbookAdminOptions['fb_admin_target'];
  $target_page = $wpbookAdminOptions['fb_page_target'];
  $stream_publish = $wpbookAdminOptions['stream_publish'];
  $stream_publish_pages = $wpbookAdminOptions['stream_publish_pages'];
  $wpbook_show_errors = $wpbookAdminOptions['show_errors'];
  $wpbook_promote_external = $wpbookAdminOptions['promote_external'];
  $wpbook_attribution_line = $wpbookAdminOptions['attribution_line'];
	$facebook = new Facebook($api_key, $secret);
	
  $ProfileContent = '<h3>Recent posts</h3><div class="wpbook_recent_posts">'
  . '<ul>' . wpbook_profile_recent_posts(5) . '</ul></div>';
  if (version_compare(PHP_VERSION, '5.0.0', '>')) {
    include(WP_PLUGIN_DIR.'/wpbook/publish_to_facebook.php');
  } else {
    wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
  }
} // end of function

function get_external_post_url($my_permalink){
  $my_options = wpbook_getAdminOptions();
  $app_url = $my_options['fb_app_url'];
  // code to get the url of the orginal post for use in the "show external url view"
  $permalink_pieces = parse_url($my_permalink);
  //get the app_url and the preceeding slash
  $permalink_app_url = "/". $app_url; 
  //remove /appname
  $external_post_permalink = str_replace_once($permalink_app_url,"",$permalink_pieces[path]);
  //re-write the post url using the site url 
  $external_site_url_pieces = parse_url(get_bloginfo('wpurl'));
    
  //break apart the external site address and get just the "site.com" part
  $external_site_url = $external_site_url_pieces[host];
  $external_post_url = get_bloginfo('siteurl').  $external_post_permalink;
  if(!empty($permalink_pieces[query])) {
    $external_post_url = $external_post_url .'?'. $permalink_pieces[query];
  }
  //return "app url is " . $app_url; 
  return $external_post_url; 
} 
  
// check to see if external post link contains the app name, and if it does, 
// only replace the first instance 
function str_replace_once($needle, $replace, $haystack) {
  // Looks for the first occurence of $needle in $haystack
  // and replaces it with $replace.
  $pos = strpos($haystack, $needle);
  if ($pos === false) {
    // Nothing found
    return $haystack;
  }
  return substr_replace($haystack, $replace, $pos, strlen($needle));
}
  
// attribution line 
function wpbook_attribution_line($attribution_line,$author){
  if($author='')
    $author = get_the_author();
  $attribution_line = str_replace('%author%',$author,$attribution_line);
  $attribution_line = str_replace('%blogname%',html_entity_decode(get_bloginfo('name'),ENT_QUOTES),$attribution_line);
  return $attribution_line;
}  
  
/*
 * Use postmeta to enable users to turn off streaming on case-by-case basis
 * Based on how Alex King's Twitter Tools handles the same case for pushing
 * posts to twitter
 */
function wpbook_meta_box() {
  global $post;
  $wpbook_publish = get_post_meta($post->ID, 'wpbook_fb_publish', true);
  if ($wpbook_publish == '') {
    $wpbook_publish = 'yes';
  }
  echo '<p>'.__('Publish this post to Facebook Wall?', 'wpbook').'&nbsp;';
  echo '<input type="radio" name="wpbook_fb_publish" id="wpbook_fb_publish_yes" value="yes" ';
  checked('yes', $wpbook_publish, false);
  echo ' /> <label for="wpbook_fb_publish_yes">'.__('Yes', 'wpbook').'</label> &nbsp;&nbsp;';
  echo '<input type="radio" name="wpbook_fb_publish" id="wpbook_fb_publish_no" value="no" ';
  checked('no', $wpbook_publish, false);
  echo ' /> <label for="wpbook_fb_publish_no">'.__('No', 'wpbook').'</label>';
  echo '</p>';
  do_action('wpbook_post_options');
}
  
function wpbook_add_meta_box() {
  global $wp_version;
  if (version_compare($wp_version, '2.7', '< ')) {
    add_meta_box('wpbook_post_form','WPBook', 'wpbook_meta_box', 'post', 'side');
  } else {
    add_meta_box('wpbook_post_form','WPBook','wpbook_meta_box','post','normal');
  }
}
  
function wpbook_store_post_options($post_id, $post = false) {
  if (!$post || $post->post_type == 'revision') { // store the metadata with the post, not the revision
		return;
	}  
  $wpbookAdminOptions = wpbook_getAdminOptions();
  $post = get_post($post_id);
  $notify_meta = get_post_meta($post_id, 'wpbook_fb_publish', true);
  $posted_meta = $_POST['wpbook_fb_publish'];
    
  $save = false;
  if (!empty($posted_meta)) {
    $posted_meta == 'yes' ? $meta = 'yes' : $meta = 'no';
    $save = true;
  }
  else if (empty($notify_meta)) {
    if (($wpbookAdminOptions['stream_publish']) || ($wpbookAdminOptions['stream_publish_pages'])) {
      $meta = 'yes';
    } else {
      $meta = 'no';
    }
    $save = true;
  }
  else {
    $save = false;
  }
    
  if ($save) {
    if (!update_post_meta($post_id, 'wpbook_fb_publish', $meta)) {
      add_post_meta($post_id, 'wpbook_fb_publish', $meta);
    }
  }
}
add_action('draft_post', 'wpbook_store_post_options', 1, 2);
add_action('publish_post', 'wpbook_store_post_options', 1, 2);
add_action('save_post', 'wpbook_store_post_options', 1, 2);

  
// based on sample code here:
// http://willnorris.com/2009/06/wordpress-plugin-pet-peeve-2-direct-calls-to-plugin-files  
// thanks will  
function wpbook_parse_request($wp) {
  if (array_key_exists('wpbook', $wp->query_vars)){
    if($wp->query_vars['wpbook'] == 'comment-handler') {  // first process requests with "wpbook=comment-handler"
      // process the request - in our case this is a comment being posted
      nocache_headers();
      $comment_post_ID = (int) $_POST['comment_post_ID'];
      global $wpdb;
      $status = $wpdb->get_row("SELECT post_status, comment_status FROM "
                               . "$wpdb->posts WHERE ID = '$comment_post_ID'");
      if ( empty($status->comment_status) ) {
        do_action('comment_id_not_found', $comment_post_ID);
        exit;
      } elseif ( !comments_open($comment_post_ID) ) {
        do_action('comment_closed', $comment_post_ID);
        wp_die( __('Sorry, comments are closed for this item.') );
      } elseif ( in_array($status->post_status, array('draft', 'pending') ) ) {
        do_action('comment_on_draft', $comment_post_ID);
        exit;
      }
     
      $wpbookOptions = get_option('wpbookAdminOptions');
      if (!empty($wpbookOptions)) {
        foreach ($wpbookOptions as $key => $option)
          $wpbookAdminOptions[$key] = $option;
      }
     
      $comment_author       = trim(strip_tags($_POST['author']));
      $comment_author_email = trim($_POST['email']);
      $comment_author_url   = trim($_POST['url']);
      $comment_content      = trim($_POST['comment']);
      $comment_type = '';
       
      $wpbook_require_email = $wpbookOptions['require_email'];
     
      // need to account here for wpadminOptions version of email required
      if(($wpbook_require_email == "true") && ('' == $comment_author_email)){
        echo '<p>Sorry: comments require an email address</p>';
        wp_die( __('Error: please enter an e-mail.'));
      }
      
      if($comment_author_email != ''){
        if(!preg_match('/^[A-Z0-9._%-]+@[A-Z0-9.-]+\.(?:[A-Z]{2}|com|org|net|biz|'
                       . 'info|name|aero|biz|info|jobs|museum|name|edu)$/i', 
                       $comment_author_email)) {
        wp_die( __('Error: please enter a valid e-mail.'));
        }
      }
      
      if ( '' == $comment_content )
        wp_die( __('Error: please type a comment.') );
      
      $commentdata = compact('comment_post_ID', 'comment_author', 
                             'comment_author_email', 'comment_author_url',
                             'comment_content', 'comment_type', 'user_ID');
      
      $comment_id = wp_new_comment( $commentdata );
      
      $comment = get_comment($comment_id);
      if ( !$user->ID ) {
        setcookie('comment_author_' . COOKIEHASH, 
                  $comment->comment_author, time() + 30000000, 
                  COOKIEPATH, COOKIE_DOMAIN);
        setcookie('comment_author_email_' . COOKIEHASH,
                  $comment->comment_author_email, time() + 30000000, 
                  COOKIEPATH, COOKIE_DOMAIN);
        setcookie('comment_author_url_' . COOKIEHASH, 
                  clean_url($comment->comment_author_url), 
                  time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
      }
      
      // all done parsing, redirect to post, on comment anchor
      
      $redirect_url = get_permalink($comment_post_ID);
      $redirect_url .= '#comment-' . $comment_id;
      
      // switched to raw php header redirect as $facebook->redirect was
      // problematic and no fb session needed in this page
      header( 'Location: ' . $redirect_url );
    }
    if($wp->query_vars['wpbook'] == 'update_profile_boxes') {  // first process requests with "wpbook=comment-handler"
      if((!class_exists('FacebookRestClient')) && (!version_compare(PHP_VERSION, '5.0.0', '<'))) {
        include_once(WP_PLUGIN_DIR . '/wpbook/client/facebook.php');
      }
      $wpbookOptions = get_option('wpbookAdminOptions');
      if (!empty($wpbookOptions)) {
        foreach ($wpbookOptions as $key => $option)
        $wpbookAdminOptions[$key] = $option;
      }

      if (version_compare(PHP_VERSION, '5.0.0', '>')) {
        include(WP_PLUGIN_DIR.'/wpbook/update_profile_boxes.php');
      } else {
        wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
      }
      
      $redirect_url = $wpbookAdminOptions['app_url'];
      header( 'Location: ' . $redirect_url );
    }
    if($wp->query_vars['wpbook'] == 'catch_permissions') {  // do something with infinite session key
      /* reverted to showing the infinite session key and asking user to enter
       * into WPBook settings - all the other settings are done that way, and
       * this avoids the oddity of trying to 'hide' it on the settings page
       */
      $my_session_key = $_GET['fb_sig_session_key'];
      echo "<p>Your session key is $my_session_key.</p>";
      echo "<p>Please copy that into the appropriate place in WPBook settings</p>";
    }
  }
}
  
function wpbook_query_vars($vars) {
    $vars[] = 'wpbook';
    return $vars;
}

/**
  * Thanks Otto - http://lists.automattic.com/pipermail/wp-hackers/2009-July/026759.html
  */
function wpbook_activation_check(){
  if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    deactivate_plugins(basename(__FILE__)); // Deactivate ourself
    wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
  }
  if (version_compare($wp_version, '2.6', '< ')) {
    wp_die("This plugin requires WordPress 2.6 or greater.");
  }
}

register_activation_hook(__FILE__, 'wpbook_activate');
#register_deactivation_hook(__FILE__, 'wpbook_deactivate');
  
add_filter('query_vars', 'wpbook_query_vars');	
add_filter('post_link','fb_filter_postlink',1,1);
add_filter('page_link','fb_filter_pagelink',1,1); 
add_filter('tag_link','fb_filter_taglink',1,1); 
add_filter('category_link','fb_filter_catlink',1,1); 
add_action('admin_menu', 'wpbook_options_page');
add_action('wp', 'wpbook_parse_request');
add_action('admin_menu', 'wpbook_add_meta_box');
  
	
// these capture new posts, not edits of previous posts	
add_action('future_to_publish','wpbook_publish_to_facebook');	
add_action('new_to_publish','wpbook_publish_to_facebook');
add_action('draft_to_publish','wpbook_publish_to_facebook');  
  
// cron job task  
add_action('wpbook_cron_job', 'wpbook_import_comments');

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
} else { 
  include("wpbook_cron.php");
}
?>

