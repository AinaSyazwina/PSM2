<?php
include 'config.php';

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'amount' => null
];

// Extract the fine type from the request
$fineType = json_decode(file_get_contents('php://input'))->fineType ?? '';

if ($fineType) {
    $stmt = $conn->prepare("SELECT amount FROM finebook WHERE type = ?");
    $stmt->bind_param("s", $fineType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['amount'] = $row['amount'];
    } else {
        $response['message'] = 'Fine type not found.';
    }

    $stmt->close();
} else {
    $response['message'] = 'Fine type is missing.';
}

$conn->close();
echo json_encode($response);
?>
