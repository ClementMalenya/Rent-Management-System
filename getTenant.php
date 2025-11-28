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
    $stmt = $pdo->query("
        SELECT t.id, t.name, t.contact, h.house_code, t.rent_amount, t.month, t.status 
        FROM tenants t 
        JOIN houses h ON t.house_id = h.id
        ORDER BY h.house_code
    ");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tenants);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching tenants: ' . $e->getMessage()
    ]);
}
?>