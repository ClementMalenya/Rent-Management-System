<?php
include 'dbconnect.php';

header('Content-Type: application/json');

// Check if PDO connection is established
if (!isset($pdo)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection not established'
    ]);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, house_code, rent_amount, status FROM houses ORDER BY house_code");
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($houses);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching houses: ' . $e->getMessage()
    ]);
}
?>