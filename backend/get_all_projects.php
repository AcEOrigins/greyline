<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database configurations
$users_host = "localhost";
$users_dbname = "u775021278_users_manage";
$users_username = "u775021278_userAdmin";
$users_password = ">q}Q>']6LNp~g+7";

$contacts_host = "localhost";
$contacts_dbname = "u775021278_Greyline";
$contacts_username = "u775021278_devAdmin";
$contacts_password = "g15^ajHAnJH=";

try {
    // Connect to users database
    $users_dsn = "mysql:host=$users_host;dbname=$users_dbname;charset=utf8mb4";
    $users_options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    $users_pdo = new PDO($users_dsn, $users_username, $users_password, $users_options);

    // Connect to contacts database
    $contacts_dsn = "mysql:host=$contacts_host;dbname=$contacts_dbname;charset=utf8mb4";
    $contacts_pdo = new PDO($contacts_dsn, $contacts_username, $contacts_password, $users_options);

    // Get all users with their projects and notes
    $stmt = $users_pdo->prepare("
        SELECT 
            u.id as user_id,
            u.email,
            u.first_name,
            u.last_name,
            u.company_name,
            u.phone,
            u.created_at as user_created_at,
            up.contact_id,
            up.project_status,
            up.created_at as project_created_at
        FROM users u
        LEFT JOIN user_projects up ON u.id = up.user_id
        ORDER BY u.created_at DESC, up.created_at DESC
    ");
    $stmt->execute();
    $userProjects = $stmt->fetchAll();

    $result = [];
    
    foreach ($userProjects as $userProject) {
        $userId = $userProject['user_id'];
        $contactId = $userProject['contact_id'];
        
        // Initialize user if not exists
        if (!isset($result[$userId])) {
            $result[$userId] = [
                'user_id' => $userId,
                'email' => $userProject['email'],
                'first_name' => $userProject['first_name'],
                'last_name' => $userProject['last_name'],
                'company_name' => $userProject['company_name'],
                'phone' => $userProject['phone'],
                'user_created_at' => $userProject['user_created_at'],
                'projects' => []
            ];
        }
        
        if ($contactId) {
            // Get contact details
            $stmt = $contacts_pdo->prepare("
                SELECT 
                    id,
                    job_number,
                    project_title,
                    name,
                    message,
                    status,
                    submitted_at
                FROM contacts 
                WHERE id = ?
            ");
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch();
            
            if ($contact) {
                // Get notes for this project
                $stmt = $users_pdo->prepare("
                    SELECT * FROM user_notes 
                    WHERE user_id = ? AND contact_id = ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$userId, $contactId]);
                $notes = $stmt->fetchAll();
                
                $result[$userId]['projects'][] = [
                    'contact_id' => $contactId,
                    'job_number' => $contact['job_number'],
                    'project_title' => $contact['project_title'],
                    'client_name' => $contact['name'],
                    'message' => $contact['message'],
                    'status' => $contact['status'],
                    'project_status' => $userProject['project_status'],
                    'submitted_at' => $contact['submitted_at'],
                    'project_created_at' => $userProject['project_created_at'],
                    'notes' => $notes
                ];
            }
        }
    }

    // Convert to array and remove empty projects
    $finalResult = array_values($result);
    
    echo json_encode([
        'success' => true,
        'data' => $finalResult,
        'total_users' => count($finalResult)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 