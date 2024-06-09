<?php
header('Content-Type: application/json');
include 'config.php'; // Make sure your database credentials are correct

$queryType = $_GET['queryType'] ?? 'unknown';
$genre = isset($_GET['genre']) ? $conn->real_escape_string($_GET['genre']) : '';
error_log("Received genre: $genre");
$author = isset($_GET['author']) ? $conn->real_escape_string($_GET['author']) : '';
$sql = "";

switch ($queryType) {
    case 'books_by_author':
        $sql = "SELECT author1 AS author, Title FROM books WHERE author1 LIKE '%$author%'";
        break;

    case 'recently_added_books':
        $sql = "SELECT Title FROM books WHERE DateReceived >= NOW() - INTERVAL 90 DAY";
        break;
    case 'popular_books':
        $sql = "SELECT b.Title, COUNT(ib.bookID) AS Count 
                FROM issuebook ib
                JOIN books b ON ib.bookID = b.book_acquisition
                GROUP BY ib.bookID
                ORDER BY Count DESC
                LIMIT 10";
        break;
    case 'top_authors':
        $sql = "SELECT author1 AS author, COUNT(*) AS num_books 
                FROM books b
                JOIN issuebook ib ON b.book_acquisition = ib.bookID 
                GROUP BY author1
                ORDER BY num_books DESC 
                LIMIT 3";
        break;
        
        case 'books_by_genre':
            $genre = $conn->real_escape_string($genre);
            $sql = "SELECT Title FROM books WHERE genre = '$genre'";
            break;
        default:
            echo json_encode(['error' => 'Invalid query type']);
            exit;
    }
    
$result = $conn->query($sql);
if ($result) {
    $books = [];
    if ($queryType === 'popular_books') {
        $mostPopular = '';
        $maxCount = 0;
        while ($row = $result->fetch_assoc()) {
            $books[] = ['title' => $row['Title'], 'count' => $row['Count']];
            if ($row['Count'] > $maxCount) {
                $mostPopular = $row['Title'];
                $maxCount = $row['Count'];
            }
        }
        echo json_encode(['message' => "The most popular book is '$mostPopular' with $maxCount issues.", 'data' => $books]);
    } 
    
    elseif ($queryType === 'top_authors') {
        $authors = [];
        while ($row = $result->fetch_assoc()) {
            $authors[] = ['author' => $row['author'], 'count' => $row['num_books']];
        }
        $messages = array_map(function($author) {
            return "{$author['author']}: {$author['count']} books";
        }, $authors);
        echo json_encode(['message' => 'Top authors and their book issues: ' . implode(', ', $messages)]);
    }
    
    
    elseif ($queryType === 'books_by_genre') {
        $titles = [];
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['Title'];
        }
        echo json_encode(['message' => "The books available for $genre are: " . implode(', ', $titles)]);
    }
    
    
    elseif ($queryType === 'recently_added_books') {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row['Title'];
        }
        echo json_encode(['message' => "The recent books added are: " . implode(', ', $books)]);
    } elseif ($queryType === 'books_by_author') {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row['Title'];
        }
        echo json_encode(['message' => "The book titles for author $author are: " . implode(', ', $books)]);
    } else {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row['Title'];
        }
        echo json_encode(['books' => $books]);
    }
} else {
    echo json_encode(['error' => 'SQL error: ' . $conn->error]);
}

$conn->close();
?>