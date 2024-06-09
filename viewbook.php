

<?php

include 'config.php'; // Include your database configuration file

$ISBN = $_GET['ISBN'] ?? '';

if (empty($ISBN)) {
    echo 'Invalid book information.';
    exit;
}

$query = "SELECT ISBN, author1, author2, Title, PublishDate, PublicPlace, Copy, genre, PageNum, DateReceived, Price, book_acquisition FROM books WHERE ISBN = '$ISBN'";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Error executing query: " . mysqli_error($conn);
    exit;
}

if ($row = mysqli_fetch_assoc($result)) {
  
    echo "<div class='book-Details'>";
    echo "<h2>Book Details</h2>";

    echo "<p><strong>Book Acquisition:</strong> " . htmlspecialchars($row['book_acquisition']) . "</p>";
    echo "<p><strong>ISBN:</strong> " . htmlspecialchars($row['ISBN']) . "</p>";
    echo "<p><strong>Author 1:</strong> " . htmlspecialchars($row['author1']) . "</p>";
    echo "<p><strong>Author 2:</strong> " . (empty($row['author2']) ? 'None' : htmlspecialchars($row['author2'])) . "</p>";
    echo "<p><strong>Title:</strong> " . htmlspecialchars($row['Title']) . "</p>";
    echo "<p><strong>Publish Date:</strong> " . htmlspecialchars($row['PublishDate']) . "</p>";
    echo "<p><strong>Publication Place:</strong> " . htmlspecialchars($row['PublicPlace']) . "</p>";
    echo "<p><strong>Copy:</strong> " . htmlspecialchars($row['Copy']) . "</p>";
    echo "<p><strong>genre:</strong> " . htmlspecialchars($row['genre']) . "</p>";
    echo "<p><strong>Page Number:</strong> " . htmlspecialchars($row['PageNum']) . "</p>";
    echo "<p><strong>Date Received:</strong> " . htmlspecialchars($row['DateReceived']) . "</p>";
    echo "<p><strong>Price:</strong> " . htmlspecialchars($row['Price']) . "</p>";
    echo "<p><strong>Box Serial Number:</strong> " . (empty($row['BoxSerialNum']) ? 'None' : htmlspecialchars($row['BoxSerialNum'])) . "</p>";
    
    $boxQuery = "SELECT BoxSerialNum, CopyCount FROM book_distribution WHERE ISBN = '$ISBN'";
    $boxResult = mysqli_query($conn, $boxQuery);

    if ($boxResult && mysqli_num_rows($boxResult) > 0) {
        while ($box = mysqli_fetch_assoc($boxResult)) {
            echo "<p><strong>Box Serial Number:</strong> " . htmlspecialchars($box['BoxSerialNum']) . ", <strong>Copy Count:</strong> " . htmlspecialchars($box['CopyCount']) . "</p>";
        }
    } else {
        echo "<p>No box distribution details available.</p>";
    }
    
    echo "</div>"; 
} else {
    echo 'Book data not found.';
}