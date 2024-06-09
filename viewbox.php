
<?php

include 'config.php'; 

$BoxSerialNum = $_GET['BoxSerialNum'] ?? '';

if (empty($BoxSerialNum)) {
    echo 'Invalid box information.';
    exit;
}

$query = "SELECT BoxSerialNum, category, DateCreate, BookQuantity, color, status FROM boxs WHERE BoxSerialNum = '$BoxSerialNum'";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Error executing query: " . mysqli_error($conn);
    exit;
}

if ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='book-Details'>";
    echo "<h2>Box Details</h2>";

    echo "<p><strong>Box Serial Number:</strong> " . htmlspecialchars($row['BoxSerialNum']) . "</p>";
    echo "<p><strong>Category:</strong> " . htmlspecialchars($row['category']) . "</p>";
    echo "<p><strong>Date Create:</strong> " . htmlspecialchars($row['DateCreate']) . "</p>";
    echo "<p><strong>Book Quantity:</strong> " . htmlspecialchars($row['BookQuantity']) . "</p>";
    echo "<p><strong>Color:</strong> " . htmlspecialchars($row['color']) . "</p>";
    echo "<p><strong>Status Date:</strong> " . htmlspecialchars($row['status']) . "</p>";
    
    
    echo "</div>";
} else {
    echo 'Box data not found.';
}

mysqli_close($conn);
?>
