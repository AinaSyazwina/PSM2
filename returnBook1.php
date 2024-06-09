<?php
include 'config.php';
$response = ['success' => false];

$isbn = $_POST['ISBN'] ?? ''; 
$memberID = $_POST['memberID'] ?? ''; 
$returnDate = $_POST['returnDate'] ?? '';

if ($isbn && $memberID && $returnDate) {
    // First, get the bookID from ISBN that has not been returned yet
    $bookQuery = "SELECT i.bookID FROM issuebook i
                  JOIN books b ON i.bookID = b.book_acquisition
                  WHERE b.ISBN = ? AND i.memberID = ? AND i.ReturnDate IS NULL
                  ORDER BY i.IssueDate DESC LIMIT 1";
    $bookStmt = $conn->prepare($bookQuery);
    if (!$bookStmt) {
        echo json_encode(['success' => false, 'error' => "Prepare failed: " . $conn->error]);
        exit;
    }
    $bookStmt->bind_param("ss", $isbn, $memberID);
    $bookStmt->execute();
    $bookResult = $bookStmt->get_result();
    if ($bookRow = $bookResult->fetch_assoc()) {
        $bookID = $bookRow['bookID'];

        // Now perform the return
        $query = "UPDATE issuebook SET ReturnDate = ? WHERE bookID = ? AND memberID = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sss", $returnDate, $bookID, $memberID);
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = "Execute failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['error'] = "Prepare failed: " . $conn->error;
        }
    } else {
        $response['error'] = "No active issue found with that ISBN and Member ID";
    }
    $bookStmt->close();
} else {
    $response['error'] = "ISBN, Member ID, or Return Date is not provided";
}
$conn->close();
echo json_encode($response);
?>
