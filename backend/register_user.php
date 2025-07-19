<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database configuration
$host = "localhost";
$dbname = "u775021278_Greyline";
$username = "u775021278_devAdmin";
$password = "ay7QOXj6";

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$companyName = trim($data['companyName'] ?? '');
$phone = trim($data['phone'] ?? '');

// Validation
if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit();
}

try {
    // Database connection
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    $pdo = new PDO($dsn, $username, $password, $options);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verificationToken = bin2hex(random_bytes(32));
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, company_name, phone, verification_token) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $passwordHash, $firstName, $lastName, $companyName, $phone, $verificationToken]);
    
    $userId = $pdo->lastInsertId();
    
    // Link user to existing contact if one exists
    $stmt = $pdo->prepare("SELECT id FROM contacts WHERE email = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->execute([$email]);
    $contact = $stmt->fetch();
    
    if ($contact) {
        // Link the most recent contact to this user
        $stmt = $pdo->prepare("INSERT INTO user_projects (user_id, contact_id, project_status) VALUES (?, ?, 'pending')");
        $stmt->execute([$userId, $contact['id']]);
    }
    
    // Send verification email (you'll need to configure email settings)
    $verificationLink = "https://greylinestudio.com/verify_email.php?token=" . $verificationToken;
    
    // For now, just return success. You can implement email sending later
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! Please check your email to verify your account.',
        'user_id' => $userId,
        'verification_link' => $verificationLink // Remove this in production
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 