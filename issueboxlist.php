<?php
include 'config.php';

$memberID = $_GET['memberID'] ?? '';

$issues = [];
if ($memberID) {
    // Note the correction of the SQL query to include BoxSerialNum and category from the boxs table.
    $query = "SELECT i.issueBoxID, i.memberID, bx.BoxSerialNum, bx.category, i.IssueDate as borrowDate, i.DueDate as dueDate 
              FROM issuebox i
              JOIN register m ON i.memberID = m.memberID 
              JOIN boxs bx ON i.BoxSerialNum = bx.BoxSerialNum 
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
