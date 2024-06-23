<?php 
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $BoxSerialNum = $_POST['BoxSerialNum'] ?? '';

    $query = "DELETE FROM boxs WHERE BoxSerialNum = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $BoxSerialNum);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Box deleted successfully";
    } else {
        if ($stmt->errno == 1451) { 
            echo "Cannot delete, item is already a foreign key";
        } else {
            echo "Error: could not delete box";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
