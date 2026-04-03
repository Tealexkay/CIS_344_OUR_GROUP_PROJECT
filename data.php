<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function getRiderTripHistory(int $userId): array
{
    $connection = getDatabaseConnection();
    $sql = "SELECT 
                t.id AS trip_id,
                b.pickup_location,
                b.dropoff_location,
                ud.full_name AS driver_name,
                t.actual_distance_km,
                t.total_fare,
                t.trip_status,
                p.payment_status,
                p.payment_method,
                t.created_at
            FROM trips t
            INNER JOIN bookings b ON t.booking_id = b.id
            INNER JOIN drivers d ON t.driver_id = d.id
            INNER JOIN users ud ON d.user_id = ud.id
            INNER JOIN riders r ON t.rider_id = r.id
            LEFT JOIN payments p ON p.trip_id = t.id
            WHERE r.user_id = ?
            ORDER BY t.created_at DESC";

    $statement = $connection->prepare($sql);
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function getDriverAssignedTrips(int $userId): array
{
    $connection = getDatabaseConnection();
    $sql = "SELECT 
                t.id AS trip_id,
                ur.full_name AS rider_name,
                b.pickup_location,
                b.dropoff_location,
                b.estimated_distance_km,
                t.total_fare,
                t.trip_status,
                t.created_at
            FROM trips t
            INNER JOIN bookings b ON t.booking_id = b.id
            INNER JOIN riders r ON t.rider_id = r.id
            INNER JOIN users ur ON r.user_id = ur.id
            INNER JOIN drivers d ON t.driver_id = d.id
            WHERE d.user_id = ?
            ORDER BY t.created_at DESC";

    $statement = $connection->prepare($sql);
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function getAdminDashboardData(): array
{
    $connection = getDatabaseConnection();

    $stats = [
        'users' => countRows($connection, 'users'),
        'riders' => countRows($connection, 'riders'),
        'drivers' => countRows($connection, 'drivers'),
        'bookings' => countRows($connection, 'bookings'),
        'trips' => countRows($connection, 'trips'),
        'payments' => countRows($connection, 'payments'),
    ];

    $query = "SELECT 
                b.id AS booking_id,
                ur.full_name AS rider_name,
                COALESCE(ud.full_name, 'Not Assigned') AS driver_name,
                b.pickup_location,
                b.dropoff_location,
                b.estimated_fare,
                b.booking_status,
                p.payment_status
            FROM bookings b
            INNER JOIN riders r ON b.rider_id = r.id
            INNER JOIN users ur ON r.user_id = ur.id
            LEFT JOIN drivers d ON b.driver_id = d.id
            LEFT JOIN users ud ON d.user_id = ud.id
            LEFT JOIN trips t ON t.booking_id = b.id
            LEFT JOIN payments p ON p.trip_id = t.id
            ORDER BY b.requested_at DESC";

    $result = $connection->query($query);
    $bookings = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    return ['stats' => $stats, 'bookings' => $bookings];
}
