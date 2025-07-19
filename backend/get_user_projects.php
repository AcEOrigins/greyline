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
$dbname = "u775021278_Greyline";
$username = "u775021278_devAdmin";
$password = "ay7QOXj6";

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

    // Get user's projects
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.job_number,
            c.project_title,
            c.message,
            c.status,
            c.submitted_at,
            up.project_status
        FROM contacts c
        INNER JOIN user_projects up ON c.id = up.contact_id
        WHERE up.user_id = ?
        ORDER BY c.submitted_at DESC
    ");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();

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