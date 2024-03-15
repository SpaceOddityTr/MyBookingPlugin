<?php
class Admin {
    public function __construct() {
        //display PHP error log
        error_log( 'Hook activated - Admin construct');

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'settings_init']);

    }

    public function add_admin_menu() {
        //display PHP error log
        error_log( 'Hook activated - admin menu');

        add_menu_page(
            'My Booking Plugin Settings', 
            'Booking Settings', 
            'manage_options', 
            'my_booking_plugin', 
            [$this, 'settings_page']
        );

        add_submenu_page(
            'my_booking_plugin', // Parent slug correction
            'Set Availability',
            'Set Availability',
            'manage_options',
            'set_availability',
            [$this, 'set_availability_page']
        );
    }



    public function settings_init() {
        //display PHP error log
        error_log( 'Hook activated - settings init');

        register_setting('my_booking_plugin', 'my_booking_plugin_settings');

        add_settings_section(
            'my_booking_plugin_section', 
            __('Booking settings', 'my-booking-plugin'), 
            [$this, 'settings_section_callback'], 
            'my_booking_plugin'
        );

        add_settings_field(
            'my_booking_plugin_text_field_0', 
            __('Email for Notifications', 'my-booking-plugin'), 
            [$this, 'settings_field_callback'], 
            'my_booking_plugin', 
            'my_booking_plugin_section'
        );
    }


    public function settings_section_callback() {
        error_log( 'Hook activated - section callback');
        echo '<p>This is the description of the settings section.</p>';
    }
    
    //Renders HTML for the custom settings field.
    public function settings_field_callback() {
        error_log( 'Hook activated - field callback');
        $options = get_option('general');
        $field_value = isset($options['new_admin_email']) ? esc_attr($options['new_admin_email']) : '';
        echo "<input type='text' name='general[new_admin_email]' value='". $field_value ."'>";
    }
    

    //Displays the plugin settings page.
    public function settings_page() {
        //display PHP error log
        error_log( 'Hook activated - settings page');
        ?>
        <form action='options.php' method='post'>
            
            <?php
            settings_fields('my_booking_plugin');
            do_settings_sections('my_booking_plugin');
    
            // Availability Modal Section
            echo "<h3>Availability Management</h3>";
            echo "<button type='button' class='add-availability-btn'>Manage Availability</button>";
    
            // Include the availability modal
            ?>
            <div id="availability-modal" class="modal">
                <h2>Add Availability for <span id="modal-date"></span></h2>
                </div>
            <?php
    
            submit_button();
            ?>
        </form>
        <?php
    }


    //Placeholder for the set availability page content.
    public function set_availability_page() {
        error_log( 'Hook activated - set  availability page');

        // Include the settings page view from the views/ directory.
        include_once __DIR__ . '/../views/Calendar.php';

    }




}
?>