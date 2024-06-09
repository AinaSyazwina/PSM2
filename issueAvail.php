<?php
include 'config.php';

header('Content-Type: application/json');

// Assume default settings for allowed issues
$maxBooksAllowed = 4;

// Collect member ID from the query string
$memberID = $_GET['memberID'] ?? '';

if (!$memberID) {
    echo json_encode(['error' => "No member ID provided in the request."]);
    exit;
}

// Prepare the query
$query = "SELECT COUNT(*) as booksIssued FROM issuebook WHERE memberID = ? AND ReturnDate IS NULL";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}

// Bind parameters, execute query, and fetch results
$stmt->bind_param("s", $memberID);
if (!$stmt->execute()) {
    echo json_encode(['error' => "Execute error: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Calculate the available books
$booksIssued = $row['booksIssued'];
$booksAvailable = $maxBooksAllowed - $booksIssued;

// Formulate the response
$response = [
    'issueAmountAllowed' => [
        'total' => $maxBooksAllowed,
        'available' => max(0, $booksAvailable) // Ensure no negative numbers
    ],
    'issueBookAllowed' => [
        'total' => $maxBooksAllowed,
        'available' => max(0, $booksAvailable) // Ensure no negative numbers
    ]
];

$stmt->close();
$conn->close();

echo json_encode($response);
?>
