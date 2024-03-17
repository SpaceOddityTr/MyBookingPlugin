<?php
/**
 * Plugin Name: MyBookingPlugin
 * Description: A custom booking system for WordPress.
 * Version: 1.0
 * Author: Your Name
 */

// Activation Hook: Runs when the plugin is activated
register_activation_hook(__FILE__, 'Activate');
function Activate() {
    // Display PHP error log for debugging (remove in production)
    error_log('Hook activated - activate');

    global $wpdb;
    $table_name = $wpdb->prefix . 'bookings';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL to create the 'bookings' table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        time time NOT NULL,
        service_id mediumint(9) NOT NULL,
        client_name varchar(255) NOT NULL,
        client_email varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation Hook: Runs when the plugin is deactivated
register_deactivation_hook(__FILE__, 'Deactivate');
function Deactivate() {
    // Display PHP error log for debugging (remove in production)
    error_log('Hook activated - deactivate');
}

// Uninstall Hook: Runs when the plugin is deleted
register_uninstall_hook(__FILE__, 'mybookingplugin_uninstall');
function mybookingplugin_uninstall() {
    // Display PHP error log for debugging (remove in production)
    error_log('Hook activated - Uninstall');

    // Delete plugin settings
    delete_option('my_booking_plugin_settings');

    // Delete database tables
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bookings");
}

// Include plugin class files 
require_once plugin_dir_path(__FILE__) . 'inc/Admin.php';
require_once plugin_dir_path(__FILE__) . 'inc/BookingCalendar.php';
require_once plugin_dir_path(__FILE__) . 'inc/Booking.php';
require_once plugin_dir_path(__FILE__) . 'inc/PublicSide.php';
require_once plugin_dir_path(__FILE__) . 'inc/Email.php';

// Instantiate plugin classes & get the plugin's URL
$url = plugin_dir_url(__FILE__);
$admin = new Admin();
$bookingCalendar = new BookingCalendar($url);
$booking = new Booking();
$publicSide = new PublicSide($BookingCalendar, $Booking);
$eMail = new EMail();

// Shortcode to display the booking form
function my_booking_shortcode_callback() {
    global $my_booking_plugin_booking; 
    $my_booking_plugin_booking->display_booking_form_shortcode();
}
add_shortcode('my_booking_form', 'my_booking_shortcode_callback');

?>