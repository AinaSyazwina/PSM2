<?php
include 'config.php'; // Make sure this path is correct

$boxSerialNum = isset($_GET['BoxSerialNum']) ? $_GET['BoxSerialNum'] : '';

if ($boxSerialNum) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM boxs WHERE BoxSerialNum = ?");
    $stmt->bind_param("s", $boxSerialNum);
    $stmt->execute();
    $stmt->bind_result($count); // This line is added
    $stmt->fetch(); // This line is added

    $exists = $count > 0; // This line is modified

    echo json_encode(['exists' => $exists]);
    $stmt->close();
} else {
    echo json_encode(['exists' => false, 'error' => 'No Box Serial Number provided.']);
}

$conn->close();
?>
