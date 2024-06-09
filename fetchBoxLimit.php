<?php
// Your database connection file
include 'config.php';

// Set the content type for JSON response
header('Content-Type: application/json');

// Maximum number of boxes that can be borrowed
$maxBoxesAllowed = 2;

// Retrieve memberID from the query parameter
$memberID = isset($_GET['memberID']) ? $_GET['memberID'] : '';

// Initialize the array to store the response
$response = [
    'issueAmountAllowed' => [
        'total' => $maxBoxesAllowed,
        'available' => $maxBoxesAllowed
    ],
    'issueBoxAllowed' => [
        'total' => $maxBoxesAllowed,
        'available' => $maxBoxesAllowed
    ]
];

if ($memberID) {
    // Prepare the query to count the number of boxes currently borrowed by the member
    $query = "SELECT COUNT(*) as boxesBorrowed FROM issuebox WHERE memberID = ? AND ReturnDate IS NULL";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $memberID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Calculate the available boxes
        $boxesBorrowed = $row['boxesBorrowed'];
        $boxesAvailable = $maxBoxesAllowed - $boxesBorrowed;
        
        // Update the response with the actual values
        $response['issueAmountAllowed']['available'] = $boxesAvailable;
        $response['issueBoxAllowed']['available'] = $boxesAvailable;
    } else {
        // Handle errors in preparing the statement
        $response['error'] = "Prepare failed: " . $conn->error;
    }
    $stmt->close();
} else {
    $response['error'] = "No memberID provided in the request.";
}

// Close the database connection
$conn->close();

// Encode the response array as JSON and return it
echo json_encode($response);
?>
