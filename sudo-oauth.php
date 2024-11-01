<?php
/**
 * Plugin Name: Sudo Oauth
 * Plugin URI: http://id.sudo.vn
 * Description: Plugin support to connect to ID Sudo system - a management account system. If you want to build a management account system for SEO, Manager staff please contact me.
 * Author: caotu
 * Version: 2.0.5
 * Author URI: http://sudo.vn
*/

// Plugin Folder Path
if ( ! defined( 'SUDOOAUTH_PLUGIN_DIR' ) ) {
	define( 'SUDOOAUTH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Root File
if ( ! defined( 'SUDOOAUTH_PLUGIN_FILE' ) ) {
	define( 'SUDOOAUTH_PLUGIN_FILE', __FILE__ );
}

require_once SUDOOAUTH_PLUGIN_DIR . 'classes/sudo-walker.php';
require_once SUDOOAUTH_PLUGIN_DIR . 'classes/sudo-datetime.php';
require_once SUDOOAUTH_PLUGIN_DIR . 'classes/sudo-rewrite.php';
if (is_admin()) {
    require_once SUDOOAUTH_PLUGIN_DIR . 'includes/data.php';
    require_once SUDOOAUTH_PLUGIN_DIR . 'includes/admin.php';
    require_once SUDOOAUTH_PLUGIN_DIR . 'includes/role.php';
}

// Disable change password, email
if ( is_admin() )
    add_action( 'init', 'disable_password_fields', 10 );
function disable_password_fields() {
    if ( ! current_user_can( 'administrator' ) ) {
        $current_user = wp_get_current_user();
        if(strpos($current_user->user_email,'@sudo.vn')) {
            $show_password_fields = add_filter( 'show_password_fields', '__return_false' );
        }
    }	
}

add_action( 'user_profile_update_errors', 'prevent_email_change', 10, 3 );
function prevent_email_change( $errors, $update, $user ) {
    $old = get_user_by('id', $user->ID);
    if( $user->user_email != $old->user_email )
        $user->user_email = $old->user_email;
}

// Restrict categories
add_filter( 'list_terms_exclusions', 'sudo_exclusions_terms' );
function sudo_exclusions_terms() {
   $excluded = '';
   $current_user = wp_get_current_user();
   if(current_user_can('sudooauth_author')):
     if(strpos($current_user->user_email,'@sudo.vn')) {
        $multicat_settings = get_option('sudooauth_option_multicat');
        if ( $multicat_settings != false ) {
           $str_cat_list = '';
           foreach($multicat_settings as $value) {
              $str_cat_list .= $value.',';
           }
           $str_cat_list = rtrim($str_cat_list,',');
           $excluded = " AND ( t.term_id IN ( $str_cat_list ) OR tt.taxonomy NOT IN ( 'category' ) )";
        }
     }
    endif;
   return $excluded;
}

// Restrict post per day
add_action( 'admin_init', 'sudo_post_per_day_limit' );
function sudo_post_per_day_limit() {
   $current_user = wp_get_current_user();
   if(strpos($current_user->user_email,'@sudo.vn')) {
      global $wpdb;
      $tz = new DateTimeZone('Asia/Bangkok');
      $time_current_sv = new SudoDateTime();
      $time_current_sv_str = $time_current_sv->format('Y-m-d H:i:s');
      $time_current_sv_int = $time_current_sv->getTimestamp();
      
      $time_current_sv->setTimeZone($tz);
      $time_current_tz_str = $time_current_sv->format('Y-m-d H:i:s');
      $time_current_tz = new SudoDateTime($time_current_tz_str);
      $time_current_tz_int = $time_current_tz->getTimestamp();
      
      $time_start_tz_str = $time_current_sv->format('Y-m-d 00:00:01');
      $time_start_tz = new SudoDateTime($time_start_tz_str);
      $time_start_tz_int = $time_start_tz->getTimestamp();
      
      $time_start_sv_int = $time_current_sv_int - $time_current_tz_int + $time_start_tz_int;
      $time_start_sv_str = date('Y-m-d H:i:s',$time_start_sv_int);
      $time_start_sv = new SudoDateTime($time_start_sv_str);
      
      $count_post_today = $wpdb->get_var("SELECT COUNT(ID)
                                          FROM $wpdb->posts 
                                          WHERE post_status = 'publish'
                                          AND post_author = $current_user->ID 
                                          AND post_type NOT IN('attachment','revision')
                                          AND post_date_gmt >= '$time_start_sv_str'");
                                          
      if($count_post_today >= get_option('sudooauth_option_limitpost',1)) {
         global $pagenow;
         /* Check current admin page. */
         if($pagenow == 'post-new.php'){
            echo '<meta http-equiv="Content-Type" content="text/html"; charset="utf-8">';
            echo "<center>";
            echo '<br /><br />Giới hạn '.get_option('sudooauth_option_limitpost',1).' bài 1 ngày.<br /><br /> Hôm nay bạn đã đăng đủ bài trên trang này rồi.<br /><br /> Vui lòng quay lại vào ngày mai, xin cám ơn!';
            echo "</center>";
            exit();
         }
      }
   }
}

// Restrict 5 backlink AND Random backlink follow - nofollow (30% nofollow)
add_filter( 'wp_insert_post_data' , 'sudooauth_set_backlink' , '99', 2 );
function sudooauth_set_backlink( $data ) {    
    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    $link_number = get_option('sudooauth_option_link_number',1);
    $nofollow_number = get_option('sudooauth_option_nofolow_number',1);
    if(preg_match_all("/$regexp/siU", $data['post_content'], $matches, PREG_SET_ORDER)) {
        /*
        array(4) {
            [0]=> '<a href="link">text</a>'
            [1]=> ''
            [2]=> '\'
            [3]=> 'text'
          }
        */
        $total_link = count($matches);
        if($total_link == 0) {
            return $data;
        }
        //Restrict $link_number backlink
        if($total_link > $link_number) {
            foreach($matches as $key=>$value) {
                if($key >= $link_number) {
                    $data['post_content'] = str_replace($value[0],$value[3],$data['post_content']);
                    unset($matches[$key]);
                }
            }
        }
            
        $dom = new DOMDocument();
        $dom->loadHTML($data['post_content']);
        $xpath = new DOMXPath($dom);
        $hrefs = $xpath->query("//a");
        $fil_nfl = 0;
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            //if($href->hasAttribute('rel')) echo $href->getAttribute('rel');
            if($href->getAttribute('rel') == 'nofollow' || $href->getAttribute('rel') == '\"nofollow\"') {
                $fil_nfl++;
            }
        }
        
        // if(($hrefs->length == 5 && $fil_nfl != 2) || ($hrefs->length == 4 && $fil_nfl != 1) ||  ($hrefs->length == 3 && $fil_nfl != 1)) {
        // return $hrefs->length;
        if(($hrefs->length <= $link_number && $fil_nfl != $nofollow_number)) {
            //Remove all rel
            $data['post_content'] = preg_replace('/(<[^>]+) rel=".*?"/i', '$1', $data['post_content']);
            
            //Random and add rel nofollow
            // $slot1 = rand(0,3);
            // $slot2 = rand($slot1,4);
            foreach($matches as $key=>$value) {
                if($key < $nofollow_number) {
                    $nfl_add = str_replace('href', 'rel="nofollow" href',$value[0]);
                    $data['post_content'] = str_replace($value[0],$nfl_add,$data['post_content']);
                }else {
                    $nfl_remove = str_replace('nofollow', '',$value[0]);
                    $data['post_content'] = str_replace($value[0],$nfl_remove,$data['post_content']);
                }
            }   
        }
    }
    return $data;
}

//show author biographical info
add_filter( 'the_content', 'sudooauth_show_bio' );
function sudooauth_show_bio($content) {
    if(is_single()) {
        $author_id = get_the_author_meta('ID');
        $author_email = get_the_author_meta('email');
        if(strpos($author_email,'@sudo.vn')) {
            $author_name = get_the_author();
            $author_bio = get_the_author_meta('description');
            $content .= '<div id="sudo-oauth-bio">
                <div class="sudo-oauth-bio-name">Tác giả: <span>'.$author_name.'</span></div>
                <div class="sudo-oauth-bio-des">'.nl2br($author_bio).'</div>
            </div>
            <style>
            #sudo-oauth-bio {
                overflow: hidden;
                width: 100%;
                margin: 15px 0px 20px 0px;
                padding: 10px 0px;
                border-top: 3px solid #ccc;
                border-bottom: 3px solid #ccc;
            }
            .sudo-oauth-bio-name span {
                font-weight: bold;
            }
            .sudo-oauth-bio-des {
                margin-top: 10px;
                line-height: 1.3em;
            }
            </style>';
        }
    }
    return $content;
}

function optionExists($option_name) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option_name));
    if (is_object($row)) {
        return true;
    }
    return false;
}

