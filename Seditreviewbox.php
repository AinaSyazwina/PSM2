<?php
include 'config.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issueBoxID'], $_POST['rating'], $_POST['review']) && !empty($_SESSION['memberID'])) {
    $memberID = $_SESSION['memberID'];
    $issueBoxID = $_POST['issueBoxID'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    $stmt = $conn->prepare("UPDATE reviewbox SET rating = ?, review = ?, DateReviewEdit = NOW() WHERE issueBoxID = ? AND memberID = ?");
    $stmt->bind_param('isii', $rating, $review, $issueBoxID, $memberID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating review.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
