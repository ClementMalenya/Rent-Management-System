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

$payment_number = filter_input(INPUT_POST, 'payment_number', FILTER_SANITIZE_STRING) ?? '';

if (empty($payment_number)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Payment number is required.'
    ]);
    exit;
}

try {
    // Check if payment number exists
    $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = 'payment_number'");
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'payment_number'");
        $stmt->execute([$payment_number]);
    } else {
        // Insert new
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('payment_number', ?)");
        $stmt->execute([$payment_number]);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payment number updated successfully.',
        'payment_number' => $payment_number
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error updating payment number: ' . $e->getMessage()
    ]);
}
?>