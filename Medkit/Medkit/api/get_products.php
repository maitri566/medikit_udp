<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$conn = getDBConnection();

// Get category filter and search from query params
$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];
$types = "";

if ($category !== 'all' && $category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

$sql .= " ORDER BY id ASC";

if ($limit > 0) {
    $sql .= " LIMIT ?";
    $params[] = $limit;
    $types .= "i";
}

$stmt = $conn->prepare($sql);
if (!$params) {
    $stmt = $conn->query($sql);
    $result = $stmt;
} else {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'category' => $row['category'],
        'price' => (float)$row['price'],
        'oldPrice' => (float)$row['old_price'],
        'rating' => (float)$row['rating'],
        'image' => $row['image'],
        'discount' => $row['discount'],
        'description' => $row['description'],
        'stock' => (int)$row['stock']
    ];
}

jsonResponse(true, 'Products fetched', $products);
