<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

session_destroy();
jsonResponse(true, 'Logged out successfully');
