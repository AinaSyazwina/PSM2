<?php
include 'config.php';

$bookID = $_GET['bookID'] ?? '';
$memberID = $_GET['memberID'] ?? '';  // Make sure this is being received
$response = ['success' => false];

if ($bookID && $memberID) {
    $query = "SELECT IssueDate, DueDate, ReturnDate FROM issuebook WHERE bookID = ? AND memberID = ? ORDER BY IssueDate DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $bookID, $memberID);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response = [
                'success' => true,
                'issueDate' => $row['IssueDate'],
                'dueDate' => $row['DueDate'],
                'isReturned' => !is_null($row['ReturnDate'])
            ];
        } else {
            $response['error'] = 'No information available for this book';  // Provide a specific error
        }
    } else {
        $response['error'] = 'Failed to execute query';  // Error in query execution
    }
    $stmt->close();
} else {
    $response['error'] = 'Missing book ID or member ID';  // Error if ID is missing
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>
