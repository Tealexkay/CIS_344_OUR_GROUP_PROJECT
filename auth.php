<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function registerUser(array $data): array
{
    $connection = getDatabaseConnection();

    $fullName = sanitizeInput($data['full_name'] ?? '');
    $email = filter_var(sanitizeInput($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = sanitizeInput($data['phone'] ?? '');
    $password = (string) ($data['password'] ?? '');
    $confirmPassword = (string) ($data['confirm_password'] ?? '');
    $role = sanitizeInput($data['role'] ?? 'rider');

    if ($fullName === '' || !$email || $phone === '' || $password === '' || $confirmPassword === '') {
        return ['success' => false, 'message' => 'All required fields must be completed.'];
    }

    if (!in_array($role, ['rider', 'driver'], true)) {
        return ['success' => false, 'message' => 'Invalid role selected.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    $checkSql = 'SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1';
    $checkStatement = $connection->prepare($checkSql);
    $checkStatement->bind_param('ss', $email, $phone);
    $checkStatement->execute();
    $existingUser = $checkStatement->get_result()->fetch_assoc();
    $checkStatement->close();

    if ($existingUser) {
        return ['success' => false, 'message' => 'An account with this email or phone already exists.'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertUserSql = 'INSERT INTO users (full_name, email, phone, password_hash, role, account_status) VALUES (?, ?, ?, ?, ?, "active")';
    $insertUserStatement = $connection->prepare($insertUserSql);
    $insertUserStatement->bind_param('sssss', $fullName, $email, $phone, $passwordHash, $role);

    if (!$insertUserStatement->execute()) {
        $insertUserStatement->close();
        return ['success' => false, 'message' => 'Failed to create user account.'];
    }

    $userId = (int) $connection->insert_id;
    $insertUserStatement->close();

    if ($role === 'rider') {
        $statement = $connection->prepare('INSERT INTO riders (user_id, preferred_payment_method) VALUES (?, "cash")');
        $statement->bind_param('i', $userId);
        $statement->execute();
        $statement->close();
    }

    if ($role === 'driver') {
        $licenseNumber = 'DRV-' . date('Ymd') . '-' . $userId;
        $statement = $connection->prepare('INSERT INTO drivers (user_id, license_number, availability_status, rating) VALUES (?, ?, "offline", 5.00)');
        $statement->bind_param('is', $userId, $licenseNumber);
        $statement->execute();
        $driverId = (int) $connection->insert_id;
        $statement->close();

        $vehicleType = 'Sedan';
        $brand = 'Not Set';
        $model = 'Not Set';
        $color = 'Unknown';
        $plateNumber = 'TEMP-' . $userId . '-' . rand(100, 999);
        $seatCapacity = 4;

        $vehicleStatement = $connection->prepare('INSERT INTO vehicles (driver_id, vehicle_type, brand, model, color, plate_number, seat_capacity) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $vehicleStatement->bind_param('isssssi', $driverId, $vehicleType, $brand, $model, $color, $plateNumber, $seatCapacity);
        $vehicleStatement->execute();
        $vehicleStatement->close();
    }

    return ['success' => true, 'message' => 'Registration successful. Please log in.'];
}

function loginUser(string $email, string $password): array
{
    $connection = getDatabaseConnection();

    $email = sanitizeInput($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        return ['success' => false, 'message' => 'Please enter a valid email and password.'];
    }

    $statement = $connection->prepare('SELECT id, full_name, email, password_hash, role, account_status FROM users WHERE email = ? LIMIT 1');
    $statement->bind_param('s', $email);
    $statement->execute();
    $result = $statement->get_result();
    $user = $result->fetch_assoc();
    $statement->close();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid login credentials.'];
    }

    if ($user['account_status'] !== 'active') {
        return ['success' => false, 'message' => 'Your account is not active.'];
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    return ['success' => true, 'message' => 'Login successful.', 'role' => $user['role']];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
