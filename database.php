<?php

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'sharing_ride_db';
const DB_USER = 'root';
const DB_PASS = '';

function getDatabaseConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($connection->connect_error) {
        die('Database connection failed: ' . htmlspecialchars($connection->connect_error));
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}
