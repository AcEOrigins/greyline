<?php
// --- CONFIG --- //
$host = "localhost";
$dbname = "u775021278_Greyline";
$username = "u775021278_devAdmin";
$password = "ay7QOXj6";

// --- FETCH FORM DATA --- //
$name    = $_POST['name'] ?? '';
$email   = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';
$status  = 'New';
$notes   = '';
$project_title = ''; // Blank for now

// --- VALIDATE --- //
if ($name && $email && $message) {
    try {
        // DB connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate job number
        $stmt = $pdo->query("SELECT job_number FROM contacts ORDER BY id DESC LIMIT 1");
        $lastJob = $stmt->fetchColumn();

        if ($lastJob && preg_match('/JOB-(\\d+)/', $lastJob, $matches)) {
            $next = str_pad(((int)$matches[1]) + 1, 5, "0", STR_PAD_LEFT);
        } else {
            $next = "00001";
        }

        $job_number = "JOB-" . $next;

        // Insert into DB
        $stmt = $pdo->prepare("INSERT INTO contacts (job_number, project_title, name, email, message, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$job_number, $project_title, $name, $email, $message, $status, $notes]);

        header("Location: ../success.html");
        exit();

    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Error: missing required fields.";
}
?>
