<?php
require_once __DIR__ . '/../../includes/booking.php';
require_once __DIR__ . '/../../includes/data.php';
requireRole('driver');

$assignedTrips = getDriverAssignedTrips((int) getCurrentUserId());

if (isPostRequest()) {
    requireValidCsrfToken($_POST['csrf_token'] ?? null);
    $tripId = (int) ($_POST['trip_id'] ?? 0);
    $actualDistance = (float) ($_POST['actual_distance_km'] ?? 0);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $result = completeTrip($tripId, $actualDistance, $notes, (int) getCurrentUserId());
    setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    redirectTo('/sharing_ride_application/pages/driver/complete_trip.php');
}

$pageTitle = 'Complete Trip';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-card">
    <h2>Complete Assigned Trip</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo escapeOutput(generateCsrfToken()); ?>">
        <label>Select Trip
            <select name="trip_id" required>
                <?php foreach ($assignedTrips as $trip): ?>
                    <option value="<?php echo escapeOutput((string) $trip['trip_id']); ?>">Trip #<?php echo escapeOutput((string) $trip['trip_id']); ?> - <?php echo escapeOutput($trip['rider_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Actual Distance (KM)
            <input type="number" step="0.01" min="0.01" name="actual_distance_km" required>
        </label>
        <label>Trip Notes
            <textarea name="notes" rows="4"></textarea>
        </label>
        <button type="submit" class="button primary">Complete Trip</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
