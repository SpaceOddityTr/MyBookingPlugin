(function ($) { // Create an isolated scope using jQuery's no-conflict mode to avoid conflicts with other libraries that might use $.
    $(function() { // Wait for the DOM to be fully loaded before executing the code.

        // Initialize the booking modal and its timepicker component.
        let modal = $('#booking-modal'); // Reference the modal by its ID.
        modal.find('#time').timepicker({ // Initialize the timepicker on the input field inside the modal.
            timeFormat: 'H:mm', // Set the time format to 24-hour format.
            interval: 30, // Set time selection intervals to 30 minutes.
            minTime: '00:00', // Set the minimum selectable time to midnight.
            maxTime: '23:30', // Set the maximum selectable time to 23:30.
            defaultTime: '11:00', // Pre-select 11:00 AM as the default time.
            startTime: '00:00', // Start the time dropdown at midnight.
            dynamic: false, // Do not dynamically update the start time as the user scrolls.
            dropdown: true, // Enable the dropdown list for time selection.
            scrollbar: true // Enable a scrollbar for the time selection dropdown.
        });

        // Initialize the calendar component.
        let calendarEl = $('#calendar').get(0); // Reference the calendar element.
        var calendar = new FullCalendar.Calendar(calendarEl, { // Initialize FullCalendar on the referenced element.
            initialView: 'dayGridMonth', // Set the initial view to show a month grid.
            dateClick: function(info) { // Define what happens when a date is clicked on the calendar.
                let formattedDate = FullCalendar.formatDate(info.date, { // Format the clicked date.
                    month: 'long',
                    year: 'numeric',
                    day: 'numeric',
                    weekday: 'long'
                });

                // Update the modal with the selected date information.
                modal.find('#selected-date').html(formattedDate);
                modal.find('#date').val(info.dateStr);

                modal.dialog('open'); // Open the booking modal dialog.
            },
        });
        calendar.render(); // Render the calendar UI.

        // Configure the booking modal dialog.
        modal.dialog({
            autoOpen: false, // The modal dialog will not open automatically upon initialization.
            buttons: [ // Define buttons in the modal dialog.
                { // Close button configuration.
                    text: 'Close',
                    click: function () {
                        modal.dialog('close'); // Close the modal when the button is clicked.
                    },
                },
                { // Create Event button configuration.
                    text: 'Create Event',
                    click: function () {
                        // Collect selected time and date from the modal inputs.
                        let time = modal.find('input#time').val();
                        let date = modal.find('input#date').val();

                        // Send an AJAX request to create a booking event.
                        $.ajax({
                            url: MyBookingPluginAjax.ajaxurl, // The URL to which the request is sent (provided by WordPress AJAX infrastructure).
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'add_booking', // The action identifier for the WordPress hook.
                                security: MyBookingPluginAjax.security, // Nonce for security.
                                time: time, // Selected time.
                                date: date, // Selected date.
                            }
                        }).done(function (data) {
                            console.log('Response received'); // Log the response upon successful AJAX request.
                        });

                        modal.dialog('close'); // Close the modal after creating the event.
                    },
                },
            ],
        });
    });
})(jQuery); // Pass jQuery to the self-invoking function to use $ safely inside.
