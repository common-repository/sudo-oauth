<?php
function sudooauth_create_table () {
   global $wpdb;
   $table_name = $wpdb->prefix.'sudo_users';
   if($wpdb->get_var("SHOW TABLEs LIKE $table_name") != $table_name) {
      $sql = "CREATE TABLE ".$table_name."(               
               use_id INTEGER(11) UNSIGNED AUTO_INCREMENT,
               use_email VARCHAR(255) NOT NULL,
               use_pass VARCHAR(255) NOT NULL,
               use_time INTEGER(11) NOT NULL,
               PRIMARY KEY (use_id)
            )";
      require_once(ABSPATH.'wp-admin/includes/upgrade.php');
      dbDelta($sql);
   }
}
register_activation_hook(SUDOOAUTH_PLUGIN_FILE,'sudooauth_create_table');