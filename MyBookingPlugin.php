<?php
/**
 * Plugin Name: MyBookingPlugin
 * Description: A custom booking system for WordPress.
 * Version: 1.0
 * Author: Dmitrii Sevbo
 *
 * @package MyBookingPlugin
 */

// Basic information about the plugin which is used by WordPress to display in the plugin list.
define('MBP_MAIN_FILE', __FILE__); // Defines the main plugin file path.
define('MBP_ROOT_DIR', plugin_dir_path(__FILE__)); // Defines the root directory of the plugin.
define('MBP_ROOT_URL', plugin_dir_url(__FILE__)); // Defines the root URL for the plugin.

// Register function to run when the plugin is activated.
register_activation_hook(__FILE__, 'activate');
function activate() {
    error_log('Hook activated - activate'); // Logs a message indicating the plugin is being activated.
    global $wpdb; // Global WordPress database access object.
    $table_name = $wpdb->prefix . 'bookings'; // Defines the table name with WP prefix.
    $charset_collate = $wpdb->get_charset_collate(); // Gets the current charset and collation for the database.

    // SQL to create a new table for bookings if it doesn't already exist.
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `date` DATE NOT NULL,
        `time` TIME NOT NULL,
        `service_name` VARCHAR(255) NULL,
        `client_name` VARCHAR(255) NULL,
        `client_email` VARCHAR(255) NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); // WordPress function for database operations.
    dbDelta($sql); // Executes the SQL statement to create the table.
}

// Register function to run when the plugin is deactivated.
register_deactivation_hook(__FILE__, 'deactivate');
function deactivate() {
    error_log('Hook activated - deactivate'); // Logs a message indicating the plugin is being deactivated.

    global $wpdb; 
    $table_name = $wpdb->prefix . 'bookings';

    // DELETE booking data
    $wpdb->query("DELETE FROM $table_name");
}

// Register function to run when the plugin is uninstalled.
register_uninstall_hook(__FILE__, 'mybookingplugin_uninstall');
function mybookingplugin_uninstall() {
    error_log('Hook activated - Uninstall'); // Logs a message indicating the plugin is being uninstalled.

    delete_option('my_booking_plugin_settings'); // Deletes plugin settings from the database.

    global $wpdb; // Global WordPress database access object.
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bookings"); // Drops the bookings table.
}

// Set the root directory and URL for the plugin.
$rootDir = MBP_ROOT_DIR;
$url = MBP_ROOT_URL;

// Include necessary class files from the inc directory.
require_once "{$rootDir}inc/Admin.php";
require_once "{$rootDir}inc/BookingCalendarController.php";
require_once "{$rootDir}inc/Booking.php";
require_once "{$rootDir}inc/PublicSide.php";
require_once "{$rootDir}inc/Email.php";
require_once "{$rootDir}inc/View.php";

// Instantiate plugin classes.
$booking = new Booking();
$admin = new Admin($rootDir, $booking);
$bookingCalendar = new BookingCalendarController($url, $booking);
$publicSide = new PublicSide('MyBookingPlugin', '1.0', $booking, $url, $rootDir);
$eMail = new Email($booking);

// Register a shortcode for displaying the booking form.
add_shortcode('my_booking_form', [$publicSide, 'renderShortcode']);