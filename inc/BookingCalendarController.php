<?php
class BookingCalendarController extends Admin
{
    // Class methods here

    protected $baseUrl;
    /** @var Booking $booking */
    protected $booking;

    public function __construct(string $baseUrl, Booking $booking) {
        //display PHP error log
        error_log( 'Hook activated - BookingCalendar construct');
        $this->baseUrl = $baseUrl;
        $this->booking = $booking;

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('wp_ajax_add_booking', [$this, 'add_booking']);
        add_action('wp_ajax_update_booking', [$this, 'update_booking']);
        add_action('wp_ajax_delete_booking', [$this, 'delete_booking']);
        add_action('admin_footer', [$this, 'print_availability_modal']);
    }



  
    public function add_booking() {
        //display PHP error log
        error_log( 'Hook activated - add booking');

        // Check for nonce for security
        check_ajax_referer('mybookingplugin_nonce', 'security');

        // Simplified example, sanitize and validate your inputs
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
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }


    


    public function update_booking() {
        // Display PHP error log
        error_log( 'Hook activated - update booking');

        // Nonce verification
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'update_booking_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
            return;
        }

        // Sanitize input data
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $service = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $client_name = sanitize_text_field($_POST['client_name']);
        $client_email = sanitize_email($_POST['client_email']);

        try {
            $this->booking->update($booking_id, [ // Data to update
                'date' => $date,
                'time' => $time,
                'service_id' => $service,
                'client_name' => $client_name,
                'client_email' => $client_email,
            ]);
            wp_send_json_success( [ 'message' => 'Booking updated successfully' ] );
        } catch (RuntimeException $e) {
            wp_send_json_error( [ 'message' => $e->getMessage() ] );
        } finally {
            wp_die();
        }
    }


    public function delete_booking() {
        //display PHP error log
        error_log( 'Hook activated - delete booking');

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'delete_booking_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
            return;
        }

        $bookingId = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        if ($bookingId === null) {
            wp_send_json_error( [ 'message' => 'The "booking_id" field is required' ] );
            wp_die();
        }

        try {
            $this->booking->delete($bookingId);
            wp_send_json_success( [ 'message' => 'Booking deleted successfully' ] );
        } catch (RuntimeException $e) {
            wp_send_json_error( [ 'message' => $e->getMessage() ] );
        } finally {
            wp_die();
        }
    }
    

    public function list_available_bookings()
    {
        global $wpdb;

        // Get start date and end date from AJAX request (sanitize these inputs!)
        $startDate = $_POST['start'];
        $endDate = $_POST['end'];

        try {
            $results = $this->booking->getAvailable($startDate, $endDate);
            // Transform data into FullCalendar event array format
            $events = array_map(function($item) {
                return [
                    'title' => 'Available',
                    'start' => $item->date . 'T' . $item->time,
                ];
            }, $results);
            wp_send_json_success($events);
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            wp_die();
        }

    }

    public function print_availability_modal() {
        error_log( 'Hook activated - availability modal');
        // Check if we're on the correct admin page before adding the modal
        $current_screen = get_current_screen();
        if ('booking-settings_page_set_availability' !== $current_screen->id) {
            return;
        }

        ?>
        <div id="booking-modal" style="display: none;">
            <h2>Add Booking Time <span id="selected-date"></span></h2>
            <input  id="time" type="text" name="time" value="" />
            <input  id="date" type="hidden" name="date" value="" />
        </div>
        <?php
    }


    public function admin_enqueue_scripts($hook) {


        // Enqueue FullCalendar script from CDN
        wp_register_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js', array('jquery'), null, true);
    
        // Enqueue your custom calendar.js script
        wp_register_script('mybookingplugin-calendar-js', plugins_url('assets/js/calendar.js', __DIR__), array('jquery', 'fullcalendar', 'jquery-ui-dialog'), '1', true);
        
        wp_register_script('timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery'), null, true);

        wp_register_style('timepicker-style', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css', [], null);

        wp_register_style('mybookingstyles', $this->baseUrl.'assets/css/styles.css', [], null);
        // Localize the script with nonce and ajax URL
        wp_localize_script(
            'mybookingplugin-calendar-js', // The handle of the enqueued script
            'MyBookingPluginAjax',      // The name of the JavaScript object that will be created in the global scope
            array(                      // The array of data that will be made available to the script
                'ajaxurl' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('mybookingplugin_nonce')
                
            )
        );

        
        // Check to make sure we're on the correct admin page
        if ('booking-settings_page_set_availability' !== $hook) {
            return;
        }

        wp_enqueue_script('fullcalendar');
        wp_enqueue_script('mybookingplugin-calendar-js');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('timepicker-style');
        wp_enqueue_script('timepicker');
        wp_enqueue_style('mybookingstyles');
    }
    
    
}

?>