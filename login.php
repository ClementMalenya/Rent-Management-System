<?php
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }
    
    try {
        // Check if user exists (exact match)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // User exists in database - verify password
            if (password_verify($password, $user['password_hash'])) {
                // Login successful
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful', 
                    'data' => [
                        'user_type' => $user['user_type'],
                        'full_name' => $user['full_name'] ?: $username
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            // User doesn't exist in database - treat as tenant with default password
            if ($username === 'landlord') {
                echo json_encode(['success' => false, 'message' => 'Landlord not found. Please contact administrator.']);
            } else {
                // For any other username, treat as tenant and check default password
                if ($password === '@tenant123') {
                    // Auto-create tenant user or just allow login
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Login successful', 
                        'data' => [
                            'user_type' => 'tenant',
                            'full_name' => $username
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid tenant password. Use @tenant123']);
                }
            }
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>