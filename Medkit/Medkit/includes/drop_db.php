<?php
$conn = new mysqli('127.0.0.1', 'root', '');
if ($conn->connect_error) {
    die("Connect error: " . $conn->connect_error);
}
echo "Connected successfully\n";

if ($conn->query("DROP DATABASE IF EXISTS medkit_db")) {
    echo "Database dropped\n";
} else {
    echo "Drop error: " . $conn->error . "\n";
}
