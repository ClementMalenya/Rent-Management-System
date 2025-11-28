<?php
require_once 'dbconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $security_answer = trim($_POST['security_answer'] ?? '');
    
    if (empty($username) || empty($new_password)) {
        echo json_encode(['success' => false, 'message' => 'Username and new password are required']);
        exit;
    }
    
    if (strlen($new_password) < 4) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 4 characters long']);
        exit;
    }
    
    try {
        // Get security answer from database
        $stmt = $pdo->prepare("SELECT security_question, security_answer FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        // Use whatever security answer is in database
        $expectedAnswer = $user['security_answer'];
        
        if (empty($security_answer) || strtolower(trim($security_answer)) !== strtolower(trim($expectedAnswer))) {
            echo json_encode(['success' => false, 'message' => 'Incorrect security answer']);
            exit;
        }

        // Update password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$new_password_hash, $username]);

        echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>