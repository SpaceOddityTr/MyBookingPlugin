<?php
/** @var array<array> $bookings */
/** @var callable $getServiceLabel */
?>

<h2>All Bookings</h2>
<table class="wp-list-table widefat striped">
    <thead>
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
        <?php foreach ($bookings as $booking): ?>
            <tr id="bookings-<?= $booking['id'] ?>">
                <td><?= esc_html($booking['id']) ?></td>
                <td><?= esc_html($getServiceLabel($booking['service_name'] ?? '')) ?></td>
                <td><?= esc_html($booking['client_name']) ?></td>
                <td><?= esc_html($booking['client_email']) ?></td>
                <td><?= esc_html($booking['date']) ?></td>
                <td><?= esc_html($booking['time']) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>'
