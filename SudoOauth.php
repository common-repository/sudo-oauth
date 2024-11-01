<?php
$client_id = get_option('sudooauth_option_name');
$client_key = get_option('sudooauth_option_pwd');
$host_id = get_option('sudooauth_option_host');
if(!$client_id || !$client_key || $client_id == '' || $client_key == '')
    die('Bạn chưa nhập thông tin Client mà ID đã cấp !');
$access_code = $_REQUEST['access_code'];
if(isset($access_code) && $access_code != '') {
    $token_url = $host_id.'/oauth/accessCode/'.$access_code.'';
    //try curl
    if(function_exists('curl_version')) {
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "{$client_id}:{$client_key}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respone_data = curl_exec($ch);
        $respone_data = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $respone_data);
        if($respone_data) {
            $info = json_decode(base64_decode($respone_data),true);
        }else {
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic " . base64_encode("$client_id:$client_key")
                )
            ));
            $respone_data = file_get_contents($token_url, false, $context);
            $respone_data = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $respone_data);
            $info = json_decode(base64_decode($respone_data),true);
        }        
    }else {
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$client_id:$client_key")
            )
        ));
        $respone_data = file_get_contents($token_url, false, $context);
        $respone_data = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $respone_data);
        $info = json_decode(base64_decode($respone_data),true);
    }
        
    if($info['status'] == 1) {
        global $wpdb;
        $user = array();
        $user['email'] = $info['user']['email'];
        $user['name'] = substr($user['email'],0,strpos($user['email'],'@'));
        $user['email'] = $user['name'].'@sudo.vn';

        $check_user = $wpdb->get_results('SELECT ID FROM '.$wpdb->prefix.'users WHERE user_email = "'.$user['email'].'"',ARRAY_A);
        if($check_user) {
            $check_sudo_user = $wpdb->query('SELECT use_id FROM '.$wpdb->prefix.'sudo_users WHERE use_email = "'.$user['email'].'"');
            if($check_sudo_user) {
                //Update _sudo_access
                $user_sudo_access = get_user_meta($check_user[0]['ID'],'_sudo_access');
                if(is_array($user_sudo_access)) $user_sudo_access = $user_sudo_access[0];
                if($user_sudo_access != get_option('sudooauth_option_cat')) {
                    if( update_user_meta( $check_user[0]['ID'], '_sudo_access', get_option('sudooauth_option_cat') ) != false) {
                        $sudo_user = $wpdb->get_row('SELECT use_id,use_pass FROM '.$wpdb->prefix.'sudo_users WHERE use_email = "'.$user['email'].'" ORDER BY use_id DESC LIMIT 1',ARRAY_A);
                        $user['password'] = md5($sudo_user['use_pass'].$info['user']['id']);
                  
                        $user_signon = wp_signon( array('user_login'=>$user['name'],'user_password'=>$user['password'],'remember'=>false), false );
                        if ( is_wp_error($user_signon) ) {
                            echo $user_signon->get_error_message();
                            echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                            die('Không thể đăng nhập');
                        }else {
                            wp_set_current_user( $user_signon->ID );
                            wp_set_auth_cookie( $user_signon->ID );
                            $u_id = wp_update_user( array( 'ID' => $user_signon->ID, 'role' => 'sudooauth_author' ) );
                            wp_redirect( ''.admin_url().'post-new.php' );
                            exit;
                        }
                    }else {
                        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                        die('Không thể hạn chế được danh mục đăng bài cho thành viên này');
                    }
                }else {
                    $sudo_user = $wpdb->get_row('SELECT use_id,use_pass FROM '.$wpdb->prefix.'sudo_users WHERE use_email = "'.$user['email'].'" ORDER BY use_id DESC LIMIT 1',ARRAY_A);
                    $user['password'] = md5($sudo_user['use_pass'].$info['user']['id']);
               
                    $user_signon = wp_signon( array('user_login'=>$user['name'],'user_password'=>$user['password'],'remember'=>false), false );
                    if ( is_wp_error($user_signon) ) {
                        echo $user_signon->get_error_message();
                        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                        die('Không thể đăng nhập');
                    }else {
                        wp_set_current_user( $user_signon->ID );
                        wp_set_auth_cookie( $user_signon->ID );
                        $u_id = wp_update_user( array( 'ID' => $user_signon->ID, 'role' => 'sudooauth_author' ) );
                        wp_redirect( ''.admin_url().'post-new.php' );
                        exit;
                    }
                } 
            }else {
                echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                die('Tài khoản này đã có trước khi kết nối với Sudo ID !');
            }
        }else {
            $sudo_pass = rand(111111,999999);
            $user['password'] = md5($sudo_pass.$info['user']['id']);
            $u_id = wp_create_user($user['name'],$user['password'],$user['email']);
            if(is_object($u_id)) {
                $err = $u_id->errors;
                $existing_user_email = $err['existing_user_email'][0];
                $existing_user_login = $err['existing_user_login'][0];
                echo $existing_user_email.'-'.$existing_user_login;die;
            }else {
                //Update _sudo_access
                if( update_user_meta( $u_id, '_sudo_access', get_option('sudooauth_option_cat') ) != false) {
                    //update role
                    $u_id = wp_update_user( array( 'ID' => $u_id, 'role' => 'sudooauth_author' ) );
                    if(is_object($u_id)) {
                        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                        die('Không thể set quyền cho user');   
                    }
                   
                    $wpdb->insert( 
                   	    ''.$wpdb->prefix.'sudo_users', 
                   	    array( 
                            'use_email' => $user['email'], 
                            'use_pass' => $sudo_pass,
                            'use_time' => time()
                   	    ), 
                   	    array(
                   		   '%s','%s','%d' 
                   	    )
                    );
                   
                    $user_signon = wp_signon( array('user_login'=>$user['name'],'user_password'=>$user['password'],'remember'=>false), false );
                    if ( is_wp_error($user_signon) ) {
                        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                        echo $user_signon->get_error_message();
                        die('Không thể đăng nhập');
                    }else {
                        wp_set_current_user( $user_signon->ID );
                        wp_set_auth_cookie( $user_signon->ID );
                        wp_redirect( ''.admin_url().'post-new.php' );
                        exit;
                    }
                }else {
                    echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
                    die('Không thể hạn chế được danh mục đăng bài cho thành viên này');
                }
            }
        }
    }else {
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        echo $info['message'];
        echo '<br />';
        die('Lỗi kết nối !');
    }
}else {
    echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
    die('Không tìm thấy Access Code !');
}
?>