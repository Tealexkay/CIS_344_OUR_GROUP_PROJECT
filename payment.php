<?php
require_once __DIR__ . '/../../includes/booking.php';
require_once __DIR__ . '/../../includes/data.php';
requireRole('rider');

$userId = (int) getCurrentUserId();
$tripHistory = getRiderTripHistory($userId);

if (isPostRequest()) {
    requireValidCsrfToken($_POST['csrf_token'] ?? null);
    $tripId = (int) ($_POST['trip_id'] ?? 0);
    $result = recordPaymentForTrip($_POST, $tripId, $userId);
    setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    redirectTo('/sharing_ride_application/pages/rider/payment.php');
}

$pageTitle = 'Record Payment';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-card">
    <h2>Record Payment</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo escapeOutput(generateCsrfToken()); ?>">
        <label>Select Trip
            <select name="trip_id" required>
                <?php foreach ($tripHistory as $trip): ?>
                    <option value="<?php echo escapeOutput((string) $trip['trip_id']); ?>">Trip #<?php echo escapeOutput((string) $trip['trip_id']); ?> - $<?php echo escapeOutput(number_format((float) $trip['total_fare'], 2)); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Amount
            <input type="number" step="0.01" min="0.01" name="amount" required>
        </label>
        <label>Payment Method
            <select name="payment_method">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="wallet">Wallet</option>
            </select>
        </label>
        <input type="hidden" name="payment_status" value="paid">
        <button type="submit" class="button primary">Record Payment</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
