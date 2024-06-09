<?php
include 'config.php';

$response = [
    'success' => false,
    'message' => 'An unexpected error occurred.'
];

// Retrieve data from the POST request
$memberID = $_POST['memberID'] ?? null;
$ISBN = $_POST['ISBN'] ?? null;
$fineType = $_POST['paymentFor'] ?? null;
$total = $_POST['total'] ?? null;
$fineID = $_POST['fineID'] ?? null;

$requiredAmounts = [
    "return_late" => 2.00,
    "missing" => 5.00,
    "damage" => 4.00
];

if ($memberID && $ISBN && $fineID && $total && isset($requiredAmounts[$fineType])) {
    if ($total != $requiredAmounts[$fineType]) {
        $response['message'] = 'Incorrect amount entered for the selected fine type.';
    } else {
        $issueIDStmt = $conn->prepare("
            SELECT issuebook.issueID
            FROM issuebook
            INNER JOIN books ON issuebook.bookID = books.book_acquisition
            WHERE issuebook.memberID = ? AND books.ISBN = ?
            ORDER BY issuebook.IssueDate DESC
            LIMIT 1;
        ");
        $issueIDStmt->bind_param("ss", $memberID, $ISBN);
        $issueIDStmt->execute();
        $issueIDResult = $issueIDStmt->get_result();

        if ($issueData = $issueIDResult->fetch_assoc()) {
            $issueBookID = $issueData['issueID'];

            // Check for duplicate unpaid fines matching the issueBookID, memberID, and fineID
            $stmt = $conn->prepare("
                SELECT * FROM fines 
                WHERE memberID = ? AND issueBookID = ? AND fineID = ? AND isPaid = 0
            ");
            $stmt->bind_param("sii", $memberID, $issueBookID, $fineID);
            $stmt->execute();
            $finesResult = $stmt->get_result();

            if ($fineData = $finesResult->fetch_assoc()) {
                $updateStmt = $conn->prepare("
                    UPDATE fines
                    SET isPaid = 1, datePaid = NOW(), amount = ?
                    WHERE memberID = ? AND issueBookID = ? AND fineID = ?
                ");
                $updateStmt->bind_param("disi", $total, $memberID, $issueBookID, $fineID);
                $updateStmt->execute();

                if ($updateStmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'The fine has been successfully paid.';
                } else {
                    $response['message'] = 'The fine could not be updated or may have already been paid.';
                }
                $updateStmt->close();
            } else {
                $response['message'] = 'No unpaid fines found for this book and fine type.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'No book fine record found for the given member and ISBN.';
        }
        $issueIDStmt->close();
    }
} else {
    $response['message'] = 'Required fields are missing.';
}

$conn->close();
echo json_encode($response);
?>
