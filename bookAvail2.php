<?php
require_once 'config.php';


$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Default to first page if not specified
$perPage = 10;  // Assume a default if not provided via AJAX

$offset = ($page - 1) * $perPage;  // Calculate the offset based on the current page

$booksQuery = "
SELECT 
    b.bookID, 
    b.title, 
    b.ISBN,
    b.book_acquisition, 
    b.Copy AS totalCopies, 
    IFNULL(bd.totalInBoxes, 0) AS totalInBoxes, 
    IFNULL(ib.totalIssues, 0) AS totalIssues
FROM 
    books b
LEFT JOIN 
    (SELECT ISBN, SUM(CopyCount) AS totalInBoxes FROM book_distribution GROUP BY ISBN) bd 
    ON b.ISBN = bd.ISBN
LEFT JOIN 
    (SELECT bookID, COUNT(*) AS totalIssues FROM issuebook WHERE ReturnDate IS NULL GROUP BY bookID) ib 
    ON b.book_acquisition = ib.bookID
WHERE 
    b.title LIKE '%$searchTerm%' OR b.bookID LIKE '%$searchTerm%'
ORDER BY 
    b.DateReceived DESC
LIMIT 
    $perPage OFFSET $offset";

$booksResult = $conn->query($booksQuery);


// Display each book in a row
while ($row = $booksResult->fetch_assoc()) {
    $totalCopiesAvailable = $row['totalCopies'] - $row['totalInBoxes'] - $row['totalIssues'];
    $totalBooks = $row['totalCopies'] - $row['totalInBoxes'];  
    $statusClass = $totalCopiesAvailable > 0 ? 'available' : 'unavailable';
    $statusText = $totalCopiesAvailable > 0 ? 'Available' : 'Unavailable';

    echo "<tr>
            <td>{$row['title']}</td>
            <td>{$row['ISBN']}</td>
            <td>{$totalBooks}</td> <!-- Display total books -->
            <td>{$totalCopiesAvailable}</td>
            <td>{$row['totalIssues']}</td>
            <td><span class='status-button $statusClass'>$statusText</span></td>
          </tr>";
}
?>
