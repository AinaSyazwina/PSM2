<?php
include 'config.php';

$response = [
    'success' => false,
    'message' => 'An error occurred.'
];

$memberID = $_POST['memberID'] ?? '';
$isbn = $_POST['isbn'] ?? ''; 
$paymentFor = $_POST['paymentFor'] ?? '';
$total = $_POST['total'] ?? '';

$fineMapping = [
    "return_late" => "1",
    "exceed" => "2",
    "missing" => "3",
    "damage" => "4"
];

$fineID = $fineMapping[$paymentFor] ?? null;

// Validate input
if (!$memberID || !$isbn || !$fineID || !$total) {
    $response['message'] = 'Required fields are missing or incorrect.';
    echo json_encode($response);
    exit;
}

// Check if the student is active
$studentStatusQuery = $conn->prepare("SELECT status FROM register WHERE memberID = ?");
$studentStatusQuery->bind_param("s", $memberID);
$studentStatusQuery->execute();
$studentStatusResult = $studentStatusQuery->get_result();

if ($studentStatusResult->num_rows == 0) {
    $response['message'] = 'Student not found.';
    echo json_encode($response);
    exit;
}

$studentStatusRow = $studentStatusResult->fetch_assoc();
if ($studentStatusRow['status'] !== 'active') {
    $response['message'] = 'The student is not active. Fines cannot be issued.';
    echo json_encode($response);
    exit;
}
$studentStatusQuery->close();

// Retrieve the book acquisition (bookID) based on ISBN
$bookID_stmt = $conn->prepare("SELECT book_acquisition FROM books WHERE ISBN = ?");
$bookID_stmt->bind_param("s", $isbn);
$bookID_stmt->execute();
$bookID_result = $bookID_stmt->get_result();

if ($bookID_result->num_rows == 0) {
    $response['message'] = 'No matching book acquisition ID for provided ISBN.';
    echo json_encode($response);
    exit;
}

$bookID_row = $bookID_result->fetch_assoc();
$book_acquisition = $bookID_row['book_acquisition'];
$bookID_stmt->close();

// Retrieve the issueID based on both book acquisition (bookID) and memberID
$issueID_stmt = $conn->prepare("SELECT issueID, returnDate FROM issuebook WHERE bookID = ? AND memberID = ?");
$issueID_stmt->bind_param("is", $book_acquisition, $memberID);
$issueID_stmt->execute();
$issueID_result = $issueID_stmt->get_result();

if ($issueID_result->num_rows == 0) {
    $response['message'] = 'No matching issueID for the provided book acquisition ID and memberID.';
    echo json_encode($response);
    exit;
}

$issueID_row = $issueID_result->fetch_assoc();
$issueID = $issueID_row['issueID'];
$returnDate = $issueID_row['returnDate'];
$issueID_stmt->close();

if ($paymentFor === 'missing' && !is_null($returnDate)) {
    $response['message'] = 'Cannot issue "missing" fine for a returned book.';
    echo json_encode($response);
    exit;
}

if ($paymentFor === 'damage' && is_null($returnDate)) {
    $response['message'] = 'Cannot issue "damage" fine for a book that has not been returned.';
    echo json_encode($response);
    exit;
}

// Check for duplicate fine
$duplicateCheckQuery = $conn->prepare("SELECT * FROM fines WHERE memberID = ? AND issueBookID = ? AND fineID = ?");
$duplicateCheckQuery->bind_param("ssi", $memberID, $issueID, $fineID);
$duplicateCheckQuery->execute();
$duplicateCheckResult = $duplicateCheckQuery->get_result();

if ($duplicateCheckResult->num_rows > 0) {
    $response['message'] = 'A fine for this ISBN, memberID, and fineID has already been issued.';
    echo json_encode($response);
    exit;
}

// Insert the fine record
$fine_stmt = $conn->prepare("INSERT INTO fines (memberID, issueBookID, fineID, amount, isPaid) VALUES (?, ?, ?, ?, 0)");
if (false === $fine_stmt) {
    $response['message'] = 'Prepare statement failed: ' . htmlspecialchars($conn->error);
    echo json_encode($response);
    exit;
}

$bind = $fine_stmt->bind_param("sssd", $memberID, $issueID, $fineID, $total);
if (false === $bind) {
    $response['message'] = 'Bind param failed: ' . htmlspecialchars($fine_stmt->error);
    echo json_encode($response);
    exit;
}

$exec = $fine_stmt->execute();
if (false === $exec) {
    // Log the error message for debugging
    error_log('Execute failed: ' . htmlspecialchars($fine_stmt->error));
    $response['message'] = 'Execute failed: ' . htmlspecialchars($fine_stmt->error);
    echo json_encode($response);
    exit;
}

if ($fine_stmt->affected_rows > 0) {
    $response['success'] = true;
    $response['message'] = 'Fine has been issued successfully.';
} else {
    $response['message'] = 'No fine was issued, no rows affected.';
}

$fine_stmt->close();
$conn->close();

echo json_encode($response);
?>
