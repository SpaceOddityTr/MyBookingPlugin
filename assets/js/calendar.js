(function ($) { // Create an isolated scope to avoid conflicts with other libraries using $
    $(function() { // Execute code after the DOM (HTML structure) is fully loaded
  
      // Initialize the booking modal and its timepicker
      let modal = $('#booking-modal'); 
      modal.find('#time').timepicker({
        timeFormat: 'H:mm',       // Display time in 24-hour format
        interval: 30,             // Allow 30-minute intervals for selection
        minTime: '00:00',         // Start time is midnight
        maxTime: '23:30',         // End time is 30 minutes before the next midnight
        defaultTime: '11:00',     // Set 11:00 AM as the default time
        startTime: '00:00',       // Initial start time of the dropdown
        dynamic: false,           // Use fixed intervals
        dropdown: true,           // Show a dropdown for time selection
        scrollbar: true           // Enable scrollbar in the dropdown
      });
  
      // Initialize the calendar
      let calendarEl = $('#calendar').get(0); 
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Initial view is monthly
        dateClick: function(info) { // Function executes when a date on the calendar is clicked 
  
          let formattedDate = FullCalendar.formatDate(info.date, { // Format the clicked date
            month: 'long',  
            year: 'numeric',
            day: 'numeric',
            weekday: 'long'  
          });
  
          // Update elements in the modal to show the selected date 
          modal.find('#selected-date').html(formattedDate); 
          modal.find('#date').val(info.dateStr);
  
          modal.dialog('open'); // Open the booking modal
        },
      });
      calendar.render(); // Display the calendar
  
      // Configure the booking modal
      modal.dialog({
        autoOpen: false, // Prevent automatic opening on initialization
        buttons: [ 
          { // Close Button
            text: 'Close',
            click: function () {
              modal.dialog('close'); 
            },
          },
          { // Create Event Button
            text: 'Create Event',
            click: function () {
              // Get selected time and date from the modal
              time = modal.find('input#time').val();
              date = modal.find('input#date').val();
  
              // Send AJAX request to create the booking event
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
                console.log('Response received');  
              });
  
              modal.dialog('close'); // Close the modal after creating the event
            },
          },
        ],
      }); 
    });
  })(jQuery); 
  