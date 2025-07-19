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
$host = "localhost";
$dbname = "u775021278_users_manage";
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

    switch ($method) {
        case 'GET':
            // Get notes for a specific user and project
            if (isset($_GET['user_id']) && isset($_GET['contact_id'])) {
                $stmt = $pdo->prepare("
                    SELECT * FROM user_notes 
                    WHERE user_id = ? AND contact_id = ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$_GET['user_id'], $_GET['contact_id']]);
                $notes = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'notes' => $notes]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'user_id and contact_id required']);
            }
            break;

        case 'POST':
            // Create a new note
            if (isset($data['user_id']) && isset($data['contact_id']) && isset($data['note_title']) && isset($data['note_content'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO user_notes (user_id, contact_id, note_title, note_content, note_type, priority, created_by, is_private) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['user_id'],
                    $data['contact_id'],
                    $data['note_title'],
                    $data['note_content'],
                    $data['note_type'] ?? 'general',
                    $data['priority'] ?? 'medium',
                    $data['created_by'] ?? 'employee',
                    $data['is_private'] ?? false
                ]);
                
                $noteId = $pdo->lastInsertId();
                
                // Get the created note
                $stmt = $pdo->prepare("SELECT * FROM user_notes WHERE id = ?");
                $stmt->execute([$noteId]);
                $note = $stmt->fetch();
                
                echo json_encode(['success' => true, 'message' => 'Note created successfully', 'note' => $note]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            }
            break;

        case 'PUT':
            // Update an existing note
            if (isset($data['id']) && isset($data['note_title']) && isset($data['note_content'])) {
                $stmt = $pdo->prepare("
                    UPDATE user_notes 
                    SET note_title = ?, note_content = ?, note_type = ?, priority = ?, is_private = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['note_title'],
                    $data['note_content'],
                    $data['note_type'] ?? 'general',
                    $data['priority'] ?? 'medium',
                    $data['is_private'] ?? false,
                    $data['id']
                ]);
                
                // Get the updated note
                $stmt = $pdo->prepare("SELECT * FROM user_notes WHERE id = ?");
                $stmt->execute([$data['id']]);
                $note = $stmt->fetch();
                
                echo json_encode(['success' => true, 'message' => 'Note updated successfully', 'note' => $note]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            }
            break;

        case 'DELETE':
            // Delete a note
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("DELETE FROM user_notes WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                echo json_encode(['success' => true, 'message' => 'Note deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Note ID required']);
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