// decative or active plugin
function deactivate_plugin_conditional() {
    if(optionExists('sudooauth_option_activeplugin') == FALSE){
        update_option('sudooauth_option_activeplugin','1');
    }
    if(optionExists('sudooauth_option_uploadfiles') == FALSE){
        update_option('sudooauth_option_uploadfiles','1');
    }
    if(get_option('sudooauth_option_activeplugin') != '1' ){
        if(current_user_can('sudooauth_author')):
          global $pagenow;
             /* Check current admin page. */
             if($pagenow == 'post-new.php'){ 
              wp_logout();
              echo '<meta http-equiv="Content-Type" content="text/html"; charset="utf-8">';
                echo "<center>";
                echo '<br /><br />Sudo Oauth hiện đang ngừng sử dụng với website này.<br /><br />Hệ thống tự động trở về trang chủ sau 5 giây!</a>';
                echo "</center>";
                header( "refresh:5;url='".home_url()."'" );
            exit();    
            }
        endif;
    }
  
}
add_action( 'admin_init', 'deactivate_plugin_conditional' );

function add_stylesheet_plugin_to_admin() {
  wp_enqueue_style( 'sudo-style', plugin_dir_url( __FILE__ ). 'lib/style.css' );
}
add_action( 'admin_enqueue_scripts', 'add_stylesheet_plugin_to_admin' );

function add_jquery_plugin_to_admin() {
  wp_enqueue_script( 'sudo-scripts', plugin_dir_url( __FILE__ ). 'lib/main.js' );
}
add_action( 'admin_enqueue_scripts', 'add_jquery_plugin_to_admin' );  