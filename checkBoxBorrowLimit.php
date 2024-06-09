<?php
include 'config.php'; // Make sure your DB connection settings are included

header('Content-Type: application/json');
$memberID = $_GET['memberID'] ?? '';

$query = "SELECT COUNT(*) as currentlyBorrowed FROM issuebox WHERE memberID = ? AND ReturnDate IS NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $memberID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode($row);

$stmt->close();
$conn->close();
?>
