<?php
require_once __DIR__ . '/../../includes/data.php';
requireRole('rider');

$connection = getDatabaseConnection();
$userId = getCurrentUserId();
$tripHistory = getRiderTripHistory((int) $userId);
$riderId = getRiderIdByUserId($connection, (int) $userId);
$bookingCount = 0;
if ($riderId) {
    $statement = $connection->prepare('SELECT COUNT(*) AS total FROM bookings WHERE rider_id = ?');
    $statement->bind_param('i', $riderId);
    $statement->execute();
    $bookingCount = (int) ($statement->get_result()->fetch_assoc()['total'] ?? 0);
    $statement->close();
}

$pageTitle = 'Rider Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="grid three-columns">
    <article class="card stat-card"><h3>Total Bookings</h3><p><?php echo $bookingCount; ?></p></article>
    <article class="card stat-card"><h3>Total Trips</h3><p><?php echo count($tripHistory); ?></p></article>
    <article class="card stat-card"><h3>Quick Action</h3><p><a class="button primary" href="/sharing_ride_application/pages/rider/book_ride.php">Book a Ride</a></p></article>
</section>
<section class="card">
    <h2>Recent Trip History</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Trip ID</th><th>Driver</th><th>Route</th><th>Fare</th><th>Status</th><th>Payment</th></tr>
            </thead>
            <tbody>
            <?php if ($tripHistory): ?>
                <?php foreach ($tripHistory as $trip): ?>
                    <tr>
                        <td><?php echo escapeOutput((string) $trip['trip_id']); ?></td>
                        <td><?php echo escapeOutput($trip['driver_name']); ?></td>
                        <td><?php echo escapeOutput($trip['pickup_location'] . ' to ' . $trip['dropoff_location']); ?></td>
                        <td>$<?php echo escapeOutput(number_format((float) $trip['total_fare'], 2)); ?></td>
                        <td><?php echo escapeOutput($trip['trip_status']); ?></td>
                        <td><?php echo escapeOutput($trip['payment_status'] ?? 'pending'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No trip records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
