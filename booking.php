<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function createBooking(array $data, int $userId): array
{
    $connection = getDatabaseConnection();
    $riderId = getRiderIdByUserId($connection, $userId);

    if (!$riderId) {
        return ['success' => false, 'message' => 'Rider profile not found.'];
    }

    $pickupLocation = sanitizeInput($data['pickup_location'] ?? '');
    $dropoffLocation = sanitizeInput($data['dropoff_location'] ?? '');
    $estimatedDistance = (float) ($data['estimated_distance_km'] ?? 0);
    $estimatedDuration = (int) ($data['estimated_duration_minutes'] ?? 0);
    $paymentMethod = sanitizeInput($data['payment_method'] ?? 'cash');

    if ($pickupLocation === '' || $dropoffLocation === '' || $estimatedDistance <= 0) {
        return ['success' => false, 'message' => 'Pickup, drop-off, and distance are required.'];
    }

    if (!in_array($paymentMethod, ['cash', 'card', 'wallet'], true)) {
        return ['success' => false, 'message' => 'Invalid payment method selected.'];
    }

    $driverId = findAvailableDriverId($connection);
    $estimatedFare = calculateEstimatedFare($estimatedDistance);

    if ($driverId) {
        $statement = $connection->prepare('INSERT INTO bookings (rider_id, driver_id, pickup_location, dropoff_location, estimated_distance_km, estimated_duration_minutes, base_fare, distance_rate, estimated_fare, booking_status, payment_method) VALUES (?, ?, ?, ?, ?, ?, 5.00, 2.00, ?, "accepted", ?)');
        $statement->bind_param('iissdids', $riderId, $driverId, $pickupLocation, $dropoffLocation, $estimatedDistance, $estimatedDuration, $estimatedFare, $paymentMethod);
    } else {
        $statement = $connection->prepare('INSERT INTO bookings (rider_id, pickup_location, dropoff_location, estimated_distance_km, estimated_duration_minutes, base_fare, distance_rate, estimated_fare, booking_status, payment_method) VALUES (?, ?, ?, ?, ?, 5.00, 2.00, ?, "pending", ?)');
        $statement->bind_param('issdids', $riderId, $pickupLocation, $dropoffLocation, $estimatedDistance, $estimatedDuration, $estimatedFare, $paymentMethod);
    }

    if (!$statement->execute()) {
        $statement->close();
        return ['success' => false, 'message' => 'Failed to save booking.'];
    }

    $bookingId = (int) $connection->insert_id;
    $statement->close();

    if ($driverId) {
        $updateDriver = $connection->prepare('UPDATE drivers SET availability_status = "busy" WHERE id = ?');
        $updateDriver->bind_param('i', $driverId);
        $updateDriver->execute();
        $updateDriver->close();

        $tripStatement = $connection->prepare('INSERT INTO trips (booking_id, rider_id, driver_id, start_time, total_fare, trip_status) VALUES (?, ?, ?, NOW(), ?, "ongoing")');
        $tripStatement->bind_param('iiid', $bookingId, $riderId, $driverId, $estimatedFare);
        $tripStatement->execute();
        $tripStatement->close();
    }

    return [
        'success' => true,
        'message' => $driverId ? 'Booking created and assigned to an available driver.' : 'Booking created. No driver is currently available.',
    ];
}

function recordPaymentForTrip(array $data, int $tripId, int $userId): array
{
    $connection = getDatabaseConnection();
    $amount = (float) ($data['amount'] ?? 0);
    $paymentMethod = sanitizeInput($data['payment_method'] ?? 'cash');
    $paymentStatus = sanitizeInput($data['payment_status'] ?? 'paid');
    $transactionReference = 'TXN-' . time() . '-' . rand(100, 999);

    if ($amount <= 0) {
        return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
    }

    if (!in_array($paymentMethod, ['cash', 'card', 'wallet'], true)) {
        return ['success' => false, 'message' => 'Invalid payment method.'];
    }

    $ownershipStatement = $connection->prepare('SELECT t.id FROM trips t INNER JOIN riders r ON t.rider_id = r.id WHERE t.id = ? AND r.user_id = ? LIMIT 1');
    $ownershipStatement->bind_param('ii', $tripId, $userId);
    $ownershipStatement->execute();
    $ownedTrip = $ownershipStatement->get_result()->fetch_assoc();
    $ownershipStatement->close();

    if (!$ownedTrip) {
        return ['success' => false, 'message' => 'You are not allowed to record payment for this trip.'];
    }

    $checkStatement = $connection->prepare('SELECT id FROM payments WHERE trip_id = ? LIMIT 1');
    $checkStatement->bind_param('i', $tripId);
    $checkStatement->execute();
    $existing = $checkStatement->get_result()->fetch_assoc();
    $checkStatement->close();

    if ($existing) {
        return ['success' => false, 'message' => 'Payment has already been recorded for this trip.'];
    }

    $statement = $connection->prepare('INSERT INTO payments (trip_id, amount, payment_method, payment_status, transaction_reference, paid_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $statement->bind_param('idsss', $tripId, $amount, $paymentMethod, $paymentStatus, $transactionReference);

    if (!$statement->execute()) {
        $statement->close();
        return ['success' => false, 'message' => 'Failed to record payment.'];
    }

    $statement->close();
    return ['success' => true, 'message' => 'Payment recorded successfully.'];
}

function completeTrip(int $tripId, float $actualDistanceKm, string $notes, int $userId): array
{
    $connection = getDatabaseConnection();

    if ($actualDistanceKm <= 0) {
        return ['success' => false, 'message' => 'Actual distance must be greater than zero.'];
    }

    $tripStatement = $connection->prepare('SELECT t.booking_id, t.driver_id FROM trips t INNER JOIN drivers d ON t.driver_id = d.id WHERE t.id = ? AND d.user_id = ? LIMIT 1');
    $tripStatement->bind_param('ii', $tripId, $userId);
    $tripStatement->execute();
    $trip = $tripStatement->get_result()->fetch_assoc();
    $tripStatement->close();

    if (!$trip) {
        return ['success' => false, 'message' => 'Trip not found or not assigned to you.'];
    }

    $totalFare = calculateEstimatedFare($actualDistanceKm);
    $tripStatus = 'completed';
    $statement = $connection->prepare('UPDATE trips SET end_time = NOW(), actual_distance_km = ?, total_fare = ?, trip_status = ?, notes = ? WHERE id = ?');
    $statement->bind_param('ddssi', $actualDistanceKm, $totalFare, $tripStatus, $notes, $tripId);
    $statement->execute();
    $statement->close();

    $bookingStatus = 'completed';
    $bookingStatement = $connection->prepare('UPDATE bookings SET booking_status = ? WHERE id = ?');
    $bookingStatement->bind_param('si', $bookingStatus, $trip['booking_id']);
    $bookingStatement->execute();
    $bookingStatement->close();

    $driverStatus = 'available';
    $driverStatement = $connection->prepare('UPDATE drivers SET availability_status = ? WHERE id = ?');
    $driverStatement->bind_param('si', $driverStatus, $trip['driver_id']);
    $driverStatement->execute();
    $driverStatement->close();

    return ['success' => true, 'message' => 'Trip completed successfully.', 'total_fare' => $totalFare];
}
