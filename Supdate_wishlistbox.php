<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT memberID FROM register WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$memberID = $user['memberID'];

$boxSerialNum = $data['BoxSerialNum'];
$isLoved = $data['isLoved'];

if ($isLoved) {
    $query = "INSERT INTO wishlistbox (memberID, BoxSerialNum) VALUES (?, ?)";
} else {
    $query = "DELETE FROM wishlistbox WHERE memberID = ? AND BoxSerialNum = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $memberID, $boxSerialNum);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
