<?php
require_once( plugin_dir_path(__FILE__) . './Booking.php' );
class Admin {
    protected $baseDir;
    protected $booking;  // Declare the property

    public function __construct(string $baseDir, Booking $booking) {
        //display PHP error log
        error_log('Admin class constructor called');
        $this->booking = $booking;
        $this->baseDir = $baseDir;

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'settings_init']);
    }
    
    public function add_admin_menu() {
        //display PHP error log
        error_log('add_admin_menu called');

        // Top-level menu item
        add_menu_page(
            'My Booking Plugin Settings', // Page title 
            'Booking Settings',           // Menu title 
            'manage_options',             // Required capability to view the page
            'my_booking_plugin',          // Menu slug
            [$this, 'settings_page']      // Callback function to render the page
        );

        // Submenu item (Note the correction to fix nesting)
        add_submenu_page(
            'my_booking_plugin',          // Parent slug
            'Set Availability',
            'Set Availability',
            'manage_options',
            'set_availability',
            [$this, 'set_availability_page']
        );

        add_submenu_page(
            'my_booking_plugin',   // Parent slug
            'All Bookings',        // Page title
            'All Bookings',        // Menu title
            'manage_options',      // Required capability
            'all_bookings',        // Menu slug
            [$this, 'list_all_bookings']
        );
    }
    

    public function settings_init() {
        //display PHP error log
        error_log( 'Hook activated - settings init');

        // Register settings group for saving options
        register_setting('my_booking_plugin', 'my_booking_plugin_admin_email');

        // Settings Section
        add_settings_section(
            'my_booking_plugin_section',
            __('Booking settings', 'my-booking-plugin'),    // Section title
            [$this, 'settings_section_callback'],           // Callback to display description
            'my_booking_plugin'                             // Page the section belongs to
        );

        // Settings Field: Email for Notifications
        add_settings_field(
            'my_booking_plugin_admin_email',
            __('Email for Notifications', 'my-booking-plugin'),
            [$this, 'settings_field_callback'],   // Callback to render the input field
            'my_booking_plugin',                  // Page to display the field on
            'my_booking_plugin_section'           // Section to display the field in
        );
    }


    public function settings_section_callback() {
        error_log( 'Hook activated - section callback');
        echo '<p>This is the description of the settings section.</p>';
    }
    
    //Renders HTML for the custom settings field to collect the admin email
    public function settings_field_callback() {
        error_log( 'Hook activated - field callback');

        // Get saved options, use default if empty
        $option = get_option('my_booking_plugin_admin_email');
        $field_value = $option !== false
            ? $option
            : '';

        // Render the email input field
        echo "<input type='text' id='my_booking_plugin_admin_email' name='my_booking_plugin_admin_email' value='". $field_value ."'>";
    }


    //Displays the plugin settings page with the settings form and availability management section
    public function settings_page() {
        error_log( 'Hook activated - settings page');
        ?>
        <form action="options.php" method="post">
            <?php
            settings_fields('my_booking_plugin');
            do_settings_sections('my_booking_plugin');
            submit_button();
            ?>
        </form>
        <?php
    }

    // Renders the "Set Availability" page
    public function set_availability_page() {
        error_log( 'Hook activated - set availability page');
        include_once __DIR__ . '/../views/Calendar.php';
    }

    public function list_all_bookings()
    {
        $bookings = $this->booking->getAll();
        $view = new View("{$this->baseDir}views/admin/bookings-list.php");
        echo $view->render([
            'bookings' => $bookings,
            'getServiceLabel' =>  function (string $serviceName): string {
                return $this->getServiceLabel($serviceName);
            },
        ]);
    }

    /**
     * Retrieves the human-readable service label.
     *
     * @param int $serviceName The name of the service.
     * @return string The lable of the service.
     */
    protected function getServiceLabel(string $serviceName): string {
        $serviceName = str_replace('_', ' ', $serviceName);
        return ucwords($serviceName);
    }

}
