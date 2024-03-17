// assets/js/public.js
(function($) {
    $(function () {
        // Form Setup
        let bookingForm = $('#my-booking-plugin-form'); // Store a reference to the booking form for efficiency

        // Form Submission Handling
        bookingForm.on('submit', function(e) {
            e.preventDefault(); // Stop the form from submitting normally (reloading the page)
            
            let formData = {
                booking_id: bookingForm.find('[name="booking_id"] option:selected').val(),
                service_name: bookingForm.find('[name="service_name"] option:selected').val(),
                client_name: bookingForm.find('[name="name"]').val(),
                client_email: bookingForm.find('[name="email"]').val(),
            };

            // Send Booking Data to Server (WordPress)
            $.ajax({
                type: 'POST', 
                url: MyBookingPlugin.ajax_url,  // Send the data to the backend URL
                data: {
                    action: 'book_slot',   
                    data: formData,
                },

                // Handle Successful Booking
                success: function(response) {
                    if (response.success) { 
                        // Remove old messages, if any
                        bookingForm.find('.my-booking-plugin-message').remove(); 

                        // Display success message
                        bookingForm.prepend('<div class="my-booking-plugin-message my-booking-plugin-message-success">Booking successful!</div>');

                        bookingForm[0].reset(); // Clear the form fields
                    } else {
                        // Handle Booking Error
                        bookingForm.find('.my-booking-plugin-message').remove(); // Remove old messages

                        // Display error message (received from the server)
                        bookingForm.prepend('<div class="my-booking-plugin-message my-booking-plugin-message-error">' + response.data.message + '</div>');
                    }
                },

                // Handle AJAX Communication Error
                error: function() { 
                    // Remove old messages
                    bookingForm.find('.my-booking-plugin-message').remove(); 

                    // Display a generic error message 
                    bookingForm.prepend('<div class="my-booking-plugin-message my-booking-plugin-message-error">An error occurred, please try again.</div>');
                }
            }); 
        });
    })
})(jQuery)

