<?php
// Employee App Preferences and User Data API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Database configuration
$host = "localhost";
$dbname = "u775021278_project_manage";
$username = "u775021278_userAdmin";
$password = ">q}Q>']6LNp~g+7";

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
    $pdo = new PDO($dsn, $username, $password, $options);

    // Get employee ID from session token (you'll need to implement session validation)
    $sessionToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $employeeId = null;
    
    if ($sessionToken) {
        $stmt = $pdo->prepare("SELECT employee_id FROM employee_sessions WHERE session_token = ? AND is_active = TRUE AND expires_at > NOW()");
        $stmt->execute([$sessionToken]);
        $session = $stmt->fetch();
        if ($session) {
            $employeeId = $session['employee_id'];
        }
    }

    if (!$employeeId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Invalid session']);
        exit();
    }

    switch ($method) {
        case 'GET':
            // Get employee preferences and data
            $action = $_GET['action'] ?? 'preferences';
            
            switch ($action) {
                case 'preferences':
                    // Get app preferences
                    $stmt = $pdo->prepare("SELECT * FROM employee_app_preferences WHERE employee_id = ?");
                    $stmt->execute([$employeeId]);
                    $preferences = $stmt->fetch();
                    
                    if (!$preferences) {
                        // Create default preferences if none exist
                        $stmt = $pdo->prepare("
                            INSERT INTO employee_app_preferences (employee_id) 
                            VALUES (?)
                        ");
                        $stmt->execute([$employeeId]);
                        
                        $stmt = $pdo->prepare("SELECT * FROM employee_app_preferences WHERE employee_id = ?");
                        $stmt->execute([$employeeId]);
                        $preferences = $stmt->fetch();
                    }
                    
                    echo json_encode(['success' => true, 'data' => $preferences]);
                    break;
                    
                case 'app_data':
                    // Get app data by key or all data
                    $dataKey = $_GET['key'] ?? null;
                    
                    if ($dataKey) {
                        $stmt = $pdo->prepare("SELECT * FROM employee_app_data WHERE employee_id = ? AND data_key = ?");
                        $stmt->execute([$employeeId, $dataKey]);
                        $appData = $stmt->fetch();
                        
                        if ($appData && $appData['expires_at'] && strtotime($appData['expires_at']) < time()) {
                            // Delete expired data
                            $stmt = $pdo->prepare("DELETE FROM employee_app_data WHERE id = ?");
                            $stmt->execute([$appData['id']]);
                            $appData = null;
                        }
                        
                        echo json_encode(['success' => true, 'data' => $appData]);
                    } else {
                        // Get all non-expired app data
                        $stmt = $pdo->prepare("
                            SELECT * FROM employee_app_data 
                            WHERE employee_id = ? 
                            AND (expires_at IS NULL OR expires_at > NOW())
                            ORDER BY updated_at DESC
                        ");
                        $stmt->execute([$employeeId]);
                        $appData = $stmt->fetchAll();
                        
                        echo json_encode(['success' => true, 'data' => $appData]);
                    }
                    break;
                    
                case 'recent_activities':
                    // Get recent activities
                    $limit = $_GET['limit'] ?? 20;
                    $stmt = $pdo->prepare("
                        SELECT * FROM employee_recent_activities 
                        WHERE employee_id = ? 
                        ORDER BY activity_timestamp DESC 
                        LIMIT ?
                    ");
                    $stmt->execute([$employeeId, $limit]);
                    $activities = $stmt->fetchAll();
                    
                    echo json_encode(['success' => true, 'data' => $activities]);
                    break;
                    
                case 'bookmarks':
                    // Get bookmarks
                    $stmt = $pdo->prepare("
                        SELECT * FROM employee_bookmarks 
                        WHERE employee_id = ? 
                        ORDER BY sort_order ASC, created_at DESC
                    ");
                    $stmt->execute([$employeeId]);
                    $bookmarks = $stmt->fetchAll();
                    
                    echo json_encode(['success' => true, 'data' => $bookmarks]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'POST':
            // Create or update preferences and data
            $action = $_GET['action'] ?? 'preferences';
            
            switch ($action) {
                case 'preferences':
                    // Update app preferences
                    $allowedFields = [
                        'theme', 'language', 'timezone', 'date_format', 'time_format',
                        'notifications_enabled', 'email_notifications', 'push_notifications',
                        'dashboard_layout', 'sidebar_collapsed', 'auto_refresh_interval', 'default_view'
                    ];
                    
                    $updateFields = [];
                    $updateValues = [];
                    
                    foreach ($allowedFields as $field) {
                        if (isset($data[$field])) {
                            $updateFields[] = "$field = ?";
                            $updateValues[] = $data[$field];
                        }
                    }
                    
                    if (!empty($updateFields)) {
                        $updateValues[] = $employeeId;
                        $sql = "UPDATE employee_app_preferences SET " . implode(', ', $updateFields) . " WHERE employee_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($updateValues);
                        
                        echo json_encode(['success' => true, 'message' => 'Preferences updated successfully']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
                    }
                    break;
                    
                case 'app_data':
                    // Store app data
                    $dataKey = $data['key'] ?? null;
                    $dataValue = $data['value'] ?? null;
                    $dataType = $data['type'] ?? 'custom';
                    $expiresAt = $data['expires_at'] ?? null;
                    
                    if (!$dataKey || $dataValue === null) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Key and value are required']);
                        exit();
                    }
                    
                    // Insert or update app data
                    $stmt = $pdo->prepare("
                        INSERT INTO employee_app_data (employee_id, data_key, data_value, data_type, expires_at) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        data_value = VALUES(data_value), 
                        data_type = VALUES(data_type), 
                        expires_at = VALUES(expires_at),
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$employeeId, $dataKey, json_encode($dataValue), $dataType, $expiresAt]);
                    
                    echo json_encode(['success' => true, 'message' => 'App data stored successfully']);
                    break;
                    
                case 'recent_activity':
                    // Log recent activity
                    $activityType = $data['activity_type'] ?? null;
                    $activityData = $data['activity_data'] ?? null;
                    
                    if (!$activityType) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Activity type is required']);
                        exit();
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO employee_recent_activities (employee_id, activity_type, activity_data) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$employeeId, $activityType, json_encode($activityData)]);
                    
                    // Keep only last 100 activities per employee
                    $stmt = $pdo->prepare("
                        DELETE FROM employee_recent_activities 
                        WHERE employee_id = ? 
                        AND id NOT IN (
                            SELECT id FROM (
                                SELECT id FROM employee_recent_activities 
                                WHERE employee_id = ? 
                                ORDER BY activity_timestamp DESC 
                                LIMIT 100
                            ) as temp
                        )
                    ");
                    $stmt->execute([$employeeId, $employeeId]);
                    
                    echo json_encode(['success' => true, 'message' => 'Activity logged successfully']);
                    break;
                    
                case 'bookmark':
                    // Add bookmark
                    $bookmarkType = $data['bookmark_type'] ?? null;
                    $bookmarkName = $data['bookmark_name'] ?? null;
                    $bookmarkData = $data['bookmark_data'] ?? null;
                    $sortOrder = $data['sort_order'] ?? 0;
                    
                    if (!$bookmarkType || !$bookmarkName || !$bookmarkData) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Bookmark type, name, and data are required']);
                        exit();
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO employee_bookmarks (employee_id, bookmark_type, bookmark_name, bookmark_data, sort_order) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$employeeId, $bookmarkType, $bookmarkName, json_encode($bookmarkData), $sortOrder]);
                    
                    echo json_encode(['success' => true, 'message' => 'Bookmark added successfully']);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'DELETE':
            // Delete app data or bookmarks
            $action = $_GET['action'] ?? '';
            $id = $_GET['id'] ?? null;
            $key = $_GET['key'] ?? null;
            
            switch ($action) {
                case 'app_data':
                    if ($key) {
                        $stmt = $pdo->prepare("DELETE FROM employee_app_data WHERE employee_id = ? AND data_key = ?");
                        $stmt->execute([$employeeId, $key]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Key parameter is required']);
                        exit();
                    }
                    break;
                    
                case 'bookmark':
                    if ($id) {
                        $stmt = $pdo->prepare("DELETE FROM employee_bookmarks WHERE id = ? AND employee_id = ?");
                        $stmt->execute([$id, $employeeId]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'ID parameter is required']);
                        exit();
                    }
                    break;
                    
                case 'recent_activities':
                    $stmt = $pdo->prepare("DELETE FROM employee_recent_activities WHERE employee_id = ?");
                    $stmt->execute([$employeeId]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    exit();
            }
            
            echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 