<?php
/*
Plugin Name: WPBook
Plugin URI: http://www.openparenthesis.org/code/wp
Date: 2009, February 9
Description: Plugin to embed Wordpress Blog into Facebook Canvas using the Facebook Platform. 
Author: John Eckman
Author URI: http://johneckman.com
Version: 1.2
*/

/*
Note: This plugin draws from: 
   Alex King's WP-Mobile plugin (http://alexking.org/projects/wordpress ) 
   and BraveNewCode's WPTouch (http://www.bravenewcode.com/wptouch/ )
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
                           $fb_app_url,$invite_friends,$require_email,$give_credit,
                           $enable_share, $allow_comments,$links_position,$enable_external_link,$enable_profile_link) {
  $wpbookAdminOptions = array('wpbook_installation' => $wpbook_installation,
                              'fb_api_key' => $fb_api_key,
                              'fb_secret'  => $fb_secret,
                              'fb_app_url' => $fb_app_url,
                              'invite_friends' => $invite_friends,
                              'require_email' => $require_email,
                              'give_credit' => $give_credit,
                              'enable_share' => $enable_share,
                              'allow_comments' => $allow_comments,
                              'links_position' => $links_position,
                              'enable_external_link' => $enable_external_link,
                              'enable_profile_link' => $enable_profile_link);
  update_option('wpbookAdminOptions', $wpbookAdminOptions);
}
  
function wpbook_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page('WPBook', 'WPBook', 8, 
		  basename(__FILE__), 'wpbook_subpanel');
	}
}

function wpbook_subpanel() {
  if (is_authorized()) {
    $wpbookAdminOptions = wpbook_getAdminOptions();
    if (isset($_POST['fb_api_key']) && isset($_POST['fb_secret']) && isset($_POST['fb_app_url']) 
      && (!empty($_POST['fb_api_key']))  && (!empty($_POST['fb_secret'])) && (!empty($_POST['fb_app_url']))) { 
      $fb_api_key = $_POST['fb_api_key'];
      $fb_secret = $_POST['fb_secret'];
      $fb_app_url = $_POST['fb_app_url'];
      $invite_friends = $_POST['invite_friends'];
      $require_email = $_POST['require_email'];
      $give_credit = $_POST['give_credit'];
      $enable_share = $_POST['enable_share'];
      $allow_comments = $_POST['allow_comments'];
      $links_position = $_POST['links_position'];
      $enable_external_link = $_POST['enable_external_link'];
      $enable_profile_link = $_POST['enable_profile_link'];
      setAdminOptions(1, $fb_api_key, $fb_secret, $fb_app_url,
                      $invite_friends,$require_email,$give_credit,$enable_share,$allow_comments,$links_position,$enable_external_link,$enable_profile_link);
      $flash = "Your settings have been saved. ";
    } 
    elseif (($wpbookAdminOptions['fb_api_key'] != "") || ($wpbookAdminOptions['fb_secret'] != "") || ($wpbookAdminOptions['fb_app_url'] != "")
            || (!empty($_POST['fb_api_key']))  || (!empty($_POST['fb_secret'])) || (!empty($_POST['fb_app_url']))){
      $flash = "";
    }
    else {$flash = "Please complete all necessary fields";}
  } else {
    $flash = "You don't have enough access rights.";
  }   
  
  if (is_authorized()) {
    $wpbookAdminOptions = wpbook_getAdminOptions();
    if ($wpbookAdminOptions['wpbook_installation'] != 1) {  
      setAdminOptions(1, null,null,null,null,null,null,null,null,null,null,null);
    }
    
    if ($flash != '') echo '<div id="message"class="updated fade">'
      . '<p>' . $flash . '</p></div>';
    
    // jquery functions to replace the old hid show div functions  
    //  this should also probally be refactord to make it smaller at some point
    ?>
    <script language="javascript" type="text/javascript">
    jQuery(document).ready(function($) {
    //see if allow comment is checked on page load 
    if ($('#allow_comments').is(':checked'))
      {$('#comments_options').show();}
    else  
      {$('#comments_options').hide('fast');}
                       
    //see if share or original links are checked on page load 
    if (($('#enable_share').is(':checked')) || ($('#enable_external_link').is(':checked')) )
      {$('#position_option').show();}
    else  
      {$('#position_option').hide('fast');}
                       
    //toggle status of allow comments on click 
    $('#allow_comments').click(function(){
      if ($('#allow_comments').is(':checked'))
        {$('#comments_options').show('fast');}
      else
        {$('#comments_options').hide('fast');}
      });
                       
    //toggle status of share and original links on click 
    $('#enable_share').click(function(){
      if ($('#enable_share').is(':checked'))
        {$('#position_option').show('fast');}
      else if ($('#enable_external_link').is(':checked'))
        {$('#position_option').show('fast');}
      else
        {$('#position_option').hide('fast');}
      });
                       
    $('#enable_external_link').click(function(){
      if ($('#enable_external_link').is(':checked'))
        {$('#position_option').show('fast');}
      else if ($('#enable_share').is(':checked'))
        {$('#position_option').show('fast');}
      else
        {$('#position_option').hide('fast');}
    });
  });
</script>
<?php
	echo '<div class="wrap">';
  echo '<h2>Set Up Your Facebook Application</h2><p>';
  echo 'This plugin allows you to embed your blog into the Facebook canvas';
  echo ', allows Facebook users to comment on or share your blog posts, and ';
  echo 'puts your 5 most recent posts in users profiles (with their permission).</p>';
  echo '<p><a href="../wp-content/plugins/wpbook/instructions/index.html" target="_blank">Detailed instructions</a>, with screenshots</p>';
  echo '<form action="'. $_SERVER["REQUEST_URI"] .'" method="post">';
  echo '<ol>';
  echo '<li>To use this app, you must register for an API key at ';
  echo '<a href="http://www.facebook.com/developers/">';
  echo 'http://www.facebook.com/developers/</a>.  Follow the link and click ';
  echo '"set up a new application."  After you\'ve obtained the necessary ';
  echo 'info, fill in both your application\'s API and Secret keys.</li>';
  echo '<li>Enter Your Facebook Application\'s API Key:';
  echo '<br /><input type="text" name="fb_api_key" value="';
  echo htmlentities($wpbookAdminOptions['fb_api_key']) .'" size="45" /></li>';
  echo '<li>Enter Your Facebook Application\'s Secret:<br />';
  echo '<input type="text" name="fb_secret" value="';
  echo htmlentities($wpbookAdminOptions['fb_secret']) .'" size="45" /></li>';
  echo '<li>Enter Your Facebook Application\'s Canvas Page URL, ';
  echo '<strong>NOT</strong> INCLUDING "http://apps.facebook.com/"<br />';
  echo '<input type="text" name="fb_app_url" value="';
  echo htmlentities($wpbookAdminOptions['fb_app_url']) .'" size="45" /></li>';
  
  // Here starts the "invite friends" section
  echo '<li><input type="checkbox" name="invite_friends" value = "true"';
  if( htmlentities($wpbookAdminOptions['invite_friends']) == "true"){ 
    echo("checked");
  }
  echo '> Show Invite Friends Link </li>';
  // Now let's handle commenting - only show require_email if comments on
  echo '<li><input type="checkbox" name="allow_comments" value="true" ';
  if( htmlentities($wpbookAdminOptions['allow_comments']) == "true") {
    echo("checked");
  }
  echo ' id="allow_comments" > Allow comments inside Facebook';
  echo '<div id="comments_options">';
  echo '<input type="checkbox" name="require_email" value = "true"';
  if( htmlentities($wpbookAdminOptions['require_email']) == "true"){ 
    echo("checked");
  }
  echo '> Require Comment Authors E-mail Address</div> </li>';
  //start give credit option 
  echo '<li><input type="checkbox" name="give_credit" value="true"';
  if( htmlentities($wpbookAdminOptions['give_credit']) == "true"){
    echo("checked");
  }
  echo '> Give WPBook Credit (in Facebook)</li>';
  // show share option 
  echo '<li><input type="checkbox" name="enable_share" value="true"';
  if( htmlentities($wpbookAdminOptions['enable_share']) == "true"){
    echo("checked");
  }
  echo ' id="enable_share"> Enable "Share This Post" (in Facebook)</li>';
  // show external link option 
  
  echo '<li><input type="checkbox" name="enable_external_link" value="true"';
  if( htmlentities($wpbookAdminOptions['enable_external_link']) == "true"){
    echo("checked");
  }
  echo ' id="enable_external_link"> Enable "view post at external site" link</li>';
  
  //links button position for external and share button 
  //see if share button or external link is enabled first
  echo '<div id="position_option">';
  echo '<li>Link Position for share button and external link button (on both single and list views):<br/>';
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
  echo '> Bottom <br/></li>';
  echo'</div>';
  echo '<li><input type="checkbox" name="enable_profile_link" value="true" ';
  if( htmlentities($wpbookAdminOptions['enable_profile_link']) == "true") {
    echo("checked");
  }
  echo ' > Enable Facebook users to add your app to their profile';
  echo '</li></ol>';
  echo '<p><input type="submit" value="Save" class="button"';
  echo 'name="wpbook_save_button" /></p></form>';
  echo '</div>';
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
      . "$join WHERE post_status = 'publish' AND post_type != 'page' ";
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
  $time_difference = get_settings('gmt_offset');
  $now = gmdate("Y-m-d H:i:s",time());
    
  $join = apply_filters('posts_join', $join);
  $where = apply_filters('posts_where', $where);
  $groupby = apply_filters('posts_groupby', $groupby);
  if (!empty($groupby)) { $groupby = ' GROUP BY '.$groupby; }
  
  $request = "SELECT ID, post_title, post_excerpt FROM $wpdb->posts "
    . "$join WHERE post_status = 'publish' AND post_type != 'page' ";
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
		$dir = get_bloginfo('wpurl') . "/wp-content/plugins/wpbook/theme";
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
             
// also have to change permalinks and next/prev links and more links
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
	
function wp_update_profile_boxes() {
  if(!class_exists('FacebookRestClient')) {
    if (version_compare(PHP_VERSION,'5','>=')) {
      include_once(ABSPATH.'wp-content/plugins/wpbook/client/facebook.php');
	  } else {
		  include_once(ABSPATH.'wp-content/plugins/wpbook/php4client/'
        . 'facebook.php');
		  include_once(ABSPATH.'wp-content/plugins/wpbook/php4client/'
        . 'facebookapi_php4_restlib.php');
	  }
  }           
	$wpbookOptions = get_option('wpbookAdminOptions');
	
	if (!empty($wpbookOptions)) {
		foreach ($wpbookOptions as $key => $option)
		$wpbookAdminOptions[$key] = $option;
	}
	
	$api_key = $wpbookAdminOptions['fb_api_key'];
	$secret  = $wpbookAdminOptions['fb_secret'];
	
	$facebook = new Facebook($api_key, $secret);
	
  $ProfileContent = '<h3>Recent posts</h3><div class="wpbook_recent_posts">'
  . '<ul>' . wpbook_profile_recent_posts(5) . '</ul></div>';
  
  // this call just updates the RefHandle, already set for the user profile
  $facebook->api_client->call_method('facebook.Fbml.setRefHandle',
                                     array('handle' => 'recent_posts',
                                            'fbml' => $ProfileContent,
                                    ) );
}
	
add_filter('post_link','fb_filter_postlink',1,1);
add_action('admin_menu', 'wpbook_options_page');
	
// these capture new posts, not edits of previous posts	
add_action('future_to_publish','wp_update_profile_boxes');	
add_action('new_to_publish','wp_update_profile_boxes');
add_action('draft_to_publish','wp_update_profile_boxes');  
?>
