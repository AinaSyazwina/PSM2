<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if (isset($data['id'])) {
    $memberID = $_SESSION['memberID'];
    $quoteID = $data['id'];

    $sql = "DELETE FROM quotes WHERE quoteID = ? AND memberID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $quoteID, $memberID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}

$conn->close();
?>
