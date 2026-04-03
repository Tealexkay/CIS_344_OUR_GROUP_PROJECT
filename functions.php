<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

function sanitizeInput(?string $value): string
{
    return trim((string) $value);
}

function escapeOutput(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function isPostRequest(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function requireLogin(): void
{
    if (empty($_SESSION['user'])) {
        setFlashMessage('error', 'Please log in to continue.');
        redirectTo('/sharing_ride_application/pages/auth/login.php');
    }
}

function requireRole(string $role): void
{
    requireLogin();

    if (($_SESSION['user']['role'] ?? '') !== $role) {
        setFlashMessage('error', 'You are not authorized to access that page.');
        redirectTo('/sharing_ride_application/index.php');
    }
}

function getCurrentUserId(): ?int
{
    return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
}

function getCurrentUserRole(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function requireValidCsrfToken(?string $token): void
{
    if (!verifyCsrfToken($token)) {
        setFlashMessage('error', 'Security validation failed. Please try again.');
        redirectTo($_SERVER['REQUEST_URI'] ?? '/sharing_ride_application/index.php');
    }
}

function calculateEstimatedFare(float $distanceKm, float $baseFare = 5.00, float $distanceRate = 2.00): float
{
    return round($baseFare + ($distanceKm * $distanceRate), 2);
}

function findAvailableDriverId(mysqli $connection): ?int
{
    $sql = "SELECT id FROM drivers WHERE availability_status = 'available' ORDER BY rating DESC, id ASC LIMIT 1";
    $result = $connection->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        return (int) $row['id'];
    }

    return null;
}

function getRiderIdByUserId(mysqli $connection, int $userId): ?int
{
    $statement = $connection->prepare('SELECT id FROM riders WHERE user_id = ? LIMIT 1');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();
    $statement->close();

    return $row ? (int) $row['id'] : null;
}

function getDriverIdByUserId(mysqli $connection, int $userId): ?int
{
    $statement = $connection->prepare('SELECT id FROM drivers WHERE user_id = ? LIMIT 1');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();
    $statement->close();

    return $row ? (int) $row['id'] : null;
}

function countRows(mysqli $connection, string $table): int
{
    $allowedTables = ['users', 'riders', 'drivers', 'bookings', 'trips', 'payments'];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $connection->query("SELECT COUNT(*) AS total FROM {$table}");
    $row = $result ? $result->fetch_assoc() : ['total' => 0];

    return (int) ($row['total'] ?? 0);
}
