<?php
/**
 * Handles the public-facing functionality of the plugin.
 *
 * @package MyBookingPlugin
 */
class PublicSide {

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $pluginCode;

    protected $baseUrl;
    protected $baseDir;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    /** @var Booking */
    protected $booking;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginCode The unique identifier of this plugin.
     * @param string $version The current version of the plugin.
     */
    public function __construct(string $pluginCode, string $version, Booking $booking, string $baseUrl, string $baseDir)
    {
        $this->pluginCode = $pluginCode;
        $this->version = $version;
        $this->booking = $booking;
        $this->baseUrl = $baseUrl;
        $this->baseDir = $baseDir;

        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_nopriv_book_slot', [$this, 'book']);
        add_action('wp_ajax_book_slot', [$this, 'book']);
    }

    /**
     * Handles a request to book a service.
     */
    public function book()
    {
        error_log("Handle Booking Function Reached");
        error_log(print_r($_POST, true));

        // Sanitize all input data
        $data = $_POST['data'] ?? [];

        $bookingId = sanitize_text_field($data['booking_id']);
        $serviceName = sanitize_text_field($data['service_name']);
        $clientName = sanitize_text_field($data['client_name']);
        $clientEmail = sanitize_email($data['client_email']);


        // Perform data validation
        $validationResult = $this->validateBookingData($bookingId, $clientName, $clientEmail);
        if ($validationResult !== true) {
            wp_send_json_error(['booking_errors' => $validationResult]);
            wp_die();
        }

        try {
            $this->booking->update($bookingId, [
                'service_name' => $serviceName,
                'client_name' => $clientName,
                'client_email' => $clientEmail,
            ]);
            do_action('my_bookings_plugin_booking_added', $bookingId);
            wp_send_json_success(['message' => 'Booking created successfully']);
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            wp_die();
        }
    }

    /**
     * Validates the booking form data.
     *
     * @param string $bookingId The booking ID
     * @param string $serviceName The selected service
     * @param string $clientName The customer's name
     * @param string $clientEmail The customer's email address
     * @return bool|array True if data is valid, otherwise an array of error messages
     */
    protected function validateBookingData($bookingId, $clientName, $clientEmail)
    {
        $errors = [];

        if (empty($bookingId)) {
            $errors[] = __('The `booking_id` field is required.', 'mybookingplugin');
        }

        // Name validation
        if (empty($clientName)) {
            $errors[] = __('The `name` field is required.', 'mybookingplugin');
        }

        // Email validation
        if (empty($clientEmail) || !is_email($clientEmail)) {
            $errors[] = __('Invalid email address.', 'mybookingplugin');
        }

        // Return the result
        return empty($errors) ? true : $errors;
    }

    public function printAllAvailableBookings()
    {
        check_ajax_referer('mybookingplugin_front_nonce', 'security');
        $bookings = $this->booking->getAvailable();
        wp_send_json_success(['bookings' => $bookings]);
        wp_die();
    }



    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueueStyles()
    {
        wp_enqueue_style(
            $this->pluginCode . '-style',
            $this->baseUrl.'assets/css/booking.css',
            [], // Dependencies
            $this->version,
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueueScripts()
    {
        wp_enqueue_script(
            $this->pluginCode . '-script',
            $this->baseUrl.'assets/js/public.js',
            ['jquery'], // Dependencies
            $this->version,
            true
        );

        // Localize the script with new data
        $translation_array = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            // Additional data can be added here
        );
        wp_localize_script($this->pluginCode . '-script', $this->pluginCode, $translation_array);
    }

    /**
     * Renders the booking form using output buffering.
     *
     * @return string The HTML for the booking form.
     */
    public function displayBookingForm()
    {
        $slots = $this->booking->getAvailable();
        $view = new View("{$this->baseDir}views/BookingForm.php");
        return $view->render([
            'formAction' => admin_url('admin-post.php'),
            'availableSlots' => $slots,
        ]);
    }

    // Shortcode to display the booking form
    public function renderShortcode()
    {
       echo  $this->displayBookingForm();
    }

}
