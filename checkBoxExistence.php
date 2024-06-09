<?php 
include 'config.php';

$response = ['exists' => false];
$boxSerialNum = $_GET['BoxSerialNum'] ?? '';

// Prepare the SQL statement
$stmt = $conn->prepare("SELECT COUNT(*) FROM boxs WHERE BoxSerialNum = ?");
$stmt->bind_param("s", $boxSerialNum);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_row();

// Check if the box exists
if ($row[0] > 0) {
    $response['exists'] = true;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
