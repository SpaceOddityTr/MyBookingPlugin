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
    private $pluginSlug;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginSlug The unique identifier of this plugin.
     * @param string $version The current version of the plugin.
     */
    public function __construct($pluginSlug, $version) {
        $this->pluginSlug = $pluginSlug;
        $this->version = $version;
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }



    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueueStyles() {
        wp_enqueue_style(
            $this->pluginSlug . '-style',
            plugin_dir_url(__FILE__) . 'assets/css/public.css',
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
            $this->pluginSlug . '-script',
            plugin_dir_url(__FILE__) . 'assets/js/public.js',
            array('jquery'), // Dependencies
            $this->version,
            true
        );

        // Localize the script with new data
        $translation_array = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            // Additional data can be added here
        );
        wp_localize_script($this->pluginSlug . '-script', $this->pluginSlug, $translation_array);
    }

    /**
     * Display the booking form on the front-end.
     */
    public function displayBookingForm() {
        include_once __DIR__ . '/../views/BookingForm.php';
    }
}
