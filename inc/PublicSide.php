<?php
/**
 * Handles the public-facing functionality of the plugin, including enqueuing scripts and styles,
 * and managing AJAX booking requests.
 * 
 * @package MyBookingPlugin
 */
class PublicSide {

    /**
     * The unique identifier of this plugin, used to prefix styles, scripts, and other elements to avoid conflicts.
     */
    protected $pluginCode;

    /**
     * The base URL for the plugin, used for linking to assets like CSS and JavaScript files.
     */
    protected $baseUrl;

    /**
     * The base directory path for the plugin, used for including files.
     */
    protected $baseDir;

    /**
     * The current version of the plugin, used for cache busting of scripts and styles.
     */
    protected $version;

    /**
     * Instance of the Booking class to handle booking operations.
     */
    protected $booking;

    /**
     * Sets up the public side functionality, including assets and AJAX actions.
     *
     * @param string $pluginCode Unique identifier of the plugin.
     * @param string $version Current version of the plugin.
     * @param Booking $booking Booking class instance for handling booking operations.
     * @param string $baseUrl Base URL for linking to assets.
     * @param string $baseDir Base directory path for including files.
     */
    public function __construct(string $pluginCode, string $version, Booking $booking, string $baseUrl, string $baseDir)
    {
        $this->pluginCode = $pluginCode;
        $this->version = $version;
        $this->booking = $booking;
        $this->baseUrl = $baseUrl;
        $this->baseDir = $baseDir;

        // Hooks to enqueue styles and scripts for the front-end.
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

        // AJAX actions for logged-in and non-logged-in users to handle booking requests.
        add_action('wp_ajax_nopriv_book_slot', [$this, 'book']);
        add_action('wp_ajax_book_slot', [$this, 'book']);
    }

    /**
     * Handles AJAX request for booking a slot. Sanitizes input data, validates it,
     * updates booking information in the database, and returns the outcome.
     */
    public function book()
    {
        // Log reaching the booking function and the received POST data for debugging.
        error_log("Handle Booking Function Reached");
        error_log(print_r($_POST, true));

        // Sanitize incoming booking data from the AJAX request.
        $data = $_POST['data'] ?? [];

        $bookingId = sanitize_text_field($data['booking_id']);
        $serviceName = sanitize_text_field($data['service_name']);
        $clientName = sanitize_text_field($data['client_name']);
        $clientEmail = sanitize_email($data['client_email']);

        // Validate the sanitized booking data. If validation fails, return errors.
        $validationResult = $this->validateBookingData($bookingId, $clientName, $clientEmail);
        if ($validationResult !== true) {
            wp_send_json_error(['booking_errors' => $validationResult]);
            wp_die(); // Properly terminate the AJAX request.
        }

        // Attempt to update the booking with the sanitized and validated data.
        try {
            $this->booking->update($bookingId, [
                'service_name' => $serviceName,
                'client_name' => $clientName,
                'client_email' => $clientEmail,
            ]);
            // Trigger a custom action after booking update, which can be used for notifications, etc.
            do_action('my_bookings_plugin_booking_added', $bookingId);
            wp_send_json_success(['message' => 'Booking created successfully']); // Send success response.
        } catch (RuntimeException $e) {
            wp_send_json_error(['message' => $e->getMessage()]); // Send error response on exception.
        } finally {
            wp_die(); // Ensure AJAX request is properly terminated.
        }
    }

    /**
     * Validates the booking form data. Checks for required fields and validates email address format.
     *
     * @param string $bookingId The booking ID, must not be empty.
     * @param string $clientName The customer's name, required field.
     * @param string $clientEmail The customer's email address, must be a valid email format.
     * @return bool|array Returns true if all data is valid. If invalid, returns an array of error messages.
     */
    protected function validateBookingData($bookingId, $clientName, $clientEmail)
    {
        // Initialize an array to collect potential error messages.
        $errors = [];

        // Check for missing booking ID.
        if (empty($bookingId)) {
            $errors[] = __('The `booking_id` field is required.', 'mybookingplugin');
        }

        // Ensure the client name is provided.
        if (empty($clientName)) {
            $errors[] = __('The `name` field is required.', 'mybookingplugin');
        }

        // Validate the email address format.
        if (empty($clientEmail) || !is_email($clientEmail)) {
            $errors[] = __('Invalid email address.', 'mybookingplugin');
        }

        // Return true if no errors, otherwise return the errors array.
        return empty($errors) ? true : $errors;
    }

    /**
     * Retrieves all available booking slots and sends them to the frontend.
     * Intended to be used with AJAX to dynamically update available slots on the booking form.
     */
    public function printAllAvailableBookings()
    {
        // Verify the AJAX request to ensure it's coming from the correct source for security.
        check_ajax_referer('mybookingplugin_front_nonce', 'security');

        // Retrieve all available bookings.
        $bookings = $this->booking->getAvailable();

        // Return the bookings to the frontend.
        wp_send_json_success(['bookings' => $bookings]);

        // Ensure the AJAX handler stops execution after sending the response.
        wp_die();
    }

    /**
     * Enqueues the CSS stylesheets needed for the public-facing side of the plugin.
     * This method is hooked into WordPress and called at the appropriate time to ensure styles are loaded correctly.
     */
    public function enqueueStyles() 
    {
        wp_enqueue_style(
            $this->pluginCode . '-style', // Handle for the stylesheet.
            $this->baseUrl . 'assets/css/booking.css', // Path to the stylesheet file.
            [], // Dependencies. This stylesheet doesn't depend on any other styles.
            $this->version // Version number of the stylesheet for cache management.
        );
    }

    /**
     * Enqueues the JavaScript files needed for the public-facing side of the plugin.
     * This includes setting up AJAX URLs and any other necessary JavaScript variables.
     */
    public function enqueueScripts() 
    {
        wp_enqueue_script(
            $this->pluginCode . '-script', // Handle for the script.
            $this->baseUrl . 'assets/js/public.js', // Path to the script file.
            ['jquery'], // Script dependencies, ensuring jQuery is loaded first.
            $this->version, // Script version for cache management.
            true // Load in the footer.
        );

        // Localize the script with new data for AJAX requests.
        $translation_array = array(
            'ajax_url' => admin_url('admin-ajax.php'), // Providing AJAX URL for use in the JavaScript file.
            // Additional data can be added here to be passed to the frontend.
        );
        wp_localize_script($this->pluginCode . '-script', $this->pluginCode, $translation_array);
    }

    /**
     * Generates the booking form HTML and returns it as a string.
     * This method leverages the View class for rendering, allowing for separation of logic and presentation.
     *
     * @return string The HTML content of the booking form.
     */
    public function displayBookingForm()
    {
        // Retrieve available booking slots.
        $slots = $this->booking->getAvailable();

        // Initialize the view with the path to the booking form template.
        $view = new View("{$this->baseDir}views/booking-form.php");

        // Render the view with data and return the HTML content.
        return $view->render([
            'formAction' => admin_url('admin-post.php'), // Form action URL.
            'availableSlots' => $slots, // Data passed to the view for rendering.
        ]);
    }

    /**
     * Shortcode handler to display the booking form.
     * This method used to add the booking form to website via [shortcode].
     */
    public function renderShortcode() 
    {
        echo $this->displayBookingForm(); // Echoes the HTML content of the booking form.
    }
}
