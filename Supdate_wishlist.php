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

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$memberID = $user['memberID'];

if (isset($data['book_acquisition']) && isset($data['isLoved'])) {
    $bookAcquisition = $data['book_acquisition'];
    $isLoved = $data['isLoved'];

    if ($isLoved) {
        $query = "INSERT INTO wishlistbook (memberID, book_acquisition) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $memberID, $bookAcquisition);
    } else {
        $query = "DELETE FROM wishlistbook WHERE memberID = ? AND book_acquisition = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $memberID, $bookAcquisition);
    }

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
