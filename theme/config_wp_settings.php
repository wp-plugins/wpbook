<?php
  
$wpbookOptions = get_option('wpbookAdminOptions');
  
if (!empty($wpbookOptions)) {
  foreach ($wpbookOptions as $key => $option)
  $wpbookAdminOptions[$key] = $option;
}
  
//get options from wordpress settings
$api_key = $wpbookAdminOptions['fb_api_key'];
$secret  = $wpbookAdminOptions['fb_secret'];
$app_url = $wpbookAdminOptions['fb_app_url'];
$invite_friends = $wpbookAdminOptions['invite_friends']; 
$require_email = $wpbookAdminOptions['require_email']; 
$allow_comments = $wpbookAdminOptions['allow_comments'];
$give_credit = $wpbookAdminOptions['give_credit'];
$enable_share = $wpbookAdminOptions['enable_share'];
$links_position = $wpbookAdminOptions['links_position'];
$enable_external_link = $wpbookAdminOptions['enable_external_link'];
$enable_profile_link = $wpbookAdminOptions['enable_profile_link'];
$timestamp_date_format = $wpbookAdminOptions['timestamp_date_format'];
$timestamp_time_format = $wpbookAdminOptions['timestamp_time_format'];
$show_date_title = $wpbookAdminOptions['show_date_title'];
$custom_header = $wpbookAdminOptions['custom_header'];
$custom_footer = $wpbookAdminOptions['custom_footer'];
$show_custom_header_footer = $wpbookAdminOptions['show_custom_header_footer'];
$app_name = get_bloginfo('name');
if ($app_name == '' || (empty($app_name)) ) {
  $app_name = 'Blog Title';
}
$use_gravatar = $wpbookAdminOptions['use_gravatar'];
$gravatar_rating = $wpbookAdminOptions['gravatar_rating'];
$gravatar_default = $wpbookAdminOptions['gravatar_default'];
$show_pages = $wpbookAdminOptions['show_pages'];
$exclude_pages_true = $wpbookAdminOptions['exclude_true'];
$exclude_pages_list = $wpbookAdminOptions['exclude_pages'];
$show_pages_menu = $wpbookAdminOptions['show_pages_menu'];
$show_page_list = $wpbookAdminOptions['show_pages_list'];
$show_post_list = $wpbookAdminOptions['show_recent_post_list'];
$recent_post_list_amount= $wpbookAdminOptions['recent_post_amount'];
$wpbook_show_errors = $wpbookAdminOptions['show_errors'];

  
//write the custom header and footer 
function custom_header_footer($custom_template_header_footer,$date,$time){
  $author = get_the_author();
  $category = get_the_category();
  $date = get_the_time($date);
  $time = get_the_time($time);
  $permalink = '<a href="' . get_permalink() . '">permalink</a>';
  $posttags_link = get_the_tag_list(); 
  if ($posttags_link) {$posttags_link_data = get_the_tag_list('',', ', ''); }
  else { $posttags_link_data = "no tags";}
    
  $postcategory_link = get_the_category_list(); 
  if ($posttags_link) {$postcategory_link_data = get_the_category_list(','); }
  else { $postcategory_link_data = "no categories";}
  $posttags = get_the_tags();  
  if ($posttags) {
    $tag_count = count($posttags);
    $i = 0;
    foreach($posttags as $tags) {
      $i++;
      $write_tags .= $tags->name ;
      if($i<$tag_count){
        $write_tags .= ', ';
      }
    } 
  }
  else {$write_tags  = "no tags";}
    
  $postcategory = get_the_category();
  if ($postcategory) {
    $category_count = count($postcategory);
    $i = 0;
    foreach($postcategory as $category) {
      $i++;
      $write_category .= $category->name ;
      if($i<$category_count){
        $write_category .= ', ';
      }
    } 
  }
  else {$write_category  = "no categories";}
  
  $custom_template_header_footer = str_replace("%author%", "$author", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%category%", "$write_category", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%category_link%", "$postcategory_link_data", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%time%", "$time", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%date%", "$date", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%tags%", "$write_tags", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%tag_link%", "$posttags_link_data", "$custom_template_header_footer");
  $custom_template_header_footer = str_replace("%permalink%","$permalink","$custom_template_header_footer");
  
  return $custom_template_header_footer;
}  // end function custom_header/footer
  
?>



