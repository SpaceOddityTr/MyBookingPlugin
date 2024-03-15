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
        add_action('admin_post_handle_booking_form_submission', [$this, 'handle_booking_form_submission']);
        add_shortcode('display_booking_form', [$this, 'display_booking_form_shortcode']);
    }

    /**
     * Handles the submission of the booking form.
     */
    public function handle_booking_form_submission() {
        if (!current_user_can('book_services')) {
            wp_die(__('You do not have permission to submit this form.', 'mybookingplugin'));
        }

        check_admin_referer('my_booking_plugin_booking_action', 'my_booking_plugin_nonce');

        $service = sanitize_text_field($_POST['service']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        $validation_result = $this->validate_booking_data($service, $date, $time, $name, $email);
        if ($validation_result === true) {
            if ($this->save_booking($service, $date, $time, $name, $email)) {
                wp_redirect(add_query_arg(['booking_status' => 'success'], home_url()));
                exit;
            } else {
                wp_redirect(add_query_arg(['booking_status' => 'error'], home_url()));
                exit;
            }
        } else {
            wp_redirect(add_query_arg(['booking_errors' => implode(',', $validation_result)], home_url()));
            exit;
        }
    }

    private function validate_booking_data($service, $date, $time, $name, $email) {
        $errors = [];

        if (empty($service) || !in_array($service, $this->valid_services)) {
            $errors[] = __('Invalid service selected.', 'mybookingplugin');
        }

        // Using DateTime for better date validation
        try {
            new DateTime($date);
        } catch (Exception $e) {
            $errors[] = __('Invalid date format.', 'mybookingplugin');
        }

        // Basic time validation, consider using DateTime here as well
        if (empty($time) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors[] = __('Invalid time format.', 'mybookingplugin');
        }

        if (empty($name)) {
            $errors[] = __('Name is required.', 'mybookingplugin');
        }

        if (empty($email) || !is_email($email)) {
            $errors[] = __('Invalid email address.', 'mybookingplugin');
        }

        return empty($errors) ? true : $errors;
    }

    private function save_booking($service, $date, $time, $name, $email) {
        $booking_data = [
            'post_title'    => wp_strip_all_tags("Booking for $name"),
            'post_content'  => "Service: $service\nDate: $date\nTime: $time\nEmail: $email",
            'post_status'   => 'publish',
            'post_type'     => 'booking',
            'meta_input'    => [
                'service' => $service,
                'date'    => $date,
                'time'    => $time,
                'name'    => $name,
                'email'   => $email
            ]
        ];

        $post_id = wp_insert_post($booking_data);

        if (is_wp_error($post_id)) {
            error_log('Booking save error: ' . $post_id->get_error_message());
            return false;
        }

        return true;
    }

    public function display_booking_form_shortcode() {
        ob_start();
        include plugin_dir_path(__FILE__) . '/../views/BookingForm.php';
        $form_html = ob_get_clean();
        $form_html = str_replace('[form_action]', admin_url('admin-post.php'), $form_html);
        return $form_html;
    }
}
