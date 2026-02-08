<?php
session_start();
ob_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pmr_election_2026');

// Site Configuration
define('SITE_NAME', 'Pemilihan Ketua PMR Wira');
define('SITE_URL', 'http://localhost/pmr-election-2026/');
define('SCHOOL_NAME', 'SMKN 1 Pringgabaya');

// Security
define('SECRET_KEY', 'PMR2026@SMK1PGB#SecureToken!');
define('TOKEN_LENGTH', 12);

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Functions
function generateToken($length = TOKEN_LENGTH) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $token;
}

function sanitize($data) {
    global $conn;
    return htmlspecialchars(stripslashes(trim($conn->real_escape_string($data))));
}

function isLoggedIn() {
    return isset($_SESSION['voter_id']) || isset($_SESSION['admin_id']);
}
?>