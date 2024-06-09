<?php
include 'config.php';
$response = ['success' => false, 'message' => ''];

$data = json_decode(file_get_contents('php://input'), true);
$memberID = $data['memberID'] ?? '';

if ($memberID) {
    $stmt = $conn->prepare("SELECT fullName FROM register WHERE memberID = ?");
    $stmt->bind_param("s", $memberID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['fullName'] = $row['fullName'];
    } else {
        $response['message'] = 'Member ID not found';
    }
    $stmt->close();
} else {
    $response['message'] = 'No Member ID provided';
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>
