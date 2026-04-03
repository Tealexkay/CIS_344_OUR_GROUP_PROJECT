<?php
require_once __DIR__ . '/../../includes/auth.php';
logoutUser();
session_start();
setFlashMessage('success', 'You have been logged out successfully.');
redirectTo('/sharing_ride_application/pages/auth/login.php');
