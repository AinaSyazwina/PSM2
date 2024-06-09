
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Cssfile/list.css">
</head>
<body>
    
</body>
</html>
<?php 
// In listbook.php
include 'config.php';

$BoxSerialNum = isset($_GET['BoxSerialNum']) ? $_GET['BoxSerialNum'] : '';

$stmt = $conn->prepare("
    SELECT b.ISBN, b.Title, b.DateReceived, bd.CopyCount
    FROM books b
    JOIN book_distribution bd ON b.ISBN = bd.ISBN
    WHERE bd.BoxSerialNum = ?
");
$stmt->bind_param("s", $BoxSerialNum);
$stmt->execute();
$result = $stmt->get_result();

if(!$result) {
    die("Query failed: " . mysqli_error($conn));
}

echo "<table class='table1'>";
echo "<thead><tr><th>ISBN</th><th>Title</th><th>Date Received</th><th>Copy Count</th></tr></thead><tbody>";

while($row = mysqli_fetch_assoc($result)) {
    echo "<tr class='list1'>";
    echo "<td>" . htmlspecialchars($row['ISBN']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
    echo "<td>" . htmlspecialchars($row['DateReceived']) . "</td>";
    echo "<td>" . htmlspecialchars($row['CopyCount']) . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

if(mysqli_num_rows($result) == 0) {
    echo "<tr><td colspan='4'>No books found for this box.</td></tr>";
}

?>


