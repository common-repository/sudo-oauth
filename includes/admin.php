<?php
add_action('admin_menu', 'sudooauth_create_menu');

function sudooauth_create_menu() {
        add_menu_page('Sudo Oauth Plugin Settings', 'Sudo Oauth Settings', 'administrator', __FILE__, 'sudooauth_settings_page',plugins_url('sudo-oauth/icon.png'), 100);
        add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_name' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_pwd' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_host' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_multicat' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_limitpost' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_uploadfiles' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_activeplugin' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_link_number' );
    register_setting( 'sudooauth-settings-group', 'sudooauth_option_nofolow_number' );
}

function sudooauth_settings_page() {   
?>
<div class="wrap">
<h2><?php _e('Cấu hình Sudo Oauth'); ?></h2>
<p><?php _e('Nhập thông tin client name & key được cấp bởi chủ hệ thống để kết nối website. Liên hệ caotu@sudo.vn để được hỗ trợ.'); ?></p>
<?php if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Mọi thông tin cài đặt đã được lưu') ?></strong></p>
    </div>
<?php } ?>
<form method="post" action="options.php">
    <?php settings_fields( 'sudooauth-settings-group' ); ?>
    <table class="form-table">
      <tr>
            <th scope="row" class="check_plugin"><label for="sudooauth_option_activeplugin"><?php _e('Kích hoạt plugin'); ?></label></th>
            <td>
                <input type="checkbox" id="sudooauth_option_activeplugin" name="sudooauth_option_activeplugin" value="1"<?php echo get_option('sudooauth_option_activeplugin') == '1' ? ' checked="checked"' : ''; ?> /> <?php _e('Đồng ý (bỏ tick để không kích hoạt)'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_name"><?php _e('Client name'); ?></label></th>
            <td><input type="text" id="sudooauth_option_name" name="sudooauth_option_name" value="<?php echo get_option('sudooauth_option_name') != '' ? get_option('sudooauth_option_name') : '';?>" class="regular-text"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_pwd"><?php _e('Client key'); ?></label></th>
            <td><input type="text" id="sudooauth_option_pwd" name="sudooauth_option_pwd" value="<?php echo get_option('sudooauth_option_pwd') !='' ? get_option('sudooauth_option_pwd') : '';?>" class="regular-text"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_host"><?php _e('Host'); ?></label></th>
            <td><input type="text" id="sudooauth_option_host" name="sudooauth_option_host" value="<?php echo get_option('sudooauth_option_host') != '' ? get_option('sudooauth_option_host') : 'http://id.sudo.vn';?>" class="regular-text"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_uploadfiles"><?php _e('Cho phép tải ảnh'); ?></label></th>
            <td>
                <input type="checkbox" id="sudooauth_option_uploadfiles" name="sudooauth_option_uploadfiles" value="1"<?php echo get_option('sudooauth_option_uploadfiles') == '1' ? ' checked="checked"' : ''; ?> /> <?php _e('Đồng ý (bỏ tick để không kích hoạt)'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_limitpost"><?php _e('Số lượng bài được đăng / ngày'); ?></label></th>
            <td>
              <div class="number-input">
                <span class="sub-number">-</span>
                <input type="text" min="1" max="20" id="sudooauth_option_limitpost" name="sudooauth_option_limitpost" value="<?php echo get_option('sudooauth_option_limitpost') != '' ? get_option('sudooauth_option_limitpost') : '1'; ?>" />
                <span class="add-number">+</span>
              </td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_multicat"><?php _e('Danh mục được phép đăng bài'); ?></label></th>
             <td>
             <?php
             $walker = new Sudo_Walker_Category_Checklist();
             $settings = get_option('sudooauth_option_multicat');
             if ( isset( $settings) && is_array( $settings) )
    				$selected = $settings;
    			else
    				$selected = array();
             ?>
                <div id="side-sortables" class="metabox-holder" style="float:left; padding:5px;">
       				<div class="postbox">
       					<h3 class="hndle"><span><?php _e('Nhấp để chọn'); ?></span></h3>
       
       	            <div class="inside" style="padding:0 10px;">
       						<div class="taxonomydiv">
       							<div id="id-all" class="tabs-panel tabs-panel-active">
       								<ul class="categorychecklist form-no-clear">
       								<?php
       									wp_list_categories(
       										array(
       										'selected_cats'  => $selected,
       										'options_name'   => 'sudooauth_option_multicat',
       										'hide_empty'     => 0,
       										'title_li'       => '',
       										'walker'         => $walker
       										)
       									);
       								?>
       	                     </ul>
       							</div>
       						</div>
       					</div>
       				</div>
       			</div>
             </td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_link_number"><?php _e('Số link tối đa trong bài'); ?></label></th>
            <td>
              <div class="number-input">
                <span class="sub-number">-</span>
                <input type="text" min="1" id="sudooauth_option_link_number" name="sudooauth_option_link_number" value="<?php echo get_option('sudooauth_option_link_number') != '' ? get_option('sudooauth_option_link_number') : '3'; ?>" />
                <span class="add-number">+</span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sudooauth_option_nofolow_number"><?php _e('Số lượng link nofollow'); ?></label></th>
            <td>
              <div class="number-input">
                <span class="sub-number">-</span>
                <input type="text" min="1" id="sudooauth_option_nofolow_number" name="sudooauth_option_nofolow_number" value="<?php echo get_option('sudooauth_option_nofolow_number') != '' ? get_option('sudooauth_option_nofolow_number') : '1'; ?>" />
                <span class="add-number">+</span>
              </div>
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
</div>
<?php 
}