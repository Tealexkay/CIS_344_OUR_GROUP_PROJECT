<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$flashMessage = getFlashMessage();
$pageTitle = $pageTitle ?? 'Sharing Ride Application';
$userRole = getCurrentUserRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapeOutput($pageTitle); ?></title>
    <link rel="stylesheet" href="/sharing_ride_application/assets/css/style.css">
    <script defer src="/sharing_ride_application/assets/js/app.js"></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div>
            <h1 class="brand-title"><a href="/sharing_ride_application/index.php">Sharing_Ride</a></h1>
            <p class="brand-subtitle">Ride booking and payment management system</p>
        </div>
        <nav class="site-nav">
            <a href="/sharing_ride_application/index.php">Home</a>
            <?php if (!$userRole): ?>
                <a href="/sharing_ride_application/pages/auth/register.php">Register</a>
                <a href="/sharing_ride_application/pages/auth/login.php">Login</a>
            <?php elseif ($userRole === 'rider'): ?>
                <a href="/sharing_ride_application/pages/rider/dashboard.php">Rider Dashboard</a>
                <a href="/sharing_ride_application/pages/rider/book_ride.php">Book Ride</a>
                <a href="/sharing_ride_application/pages/rider/history.php">Trip History</a>
                <a href="/sharing_ride_application/pages/auth/logout.php">Logout</a>
            <?php elseif ($userRole === 'driver'): ?>
                <a href="/sharing_ride_application/pages/driver/dashboard.php">Driver Dashboard</a>
                <a href="/sharing_ride_application/pages/driver/update_availability.php">Availability</a>
                <a href="/sharing_ride_application/pages/auth/logout.php">Logout</a>
            <?php elseif ($userRole === 'admin'): ?>
                <a href="/sharing_ride_application/pages/admin/dashboard.php">Admin Dashboard</a>
                <a href="/sharing_ride_application/pages/auth/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container main-content">
    <?php if ($flashMessage): ?>
        <div class="alert alert-<?php echo escapeOutput($flashMessage['type']); ?>">
            <?php echo escapeOutput($flashMessage['message']); ?>
        </div>
    <?php endif; ?>
