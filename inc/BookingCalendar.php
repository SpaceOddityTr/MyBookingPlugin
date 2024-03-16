<?php
class BookingCalendar extends Admin {
    // Class methods here

    protected $plugin_base_url;
    public function __construct($plugin_base_url) {
        //display PHP error log
        error_log( 'Hook activated - BookingCalendar construct');
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('wp_ajax_get_all_bookings', [$this, 'get_all_available_bookings']);
        add_action('wp_ajax_add_booking', [$this, 'add_booking']);
        add_action('wp_ajax_update_booking', [$this, 'update_booking']);
        add_action('wp_ajax_delete_booking', [$this, 'delete_booking']);
        add_action('admin_footer', [$this, 'add_availability_modal']);
        $this->plugin_base_url = $plugin_base_url;
    }



  
    public function add_booking() {
        //display PHP error log
        error_log( 'Hook activated - add booking');

        // Check for nonce for security
        check_ajax_referer('mybookingplugin_nonce', 'security');

        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';

        // Simplified example, sanitize and validate your inputs
        $date = $_POST['date'];
        $time = $_POST['time'];


        $result = $wpdb->insert($table_name, [
            'date' => $date,
            'time' => $time,
            'service_id' => 0,
            'client_name' => 0,
            'client_email' => 0,
        ]);

        if ($result) {
            wp_send_json_success(['message' => 'Booking added successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to add booking']);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function get_all_available_bookings() {
        //display PHP error log
        error_log( 'Hook activated - get  all available bookings');

        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings'; // Assuming 'bookings' is your table name
    
        $bookings = $wpdb->get_results("SELECT * FROM $table_name WHERE service_id = 0 and	client_name = 0 and	client_email = 0") ; 
    
        return $bookings;
    }
    


    public function update_booking() {
        //display PHP error log
        error_log( 'Hook activated - update booking');

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'update_booking_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
            return;
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';
    
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        // Add more fields as necessary


        //update service_id	client_name	client_email
        
        $result = $wpdb->update(
            $table_name,
            [ // Data to update
                'date' => $date,
                'time' => $time,
                // ... other fields
            ],
            [ 'ID' => $booking_id ] // Where clause
        );
    
        if ( $result ) {
            wp_send_json_success( [ 'message' => 'Booking updated successfully' ] );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to update booking' ] );
        }
    }

    public function delete_booking() {
        //display PHP error log
        error_log( 'Hook activated - delete booking');

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'delete_booking_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
            return;
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';
    
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
        $result = $wpdb->delete(
            $table_name,
            [ 'ID' => $booking_id ] // Where clause
        );
    
        if ( $result ) {
            wp_send_json_success( [ 'message' => 'Booking deleted successfully' ] );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to delete booking' ] );
        }
    }
    


// Fetch availability data for the calendar
public function get_availability_data() {
    global $wpdb;

    // Get start date and end date from AJAX request (sanitize these inputs!)
    $start_date = $_POST['start'];
    $end_date = $_POST['end'];

    // Modify your query to check for 'booked' or 'available' status as needed
    $results = $wpdb->get_results("
        SELECT date, time 
        FROM {$wpdb->prefix}bookings
        WHERE (status = 'available' OR status IS NULL) 
          AND date BETWEEN '$start_date' AND '$end_date'"
        );

    // Transform data into FullCalendar event array format
    $events = array_map(function($item) {
        return [
            'title' => 'Available', 
            'start' => $item->date . 'T' . $item->time, 
            // Consider adding 'end' or other properties if needed
        ];
    }, $results);

    wp_send_json_success($events);
    wp_die(); // Terminate the request properly 
}

// Add availability function
public function add_availability() {
    // ... Input sanitization, validation ...

    global $wpdb;
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Insert into the database
    $wpdb->insert(
        "{$wpdb->prefix}availability", 
        ['date' => $date, 'time' => $time]
    );

    // ... Handle success/error messages ...
}



public function add_availability_modal() {
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
    
        // Enqueue your custom admin.js script
        wp_register_script('mybookingplugin-calendar-js', plugins_url('assets/js/calendar.js', __DIR__), array('jquery', 'fullcalendar', 'jquery-ui-dialog'), '1', true);
        
        wp_register_script('timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery'), null, true);

        wp_register_style('timepicker-style', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css', [], null);

        wp_register_style('mybookingstyles', $this->plugin_base_url.'assets/css/styles.css', [], null);
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