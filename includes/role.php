<?php
$sudooauth_role = get_role( 'sudooauth_author' );
if(!$sudooauth_role) {
    add_role(
        'sudooauth_author',
        __( 'SudoOauth Author' ),
        array(
            'read'         => true,
            'edit_posts'   => true,
            'edit_published_posts' => true,
            'delete_posts' => false,
            'publish_posts' => true
        )
    );
}
$sudooauth_author = get_role( 'sudooauth_author' );
//$sudooauth_author->add_cap( 'edit_published_posts' ); 
$sudoautho_capabilities = $sudooauth_author->capabilities;
if(get_option('sudooauth_option_uploadfiles') == '1') {
    if(!array_key_exists('upload_files',$sudoautho_capabilities)) {
        if(!$sudoautho_capabilities['upload_files']) {
            function add_sudooauth_caps() {
                $role = get_role( 'sudooauth_author' );
                $role->add_cap( 'upload_files' ); 
            }
            add_action( 'admin_init', 'add_sudooauth_caps');
        }
    }
}else {
    if(array_key_exists('upload_files',$sudoautho_capabilities)) {
        if($sudoautho_capabilities['upload_files']) {
            function add_sudooauth_caps() {
                $role = get_role( 'sudooauth_author' );
                $role->remove_cap( 'upload_files' );
            }
            add_action( 'admin_init', 'add_sudooauth_caps');
        }
    }
}