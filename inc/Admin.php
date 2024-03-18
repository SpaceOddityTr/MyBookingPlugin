<?php
/**
 * Class definition for the Admin area of a custom WordPress booking plugin
 * 
 * @package MyBookingPlugin
 */
class Admin
{
    protected $baseDir; // Directory path for plugin assets or includes
    protected $booking; // Instance of Booking class for managing booking operations

    // Constructor method to initialize the Admin object
    public function __construct(string $baseDir, Booking $booking)
    {
        // Log a message to the PHP error log for debugging purposes
        error_log('Admin class constructor called');
        $this->booking = $booking; // Assign the Booking instance
        $this->baseDir = $baseDir; // Set the base directory for plugin resources

        // WordPress hooks to add menu items to the admin dashboard and initialize plugin settings
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'settings_init']);
    }
    
    // Function to add menu and submenu items to the WordPress admin dashboard
    public function add_admin_menu() 
    {
        // Log a message for debugging
        error_log('add_admin_menu called');

        // Add a top-level menu item for the booking plugin settings
        add_menu_page(
            'My Booking Plugin Settings', // The text to be displayed in the title tags of the page when the menu is selected
            'Booking Settings',           // The text to be used for the menu
            'manage_options',             // The capability required for this menu to be displayed to the user
            'my_booking_plugin',          // The slug name to refer to this menu by (should be unique for this menu)
            [$this, 'settings_page']      // The function to be called to output the content for this page
        );

        // Add submenu items under the top-level menu created above
        add_submenu_page(
            'my_booking_plugin',          // The slug name for the parent menu
            'Set Availability',           // The text to be displayed in the title tags of the page when the menu is selected
            'Set Availability',           // The text to be used for the menu
            'manage_options',             // The capability required for this menu to be displayed to the user
            'set_availability',           // The slug name to refer to this menu by (should be unique for this menu)
            [$this, 'set_availability_page'] // The function to be called to output the content for this page
        );

        // Another submenu item for viewing all bookings
        add_submenu_page(
            'my_booking_plugin',   // Parent menu slug
            'All Bookings',        // Page title
            'All Bookings',        // Menu title
            'manage_options',      // Capability required
            'all_bookings',        // Menu slug
            [$this, 'list_all_bookings'] // Callback function
        );
    }
    
    // Initializes plugin settings by registering settings and sections
    public function settings_init()
    {
        // Log a message for debugging
        error_log('Hook activated - settings init');

        // Registers a setting for the plugin, which is stored in the WordPress options table
        register_setting('my_booking_plugin', 'my_booking_plugin_admin_email');

        // Add a new section to a settings page
        add_settings_section(
            'my_booking_plugin_section',                       // Unique identifier for the section
            __('Booking settings', 'my-booking-plugin'),       // Section title
            [$this, 'settings_section_callback'],              // Callback function to output the content of the section
            'my_booking_plugin'                                // The page on which to add this section of options
        );

        // Add a new field to a section of a settings page
        add_settings_field(
            'my_booking_plugin_admin_email',                    // Unique identifier for the field
            __('Email for Notifications', 'my-booking-plugin'), // Title of the field
            [$this, 'settings_field_callback'],                 // Callback function to render the field
            'my_booking_plugin',                                // The page on which to show the field
            'my_booking_plugin_section'                         // The section where the field is to be added
        );
    }

    // Callback for settings section description
    public function settings_section_callback()
    {
        error_log('Hook activated - section callback');
    }
    
    // Renders HTML for the custom settings field (email input)
    public function settings_field_callback() 
    {
        error_log('Hook activated - field callback');

        // Retrieves the saved value from the database, with a fallback to an empty string if not set
        $option = get_option('my_booking_plugin_admin_email');
        $field_value = $option !== false ? $option : '';

        // Outputs the HTML input field for the email option
        echo "<input type='text' id='my_booking_plugin_admin_email' name='my_booking_plugin_admin_email' value='". $field_value ."'>";
    }

    // Renders the settings page with a form for the plugin settings
    public function settings_page()
    {
        error_log('Hook activated - settings page');
        ?>
        <form action="options.php" method="post">
            <?php
            // Renders necessary fields for settings registered with 'my_booking_plugin' 
            settings_fields('my_booking_plugin');
            do_settings_sections('my_booking_plugin');
            submit_button(); // Outputs a submit button with the text "Save Changes"
            ?>
        </form>
        <?php
    }

    // Callback function to render the "Set Availability" page
    public function set_availability_page()
    {
        error_log('Hook activated - set availability page');
        include_once __DIR__ . '/../views/calendar.php'; // Includes the calendar view file
    }

    // Retrieves all bookings and displays them
    public function list_all_bookings()
    {
        $bookings = $this->booking->getAll(); // Get all bookings from the Booking instance
        $view = new View("{$this->baseDir}views/admin/bookings-list.php"); // Instantiate the View class with the path to the bookings list view
        echo $view->render([ // Render the view with provided data
            'bookings' => $bookings,
            'getServiceLabel' => function (string $serviceName): string
            {
                return $this->getServiceLabel($serviceName); // Helper function to format the service name
            },
        ]);
    }

    // Helper function to format and return a human-readable label for a service name
    protected function getServiceLabel(string $serviceName): string
    {
        $serviceName = str_replace('_', ' ', $serviceName); // Replace underscores with spaces
        return ucwords($serviceName); // Capitalize the first letter of each word
    }
}
