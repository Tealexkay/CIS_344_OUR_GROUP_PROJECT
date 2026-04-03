<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole('driver');

$connection = getDatabaseConnection();
$driverId = getDriverIdByUserId($connection, (int) getCurrentUserId());

if (isPostRequest() && $driverId) {
    requireValidCsrfToken($_POST['csrf_token'] ?? null);
    $status = sanitizeInput($_POST['availability_status'] ?? 'offline');
    if (in_array($status, ['available', 'busy', 'offline'], true)) {
        $statement = $connection->prepare('UPDATE drivers SET availability_status = ? WHERE id = ?');
        $statement->bind_param('si', $status, $driverId);
        $statement->execute();
        $statement->close();
        setFlashMessage('success', 'Availability status updated successfully.');
    } else {
        setFlashMessage('error', 'Invalid availability status selected.');
    }
    redirectTo('/sharing_ride_application/pages/driver/update_availability.php');
}

$currentStatus = 'offline';
if ($driverId) {
    $statement = $connection->prepare('SELECT availability_status FROM drivers WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $driverId);
    $statement->execute();
    $currentStatus = $statement->get_result()->fetch_assoc()['availability_status'] ?? 'offline';
    $statement->close();
}

$pageTitle = 'Update Availability';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-card narrow">
    <h2>Update Driver Availability</h2>
    <p>Current status: <strong><?php echo escapeOutput($currentStatus); ?></strong></p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo escapeOutput(generateCsrfToken()); ?>">
        <label>Availability Status
            <select name="availability_status">
                <option value="available">Available</option>
                <option value="busy">Busy</option>
                <option value="offline">Offline</option>
            </select>
        </label>
        <button type="submit" class="button primary">Update Status</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
