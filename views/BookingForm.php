
<div class="my-booking-plugin-container">
    <div class="my-booking-plugin-header">
        <h2>Book Your Service</h2>
    </div>

    <form id="my-booking-plugin-form" method="post" action="[form_action]">
    <input type="hidden" name="action" value="handle_booking_form_submission">
        <!-- Nonce field for security -->
        <?php wp_nonce_field('my_booking_plugin_action', 'my_booking_plugin_nonce'); ?>

        <div class="form-group">
            <label for="my-booking-service">Select Service:</label>
            <select id="my-booking-service" name="service" required>
                <option value="">--Please choose an option--</option>
                <option value="essential_oils">Essential Oils</option>
                <option value="psychosomatics">Psychosomatics</option>
            </select>
        </div>

        <!-- Additional form fields (date, time, user details etc.) -->
        <div class="form-group">
            <label for="my-booking-date">Date:</label>
            <input type="date" id="my-booking-date" name="date" required>
        </div>

        <div class="form-group">
            <label for="my-booking-time">Time:</label>
            <input type="time" id="my-booking-time" name="time" required>
        </div>

        <div class="form-group">
            <label for="my-booking-name">Your Name:</label>
            <input type="text" id="my-booking-name" name="name" required>
        </div>

        <div class="form-group">
            <label for="my-booking-email">Email Address:</label>
            <input type="email" id="my-booking-email" name="email" required>
        </div>

        <div class="form-group">
            <input type="submit" value="Book Now">
        </div>
    </form>
</div>
