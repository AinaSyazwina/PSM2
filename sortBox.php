<?php
include 'config.php';

// Pagination settings
$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Sorting type from query parameter
$sortType = $_GET['sort'] ?? 'All';

// Start building the base query
$query = "SELECT b.*, IFNULL(SUM(bd.CopyCount), 0) AS TotalBooks
FROM boxs b
LEFT JOIN book_distribution bd ON b.BoxSerialNum = bd.BoxSerialNum";

// Apply conditions based on sort type
if ($sortType === 'BookPanda' || $sortType === 'GrabBook') {
    $query .= " WHERE b.category = '" . mysqli_real_escape_string($conn, $sortType) . "'";
}

// Add GROUP BY after WHERE
$query .= " GROUP BY b.BoxSerialNum";

// Continue with ORDER BY
switch ($sortType) {
    case 'Latest':
        $query .= " ORDER BY b.DateCreate DESC";
        break;
    case 'Oldest':
        $query .= " ORDER BY b.DateCreate ASC";
        break;
    case 'All':
    default:
        // Keep default sort if no sort is specified or if it's 'All'
        $query .= " ORDER BY b.DateCreate DESC";
        break;
}

// Append LIMIT and OFFSET for pagination
$query .= " LIMIT $recordsPerPage OFFSET $offset";

$result = mysqli_query($conn, $query);

// Query for counting total records for pagination
$countQuery = "SELECT COUNT(DISTINCT b.BoxSerialNum) AS totalRecords FROM boxs b";
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['totalRecords'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Output for debugging
echo "<table>";
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . (!empty($row['Boxpicture']) ? "<img src='" . htmlspecialchars($row['Boxpicture']) . "' alt='Box Image' class='box-image'>" : "No image") . "</td>";
        echo "<td>" . htmlspecialchars($row['BoxSerialNum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>" . date('Y-m-d', strtotime($row['DateCreate'])) . "</td>";
        echo "<td>" . htmlspecialchars($row['color']) . "</td>";
        echo "<td>" . $row['TotalBooks'] . ' / ' . $row['BookQuantity'] . "</td>";
        echo "<td><div class='actions-icons'>
            <button class='eye-btn'><ion-icon name='eye-outline'></ion-icon></button>
            <button class='edit-btn'><ion-icon name='create-outline'></ion-icon></button>
            <button class='delete-btn'><ion-icon name='trash-outline'></ion-icon></button>
            <button class='list-btn' style='background-color:" . ($row['TotalBooks'] > 0 ? 'rgb(222, 148, 84)' : 'grey') . ";'><ion-icon name='menu-outline'></ion-icon></button>
        </div></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No boxes found</td></tr>";
}
echo "</table>";
?>

