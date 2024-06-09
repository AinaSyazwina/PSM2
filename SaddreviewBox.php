<?php
include 'config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $memberID = $_SESSION['memberID']; 
    $issueBoxID = $_POST['issueBoxID'];
    $rating = isset($_POST['rating']) ? $_POST['rating'] : null;
    $review = isset($_POST['review']) ? $_POST['review'] : '';
    $isReview = 1; 

    if (empty($rating)) {
        echo "Error: Please add a rating for the box.";
        exit;
    }

    if (empty($review)) {
        echo "Error: Please add a review text.";
        exit;
    }

    $checkStmt = $conn->prepare("SELECT * FROM reviewBox WHERE issueBoxID = ? AND memberID = ?");
    $checkStmt->bind_param('ii', $issueBoxID, $memberID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $checkStmt->close();

    if ($result->num_rows > 0) {
        echo "Error: You have already submitted a review for this issue.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviewBox (memberID, issueBoxID, rating, review, DateReview, isReview) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param('siisi', $memberID, $issueBoxID, $rating, $review, $isReview);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error submitting review: " . $stmt->error;
        }

        $stmt->close();
    }
    $conn->close();
}
?>
