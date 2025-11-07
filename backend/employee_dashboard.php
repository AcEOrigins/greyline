<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Database configuration
$host = "127.0.0.1";
$dbname = "u775021278_users_manage";
$username = "u775021278_userAdmin";
$password = ">q}Q>']6LNp~g+7";

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Get session token from headers or POST data
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

    // Verify session token and get employee
    $stmt = $pdo->prepare("
        SELECT e.*, es.login_time 
        FROM employees e 
        JOIN employee_sessions es ON e.id = es.employee_id 
        WHERE es.session_token = ? AND es.is_active = 1
    ");
    $stmt->execute([$sessionToken]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired session']);
        exit();
    }

    switch ($method) {
        case 'GET':
            // Get employee dashboard data
            $dashboardData = [];
            
            // Get active projects
            $stmt = $pdo->prepare("
                SELECT 
                    ep.contact_id,
                    ep.role_in_project,
                    ep.status,
                    ep.hours_logged,
                    ep.assigned_date,
                    c.job_number,
                    c.project_title,
                    c.name as client_name,
                    c.message as project_description,
                    c.submitted_at
                FROM employee_projects ep
                LEFT JOIN u775021278_Greyline.contacts c ON ep.contact_id = c.id
                WHERE ep.employee_id = ? AND ep.status = 'active'
                ORDER BY ep.assigned_date DESC
            ");
            $stmt->execute([$employee['id']]);
            $dashboardData['active_projects'] = $stmt->fetchAll();
            
            // Get today's time logs
            $stmt = $pdo->prepare("
                SELECT 
                    etl.contact_id,
                    etl.start_time,
                    etl.end_time,
                    etl.duration_minutes,
                    etl.activity_type,
                    etl.description,
                    c.job_number,
                    c.project_title
                FROM employee_time_logs etl
                LEFT JOIN u775021278_Greyline.contacts c ON etl.contact_id = c.id
                WHERE etl.employee_id = ? AND DATE(etl.start_time) = CURDATE()
                ORDER BY etl.start_time DESC
            ");
            $stmt->execute([$employee['id']]);
            $dashboardData['today_logs'] = $stmt->fetchAll();
            
            // Get this week's summary
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(duration_minutes) as total_minutes,
                    COUNT(DISTINCT contact_id) as projects_worked,
                    COUNT(*) as sessions_count
                FROM employee_time_logs 
                WHERE employee_id = ? AND start_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");
            $stmt->execute([$employee['id']]);
            $dashboardData['week_summary'] = $stmt->fetch();
            
            // Get recent notes for employee's projects
            $stmt = $pdo->prepare("
                SELECT 
                    un.note_title,
                    un.note_content,
                    un.note_type,
                    un.priority,
                    un.created_at,
                    c.job_number,
                    c.project_title
                FROM user_notes un
                JOIN employee_projects ep ON un.contact_id = ep.contact_id
                LEFT JOIN u775021278_Greyline.contacts c ON un.contact_id = c.id
                WHERE ep.employee_id = ? AND ep.status = 'active'
                ORDER BY un.created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$employee['id']]);
            $dashboardData['recent_notes'] = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'employee' => [
                    'id' => $employee['id'],
                    'employee_id' => $employee['employee_id'],
                    'firstName' => $employee['first_name'],
                    'lastName' => $employee['last_name'],
                    'role' => $employee['role'],
                    'department' => $employee['department']
                ],
                'dashboard' => $dashboardData
            ]);
            break;

        case 'POST':
            // Handle time tracking actions
            $action = $data['action'] ?? '';
            
            switch ($action) {
                case 'start_timer':
                    $contactId = $data['contact_id'] ?? '';
                    $activityType = $data['activity_type'] ?? 'development';
                    $description = $data['description'] ?? '';
                    
                    if (empty($contactId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Contact ID required']);
                        exit();
                    }
                    
                    // Check if employee is assigned to this project
                    $stmt = $pdo->prepare("SELECT id FROM employee_projects WHERE employee_id = ? AND contact_id = ? AND status = 'active'");
                    $stmt->execute([$employee['id'], $contactId]);
                    if (!$stmt->fetch()) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Not assigned to this project']);
                        exit();
                    }
                    
                    // Start timer
                    $stmt = $pdo->prepare("INSERT INTO employee_time_logs (employee_id, contact_id, start_time, activity_type, description) VALUES (?, ?, NOW(), ?, ?)");
                    $stmt->execute([$employee['id'], $contactId, $activityType, $description]);
                    
                    echo json_encode(['success' => true, 'message' => 'Timer started']);
                    break;
                    
                case 'stop_timer':
                    $logId = $data['log_id'] ?? '';
                    
                    if (empty($logId)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Log ID required']);
                        exit();
                    }
                    
                    // Stop timer and calculate duration
                    $stmt = $pdo->prepare("
                        UPDATE employee_time_logs 
                        SET end_time = NOW(), 
                            duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, NOW())
                        WHERE id = ? AND employee_id = ? AND end_time IS NULL
                    ");
                    $stmt->execute([$logId, $employee['id']]);
                    
                    if ($stmt->rowCount() > 0) {
                        // Update hours logged in employee_projects
                        $stmt = $pdo->prepare("
                            UPDATE employee_projects ep
                            SET hours_logged = (
                                SELECT COALESCE(SUM(duration_minutes), 0) / 60
                                FROM employee_time_logs etl
                                WHERE etl.employee_id = ep.employee_id 
                                AND etl.contact_id = ep.contact_id
                            )
                            WHERE employee_id = ? AND contact_id = (
                                SELECT contact_id FROM employee_time_logs WHERE id = ?
                            )
                        ");
                        $stmt->execute([$employee['id'], $logId]);
                        
                        echo json_encode(['success' => true, 'message' => 'Timer stopped']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'No active timer found']);
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