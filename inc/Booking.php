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
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        // Perform data validation
        $validation_result = $this->validate_booking_data($service, $date, $time, $name, $email);

        if ($validation_result === true) {
            // Save booking if validation passes
            if ($this->save_booking($service, $date, $time, $name, $email)) {
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
     * @param string $date The booking date
     * @param string $time The booking time
     * @param string $name The customer's name
     * @param string $email The customer's email address
     * @return bool|array True if data is valid, otherwise an array of error messages
     */
    private function validate_booking_data($service, $date, $time, $name, $email) {
        $errors = [];

        // Service validation
        if (empty($service) || !in_array($service, $this->valid_services)) {
            $errors[] = __('Invalid service selected.', 'mybookingplugin');
        }

        // Date validation (using DateTime for accurate validation)
        try {
            $bookingDate = new DateTime($date);
        } catch (Exception $e) {
            $errors[] = __('Invalid date format.', 'mybookingplugin');
        }

        // Time validation (with a simple regular expression check)
        if (empty($time) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors[] = __('Invalid time format.', 'mybookingplugin');
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
     * @param string $date The booking date
     * @param string $time The booking time
     * @param string $name The customer's name
     * @param string $email The customer's email address
     * @return bool True on success, false on error
     */
    private function save_booking($service, $date, $time, $name, $email) {
        // Prepare Booking Data
        $booking_data = [ 
            'post_title'   => wp_strip_all_tags("Booking for $name"), 
            'post_content' => "Service: $service\nDate: $date\nTime: $time\nEmail: $email",
            'post_status'  => 'publish',
            'post_type'    => 'booking', 
            'meta_input'   => [
                'service' => $service, 
                'date'    => $date,
                'time'    => $time, 
                'name'    => $name,
                'email'   => $email
            ]
        ];

        $post_id = wp_insert_post($booking_data); 

        // Handle potential errors
        if (is_wp_error($post_id)) {
            error_log('Booking save error: ' . $post_id->get_error_message());
            return false; // Save failed
        }

        return true; // Save successful
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