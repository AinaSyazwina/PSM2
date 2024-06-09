<?php
include 'config.php'; // Include your DB connection settings

header('Content-Type: application/json');
$memberID = $_GET['memberID'] ?? '';

// Query to count currently borrowed books where ReturnDate is NULL
$query = "SELECT COUNT(*) as currentlyBorrowed FROM issuebook WHERE memberID = ? AND ReturnDate IS NULL";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => "Prepare error: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $memberID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode($row);

$stmt->close();
$conn->close();
?>
