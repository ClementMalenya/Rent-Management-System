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

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? '';
$contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING) ?? '';
$house_code = filter_input(INPUT_POST, 'house', FILTER_SANITIZE_STRING) ?? '';

if (empty($name) || empty($contact) || empty($house_code)) {
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

    // Check if house is already occupied
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE house_id = ?");
    $stmt->execute([$house_id]);
    $existing_tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_tenant) {
        echo json_encode([
            'success' => false, 
            'message' => 'House is already occupied by another tenant.'
        ]);
        exit;
    }

    // Insert new tenant with current month as default
    $current_month = date('F');
    $stmt = $pdo->prepare("INSERT INTO tenants (name, contact, house_id, rent_amount, month, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$name, $contact, $house_id, $rent_amount, $current_month]);

    echo json_encode([
        'success' => true, 
        'message' => 'Tenant added successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>