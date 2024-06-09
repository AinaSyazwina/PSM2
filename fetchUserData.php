<?php
include 'config.php';
$userID = $_GET['userID'] ?? ''; // Use null coalescing operator to check for existence

$response = ['success' => false];
$query = "SELECT fullname, IC, class FROM register WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    $response['success'] = true;
    $response['fullname'] = $userData['fullname'];
    $response['IC'] = $userData['IC'];
    $response['class'] = $userData['class'];
}

$stmt->close();
$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>
