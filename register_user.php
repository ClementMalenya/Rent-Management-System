<?php
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    $full_name = trim($_POST['full_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $house_code = trim($_POST['house_code'] ?? '');
    
    if (empty($username) || empty($password) || empty($user_type)) {
        echo json_encode(['success' => false, 'message' => 'Username, password, and user type are required']);
        exit;
    }
    
    if (!in_array($user_type, ['tenant', 'landlord'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
        exit;
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, user_type, full_name, contact, house_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password_hash, $user_type, $full_name, $contact, $house_code]);
        
        echo json_encode(['success' => true, 'message' => 'User registered successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>