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

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? '';
$contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING) ?? '';
$house_code = filter_input(INPUT_POST, 'house', FILTER_SANITIZE_STRING) ?? '';

if (empty($id) || empty($name) || empty($contact) || empty($house_code)) {
    echo json_encode([
        'success' => false, 
        'message' => 'All fields are required.'
    ]);
    exit;
}

try {
    // Get house_id from house_code
    $stmt = $pdo->prepare("SELECT id, rent_amount FROM houses WHERE house_code = ?");
    $stmt->execute([$house_code]);
    $house = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$house) {
        echo json_encode([
            'success' => false, 
            'message' => 'House not found: ' . $house_code
        ]);
        exit;
    }
    
    $house_id = $house['id'];
    $rent_amount = $house['rent_amount'];

    // Check if the new house is already occupied by another tenant
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE house_id = ? AND id != ?");
    $stmt->execute([$house_id, $id]);
    $existing_tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_tenant) {
        echo json_encode([
            'success' => false, 
            'message' => 'House is already occupied by another tenant.'
        ]);
        exit;
    }

    // Update tenant
    $stmt = $pdo->prepare("UPDATE tenants SET name = ?, contact = ?, house_id = ?, rent_amount = ? WHERE id = ?");
    $stmt->execute([$name, $contact, $house_id, $rent_amount, $id]);

    echo json_encode([
        'success' => true, 
        'message' => 'Tenant updated successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>