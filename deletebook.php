<?php 
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_acquisition = $_POST['bookacq'] ?? '';

    $conn->begin_transaction();

    try {
        
        $queryDistribution = "DELETE FROM book_distribution WHERE ISBN = (SELECT ISBN FROM books WHERE book_acquisition = ?)";
        $stmtDistribution = $conn->prepare($queryDistribution);
        $stmtDistribution->bind_param("s", $book_acquisition);
        $stmtDistribution->execute();
        $stmtDistribution->close();

       
        $queryBook = "DELETE FROM books WHERE book_acquisition = ?";
        $stmtBook = $conn->prepare($queryBook);
        $stmtBook->bind_param("s", $book_acquisition);
        $stmtBook->execute();

        if ($stmtBook->affected_rows > 0) {
            echo "Book deleted successfully";
            $conn->commit(); 
        } else {
            echo "Error: could not delete book";
            $conn->rollback(); 
        }

        $stmtBook->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        $conn->rollback(); 
    }

    $conn->close();
}
?>
