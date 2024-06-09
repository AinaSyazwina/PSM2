<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include 'config.php';

session_start();

if (isset($_GET['issueID']) && !empty($_SESSION['memberID'])) {
    $issueID = $_GET['issueID'];
    $memberID = $_SESSION['memberID'];

    $stmt = $conn->prepare("SELECT rating, review FROM reviews WHERE issueID = ? AND memberID = ?");
    $stmt->bind_param('is', $issueID, $memberID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No review data found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error fetching review data.']);
}

$conn->close();
?>
