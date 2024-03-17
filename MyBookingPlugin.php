<?php
/**
 * Plugin Name: MyBookingPlugin
 * Description: A custom booking system for WordPress.
 * Version: 1.0
 * Author: Your Name
 */

 define('MBP_MAIN_FILE', __FILE__);
 define('MBP_ROOT_DIR', plugin_dir_path(__FILE__));
 define('MBP_ROOT_URL', plugin_dir_url(__FILE__));


// Activation Hook: Runs when the plugin is activated
register_activation_hook(__FILE__, 'activate');
function activate() {
    // Display PHP error log for debugging (remove in production)
    error_log('Hook activated - activate');

    global $wpdb;
    $table_name = $wpdb->prefix . 'bookings';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL to create the 'bookings' table
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `date` DATE NOT NULL,
        `time` TIME NOT NULL,
        `service_name` VARCHAR(255) NULL,
        `client_name` VARCHAR(255) NULL,
        `client_email` VARCHAR(255) NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation Hook: Runs when the plugin is deactivated
register_deactivation_hook(__FILE__, 'deactivate');
function deactivate() {
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

$rootDir = MBP_ROOT_DIR;
$url = MBP_ROOT_URL;

// Include plugin class files 
require_once "{$rootDir}inc/Admin.php";
require_once "{$rootDir}inc/BookingCalendarController.php";
require_once "{$rootDir}inc/Booking.php";
require_once "{$rootDir}inc/PublicSide.php";
require_once "{$rootDir}inc/Email.php";
require_once "{$rootDir}inc/View.php";

// Instantiate plugin classes & get the plugin's URL
$booking = new Booking();
$admin = new Admin($rootDir, $booking);
$bookingCalendar = new BookingCalendarController($url, $booking);
$publicSide = new PublicSide('MyBookingPlugin', '1', $booking, $url, $rootDir);
$eMail = new EMail($booking);

add_shortcode('my_booking_form', [$publicSide, 'renderShortcode']);