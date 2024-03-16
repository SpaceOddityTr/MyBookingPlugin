(function ($) {
    $(function() {
        let modal = $('#booking-modal');
        modal.find('#time').timepicker({
            timeFormat: 'h:mm p',
            interval: 60,
            minTime: '10',
            maxTime: '6:00pm',
            defaultTime: '11',
            startTime: '10:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
        });
        let calendarEl = $('#calendar').get(0)
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            dateClick: function(info) {

                let formattedDate = FullCalendar.formatDate(info.date, {
                    month: 'long',
                    year: 'numeric',
                    day: 'numeric',
                    weekday: 'long'
                  })
                console.log(info);
                modal.find('#selected-date').html(formattedDate)
                modal.find('#date').val(info.dateStr)
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
                        time = modal.find('input#time').val()
                        date = modal.find('input#date').val()
                        $.ajax({
                            url: MyBookingPluginAjax.ajaxurl,
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'add_booking',
                                security: MyBookingPluginAjax.security,
                                time: time,
                                date: date,
                            }
                        }).done(function (data) {
                            console.log('Response received')
                        })
                        modal.dialog('close');
                    },
                },
            ],
        })
    })
})(jQuery)