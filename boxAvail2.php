<?php 
include 'config.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default to first page if not specified
$perPage = 10; // Assume a default if not provided via AJAX

$offset = ($page - 1) * $perPage; // Calculate the offset based on the current page

$query = "
SELECT 
    b.BoxSerialNum, 
    b.category,
    b.status, -- Added status to the selected fields
    b.BookQuantity,
    IF(latest_issue.ReturnDate IS NULL AND latest_issue.BoxSerialNum IS NOT NULL, 'red', 'green') AS AvailabilityColor,
    IFNULL(SUM(bd.CopyCount), 0) AS TotalBooks
FROM 
    boxs b
LEFT JOIN (
    SELECT 
        ib.BoxSerialNum,
        ib.ReturnDate
    FROM 
        issuebox ib
    WHERE 
        ib.issueBoxID IN (
            SELECT 
                MAX(issueBoxID)
            FROM 
                issuebox
            GROUP BY 
                BoxSerialNum
        )
) AS latest_issue ON b.BoxSerialNum = latest_issue.BoxSerialNum
LEFT JOIN book_distribution bd ON b.BoxSerialNum = bd.BoxSerialNum
WHERE b.BoxSerialNum LIKE '%$searchTerm%' OR b.category LIKE '%$searchTerm%'
GROUP BY b.BoxSerialNum
ORDER BY 
    b.BoxSerialNum
LIMIT 
    $perPage OFFSET $offset";

$result = $conn->query($query);
if($result === false) {
    die("Error querying the database: " . $conn->error);
}

// Display each box in a row
while ($row = $result->fetch_assoc()) {
    if ($row['status'] !== 'Open(For Issue)') {
        $statusColor = 'grey';
        $availabilityText = 'Close For Issue';
    } else if ($row['TotalBooks'] == 0) {
        $statusColor = 'grey';
        $availabilityText = 'Unavailable';
    } else {
        $statusColor = $row['AvailabilityColor'];
        $availabilityText = ($statusColor == 'red') ? 'Unavailable' : 'Available';
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['BoxSerialNum']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
    echo "<td><div class='status-button " . $statusColor . "'>" . htmlspecialchars($availabilityText) . "</div></td>";
    echo "</tr>";
}
mysqli_close($conn);
?>

<style>
.status-button {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    text-align: center;
    font-size: 12px;
    box-sizing: border-box;
    min-width: 60px;
}
.status-button.green {
    background-color: green;
}
.status-button.red {
    background-color: red;
}
.status-button.grey {
    background-color: grey;
}
</style>
