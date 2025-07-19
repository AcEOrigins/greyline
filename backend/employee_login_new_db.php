<?php
// Employee Login API - Updated for new project management database
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database configuration - NEW PROJECT MANAGEMENT DATABASE
$host = "localhost";
$dbname = "u775021278_project_manage";
$username = "u775021278_userAdmin";
$password = ">q}Q>']6LNp~g+7";

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

try {
    // Database connection to NEW database
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    $pdo = new PDO($dsn, $username, $password, $options);

    // Get employee by email
    $stmt = $pdo->prepare("SELECT id, employee_id, email, password_hash, first_name, last_name, role, department, is_active FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }

    // Check if employee is active
    if (!$employee['is_active']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Account is deactivated']);
        exit();
    }

    // Verify password (using password_verify for bcrypt)
    if (!password_verify($password, $employee['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }

    // Generate session token
    $sessionToken = bin2hex(random_bytes(32));
    
    // Get client IP and user agent
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Create session record
    $stmt = $pdo->prepare("INSERT INTO employee_sessions (employee_id, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$employee['id'], $sessionToken, $clientIP, $userAgent]);
    
    // Get employee's active projects (cross-database query)
    $stmt = $pdo->prepare("
        SELECT 
            ep.contact_id,
            ep.role_in_project,
            ep.status,
            ep.hours_logged,
            c.job_number,
            c.project_title,
            c.name as client_name
        FROM employee_projects ep
        LEFT JOIN u775021278_Greyline.contacts c ON ep.contact_id = c.id
        WHERE ep.employee_id = ? AND ep.status = 'active'
        ORDER BY ep.assigned_date DESC
    ");
    $stmt->execute([$employee['id']]);
    $activeProjects = $stmt->fetchAll();
    
    // Get today's time logs
    $stmt = $pdo->prepare("
        SELECT 
            contact_id,
            SUM(duration_minutes) as total_minutes,
            COUNT(*) as sessions_count
        FROM employee_time_logs 
        WHERE employee_id = ? AND DATE(start_time) = CURDATE()
        GROUP BY contact_id
    ");
    $stmt->execute([$employee['id']]);
    $todayLogs = $stmt->fetchAll();
    
    // Return employee data with session token
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'session_token' => $sessionToken,
        'employee' => [
            'id' => $employee['id'],
            'employee_id' => $employee['employee_id'],
            'email' => $employee['email'],
            'firstName' => $employee['first_name'],
            'lastName' => $employee['last_name'],
            'role' => $employee['role'],
            'department' => $employee['department']
        ],
        'active_projects' => $activeProjects,
        'today_logs' => $todayLogs
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 