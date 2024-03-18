<?php
/**
 * Extends the Admin class to manage the booking calendar within the WordPress admin area.
 *
 * @package MyBookingPlugin
 */
class BookingCalendarController extends Admin
{
    protected $baseUrl; // Base URL for the plugin, used to include assets like JS and CSS files.
    protected $booking; // Instance of Booking model for database operations.

    // Constructor initializes the controller with essential properties and hooks.
    public function __construct(string $baseUrl, Booking $booking)
    {
        error_log('Hook activated - BookingCalendar construct');
        $this->baseUrl = $baseUrl;
        $this->booking = $booking;

        // WordPress actions to enqueue scripts, handle AJAX requests, and print modals.
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('wp_ajax_add_booking', [$this, 'add_booking']);
        add_action('wp_ajax_update_booking', [$this, 'update_booking']);
        add_action('wp_ajax_delete_booking', [$this, 'delete_booking']);
        add_action('admin_footer', [$this, 'print_availability_modal']);
    }

    // Handles AJAX request for adding a new booking.
    public function add_booking()
    {
        error_log('Hook activated - add booking');
        check_ajax_referer('mybookingplugin_nonce', 'security');

        // Simplified example; always sanitize and validate your inputs.
        $date = $_POST['date'];
        $time = $_POST['time'];

        try {
            $this->booking->create([
                'date' => $date,
                'time' => $time,
            ]);
            wp_send_json_success(['message' => 'Booking added successfully']);
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            wp_die(); // Terminate AJAX request properly.
        }
    }

    // Handles AJAX request for updating an existing booking.
    public function update_booking()
    {
        error_log('Hook activated - update booking');
        // Nonce verification for security.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_booking_nonce')) {
            wp_send_json_error(['message' => 'Nonce verification failed']);
            return;
        }

        // Sanitization and validation of input data.
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $service = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $client_name = sanitize_text_field($_POST['client_name']);
        $client_email = sanitize_email($_POST['client_email']);

        try {
            $this->booking->update($booking_id, [
                'date' => $date,
                'time' => $time,
                'service_id' => $service,
                'client_name' => $client_name,
                'client_email' => $client_email,
            ]);
            wp_send_json_success(['message' => 'Booking updated successfully']);
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            wp_die();
        }
    }

    // Handles AJAX request for deleting a booking.
    public function delete_booking()
    {
        error_log('Hook activated - delete booking');
        // Nonce verification for security.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_booking_nonce')) {
            wp_send_json_error(['message' => 'Nonce verification failed']);
            return;
        }

        $bookingId = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        if ($bookingId === null) {
            wp_send_json_error(['message' => 'The "booking_id" field is required']);
            wp_die();
        }

        try {
            $this->booking->delete($bookingId);
            wp_send_json_success(['message' => 'Booking deleted successfully']);
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            wp_die();
        }
    }

    // Lists available bookings for a specified date range via AJAX request.
    public function list_available_bookings()
    {
        global $wpdb;

        $startDate = $_POST['start']; // Start date from AJAX. Always sanitize these inputs!
        $endDate = $_POST['end'];     // End date from AJAX. Always sanitize these inputs!

        try {
            $results = $this->booking->getAvailable($startDate, $endDate);
            // Format the data for FullCalendar events.
            $events = array_map(function ($item) {
                return [
                    'title' => 'Available',
                    'start' => $item['date'] . 'T' . $item['time'],
                ];
            }, $results);
            wp_send_json_success($events);
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            wp_die();
        }
    }

    // Prints the availability modal HTML in the admin footer on specific admin pages.
    public function print_availability_modal()
    {
        error_log('Hook activated - availability modal');
        $current_screen = get_current_screen();
        if ('booking-settings_page_set_availability' !== $current_screen->id) {
            return;
        }

        // Modal HTML code.
        ?>
        <div id="booking-modal" style="display: none;">
            <h2>Add Booking Time <span id="selected-date"></span></h2>
            <input id="time" type="text" name="time" value="" />
            <input id="date" type="hidden" name="date" value="" />
        </div>
        <?php
    }

    /**
     * Enqueues scripts and styles required for the booking calendar in the WordPress admin.
     * This includes third-party libraries as well as custom scripts and styles.
     *
     * @param string $hook Identifies the current page in the admin. Used to ensure scripts and styles are only included where needed.
     */
    public function admin_enqueue_scripts($hook)
    {

        // Registers the FullCalendar script from a CDN for use in the admin. This script provides the interactive calendar functionality.
        wp_register_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js', array('jquery'), null, true);

        // Registers a custom JavaScript file that integrates FullCalendar with WordPress-specific logic, like AJAX requests for booking operations.
        wp_register_script('mybookingplugin-calendar-js', plugins_url('assets/js/calendar.js', __DIR__), array('jquery', 'fullcalendar', 'jquery-ui-dialog'), '1', true);

        // Registers a timepicker jQuery plugin script, which enhances time selection inputs with a user-friendly UI.
        wp_register_script('timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery'), null, true);

        // Registers the CSS file for the timepicker plugin to ensure the UI looks as expected.
        wp_register_style('timepicker-style', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css', [], null);

        // Registers the plugin's custom styles, which may include styles for the booking calendar, modal dialog, and other UI components.
        wp_register_style('mybookingstyles', $this->baseUrl.'assets/css/styles.css', [], null);

        // Localizes the custom calendar.js script with necessary data for AJAX requests, including the URL to the admin-ajax.php file and a security nonce.
        wp_localize_script(
            'mybookingplugin-calendar-js', // Handle of the script to localize.
            'MyBookingPluginAjax',         // Name of the JavaScript object that will contain the localized data.
            array(
                'ajaxurl' => admin_url('admin-ajax.php'), // URL for AJAX requests in WordPress admin.
                'security' => wp_create_nonce('mybookingplugin_nonce') // Security nonce for verifying AJAX requests.
            )
        );

        // Checks if the current admin page is the specific page where the booking calendar should be displayed.
        if ('booking-settings_page_set_availability' !== $hook)
        {
            return; // Exits the function early if we're not on the correct page, preventing unnecessary script/style loading.
        }

        // Enqueues the registered scripts and styles, making them active on the page.
        wp_enqueue_script('fullcalendar');
        wp_enqueue_script('mybookingplugin-calendar-js');
        wp_enqueue_style('wp-jquery-ui-dialog'); // Ensures jQuery UI dialog styles are available for modals.
        wp_enqueue_style('timepicker-style');
        wp_enqueue_script('timepicker');
        wp_enqueue_style('mybookingstyles');
    }
}