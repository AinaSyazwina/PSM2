<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the content type to JSON
header('Content-Type: application/json');

// Retrieve the JSON input
$json_input = file_get_contents('php://input');
$input = json_decode($json_input, true);
$memberID = $input['memberID'] ?? null;

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'data' => []
];

if ($memberID !== null) {
    // Prepare the SQL query
    $stmt = $conn->prepare("
        SELECT f.memberID, b.ISBN, fb.type AS FineType, f.amount, f.datePaid
        FROM fines f
        INNER JOIN issuebook ib ON f.issueBookID = ib.issueID
        INNER JOIN books b ON ib.bookID = b.book_acquisition
        INNER JOIN finebook fb ON f.fineID = fb.fineID
        WHERE f.memberID = ?
    ");

    if ($stmt) {
        // Bind parameters and execute the query
        $stmt->bind_param("s", $memberID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Data found for this member ID.';
            $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $response['message'] = 'No data found for this member ID.';
        }
        $stmt->close();
    } else {
        // If the statement preparation failed, log the error
        $response['message'] = 'Query preparation failed: ' . $conn->error;
    }
} else {
    $response['message'] = 'Member ID is required.';
}

$conn->close();
echo json_encode($response);
?>
