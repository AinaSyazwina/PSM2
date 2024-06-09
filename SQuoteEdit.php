<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if (isset($data['id']) && isset($data['quote']) && isset($data['tags'])) {
    $memberID = $_SESSION['memberID'];
    $quoteID = $data['id'];
    $quote = $data['quote'];
    $tags = $data['tags'];

    if (empty($quote) || empty($tags)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }

    $sql = "UPDATE quotes SET quote = ?, tags = ? WHERE quoteID = ? AND memberID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $quote, $tags, $quoteID, $memberID);

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
