<?php
include 'config.php';

$memberID = isset($_GET['memberID']) ? $_GET['memberID'] : '';

$issues = [];
if ($memberID) {
    $query = "SELECT m.fullname, i.bookID, b.ISBN, i.IssueDate as borrowDate, i.DueDate as dueDate, i.ReturnDate as ReturnDate
              FROM issuebook i
              JOIN register m ON i.memberID = m.memberID
              JOIN books b ON i.bookID = b.book_acquisition
              WHERE i.memberID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $memberID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $issues[] = $row;
            }
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($issues);
?>
