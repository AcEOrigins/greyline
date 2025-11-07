<?php
// Admin Employee Management API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Database configuration
$host = "127.0.0.1";
$dbname = "u775021278_project_manage";
$username = "u775021278_PMadmin";
$password = ">q}Q>']6LNp~g+7";

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Get session token
$sessionToken = $_SERVER['HTTP_AUTHORIZATION'] ?? $data['session_token'] ?? '';

if (empty($sessionToken)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session token required']);
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

    // Verify session and get employee (must be admin)
    $stmt = $pdo->prepare("
        SELECT e.* FROM employees e 
        JOIN employee_sessions es ON e.id = es.employee_id 
        WHERE es.session_token = ? AND es.is_active = 1 AND e.role = 'admin'
    ");
    $stmt->execute([$sessionToken]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit();
    }

    $action = $_GET['action'] ?? $data['action'] ?? '';

    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list_employees':
                    $stmt = $pdo->prepare("
                        SELECT 
                            id, employee_id, email, first_name, last_name, role, 
                            department, phone, hire_date, is_active, created_at
                        FROM employees 
                        ORDER BY created_at DESC
                    ");
                    $stmt->execute();
                    $employees = $stmt->fetchAll();
                    
                    echo json_encode(['success' => true, 'employees' => $employees]);
                    break;

                case 'get_employee':
                    $employeeId = $_GET['employee_id'] ?? $data['employee_id'] ?? '';
                    
                    if (empty($employeeId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                        exit();
                    }
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            id, employee_id, email, first_name, last_name, role, 
                            department, phone, hire_date, is_active, created_at, updated_at
                        FROM employees 
                        WHERE id = ? OR employee_id = ?
                    ");
                    $stmt->execute([$employeeId, $employeeId]);
                    $employee = $stmt->fetch();
                    
                    if (!$employee) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Employee not found']);
                        exit();
                    }
                    
                    // Get employee's active projects
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
                    
                    // Get recent time logs
                    $stmt = $pdo->prepare("
                        SELECT 
                            contact_id,
                            start_time,
                            end_time,
                            duration_minutes,
                            activity_type,
                            description
                        FROM employee_time_logs 
                        WHERE employee_id = ?
                        ORDER BY start_time DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$employee['id']]);
                    $recentLogs = $stmt->fetchAll();
                    
                    $employee['active_projects'] = $activeProjects;
                    $employee['recent_logs'] = $recentLogs;
                    
                    echo json_encode(['success' => true, 'employee' => $employee]);
                    break;

                case 'employee_stats':
                    $employeeId = $_GET['employee_id'] ?? $data['employee_id'] ?? '';
                    
                    if (empty($employeeId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                        exit();
                    }
                    
                    // Get employee stats
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(DISTINCT ep.contact_id) as total_projects,
                            SUM(ep.hours_logged) as total_hours,
                            COUNT(DISTINCT etl.contact_id) as projects_with_time_logs,
                            SUM(etl.duration_minutes) / 60 as total_logged_hours
                        FROM employees e
                        LEFT JOIN employee_projects ep ON e.id = ep.employee_id
                        LEFT JOIN employee_time_logs etl ON e.id = etl.employee_id
                        WHERE e.id = ? OR e.employee_id = ?
                    ");
                    $stmt->execute([$employeeId, $employeeId]);
                    $stats = $stmt->fetch();
                    
                    echo json_encode(['success' => true, 'stats' => $stats]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;

        case 'POST':
            switch ($action) {
                case 'add_employee':
                    $employeeData = $data['employee'] ?? [];
                    
                    // Validation
                    $required = ['email', 'first_name', 'last_name', 'role', 'department'];
                    foreach ($required as $field) {
                        if (empty($employeeData[$field])) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                            exit();
                        }
                    }
                    
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
                    $stmt->execute([$employeeData['email']]);
                    if ($stmt->fetch()) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Email already exists']);
                        exit();
                    }
                    
                    // Generate employee ID
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employees");
                    $stmt->execute();
                    $count = $stmt->fetch()['count'];
                    $employeeId = 'EMP' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
                    
                    // Hash password (default password: 'password')
                    $passwordHash = password_hash('password', PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO employees (employee_id, email, password_hash, first_name, last_name, 
                                              role, department, phone, hire_date, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $employeeId,
                        $employeeData['email'],
                        $passwordHash,
                        $employeeData['first_name'],
                        $employeeData['last_name'],
                        $employeeData['role'],
                        $employeeData['department'],
                        $employeeData['phone'] ?? '',
                        $employeeData['hire_date'] ?? date('Y-m-d'),
                        true
                    ]);
                    
                    $newEmployeeId = $pdo->lastInsertId();
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Employee added successfully',
                        'employee_id' => $newEmployeeId,
                        'employee_code' => $employeeId,
                        'default_password' => 'password'
                    ]);
                    break;

                case 'assign_project':
                    $assignmentData = $data['assignment'] ?? [];
                    
                    if (empty($assignmentData['employee_id']) || empty($assignmentData['contact_id'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee ID and Contact ID required']);
                        exit();
                    }
                    
                    // Check if assignment already exists
                    $stmt = $pdo->prepare("
                        SELECT id FROM employee_projects 
                        WHERE employee_id = ? AND contact_id = ?
                    ");
                    $stmt->execute([$assignmentData['employee_id'], $assignmentData['contact_id']]);
                    if ($stmt->fetch()) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee already assigned to this project']);
                        exit();
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO employee_projects (employee_id, contact_id, role_in_project, notes)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $assignmentData['employee_id'],
                        $assignmentData['contact_id'],
                        $assignmentData['role_in_project'] ?? 'developer',
                        $assignmentData['notes'] ?? ''
                    ]);
                    
                    echo json_encode(['success' => true, 'message' => 'Project assigned successfully']);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;

        case 'PUT':
            switch ($action) {
                case 'update_employee':
                    $employeeId = $data['employee_id'] ?? '';
                    $employeeData = $data['employee'] ?? '';
                    
                    if (empty($employeeId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                        exit();
                    }
                    
                    $stmt = $pdo->prepare("
                        UPDATE employees 
                        SET first_name = ?, last_name = ?, role = ?, department = ?, 
                            phone = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ? OR employee_id = ?
                    ");
                    $stmt->execute([
                        $employeeData['first_name'],
                        $employeeData['last_name'],
                        $employeeData['role'],
                        $employeeData['department'],
                        $employeeData['phone'] ?? '',
                        $employeeData['is_active'] ?? true,
                        $employeeId,
                        $employeeId
                    ]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Employee not found']);
                    }
                    break;

                case 'reset_password':
                    $employeeId = $data['employee_id'] ?? '';
                    $newPassword = $data['new_password'] ?? 'password';
                    
                    if (empty($employeeId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                        exit();
                    }
                    
                    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("
                        UPDATE employees 
                        SET password_hash = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ? OR employee_id = ?
                    ");
                    $stmt->execute([$passwordHash, $employeeId, $employeeId]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Employee not found']);
                    }
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;

        case 'DELETE':
            switch ($action) {
                case 'deactivate_employee':
                    $employeeId = $data['employee_id'] ?? '';
                    
                    if (empty($employeeId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                        exit();
                    }
                    
                    // Deactivate employee (don't delete, just set inactive)
                    $stmt = $pdo->prepare("
                        UPDATE employees 
                        SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ? OR employee_id = ?
                    ");
                    $stmt->execute([$employeeId, $employeeId]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Employee deactivated successfully']);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Employee not found']);
                    }
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 