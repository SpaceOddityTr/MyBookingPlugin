<?php
/** 
 * @var array<array> $bookings An array of associative arrays, where each associative array represents a booking's details.
 * @var callable $getServiceLabel A function/callable that takes a service machine name (string) and returns a human-readable service name (string).
 */
?>

<h2>All Bookings</h2> <!-- Title for the bookings list -->

<!-- The table structure for displaying bookings -->
<table class="wp-list-table widefat striped">
    <thead>
        <!-- Table headers defining the columns for booking details -->
        <tr>
            <th>Booking ID</th>
            <th>Service</th>
            <th>Client Name</th>
            <th>Client Email</th>
            <th>Date</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody>
        <!-- PHP loop through each booking in the $bookings array -->
        <?php foreach ($bookings as $booking): ?>
            <!-- Each booking's details are rendered in a table row -->
            <tr id="bookings-<?= $booking['id'] ?>"> <!-- Row ID for CSS/JS targeting -->
                <td><?= esc_html($booking['id']) ?></td> <!-- Booking ID -->
                <td><?= esc_html($getServiceLabel($booking['service_name'] ?? '')) ?></td> <!-- Service name converted to human-readable form -->
                <td><?= esc_html($booking['client_name']) ?></td> <!-- Client name -->
                <td><?= esc_html($booking['client_email']) ?></td> <!-- Client email -->
                <td><?= esc_html($booking['date']) ?></td> <!-- Booking date -->
                <td><?= esc_html($booking['time']) ?></td> <!-- Booking time -->
            </tr>
        <?php endforeach ?>
    </tbody>
</table>