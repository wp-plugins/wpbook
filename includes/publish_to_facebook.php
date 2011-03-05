<?php
/*
 * This function updates profile boxes and publishes to Facebook
 * In an include to avoid PHP4 based errors
 */
function wpbook_safe_publish_to_facebook($post_ID) {  
  if(!class_exists('FacebookRestClient')) {
    include_once(WP_PLUGIN_DIR.'/wpbook/includes/client/facebook.php');
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
  
  if((!empty($api_key)) && (!empty($secret)) && (!empty($target_admin)) && (($stream_publish == "true") || $stream_publish_pages == "true")) {
    // here we should also post to the author's stream
    $my_post = get_post($post_ID);
    if(!empty($my_post->post_password)) { // post is password protected, don't post
      return;
    }
    if(get_post_type($my_post->ID) != 'post') { // only do this for posts
      return;
    }
    $publish_meta = get_post_meta($my_post->ID,'wpbook_fb_publish',true); 
    if(($publish_meta == 'no')) { // user chose not to post this one
      return;
    }
    $my_title=$my_post->post_title;
    $my_author=get_userdata($my_post->post_author)->display_name;
    if($wpbook_promote_external) { 
      $my_permalink = get_permalink($post_ID);
    } else {
      $my_permalink = wpbook_always_filter_postlink(get_permalink($post_ID));
    }
    $message = wpbook_attribution_line($wpbook_attribution_line,$my_author);
  
    if(($my_post->post_excerpt) && ($my_post->post_excerpt != '')) {
      $wpbook_description = stripslashes(wp_filter_nohtml_kses(apply_filters('the_content',$my_post->post_excerpt)));
    }
    else { 
      $wpbook_description = stripslashes(wp_filter_nohtml_kses(apply_filters('the_content',$my_post->post_content)));
    }
    if(strlen($wpbook_description) >= 995) {
      $space_index = strrpos(substr($wpbook_description, 0, 995), ' ');
      $short_desc = substr($wpbook_description, 0, $space_index);
      $short_desc .= '...';
      $wpbook_description = $short_desc;
    }
  
    $my_image = get_the_post_thumbnail($post_ID, 'thumbnail'); 
    
    if(!empty($my_image)) {
      /* message, picture, link, name, caption, description, source */      
      $attachment = array( 
                          'access_token' => '129625873775358|43619475b157e93215521953-1825518|_Mprwy3amkgAhNLAWWcAC52NrqA',
                          'name' => $my_title,
                          'link' => $my_permalink,
                          'description' => $wpbook_description,  
                          'picture' => $my_image, 
                         ); 
    } else {
      $attachment = array( 
                          'access_token' => '129625873775358|43619475b157e93215521953-1825518|_Mprwy3amkgAhNLAWWcAC52NrqA',
                          'name' => $my_title,
                          'link' => $my_permalink,
                          'description' => $wpbook_description,  
                          'comments_xid' => $post_ID, 
                          ); 
    }
    $action_links = array( array('text' => 'Read More',
                               'href' => $my_permalink
                               )
                        ); 
  
    if($stream_publish == "true") {
      $fb_response = '';
      try{
        // need new format for SDK API
        $fb_response = $facebook->api('/'. $target_admin .'/feed', 'POST', $attachment);     
      } catch (FacebookApiException $e) {
        if($wpbook_show_errors) {
          $wpbook_message = 'Caught exception in stream publish for user: ' .  $e->getMessage() .'Error code: '. $e->getCode();  
          wp_die($wpbook_message,'WPBook Error');
        } // end if for show errors
      } // end try-catch
      if($fb_response != '') {
        add_post_meta($my_post->ID,'_wpbook_user_stream_id', $fb_response);
        add_post_meta($my_post->ID,'_wpbook_user_stream_time',0); // no comments imported yet
      }  // end of if $response
    } // end of if stream_publish 
  
    if(($stream_publish_pages == "true") && (!empty($target_page))) {      
      // publish to page with new api
      $fb_response = '';
      try{
        $fb_response = $facebook->api('/'. $target_page .'/feed/','POST', $attachment); 
      } catch (FacebookApiException $e) {
        if($wpbook_show_errors) {
          $wpbook_message = 'Caught exception in publish to page ' . $e->getMessage() . ' Error code: ' . $e->getCode();
          wp_die($wpbook_message,'WPBook Error');
        } // end if for show errors
      } // end try/catch for publish to page
      if($fb_response != '') {
        add_post_meta($my_post->ID,'_wpbook_page_stream_id',$fb_response);
        add_post_meta($my_post->ID,'_wpbook_page_stream_time',0); // no comments imported
      } else {
        $wpbook_message = 'No post id returned from Facebook, $fb_response was ' .$fb_response;
        $wpbook_message = $wpbook_message . ' and $fb_page_type was ' . $fb_page_type;
        $wpbook_message .= ' and $wpbook_description was ' . $wpbook_description;
        $wpbook_message .= ' and $my_title was ' . $my_title;
        wp_die($wpbook_message,'WPBook Error publishing to page'); 
      }
    } // end of if stream_publish_pages is true AND target_page non-empty
  } // end for if stream_publish OR stream_publish_pages is true
} // end of wpbook_safe_publish_to_facebook
?>
