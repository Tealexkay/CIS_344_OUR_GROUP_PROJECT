CREATE DATABASE IF NOT EXISTS sharing_ride_db;
USE sharing_ride_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS trips;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS riders;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'rider', 'driver') NOT NULL,
    account_status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

CREATE TABLE riders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    default_pickup_location VARCHAR(255) DEFAULT NULL,
    preferred_payment_method ENUM('cash', 'card', 'wallet') DEFAULT 'cash',
    emergency_contact VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_riders_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE TABLE drivers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    license_number VARCHAR(50) NOT NULL UNIQUE,
    availability_status ENUM('available', 'busy', 'offline') NOT NULL DEFAULT 'offline',
    current_location VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(3,2) DEFAULT 5.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_drivers_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE TABLE vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    driver_id INT UNSIGNED NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(30) NOT NULL,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    seat_capacity TINYINT UNSIGNED NOT NULL DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicles_driver FOREIGN KEY (driver_id) REFERENCES drivers(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rider_id INT UNSIGNED NOT NULL,
    driver_id INT UNSIGNED DEFAULT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    dropoff_location VARCHAR(255) NOT NULL,
    estimated_distance_km DECIMAL(8,2) NOT NULL,
    estimated_duration_minutes INT UNSIGNED DEFAULT NULL,
    base_fare DECIMAL(10,2) NOT NULL DEFAULT 5.00,
    distance_rate DECIMAL(10,2) NOT NULL DEFAULT 2.00,
    estimated_fare DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'accepted', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cash', 'card', 'wallet') NOT NULL DEFAULT 'cash',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_rider FOREIGN KEY (rider_id) REFERENCES riders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_driver FOREIGN KEY (driver_id) REFERENCES drivers(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ;

CREATE TABLE trips (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    rider_id INT UNSIGNED NOT NULL,
    driver_id INT UNSIGNED NOT NULL,
    start_time DATETIME DEFAULT NULL,
    end_time DATETIME DEFAULT NULL,
    actual_distance_km DECIMAL(8,2) DEFAULT NULL,
    total_fare DECIMAL(10,2) NOT NULL,
    trip_status ENUM('ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'ongoing',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_trips_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_trips_rider FOREIGN KEY (rider_id) REFERENCES riders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_trips_driver FOREIGN KEY (driver_id) REFERENCES drivers(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trip_id INT UNSIGNED NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'wallet') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    transaction_reference VARCHAR(100) DEFAULT NULL UNIQUE,
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_trip FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ;
INSERT INTO users (full_name, email, phone, password_hash, role, account_status)
VALUES
('Computer Science', 'computerscience@gmail.com', '1007863549', '$2y$10$ibrahimcishADMIN0000000123986346200000000000project344', 'admin', 'active'),
('Yanilda Peralta', 'rideryanilda@gmail.com', '1035749861', '$2y$10$kadidiacishRIDER0000023409t4esa344000000project0000000', 'rider', 'active'),
('Lehman College', 'lehmandriver@gmail.com', '1237654902', '$2y$10$kadiatoucisDRIVER0034400pouytrewd5project000ytre340pot', 'driver', 'active');

INSERT INTO riders (user_id, default_pickup_location, preferred_payment_method, emergency_contact)
VALUES
(2, 'Campus Gate A', 'cash', '1034578099');

INSERT INTO drivers (user_id, license_number, availability_status, current_location, rating)
VALUES
(3, 'DRV-2026-001', 'available', 'City Center', 4.80);

INSERT INTO vehicles (driver_id, vehicle_type, brand, model, color, plate_number, seat_capacity)
VALUES
(1, 'Sedan', 'Toyota', 'Corolla', 'White', 'ABC-1234', 4);

INSERT INTO bookings (
    rider_id, driver_id, pickup_location, dropoff_location, estimated_distance_km,
    estimated_duration_minutes, base_fare, distance_rate, estimated_fare, booking_status, payment_method
) VALUES
(1, 1, 'Campus Gate A', 'Central Library', 6.50, 18, 5.00, 2.00, 18.00, 'accepted', 'cash');

INSERT INTO trips (
    booking_id, rider_id, driver_id, start_time, end_time, actual_distance_km,
    total_fare, trip_status, notes
) VALUES
(1, 1, 1, '2026-03-28 09:00:00', '2026-03-28 09:25:00', 6.80, 18.60, 'completed', 'Sample completed trip for testing');

INSERT INTO payments (
    trip_id, amount, payment_method, payment_status, transaction_reference, paid_at
) VALUES
(1, 18.60, 'cash', 'paid', 'TXN-1001', '2026-03-28 09:26:00');

SELECT 
    b.id AS booking_id,
    ur.full_name AS rider_name,
    ud.full_name AS driver_name,
    b.pickup_location,
    b.dropoff_location,
    b.estimated_fare,
    b.booking_status
FROM bookings b
INNER JOIN riders r ON b.rider_id = r.id
INNER JOIN users ur ON r.user_id = ur.id
LEFT JOIN drivers d ON b.driver_id = d.id
LEFT JOIN users ud ON d.user_id = ud.id;

SELECT 
    t.id AS trip_id,
    ur.full_name AS rider_name,
    ud.full_name AS driver_name,
    b.pickup_location,
    b.dropoff_location,
    t.actual_distance_km,
    t.total_fare,
    p.payment_status,
    p.payment_method
FROM trips t
INNER JOIN bookings b ON t.booking_id = b.id
INNER JOIN riders r ON t.rider_id = r.id
INNER JOIN users ur ON r.user_id = ur.id
INNER JOIN drivers d ON t.driver_id = d.id
INNER JOIN users ud ON d.user_id = ud.id
LEFT JOIN payments p ON p.trip_id = t.id;

SELECT 
    d.id AS driver_id,
    u.full_name AS driver_name,
    d.availability_status,
    v.vehicle_type,
    v.brand,
    v.model,
    v.plate_number
FROM drivers d
INNER JOIN users u ON d.user_id = u.id
LEFT JOIN vehicles v ON v.driver_id = d.id;
