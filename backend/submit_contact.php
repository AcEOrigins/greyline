<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// --- CONFIG --- //
$host = "localhost";
$dbname = "u775021278_Greyline";
$username = "u775021278_devAdmin";
$password = "ay7QOXj6";

// --- FETCH AND SANITIZE FORM DATA --- //
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$website = trim($_POST['website'] ?? '');
$timestamp = trim($_POST['timestamp'] ?? '');
$source = trim($_POST['source'] ?? '');
$status  = 'New';
$notes   = '';

// Basic XSS protection - remove HTML/script tags
$name = strip_tags($name);
$email = strip_tags($email);
$subject = strip_tags($subject);
$message = strip_tags($message);
$website = strip_tags($website);
$timestamp = strip_tags($timestamp);
$source = strip_tags($source);

// Length validation to prevent oversized inputs
if (strlen($name) > 100 || strlen($email) > 100 || strlen($subject) > 200 || strlen($message) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input too long']);
    exit();
}

// Email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// --- BOT PROTECTION --- //
if (!empty($website)) {
    // Honeypot field was filled - likely a bot
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission detected']);
    exit();
}

// Rate limiting - check for recent submissions from same IP (moved after DB connection)
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// SEO and scam company detection (less aggressive)
$spam_patterns = [
    // Very obvious SEO spam (multiple keywords together)
    '/\b(seo.*backlink|backlink.*seo|google.*ranking.*seo)\b/i',
    '/\b(digital.*marketing.*leads|leads.*generation.*marketing)\b/i',
    '/\b(website.*traffic.*boost|increase.*traffic.*organic)\b/i',
    
    // Very obvious scam patterns
    '/\b(100%.*guarantee|money.*back.*guarantee)\b/i',
    '/\b(limited.*time.*act.*now|urgent.*immediate.*action)\b/i',
    '/\b(completely.*free.*trial|no.*cost.*guarantee)\b/i',
    '/\b(earn.*money.*work.*home|get.*rich.*quick)\b/i',
    
    // Very obvious spam phrases
    '/\b(click.*here.*visit.*website|call.*now.*text.*now)\b/i',
    '/\b(best.*price.*lowest.*cheapest|discount.*guarantee)\b/i',
    '/\b(credit.*card.*payment.*bitcoin|paypal.*payment.*urgent)\b/i',
    
    // Bot patterns
    '/[a-z]{30,}/i', // Very long repeated characters
    '/[0-9]{30,}/', // Very long number sequences
    '/\b(uwu|owo|owo|uwu)\b/i', // Common bot patterns
];

// Check for spam patterns in all fields
foreach ($spam_patterns as $pattern) {
    if (preg_match($pattern, $name) || preg_match($pattern, $email) || preg_match($pattern, $subject) || preg_match($pattern, $message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message blocked - contains spam content']);
        exit();
    }
}

// Check for suspicious email domains (less aggressive)
$high_risk_domains = [
    'mail.ru', 'yandex.ru', 'qq.com', '163.com', // International spam domains
];

$email_domain = strtolower(substr(strrchr($email, "@"), 1));
if (in_array($email_domain, $high_risk_domains)) {
    // Additional check for very obvious spam content with these domains
    if (preg_match('/\b(seo.*backlink|100%.*guarantee|earn.*money.*quick|click.*here.*website)\b/i', $message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message blocked - suspicious content detected']);
        exit();
    }
}

// --- VALIDATE --- //
if ($name && $email && $subject && $message) {
    try {
        // DB connection with additional security options
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        $pdo = new PDO($dsn, $username, $password, $options);

        // Rate limiting - check for recent submissions from same IP (using timestamp column)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND source LIKE ?");
        $stmt->execute(['%' . $client_ip . '%']);
        $recent_submissions = $stmt->fetchColumn();

        if ($recent_submissions > 5) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Too many submissions. Please try again later.']);
            exit();
        }

        // Generate job number
        $stmt = $pdo->query("SELECT job_number FROM contacts ORDER BY id DESC LIMIT 1");
        $lastJob = $stmt->fetchColumn();

        if ($lastJob && preg_match('/JOB-(\\d+)/', $lastJob, $matches)) {
            $next = str_pad(((int)$matches[1]) + 1, 5, "0", STR_PAD_LEFT);
        } else {
            $next = "00001";
        }

        $job_number = "JOB-" . $next;

        // Insert into DB with additional security fields
        $current_timestamp = date('Y-m-d H:i:s');
        $source_with_ip = $source . ' - IP: ' . $client_ip;
        
        $stmt = $pdo->prepare("INSERT INTO contacts (job_number, project_title, name, email, message, status, notes, subject, timestamp, source)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$job_number, $subject, $name, $email, $message, $status, $notes, $subject, $current_timestamp, $source_with_ip]);

        // Return JSON response
        echo json_encode(['success' => true, 'message' => 'Message sent successfully', 'job_number' => $job_number]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error: missing required fields', 'debug' => [
        'name' => !empty($name),
        'email' => !empty($email),
        'subject' => !empty($subject),
        'message' => !empty($message)
    ]]);
}
?>
