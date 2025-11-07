<?php
// Enhanced Job Modal API - Comprehensive project management
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Database configuration
$host = "127.0.0.1";
$dbname = "u775021278_users_manage";
$username = "u775021278_userAdmin";
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

    // Verify session and get employee
    $stmt = $pdo->prepare("
        SELECT e.* FROM employees e 
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

    $action = $_GET['action'] ?? $data['action'] ?? '';
    $contactId = $_GET['contact_id'] ?? $data['contact_id'] ?? '';

    switch ($method) {
        case 'GET':
            if (empty($contactId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Contact ID required']);
                exit();
            }

            switch ($action) {
                case 'project_overview':
                    // Get complete project overview
                    $stmt = $pdo->prepare("
                        SELECT 
                            c.*,
                            e.first_name as assigned_employee_name,
                            e.last_name as assigned_employee_lastname
                        FROM u775021278_Greyline.contacts c
                        LEFT JOIN employees e ON c.assigned_employee_id = e.id
                        WHERE c.id = ?
                    ");
                    $stmt->execute([$contactId]);
                    $project = $stmt->fetch();
                    
                    if (!$project) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Project not found']);
                        exit();
                    }

                    // Get project tags
                    $stmt = $pdo->prepare("
                        SELECT pt.tag_name, pt.tag_color
                        FROM project_tag_assignments pta
                        JOIN project_tags pt ON pta.tag_id = pt.id
                        WHERE pta.contact_id = ?
                    ");
                    $stmt->execute([$contactId]);
                    $tags = $stmt->fetchAll();

                    // Get milestones
                    $stmt = $pdo->prepare("
                        SELECT pm.*, e.first_name as assigned_employee_name, e.last_name as assigned_employee_lastname
                        FROM project_milestones pm
                        LEFT JOIN employees e ON pm.assigned_to = e.id
                        WHERE pm.contact_id = ?
                        ORDER BY pm.due_date ASC
                    ");
                    $stmt->execute([$contactId]);
                    $milestones = $stmt->fetchAll();

                    // Get recent communications
                    $stmt = $pdo->prepare("
                        SELECT pc.*, e.first_name as created_by_name, e.last_name as created_by_lastname
                        FROM project_communications pc
                        JOIN employees e ON pc.created_by = e.id
                        WHERE pc.contact_id = ?
                        ORDER BY pc.communication_date DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$contactId]);
                    $communications = $stmt->fetchAll();

                    // Get recent activities
                    $stmt = $pdo->prepare("
                        SELECT pa.*, e.first_name as performed_by_name, e.last_name as performed_by_lastname
                        FROM project_activities pa
                        JOIN employees e ON pa.performed_by = e.id
                        WHERE pa.contact_id = ?
                        ORDER BY pa.activity_date DESC
                        LIMIT 20
                    ");
                    $stmt->execute([$contactId]);
                    $activities = $stmt->fetchAll();

                    echo json_encode([
                        'success' => true,
                        'project' => $project,
                        'tags' => $tags,
                        'milestones' => $milestones,
                        'communications' => $communications,
                        'activities' => $activities
                    ]);
                    break;

                case 'files':
                    $stmt = $pdo->prepare("
                        SELECT pf.*, e.first_name as uploaded_by_name, e.last_name as uploaded_by_lastname
                        FROM project_files pf
                        JOIN employees e ON pf.uploaded_by = e.id
                        WHERE pf.contact_id = ?
                        ORDER BY pf.upload_date DESC
                    ");
                    $stmt->execute([$contactId]);
                    echo json_encode(['success' => true, 'files' => $stmt->fetchAll()]);
                    break;

                case 'github':
                    $stmt = $pdo->prepare("
                        SELECT pg.*, e.first_name as created_by_name, e.last_name as created_by_lastname
                        FROM project_github pg
                        JOIN employees e ON pg.created_by = e.id
                        WHERE pg.contact_id = ?
                    ");
                    $stmt->execute([$contactId]);
                    echo json_encode(['success' => true, 'github' => $stmt->fetch()]);
                    break;

                case 'billing':
                    $stmt = $pdo->prepare("
                        SELECT pb.*, e.first_name as created_by_name, e.last_name as created_by_lastname
                        FROM project_billing pb
                        JOIN employees e ON pb.created_by = e.id
                        WHERE pb.contact_id = ?
                        ORDER BY pb.invoice_date DESC
                    ");
                    $stmt->execute([$contactId]);
                    echo json_encode(['success' => true, 'billing' => $stmt->fetchAll()]);
                    break;

                case 'dependencies':
                    $stmt = $pdo->prepare("
                        SELECT pd.*, e.first_name as created_by_name, e.last_name as created_by_lastname
                        FROM project_dependencies pd
                        JOIN employees e ON pd.created_by = e.id
                        WHERE pd.contact_id = ?
                        ORDER BY pd.due_date ASC
                    ");
                    $stmt->execute([$contactId]);
                    echo json_encode(['success' => true, 'dependencies' => $stmt->fetchAll()]);
                    break;

                case 'templates':
                    $stmt = $pdo->prepare("
                        SELECT pt.*, e.first_name as created_by_name, e.last_name as created_by_lastname
                        FROM project_templates pt
                        JOIN employees e ON pt.created_by = e.id
                        WHERE pt.is_active = 1
                        ORDER BY pt.template_name
                    ");
                    $stmt->execute();
                    echo json_encode(['success' => true, 'templates' => $stmt->fetchAll()]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;

        case 'POST':
            switch ($action) {
                case 'update_project':
                    $projectData = $data['project'] ?? [];
                    
                    $stmt = $pdo->prepare("
                        UPDATE u775021278_Greyline.contacts 
                        SET project_status = ?, project_priority = ?, estimated_hours = ?, 
                            start_date = ?, target_completion_date = ?, budget_amount = ?, 
                            assigned_employee_id = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $projectData['project_status'] ?? 'new',
                        $projectData['project_priority'] ?? 'medium',
                        $projectData['estimated_hours'] ?? 0,
                        $projectData['start_date'] ?? null,
                        $projectData['target_completion_date'] ?? null,
                        $projectData['budget_amount'] ?? 0,
                        $projectData['assigned_employee_id'] ?? null,
                        $contactId
                    ]);

                    // Log activity
                    $stmt = $pdo->prepare("
                        INSERT INTO project_activities (contact_id, activity_type, activity_description, performed_by)
                        VALUES (?, 'status_changed', ?, ?)
                    ");
                    $stmt->execute([$contactId, 'Project details updated', $employee['id']]);

                    echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
                    break;

                case 'add_milestone':
                    $milestoneData = $data['milestone'] ?? [];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO project_milestones (contact_id, milestone_name, milestone_description, 
                                                       due_date, priority, assigned_to, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $contactId,
                        $milestoneData['milestone_name'],
                        $milestoneData['milestone_description'] ?? '',
                        $milestoneData['due_date'],
                        $milestoneData['priority'] ?? 'medium',
                        $milestoneData['assigned_to'] ?? null,
                        $employee['id']
                    ]);

                    $milestoneId = $pdo->lastInsertId();

                    // Log activity
                    $stmt = $pdo->prepare("
                        INSERT INTO project_activities (contact_id, activity_type, activity_description, 
                                                       related_id, related_table, performed_by)
                        VALUES (?, 'milestone_created', ?, ?, 'project_milestones', ?)
                    ");
                    $stmt->execute([$contactId, "Milestone '{$milestoneData['milestone_name']}' created", $milestoneId, $employee['id']]);

                    echo json_encode(['success' => true, 'message' => 'Milestone added successfully', 'milestone_id' => $milestoneId]);
                    break;

                case 'add_communication':
                    $commData = $data['communication'] ?? [];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO project_communications (contact_id, communication_type, subject, content,
                                                           direction, sender_email, recipient_email, duration_minutes,
                                                           is_important, follow_up_required, follow_up_date, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $contactId,
                        $commData['communication_type'],
                        $commData['subject'] ?? '',
                        $commData['content'],
                        $commData['direction'],
                        $commData['sender_email'] ?? '',
                        $commData['recipient_email'] ?? '',
                        $commData['duration_minutes'] ?? null,
                        $commData['is_important'] ?? false,
                        $commData['follow_up_required'] ?? false,
                        $commData['follow_up_date'] ?? null,
                        $employee['id']
                    ]);

                    $commId = $pdo->lastInsertId();

                    // Log activity
                    $stmt = $pdo->prepare("
                        INSERT INTO project_activities (contact_id, activity_type, activity_description, 
                                                       related_id, related_table, performed_by)
                        VALUES (?, 'communication_logged', ?, ?, 'project_communications', ?)
                    ");
                    $stmt->execute([$contactId, "Communication logged: {$commData['communication_type']}", $commId, $employee['id']]);

                    echo json_encode(['success' => true, 'message' => 'Communication logged successfully', 'communication_id' => $commId]);
                    break;

                case 'add_dependency':
                    $depData = $data['dependency'] ?? [];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO project_dependencies (contact_id, dependency_name, dependency_type, 
                                                         due_date, description, created_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $contactId,
                        $depData['dependency_name'],
                        $depData['dependency_type'],
                        $depData['due_date'] ?? null,
                        $depData['description'] ?? '',
                        $employee['id']
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Dependency added successfully']);
                    break;

                case 'add_github':
                    $githubData = $data['github'] ?? [];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO project_github (contact_id, repository_url, repository_name, branch_name, created_by)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        repository_url = VALUES(repository_url),
                        repository_name = VALUES(repository_name),
                        branch_name = VALUES(branch_name),
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([
                        $contactId,
                        $githubData['repository_url'],
                        $githubData['repository_name'] ?? '',
                        $githubData['branch_name'] ?? 'main',
                        $employee['id']
                    ]);

                    // Log activity
                    $stmt = $pdo->prepare("
                        INSERT INTO project_activities (contact_id, activity_type, activity_description, performed_by)
                        VALUES (?, 'github_updated', ?, ?)
                    ");
                    $stmt->execute([$contactId, "GitHub repository linked: {$githubData['repository_url']}", $employee['id']]);

                    echo json_encode(['success' => true, 'message' => 'GitHub repository linked successfully']);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;

        case 'PUT':
            switch ($action) {
                case 'update_milestone':
                    $milestoneId = $data['milestone_id'] ?? '';
                    $milestoneData = $data['milestone'] ?? [];
                    
                    $stmt = $pdo->prepare("
                        UPDATE project_milestones 
                        SET milestone_name = ?, milestone_description = ?, due_date = ?, 
                            status = ?, priority = ?, assigned_to = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ? AND contact_id = ?
                    ");
                    $stmt->execute([
                        $milestoneData['milestone_name'],
                        $milestoneData['milestone_description'] ?? '',
                        $milestoneData['due_date'],
                        $milestoneData['status'] ?? 'pending',
                        $milestoneData['priority'] ?? 'medium',
                        $milestoneData['assigned_to'] ?? null,
                        $milestoneId,
                        $contactId
                    ]);

                    if ($stmt->rowCount() > 0) {
                        // Log activity
                        $activityType = ($milestoneData['status'] === 'completed') ? 'milestone_completed' : 'milestone_updated';
                        $stmt = $pdo->prepare("
                            INSERT INTO project_activities (contact_id, activity_type, activity_description, 
                                                           related_id, related_table, performed_by)
                            VALUES (?, ?, ?, ?, 'project_milestones', ?)
                        ");
                        $stmt->execute([$contactId, $activityType, "Milestone updated: {$milestoneData['milestone_name']}", $milestoneId, $employee['id']]);
                    }

                    echo json_encode(['success' => true, 'message' => 'Milestone updated successfully']);
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