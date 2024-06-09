<?php
include 'config.php'; 
header('Content-Type: application/json');

$isbn = $_GET['ISBN'] ?? '';
$memberID = $_GET['memberID'] ?? ''; // Get memberID from query parameters

if ($isbn && $memberID) {
    $query = "SELECT b.Title, i.IssueDate 
              FROM books b 
              JOIN issuebook i ON b.book_acquisition = i.bookID 
              WHERE b.ISBN = ? AND i.memberID = ? AND i.ReturnDate IS NULL
              ORDER BY i.IssueDate DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['isAvailable' => false, 'error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ss", $isbn, $memberID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($data = $result->fetch_assoc()) {
        echo json_encode(['isAvailable' => true, 'details' => $data]);
    } else {
        echo json_encode(['isAvailable' => false, 'error' => 'Book not found or already returned']);
    }
    $stmt->close();
} else {
    echo json_encode(['isAvailable' => false, 'error' => 'ISBN or Member ID not provided']);
}
$conn->close();
?>
