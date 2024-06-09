<?php
include 'config.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    echo 'error:unauthorized';
    exit;
}

$memberID = $_SESSION['memberID']; 
$quote = $_POST['quote'];
$tags = $_POST['tags'];

$date = date('Y-m-d');
$sql = "SELECT COUNT(*) as count FROM quotes WHERE memberID = ? AND DATE(date_added) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $memberID, $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] >= 2) {
    echo 'error:exceed_limit';
} else {
    $sql = "INSERT INTO quotes (memberID, quote, tags, date_added) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $memberID, $quote, $tags);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    $stmt->close();
}
?>
