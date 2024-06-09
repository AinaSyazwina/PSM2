<?php
include 'config.php'; // Ensure the config file has correct DB connection settings

header('Content-Type: application/json');

$isbn = isset($_GET['ISBN']) ? $_GET['ISBN'] : '';

if ($isbn) {
    $query = "SELECT 
                b.ISBN,
                b.Title,
                b.Copy as TotalLibraryCopies,
                IFNULL(SUM(d.CopyCount), 0) as CopiesInBoxes,
                (b.Copy - IFNULL(SUM(d.CopyCount), 0) - (
                    SELECT COUNT(*)
                    FROM issuebook 
                    WHERE bookID = b.book_acquisition AND ReturnDate IS NULL
                )) as AvailableCopies
              FROM books b
              LEFT JOIN book_distribution d ON b.ISBN = d.ISBN
              WHERE b.ISBN = ?
              GROUP BY b.ISBN";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['isAvailable' => false, 'error' => 'DB error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('s', $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($data = $result->fetch_assoc()) {
        $isAvailable = $data['AvailableCopies'] > 0;
        echo json_encode([
            'isAvailable' => $isAvailable,
            'details' => $data
        ]);
    } else {
        echo json_encode(['isAvailable' => false, 'error' => 'Book not found']);
    }
} else {
    echo json_encode(['isAvailable' => false, 'error' => 'No ISBN provided']);
}
$conn->close();
?>
