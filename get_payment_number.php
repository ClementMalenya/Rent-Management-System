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
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'payment_number'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'payment_number' => $result['setting_value']
        ]);
    } else {
        // Return default if not found
        echo json_encode([
            'success' => true, 
            'payment_number' => '0794 321 374'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching payment number: ' . $e->getMessage()
    ]);
}
?>