<?php
require_once __DIR__ . '/../../includes/data.php';
requireRole('rider');
$tripHistory = getRiderTripHistory((int) getCurrentUserId());
$pageTitle = 'Trip History';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="card">
    <h2>Trip History Using SQL Joins</h2>
    <p>This page combines data from trips, bookings, drivers, users, riders, and payments using INNER JOIN and LEFT JOIN queries.</p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Trip ID</th><th>Driver</th><th>Pickup</th><th>Drop-off</th><th>Distance</th><th>Total Fare</th><th>Payment Method</th><th>Payment Status</th></tr>
            </thead>
            <tbody>
            <?php if ($tripHistory): ?>
                <?php foreach ($tripHistory as $trip): ?>
                    <tr>
                        <td><?php echo escapeOutput((string) $trip['trip_id']); ?></td>
                        <td><?php echo escapeOutput($trip['driver_name']); ?></td>
                        <td><?php echo escapeOutput($trip['pickup_location']); ?></td>
                        <td><?php echo escapeOutput($trip['dropoff_location']); ?></td>
                        <td><?php echo escapeOutput((string) ($trip['actual_distance_km'] ?? 'N/A')); ?></td>
                        <td>$<?php echo escapeOutput(number_format((float) $trip['total_fare'], 2)); ?></td>
                        <td><?php echo escapeOutput($trip['payment_method'] ?? 'Not recorded'); ?></td>
                        <td><?php echo escapeOutput($trip['payment_status'] ?? 'pending'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No trip history found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
