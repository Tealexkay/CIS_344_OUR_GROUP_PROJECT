<?php
require_once __DIR__ . '/../../includes/auth.php';

if (isPostRequest()) {
    requireValidCsrfToken($_POST['csrf_token'] ?? null);
    $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
    setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);

    if ($result['success']) {
        $role = $result['role'];
        if ($role === 'rider') {
            redirectTo('/sharing_ride_application/pages/rider/dashboard.php');
        }
        if ($role === 'driver') {
            redirectTo('/sharing_ride_application/pages/driver/dashboard.php');
        }
        redirectTo('/sharing_ride_application/pages/admin/dashboard.php');
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-card narrow">
    <h2>Login</h2>
    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo escapeOutput(generateCsrfToken()); ?>">
        <label>Email Address
            <input type="email" name="email" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="button primary">Login</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
