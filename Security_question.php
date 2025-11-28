<?php
require_once 'dbconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $security_answer = trim($_POST['security_answer'] ?? '');
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }
    
    try {
        // Get security question and answer from database
        $stmt = $pdo->prepare("SELECT security_question, security_answer FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        // If just checking the question (no answer provided), return the question
        if (empty($security_answer)) {
            echo json_encode([
                'success' => true, 
                'security_question' => $user['security_question'] ?: 'What is the name of your apartment?'
            ]);
            exit;
        }

        // Check the answer against database
        $expectedAnswer = $user['security_answer'];
        
        if (strtolower(trim($security_answer)) === strtolower(trim($expectedAnswer))) {
            echo json_encode([
                'success' => true, 
                'message' => 'Security question answered correctly',
                'security_question' => $user['security_question'] ?: 'What is the name of your apartment?'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect security answer']);
        }

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>