<?php
// Include database connection first
include 'dbconnect.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if PDO connection is established
if (!isset($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not established']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data with proper validation
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? '';
    $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING) ?? '';
    $house_code = filter_input(INPUT_POST, 'house', FILTER_SANITIZE_STRING) ?? '';
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT) ?? 0;
    $month = filter_input(INPUT_POST, 'month', FILTER_SANITIZE_STRING) ?? '';

    // Validate input
    if (empty($name) || empty($contact) || empty($house_code) || $amount <= 0 || empty($month)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required and amount must be greater than 0.']);
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // 1. Get house_id from house_code
        $stmt = $pdo->prepare("SELECT id, rent_amount FROM houses WHERE house_code = ?");
        $stmt->execute([$house_code]);
        $house = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$house) {
            throw new Exception("House not found: " . $house_code);
        }
        
        $house_id = $house['id'];
        $rent_amount = $house['rent_amount'];

        // 2. Check if tenant already exists for this house
        $stmt = $pdo->prepare("SELECT id, name, contact FROM tenants WHERE house_id = ?");
        $stmt->execute([$house_id]);
        $existing_tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        $tenant_id = null;

        if ($existing_tenant) {
            // Check if the contact matches the existing tenant's contact
            if ($existing_tenant['contact'] !== $contact) {
                throw new Exception("This house is already occupied by another tenant. Only the assigned tenant can make payments.");
            }

            // Use existing tenant
            $tenant_id = $existing_tenant['id'];
            
            // Update tenant information if needed (name might change)
            $stmt = $pdo->prepare("UPDATE tenants SET name = ? WHERE id = ?");
            $stmt->execute([$name, $tenant_id]);
        } else {
            // 3. Automatically create new tenant for vacant house
            $stmt = $pdo->prepare("INSERT INTO tenants (name, contact, house_id, rent_amount, month, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$name, $contact, $house_id, $rent_amount, $month]);
            $tenant_id = $pdo->lastInsertId();
        }

        // 4. Record the payment
        $stmt = $pdo->prepare("INSERT INTO payments (tenant_id, amount, month, date_time) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$tenant_id, $amount, $month]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Payment recorded successfully and tenant created automatically.']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all payments for display
    try {
        $stmt = $pdo->query("
            SELECT p.amount, p.month, p.date_time, t.name as tenant_name, h.house_code 
            FROM payments p 
            JOIN tenants t ON p.tenant_id = t.id 
            JOIN houses h ON t.house_id = h.id 
            ORDER BY p.date_time DESC
        ");
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($payments);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching payments: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>