<?php
include 'config.php';

$memberId = isset($_GET['memberId']) ? $_GET['memberId'] : '';

$response = [
    'fullName' => ''
];

if ($memberId) {
    $query = "SELECT fullName FROM register WHERE memberID = '{$memberId}'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $response['fullName'] = $row['fullName'];
    }
}

echo json_encode($response);
?>
