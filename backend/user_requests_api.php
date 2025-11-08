<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration for users database
$host = "127.0.0.1";
$dbname = "u775021278_users_manage";
$username = "u775021278_userAdmin";
$db_password = ">q}Q>']6LNp~g+7";

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Database connection
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    $pdo = new PDO($dsn, $username, $db_password, $options);

    switch ($method) {
        case 'GET':
            // Get requests for a specific user
            if (isset($_GET['user_id'])) {
                $contactId = $_GET['contact_id'] ?? null;
                
                if ($contactId) {
                    // Get requests for a specific user and project
                    $stmt = $pdo->prepare("
                        SELECT * FROM user_requests 
                        WHERE user_id = ? AND contact_id = ? 
                        ORDER BY created_at DESC
                    ");
                    $stmt->execute([$_GET['user_id'], $contactId]);
                } else {
                    // Get all requests for a user
                    $stmt = $pdo->prepare("
                        SELECT * FROM user_requests 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC
                    ");
                    $stmt->execute([$_GET['user_id']]);
                }
                
                $requests = $stmt->fetchAll();
                echo json_encode(['success' => true, 'requests' => $requests]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'user_id required']);
            }
            break;

        case 'POST':
            // Create a new request
            if (isset($data['user_id']) && isset($data['contact_id']) && isset($data['request_title']) && isset($data['request_description'])) {
                // Validate request_type
                $validTypes = ['design_change', 'content_update', 'feature_request', 'bug_report', 'other'];
                $requestType = in_array($data['request_type'] ?? '', $validTypes) ? $data['request_type'] : 'other';
                
                // Validate priority
                $validPriorities = ['low', 'medium', 'high', 'urgent'];
                $priority = in_array($data['priority'] ?? '', $validPriorities) ? $data['priority'] : 'medium';
                
                $stmt = $pdo->prepare("
                    INSERT INTO user_requests (
                        user_id, 
                        contact_id, 
                        request_title, 
                        request_description, 
                        request_type, 
                        priority
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['user_id'],
                    $data['contact_id'],
                    $data['request_title'],
                    $data['request_description'],
                    $requestType,
                    $priority
                ]);
                
                $requestId = $pdo->lastInsertId();
                
                // Get the created request
                $stmt = $pdo->prepare("SELECT * FROM user_requests WHERE id = ?");
                $stmt->execute([$requestId]);
                $request = $stmt->fetch();
                
                echo json_encode(['success' => true, 'message' => 'Request submitted successfully', 'request' => $request]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields: user_id, contact_id, request_title, request_description']);
            }
            break;

        case 'PUT':
            // Update an existing request (typically for employees to update status)
            if (isset($data['id'])) {
                $updates = [];
                $params = [];
                
                if (isset($data['status'])) {
                    $validStatuses = ['pending', 'in_progress', 'completed', 'rejected', 'cancelled'];
                    if (in_array($data['status'], $validStatuses)) {
                        $updates[] = "status = ?";
                        $params[] = $data['status'];
                    }
                }
                
                if (isset($data['response_message'])) {
                    $updates[] = "response_message = ?";
                    $updates[] = "responded_at = CURRENT_TIMESTAMP";
                    $params[] = $data['response_message'];
                    // Note: responded_at doesn't need a parameter since it uses CURRENT_TIMESTAMP
                }
                
                if (isset($data['assigned_to'])) {
                    $updates[] = "assigned_to = ?";
                    $params[] = $data['assigned_to'];
                }
                
                if (isset($data['priority'])) {
                    $validPriorities = ['low', 'medium', 'high', 'urgent'];
                    if (in_array($data['priority'], $validPriorities)) {
                        $updates[] = "priority = ?";
                        $params[] = $data['priority'];
                    }
                }
                
                if (empty($updates)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
                    break;
                }
                
                $params[] = $data['id'];
                $sql = "UPDATE user_requests SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // Get the updated request
                $stmt = $pdo->prepare("SELECT * FROM user_requests WHERE id = ?");
                $stmt->execute([$data['id']]);
                $request = $stmt->fetch();
                
                echo json_encode(['success' => true, 'message' => 'Request updated successfully', 'request' => $request]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Request ID required']);
            }
            break;

        case 'DELETE':
            // Delete a request (soft delete by archiving)
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("UPDATE user_requests SET is_archived = 1 WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                echo json_encode(['success' => true, 'message' => 'Request archived successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Request ID required']);
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

