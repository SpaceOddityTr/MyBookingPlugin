<?php
/** 
 * @var array<array> $availableSlots An array containing information about available booking slots. Each slot is an associative array with at least 'id', 'date', and 'time' keys.
 */
?>

<!-- Container div for styling and layout purposes -->
<div class="my-booking-plugin-container">
    <!-- Header section of the booking form -->
    <div class="my-booking-plugin-header">
        <h2>Book Your Service</h2> <!-- Title of the form -->
    </div>

    <!-- The form itself, method set to "post" to submit data to the server -->
    <form id="my-booking-plugin-form" method="post"">
        <!-- Service selection dropdown -->
        <div class="form-group">
            <label for="service_name">Select service:</label>
            <select id="service_name" name="service_name" required>
                <option value="">--Please choose an option--</option>
                <!-- Hardcoded service options -->
                <option value="essential_oils">Essential Oils</option>
                <option value="psychosomatics">Psychosomatics</option>
            </select>
        </div>

        <!-- Date selection dropdown, dynamically populated based on available slots -->
        <div class="form-group">
            <label for="date">Select date:</label>
            <select id="booking_id" name="booking_id" required>
                <option value="">--Please choose an option--</option>
                <!-- Loop through each available slot and create an option for it -->
                <?php foreach ($availableSlots as $slot): ?>
                    <option value="<?= $slot['id'] ?>">
                        <?= $slot['date'] ?> @ <?= $slot['time'] ?> <!-- Display the date and time of the slot -->
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <!-- Input field for the user's full name -->
        <div class="form-group">
            <label for="name">Your full name:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <!-- Input field for the user's email address -->
        <div class="form-group">
            <label for="email">Email address:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <!-- Submit button for the form -->
        <div class="form-group">
            <input type="submit" value="Book Now">
        </div>
    </form>
</div>