<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (isLoggedIn()) {
    jsonResponse(true, 'Logged in', [
        'user_id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email']
    ]);
} else {
    jsonResponse(false, 'Not logged in');
}
