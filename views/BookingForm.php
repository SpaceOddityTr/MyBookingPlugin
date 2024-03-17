<?php
/** @var array<array> $availableSlots */
?>
    <div class="my-booking-plugin-container">
        <div class="my-booking-plugin-header">
            <h2>Book Your Service</h2>
        </div>

        <form id="my-booking-plugin-form" method="post"">

            <div class="form-group">
                <label for="service_name">Select service:</label>
                <select id="service_name" name="service_name" required>
                    <option value="">--Please choose an option--</option>
                    <option value="essential_oils">Essential Oils</option>
                    <option value="psychosomatics">Psychosomatics</option>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Select date:</label>
                <select id="booking_id" name="booking_id" required>
                    <option value="">--Please choose an option--</option>
                    <?php foreach ($availableSlots as $slot): ?>
                        <option value="<?= $slot['id'] ?>">
                            <?= $slot['date'] ?> @ <?= $slot['time'] ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Your full name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <input type="submit" value="Book Now">
            </div>
        </form> 
    </div>
