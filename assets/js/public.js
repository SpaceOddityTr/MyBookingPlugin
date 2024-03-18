// assets/js/public.js
(function($) {
    $(function () {
        // Form Setup
        let bookingForm = $('#my-booking-plugin-form'); // Store a reference to the booking form for quick access

        // Form Submission Handling
        bookingForm.on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission action, which typically reloads the page
            
            // Gather form data into an object to prepare for AJAX submission
            let formData = {
                booking_id: bookingForm.find('[name="booking_id"] option:selected').val(), // Get the selected booking ID
                service_name: bookingForm.find('[name="service_name"] option:selected').val(), // Get the selected service name
                client_name: bookingForm.find('[name="name"]').val(), // Get the client's name from the input field
                client_email: bookingForm.find('[name="email"]').val(), // Get the client's email from the input field
            };

            // AJAX request to send booking data to the server
            $.ajax({
                type: 'POST', // Use the POST method for sending data
                url: MyBookingPlugin.ajax_url,  // AJAX handler URL defined in the WordPress plugin
                data: {
                    action: 'book_slot',   // Action hook for the AJAX request on the server side
                    data: formData, // The collected form data
                },

                // Callback function on successful AJAX request
                success: function(response) {
                    if (response.success) { // Check if the server responded with a success status
                        // Clear any previous messages
                        bookingForm.find('.my-booking-plugin-message').remove(); 

                        // Display success message at the top of the form
                        bookingForm.prepend('<div class="my-booking-plugin-message my-booking-plugin-message-success">Booking successful!</div>');

                        // Reset the form fields to clear the input data
                        bookingForm[0].reset(); 
                    } else {
                        // Server responded with an error status
                        // Clear any previous messages
                        bookingForm.find('.my-booking-plugin-message').remove(); 

                        // Display error message from the server at the top of the form
                        bookingForm.prepend('<div class="my-booking-plugin-message my-booking-plugin-message-error">' + response.data.message + '</div>');
                    }
                },

                // Callback function for handling AJAX communication errors (e.g., server unreachable)
                error: function() { 
                    // Clear any previous messages
                    bookingForm.find('.my-booking-plugin-message').remove(); 

                    // Display a generic error message
                    bookingForm.prepend('<div class="my-booking-plugin-message my-booking-plugin-message-error">An error occurred, please try again.</div>');
                }
            }); 
        });
    })
})(jQuery) // Pass jQuery to an immediately invoked function expression to avoid conflicts with other libraries
