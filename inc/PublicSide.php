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
    private $pluginCode;

    private $url;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    private $version;

    /** @var Booking */
    private $booking;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginCode The unique identifier of this plugin.
     * @param string $version The current version of the plugin.
     */
    public function __construct(string $pluginCode, string $version, Booking $booking, string $url) {
        $this->url = $url;
        $this->pluginCode = $pluginCode;
        $this->version = $version;
        $this->booking = $booking;
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }



    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueueStyles() {
        wp_enqueue_style(
            $this->pluginCode . '-style',
            $this->url.'assets/css/booking.css',
            array(), // Dependencies
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueueScripts() {
        wp_enqueue_script(
            $this->pluginCode . '-script',
            $this->url.'assets/js/public.js',
            array('jquery'), // Dependencies
            $this->version,
            true
        );

        // Localize the script with new data
        $translation_array = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('mybookingplugin_front_nonce'),
            // Additional data can be added here
        );
        wp_localize_script($this->pluginCode . '-script', $this->pluginCode, $translation_array);
    }

    /**
     * Display the booking form on the front-end.
     */
    public function displayBookingForm() {
        include_once __DIR__ . '/../views/BookingForm.php';
    }

    // Shortcode to display the booking form
    public function renderShortcode() {
       echo  $this->booking->display_booking_form();
    }

}
