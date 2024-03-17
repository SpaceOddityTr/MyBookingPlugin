<?php
/**
 * @package MyBookingPlugin
 */
class Booking {
    /**
     * List of valid services offered.
     *
     * @var array
     */
    private $valid_services = ['essential_oils', 'psychosomatics'];

    /**
     * Constructor for the booking class.
     */
    public function __construct() {
        // Hook into WordPress events to handle form submission and display the shortcode
        add_action('admin_post_handle_booking_form_submission', [$this, 'handle_booking_form_submission']);
        add_action('admin_post_nopriv_handle_booking_form_submission', [$this, 'handle_booking_form_submission']); // Handle for non-logged in users (optional)
        add_shortcode('display_booking_form', [$this, 'display_booking_form']);
        add_action('wp_ajax_get_all_bookings', [$this, 'print_all_available_bookings']);
    }

    /**
     * Handles the submission of the booking form.
     */
    public function handle_booking_form_submission() {
        // Capability check (adjust the capability as needed)
        if (!current_user_can('book_services')) {
            wp_die(__('You do not have permission to submit this form.', 'mybookingplugin'));
        }

        // Security check (nonce validation)
        check_admin_referer('my_booking_plugin_booking_action', 'my_booking_plugin_nonce');

        // Sanitize all input data
        $service = sanitize_text_field($_POST['service']);
        $booking_id = sanitize_text_field($_POST['booking_id']);
        $client_name = sanitize_text_field($_POST['name']);
        $client_email = sanitize_email($_POST['email']);


        // Perform data validation
        $validation_result = $this->validate_booking_data($service, $booking_id, $client_name, $client_email);

        if ($validation_result === true) {
            // Save booking if validation passes
            if ($this->update_booking($service, $booking_id, $client_name, $client_email)) {
                // Redirect on successful booking
                wp_redirect(add_query_arg(['booking_status' => 'success'], home_url()));
                exit;
            } else {
                // Redirect on booking save error
                wp_redirect(add_query_arg(['booking_status' => 'error'], home_url()));
                exit;
            }
        } else {
            // Redirect with validation errors
            wp_redirect(add_query_arg(['booking_errors' => implode(',', $validation_result)], home_url()));
            exit;
        }
    }

    /**
     * Validates the booking form data.
     *
     * @param string $service The selected service

     * @param string $name The customer's name
     * @param string $email The customer's email address
     * @return bool|array True if data is valid, otherwise an array of error messages
     */
    private function validate_booking_data($service, $booking_id, $name, $email) {
        $errors = [];

        // Service validation
        if (empty($service) || !in_array($service, $this->valid_services)) {
            $errors[] = __('Invalid service selected.', 'mybookingplugin');
        }


        if (empty($booking_id)) {
            $errors[] = __('booking_id is required.', 'mybookingplugin');
        }

        // Name validation
        if (empty($name)) {
            $errors[] = __('Name is required.', 'mybookingplugin');
        }

        // Email validation
        if (empty($email) || !is_email($email)) {
            $errors[] = __('Invalid email address.', 'mybookingplugin');
        }

        // Return the result
        return empty($errors) ? true : $errors; 
    }

    /**
     * Saves the booking data as a custom post type.
     *
     * @param string $service The selected service
     * @param int $booking_id
     * @param string $name The customer's name
     * @param string $email The customer's email address
     * @return bool True on success, false on error
     */
    private function update_booking($booking_id, $service, $client_name, $client_email) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';
        // Update data
        $result = $wpdb->update(
            $table_name,
            [ // Data to update
                'service_id' => $service,
                'client_name' => $client_name,
                'client_email' => $client_email,
            ],
            [ 'ID' => $booking_id ] // Where clause
        );

        // Handle potential errors
        if ($result === false) {
            error_log('Booking save error');
            return false; // Save failed
        }

        return true; // Save successful
    }

    public function get_all_available_bookings() {
        //display PHP error log
        error_log( 'Hook activated - get  all available bookings');

        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings'; // Assuming 'bookings' is your table name
    
        $bookings = $wpdb->get_results("SELECT * FROM $table_name WHERE service_id = 0 and	client_name = 0 and	client_email = 0") ; 
    
        return $bookings;
    }

    public function print_all_available_bookings() {
        check_ajax_referer('mybookingplugin_front_nonce', 'security');
        $bookings = $this->get_all_available_bookings();
        wp_send_json_success(['bookings' => $bookings]);
        wp_die();
    }

    /**
     * Renders the booking form using output buffering.
     *
     * @return string The HTML for the booking form 
     */
    public function display_booking_form() {
        ob_start();
        include plugin_dir_path(__FILE__) . '/../views/BookingForm.php';
        $form_html = ob_get_clean();
        $form_html = str_replace('[form_action]', admin_url('admin-post.php'), $form_html);
        return $form_html;
    }
}