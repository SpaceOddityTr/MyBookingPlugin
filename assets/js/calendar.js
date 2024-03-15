(function ($) {
    $(function() {
        let modal = $('#booking-modal');
        let calendarEl = $('#calendar').get(0)
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            dateClick: function(info) {
                // Trigger modal or form for adding a booking here.
                // This should contain inputs for the booking details and a submit button.
                modal.dialog('open'); 
            },
        });
        calendar.render();

        modal.dialog({
            buttons: [
                {
                    text: 'Close',
                    click: function () {
                        modal.dialog('close');
                    },
                },
                {
                    text: 'Create Event',
                    click: function () {
                        $.ajax({
                            url: MyBookingPluginAjax.ajaxurl,
                            data: {
                                action: 'add_booking',
                                security: MyBookingPluginAjax.security,
                            }
                        }).done(function (data) {
                            // data contains response data
                        })
                        modal.dialog('close');
                    },
                },
            ],
        })
    })
})(jQuery)