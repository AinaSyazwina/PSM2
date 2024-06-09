<?php 
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $memberID = $_POST['memberID'] ?? '';

    $query = "DELETE FROM register WHERE memberID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $memberID);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "User deleted successfully";
    } else {
        echo "Error: could not delete user";
    }

    $stmt->close();
    $conn->close();
}
?>
