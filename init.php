<?php
if (!session_id()) {
    session_start();
}
include 'config.php';  // Include database configuration settings

// Redirect logic for users not logged in or not students
if (basename($_SERVER['PHP_SELF']) != 'index.php' && (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student')) {
    header('Location: index.php');
    exit;
}
?>
