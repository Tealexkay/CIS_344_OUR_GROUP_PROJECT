<?php
require_once __DIR__ . '/../../includes/auth.php';

if (isPostRequest()) {
    requireValidCsrfToken($_POST['csrf_token'] ?? null);
    $result = registerUser($_POST);
    setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    if ($result['success']) {
        redirectTo('/sharing_ride_application/pages/auth/login.php');
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-card narrow">
    <h2>Create Account</h2>
    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo escapeOutput(generateCsrfToken()); ?>">
        <label>Full Name
            <input type="text" name="full_name" required>
        </label>
        <label>Email Address
            <input type="email" name="email" required>
        </label>
        <label>Phone Number
            <input type="text" name="phone" required>
        </label>
        <label>Role
            <select name="role" required>
                <option value="rider">Rider</option>
                <option value="driver">Driver</option>
            </select>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>Confirm Password
            <input type="password" name="confirm_password" required>
        </label>
        <button type="submit" class="button primary">Register</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
