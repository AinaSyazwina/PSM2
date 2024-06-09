<?php
include 'config.php';

$response = ['success' => false];

// Check if all required POST parameters are present
$requiredParams = ['memberID', 'BoxSerialNum', 'borrowDate', 'dueDate'];
foreach ($requiredParams as $param) {
    if (!isset($_POST[$param]) || empty(trim($_POST[$param]))) {
        $response['error'] = "Missing parameter: $param";
        echo json_encode($response);
        exit;
    }
}

$memberID = $_POST['memberID'];
$BoxSerialNum = $_POST['BoxSerialNum'];
$borrowDate = $_POST['borrowDate'];
$dueDate = $_POST['dueDate'];

try {
    // Check if the box is open for issue
    $checkStatusQuery = "SELECT status FROM boxs WHERE BoxSerialNum = ?";
    $statusStmt = $conn->prepare($checkStatusQuery);
    $statusStmt->bind_param("s", $BoxSerialNum);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    $statusData = $statusResult->fetch_assoc();
    $statusStmt->close();

    if ($statusData['status'] !== 'Open(For Issue)') {
        $response['error'] = "This box is not open for issue.";
        echo json_encode($response);
        exit;
    }

    // Check if the box has any books in it
    $checkBooksQuery = "SELECT SUM(CopyCount) AS TotalBooks FROM book_distribution WHERE BoxSerialNum = ?";
    $booksStmt = $conn->prepare($checkBooksQuery);
    $booksStmt->bind_param("s", $BoxSerialNum);
    $booksStmt->execute();
    $booksResult = $booksStmt->get_result();
    $booksData = $booksResult->fetch_assoc();
    $booksStmt->close();

    if ($booksData['TotalBooks'] <= 0) {
        $response['error'] = "This box has no books available for borrowing.";
        echo json_encode($response);
        exit;
    }

    // Check if the box is currently borrowed and not returned
    $checkQuery = "SELECT * FROM issuebox WHERE BoxSerialNum = ? AND ReturnDate IS NULL";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $BoxSerialNum);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Box is currently issued and not returned
        $response['error'] = "This box is currently borrowed and not yet returned.";
    } else {
        // Box is available for issuing
        $query = "INSERT INTO issuebox (memberID, BoxSerialNum, IssueDate, DueDate) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $response['error'] = $conn->error;
        } else {
            $stmt->bind_param("ssss", $memberID, $BoxSerialNum, $borrowDate, $dueDate);
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = $stmt->error;
            }
            $stmt->close();
        }
    }

    $checkStmt->close();
} catch (Exception $e) {
    $response['error'] = "Exception occurred: " . $e->getMessage();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
