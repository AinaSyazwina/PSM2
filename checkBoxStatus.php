<?php
include 'config.php';

$BoxSerialNum = $_GET['BoxSerialNum'] ?? '';
$response = ['available' => false, 'message' => ''];

if (!empty($BoxSerialNum)) {

    $stmt = $conn->prepare("SELECT ReturnDate FROM issuebox WHERE BoxSerialNum = ? AND ReturnDate IS NULL");
    $stmt->bind_param("s", $BoxSerialNum);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $response['message'] = 'No matching box found or box already returned.';
    } else {
        $response['available'] = true;
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid Box Serial Number.';
}

$conn->close();
echo json_encode($response);
?>
