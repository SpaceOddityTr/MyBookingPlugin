<?php
/**
 * Plugin Name: MyBookingPlugin
 * Description: A custom booking system for WordPress.
 * Version: 1.0
 * Author: Your Name
 */


 register_activation_hook(__FILE__, 'Activate');
 function Activate() {
  //display PHP error log
  error_log( 'Hook activated - activate');

  global $wpdb;
  $table_name = $wpdb->prefix . 'bookings';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      date date NOT NULL,
      time time NOT NULL,
      service_id mediumint(9) NOT NULL,
      client_name varchar(255) NOT NULL,
      client_email varchar(255) NOT NULL,
      PRIMARY KEY  (id)
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
 

 register_deactivation_hook(__FILE__, 'Deactivate');
 function Deactivate() {
  //display PHP error log
  error_log( 'Hook activated - deactivate');

   // Deactivation code here (e.g., clean up tasks).
 }

 require_once plugin_dir_path(__FILE__) . 'inc' . DIRECTORY_SEPARATOR . 'Admin.php';
 require_once plugin_dir_path(__FILE__) . 'inc' . DIRECTORY_SEPARATOR . 'BookingCalendar.php';


 $admin = new Admin();
 $BookingCalendar = new BookingCalendar();

?>