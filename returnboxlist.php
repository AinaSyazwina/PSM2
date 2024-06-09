<?php
// returnboxlist.php

include 'config.php'; // Include your database configuration file

header('Content-Type: application/json');

$memberID = $_GET['memberID'] ?? '';

// Array to hold the return data
$returns = [];

if ($memberID) {
    // Query to get the return details. Adjust the table and column names as per your database schema
    $query = "SELECT i.issueBoxID, i.memberID, b.BoxSerialNum, b.category, i.IssueDate as borrowDate, i.DueDate, i.ReturnDate, 'Remark' as Status 
              FROM issuebox i
              INNER JOIN boxs b ON i.BoxSerialNum = b.BoxSerialNum 
              WHERE i.memberID = ? "; // Only fetch records with a ReturnDate

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $memberID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $returns[] = $row;
            }
        } else {
            $response['error'] = $stmt->error;
        }
        $stmt->close();
    } else {
        $response['error'] = $conn->error;
    }
} else {
    $response['error'] = 'Member ID is required.';
}

$conn->close();

// Echoing JSON encoded result
echo json_encode($returns);
?>
