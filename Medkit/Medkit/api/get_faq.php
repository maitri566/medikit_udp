<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$conn = getDBConnection();

$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

if (!empty($category)) {
    $stmt = $conn->prepare("SELECT id, question, answer, category FROM faq WHERE is_active = 1 AND category = ? ORDER BY display_order ASC, id ASC");
    $stmt->bind_param("s", $category);
} else {
    $stmt = $conn->prepare("SELECT id, question, answer, category FROM faq WHERE is_active = 1 ORDER BY category ASC, display_order ASC, id ASC");
}

if (!$stmt->execute()) {
    jsonResponse(false, 'Failed to retrieve FAQs');
}

$result = $stmt->get_result();

$faqs = [];
while ($row = $result->fetch_assoc()) {
    $faqs[] = $row;
}

jsonResponse(true, 'FAQs retrieved successfully', $faqs);
