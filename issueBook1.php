<?php
header('Content-Type: application/json');
include 'config.php';

error_reporting(0); // Turn off all error reporting

$response = ['success' => false];

$memberID = $_POST['memberID'] ?? '';
$isbn = $_POST['ISBN'] ?? '';
$borrowDate = $_POST['borrowDate'] ?? '';
$dueDate = $_POST['dueDate'] ?? '';

// Retrieve the book_acquisition using the ISBN provided.
$bookQuery = "SELECT book_acquisition FROM books WHERE ISBN = ?";
$bookStmt = $conn->prepare($bookQuery);
if (!$bookStmt) {
    $response['error'] = "Prepare error: " . $conn->error;
    echo json_encode($response);
    exit;
}

$bookStmt->bind_param("s", $isbn);
$bookStmt->execute();
$result = $bookStmt->get_result();
if ($bookRow = $result->fetch_assoc()) {
    $bookAcquisition = $bookRow['book_acquisition'];

    // Perform the insertion into the issuebook table using book_acquisition.
    $query = "INSERT INTO issuebook (memberID, bookID, IssueDate, DueDate) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssss", $memberID, $bookAcquisition, $borrowDate, $dueDate);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = "Execute error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['error'] = "Prepare error: " . $conn->error;
    }
} else {
    $response['error'] = "No book found with that ISBN";
}

$bookStmt->close();
$conn->close();

echo json_encode($response);
?>
