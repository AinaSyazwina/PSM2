<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberID = $_POST['memberID'];
    $eligible = $_POST['eligible'];

    if ($eligible == 1) {
        // Reset the loyalty card for a new cycle and set requested flag
        $sql = "UPDATE loyalty_cards SET stamps = 0, eligible = 0, requested = 1 WHERE memberID = ?";
    } else {
        // Simply update the eligible status and clear requested flag
        $sql = "UPDATE loyalty_cards SET eligible = 0, requested = 0 WHERE memberID = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $memberID);
    $stmt->execute();
    $stmt->close();

    echo 'Success';
}
?>
