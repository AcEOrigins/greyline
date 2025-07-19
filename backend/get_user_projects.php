<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Start session
session_start();

// Database configuration
$host = "localhost";
$dbname = "u775021278_users_manage";
$username = "u775021278_userAdmin";
$password = ">q}Q>']6LNp~g+7";

// Get user ID from session or request
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    // Try to get from POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;
}

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
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

    // Get user's projects (cross-database query)
    try {
        // Connect to contacts database
        $contacts_dsn = "mysql:host=localhost;dbname=u775021278_Greyline;charset=utf8mb4";
        $contacts_pdo = new PDO($contacts_dsn, "u775021278_devAdmin", "g15^ajHAnJH=", $options);
        
        // Get user's projects by joining across databases
        $stmt = $pdo->prepare("
            SELECT 
                up.contact_id,
                up.project_status
            FROM user_projects up
            WHERE up.user_id = ?
            ORDER BY up.created_at DESC
        ");
        $stmt->execute([$userId]);
        $userProjects = $stmt->fetchAll();
        
        // Get contact details for each project
        $projects = [];
        foreach ($userProjects as $userProject) {
            $stmt = $contacts_pdo->prepare("
                SELECT 
                    id,
                    job_number,
                    project_title,
                    message,
                    status,
                    submitted_at
                FROM contacts 
                WHERE id = ?
            ");
            $stmt->execute([$userProject['contact_id']]);
            $contact = $stmt->fetch();
            
            if ($contact) {
                $projects[] = array_merge($contact, ['project_status' => $userProject['project_status']]);
            }
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }

    // Format projects for frontend
    $formattedProjects = array_map(function($project) {
        return [
            'id' => $project['id'],
            'jobNumber' => $project['job_number'],
            'title' => $project['project_title'],
            'description' => $project['message'],
            'status' => $project['project_status'],
            'submitted_at' => $project['submitted_at'],
            'contact_status' => $project['status']
        ];
    }, $projects);

    echo json_encode([
        'success' => true,
        'projects' => $formattedProjects
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 