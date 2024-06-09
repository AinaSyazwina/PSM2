<?php
include 'config.php';
header('Content-Type: application/json');

$BoxSerialNum = $_GET['BoxSerialNum'] ?? '';

if (!empty($BoxSerialNum)) {
    $stmt = $conn->prepare("SELECT category FROM boxs WHERE BoxSerialNum = ?");
    $stmt->bind_param("s", $BoxSerialNum);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($box = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'box' => $box]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No box found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'BoxSerialNum is required']);
}
$conn->close();
?>
