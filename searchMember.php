<?php
include 'config.php';

$memberID = $_GET['memberID'] ?? '';


$response = ['success' => false];


if (empty($memberID)) {
    $response['error'] = 'Member ID is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


$query = "SELECT fullname, IC, class FROM register WHERE memberID = ? AND status = 'active'";
$stmt = $conn->prepare($query);


$stmt->bind_param("s", $memberID);


if ($stmt->execute()) {

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
 
        $userData = $result->fetch_assoc();
        
        $response = [
            'success' => true,
            'fullname' => $userData['fullname'],
            'IC' => $userData['IC'],
            'class' => $userData['class']
        ];
    } else {
       
        $response['error'] = 'Member not found or inactive';
    }
} else {

    $response['error'] = 'Error executing query';
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
