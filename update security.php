<?php
require_once 'dbconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $new_security_answer = trim($_POST['new_security_answer']);
    
    if (empty($username) || empty($new_security_answer)) {
        echo json_encode(['success' => false, 'message' => 'Username and new security answer are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET security_answer = ? WHERE username = ?");
        $stmt->execute([$new_security_answer, $username]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Security answer updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found or security answer unchanged']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>