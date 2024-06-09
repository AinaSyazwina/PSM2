<?php
include 'config.php';  // Ensure the config file has correct DB connection settings
header('Content-Type: application/json');

$BoxSerialNum = $_GET['BoxSerialNum'] ?? '';
$MemberID = $_GET['MemberID'] ?? '';

// Validate input
if (empty($BoxSerialNum) || empty($MemberID)) {
    echo json_encode(['exists' => false, 'message' => 'Both Box Serial Number and Member ID must be provided']);
    exit;
}

// Prepare the SQL query to check the latest issue status of the box
$query = "SELECT ReturnDate FROM issuebox WHERE BoxSerialNum = ? AND MemberID = ? ORDER BY BorrowDate DESC LIMIT 1";
$stmt = $conn->prepare($query);

// Handle preparation errors
if (!$stmt) {
    echo json_encode(['exists' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

// Bind parameters and execute
$stmt->bind_param("ss", $BoxSerialNum, $MemberID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the latest issue record
if ($data = $result->fetch_assoc()) {
    if ($data['ReturnDate'] === null) {
        echo json_encode(['exists' => true, 'returned' => false, 'message' => 'This box is currently borrowed and not returned.']);
    } else {
        echo json_encode(['exists' => true, 'returned' => true, 'message' => 'This box has been returned.']);
    }
} else {
    echo json_encode(['exists' => false, 'message' => 'No records found for this box.']);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
