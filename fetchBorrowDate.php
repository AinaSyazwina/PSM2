<?php
include 'config.php';  // Ensure your database connection is correctly set up

header('Content-Type: application/json');
$response = ['success' => false];

$BoxSerialNum = $_GET['BoxSerialNum'] ?? '';
$memberID = $_GET['memberID'] ?? '';

if (empty($BoxSerialNum) || empty($memberID)) {
    $response['error'] = "Missing Box Serial Number or Member ID.";
    echo json_encode($response);
    exit;
}

$query = "SELECT IssueDate FROM issuebox WHERE BoxSerialNum = ? AND memberID = ? ORDER BY IssueDate DESC LIMIT 1";
$stmt = $conn->prepare($query);

if (!$stmt) {
    $response['error'] = "Database prepare error: " . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("ss", $BoxSerialNum, $memberID);

if (!$stmt->execute()) {
    $response['error'] = "Execute error: " . $stmt->error;
    $stmt->close();
    $conn->close();
    echo json_encode($response);
    exit;
}

$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $response['success'] = true;
    $response['issueDate'] = $row['IssueDate'];
} else {
    $response['error'] = "No information found for this box serial number and member ID.";
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>
