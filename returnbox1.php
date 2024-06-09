
<?php
include 'config.php'; 

header('Content-Type: application/json');
$response = ['success' => false];

$BoxSerialNum = $_POST['BoxSerialNum'] ?? '';
$returnDate = $_POST['returnDate'] ?? '';

if (!$BoxSerialNum || !$returnDate) {
    $response['error'] = "Box Serial Number and Return Date must be provided.";
    echo json_encode($response);
    exit;
}

$query = "UPDATE issuebox SET ReturnDate = ? WHERE BoxSerialNum = ? AND ReturnDate IS NULL";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    $response['error'] = "Prepare failed: " . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("ss", $returnDate, $BoxSerialNum);

if ($stmt->execute()) {
  
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
    } else {
       
        $response['error'] = "No matching box found or box already returned.";
    }
} else {
    $response['error'] = "Execute failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>