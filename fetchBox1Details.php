<?php
include 'config.php'; // Include your database configuration file
header('Content-Type: application/json');

$BoxSerialNum = $_GET['BoxSerialNum'] ?? '';
$memberID = $_GET['memberID'] ?? '';

if (!$BoxSerialNum || !$memberID) {
    echo json_encode(['success' => false, 'error' => 'Box Serial Number and Member ID are required.']);
    exit;
}

// Prepare SQL to fetch category and the most recent borrow date where the box hasn't been returned yet
$query = "SELECT b.category, ib.IssueDate as borrowDate 
          FROM boxs b
          JOIN issuebox ib ON b.BoxSerialNum = ib.BoxSerialNum
          WHERE b.BoxSerialNum = ? AND ib.memberID = ? AND ib.ReturnDate IS NULL
          ORDER BY ib.IssueDate DESC LIMIT 1";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$stmt->bind_param("ss", $BoxSerialNum, $memberID);
$stmt->execute();
$result = $stmt->get_result();
if ($data = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'category' => $data['category'], 'borrowDate' => $data['borrowDate']]);
} else {
    echo json_encode(['success' => false, 'error' => 'No data found for this Box Serial Number with the specified Member ID.']);
}

$stmt->close();
$conn->close();
?>
