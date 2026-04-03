<?php
require_once __DIR__ . '/../../includes/booking.php';
requireRole('rider');

if (isPostRequest()) {
    requireValidCsrfToken($_POST['csrf_token'] ?? null);
    $result = createBooking($_POST, (int) getCurrentUserId());
    setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    redirectTo('/sharing_ride_application/pages/rider/book_ride.php');
}

$pageTitle = 'Book Ride';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-card">
    <h2>Book a Ride</h2>
    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo escapeOutput(generateCsrfToken()); ?>">
        <label>Pickup Location
            <input type="text" name="pickup_location" required>
        </label>
        <label>Drop-off Location
            <input type="text" name="dropoff_location" required>
        </label>
        <label>Estimated Distance (KM)
            <input type="number" step="0.01" min="0.1" name="estimated_distance_km" required>
        </label>
        <label>Estimated Duration (Minutes)
            <input type="number" min="1" name="estimated_duration_minutes" required>
        </label>
        <label>Payment Method
            <select name="payment_method" required>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="wallet">Wallet</option>
            </select>
        </label>
        <button type="submit" class="button primary">Submit Booking</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
