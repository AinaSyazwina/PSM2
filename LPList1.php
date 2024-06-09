<?php
include 'navigaLib.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    header('Location: index.php');
    exit;
}

include 'config.php';

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT * FROM register WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$memberID = $user['memberID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Cssfile/navStu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Book Details and Reviews</title>
    <style>
        .book-details {
            display: flex;
            margin-bottom: 20px;
        }
        .book-details img {
            width: 350px;
            height: 500px;
            margin-right: 20px;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .book-details img:hover {
            transform: scale(1.05);
        }
        .book-info {
            flex-grow: 1;
            max-width: 600px;
        }
        .book-title {
            font-size: 24px;
            color: #333;
            margin: 0 0 20px 0;
            font-weight: bold;
        }
        .book-description {
            font-size: 16px;
            color: #666;
            margin: 10px 0;
        }
        .rating {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .rating span {
            font-size: 18px;
            color: #FFD700;
            margin-right: 10px;
        }
        .rating .stars {
            font-size: 18px;
            color: #FFD700;
        }
        .ratings-snapshot {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        .average-rating {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .average-rating .average {
            font-size: 36px;
            color: #000;
            font-weight: bold;
        }
        .average-rating .stars {
            font-size: 24px;
            color: #FFD700;
            margin-bottom: 5px;
        }
        .average-rating .review-count {
            font-size: 18px;
            color: #333;
        }
        .ratings-bars {
            width: 100%;
        }
        .ratings-bar {
            height: 10px;
            background-color: #ddd;
            border-radius: 5px;
            margin-bottom: 4px;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        .ratings-bar-inner {
            height: 10px;
            background-color: #FFD700;
            border-radius: 5px;
        }
        .review-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .review {
            display: flex;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .review:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .review img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .review-content {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .review strong {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .review .rating {
            font-size: 20px;
            color: #FFD700;
            margin-bottom: 5px;
        }
        .review .role-model {
            font-size: 14px;
            color: #000;
            margin-bottom: 10px;
        }
        .review p {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .love-icon {
            font-size: 24px;
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            transition: color 0.3s ease, background-color 0.3s ease;
        }

        .love-icon.loved {
            color: pink;
            background-color: #ffe0e0;
        }
    </style>
</head>
<body>

<div class="container-padding" style="margin-top: 150px;">
    <h1>Book Details</h1>
    <?php
    include 'config.php';

    $bookAcquisition = isset($_GET['bookAcquisition']) ? intval($_GET['bookAcquisition']) : 0;
    $uploadDir = ''; 

    // Check if the book is in the user's wishlist
    $wishlistQuery = $conn->prepare("SELECT * FROM wishlistbook WHERE memberID = ? AND book_acquisition = ?");
    $wishlistQuery->bind_param("si", $memberID, $bookAcquisition);
    $wishlistQuery->execute();
    $wishlistResult = $wishlistQuery->get_result();
    $isLoved = $wishlistResult->num_rows > 0;

    function getRatingCount($stars, $bookAcquisition, $conn) {
        $query = "SELECT COUNT(*) as count FROM reviews r
                  INNER JOIN issuebook ib ON r.issueID = ib.issueID
                  INNER JOIN books b ON ib.bookID = b.book_acquisition
                  WHERE r.rating = ? AND b.book_acquisition = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $stars, $bookAcquisition);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    function getAverageRating($bookAcquisition, $conn) {
        $query = "SELECT AVG(rating) as avg_rating FROM reviews r
                  INNER JOIN issuebook ib ON r.issueID = ib.issueID
                  INNER JOIN books b ON ib.bookID = b.book_acquisition
                  WHERE b.book_acquisition = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $bookAcquisition);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return round($row['avg_rating'], 1);
    }

    // Fetch book details
    $bookQuery = "SELECT Title, Author1, Author2, Genre, PageNum, ISBN, Price, picture FROM books WHERE book_acquisition = ?";
    $stmt = $conn->prepare($bookQuery);
    $stmt->bind_param("i", $bookAcquisition);
    $stmt->execute();
    $bookResult = $stmt->get_result();
    if ($bookResult->num_rows > 0) {
        $book = $bookResult->fetch_assoc();
        $averageRating = getAverageRating($bookAcquisition, $conn);
        $totalReviews = getRatingCount(1, $bookAcquisition, $conn) + getRatingCount(2, $bookAcquisition, $conn) + getRatingCount(3, $bookAcquisition, $conn) + getRatingCount(4, $bookAcquisition, $conn) + getRatingCount(5, $bookAcquisition, $conn);

        // Determine the number of filled and empty stars
        $filledStars = round($averageRating);
        $emptyStars = 5 - $filledStars;

        // Generate filled (yellow) and empty (grey) stars strings
        $filledStarsHtml = str_repeat("★", $filledStars);
        $emptyStarsHtml = str_repeat("☆", $emptyStars); // Using a different character or style for empty stars

        echo "<div class='book-details'>";
        echo "<img src='" . $uploadDir . $book['picture'] . "' alt='Book Cover'>";
        echo "<div class='book-info'>";
        echo "<div class='rating'><span>{$averageRating}</span><span class='stars'>{$filledStarsHtml}{$emptyStarsHtml}</span></div>";
        echo "<h1 class='book-title'>" . htmlspecialchars($book['Title']) . "</h1>";
        echo "<p class='book-description'>" . htmlspecialchars($book['Title']) . " is a book written by " . htmlspecialchars($book['Author1']) . (!empty($book['Author2']) ? " and " . htmlspecialchars($book['Author2']) : "") . ". The ISBN is " . htmlspecialchars($book['ISBN']) . ". It has a total of " . htmlspecialchars($book['PageNum']) . " pages. The price is $" . htmlspecialchars($book['Price']) . ". The genre is " . htmlspecialchars($book['Genre']) . ", which is suitable for those who enjoy this genre.</p>";
        echo "<p class='book-description'>This book offers a thrilling experience and provides deep insights into the genre, making it a must-read for enthusiasts. The narrative style and character development are exceptional, keeping readers engaged till the very end.</p>";
        echo "<i class='far fa-heart love-icon" . ($isLoved ? " loved" : "") . "' data-book-id='{$bookAcquisition}'></i>";
        echo "</div>";
        echo "</div>";

        // Reviews text
        echo "<h2>Reviews</h2>";

        // Ratings snapshot
        echo "<div class='ratings-snapshot'>";
        echo "<div class='average-rating'>";
        echo "<div class='average'>{$averageRating} / 5</div>";
        echo "<div class='stars' style='color: #FFD700;'>{$filledStarsHtml}<span style='color: #CCC;'>{$emptyStarsHtml}</span></div>";
        echo "<div class='review-count'>{$totalReviews} Review" . ($totalReviews > 1 ? "s" : "") . "</div>";
        echo "</div>";
        echo "<div class='ratings-bars'>";
        for ($i = 5; $i > 0; $i--) {
            $ratingCount = getRatingCount($i, $bookAcquisition, $conn);
            $percentage = $totalReviews ? ($ratingCount / $totalReviews) * 100 : 0;
            echo "<div style='margin-bottom: 4px; display: flex; align-items: center;'>";
            echo "<span style='margin-right: 10px;'>{$i} <span style='color: #FFD700; text-shadow: 1px 1px 1px #000;'>★</span></span>";
            echo "<div class='ratings-bar' style='flex-grow: 1;'>";
            echo "<div class='ratings-bar-inner' style='width: {$percentage}%;'></div>";
            echo "</div>";
            echo "<span style='margin-left: 10px;'>{$ratingCount}</span>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";

        // Fetch and display reviews
        $reviewQuery = "SELECT r.*, reg.fullName, reg.role, reg.memberID, reg.picture FROM reviews r
                        INNER JOIN register reg ON r.memberID = reg.memberID
                        INNER JOIN issuebook ib ON r.issueID = ib.issueID
                        INNER JOIN books b ON ib.bookID = b.book_acquisition
                        WHERE b.book_acquisition = ? AND r.isReview = 1";
        $reviewStmt = $conn->prepare($reviewQuery);
        $reviewStmt->bind_param("i", $bookAcquisition);
        $reviewStmt->execute();
        $reviewsResult = $reviewStmt->get_result();
        if ($reviewsResult->num_rows > 0) {
            echo "<div class='review-container'>";
            while ($review = $reviewsResult->fetch_assoc()) {
                echo "<div class='review clearfix'>";
                echo "<img src='" . $uploadDir . $review['picture'] . "' alt='User Image'>";
                echo "<div class='review-content'>";
                echo "<strong>" . htmlspecialchars($review['fullName']) . "</strong>";
                echo "<div class='rating'>" . str_repeat("★", $review['rating']) . "</div>";
                echo "<div class='role-model'>Role: " . htmlspecialchars($review['role']) . " | Member ID: " . htmlspecialchars($review['memberID']) . "</div>";
                echo "<p>" . htmlspecialchars($review['review']) . "</p>";
                echo "<p style='margin-top: 10px;'>Date Reviewed: " . date("F j, Y", strtotime($review['DateReview'])) . "</p>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>No reviews available for this book.</p>";
        }
    } else {
        echo "<p>No book details found.</p>";
    }
    ?>
</div>

<script>
    document.querySelectorAll('.love-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const bookAcquisition = this.getAttribute('data-book-id');
            const isLoved = this.classList.toggle('loved');
            updateWishlist(bookAcquisition, isLoved);
        });
    });

    function updateWishlist(bookAcquisition, isLoved) {
        fetch('Supdate_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ book_acquisition: bookAcquisition, isLoved })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                console.error('Failed to update wishlist:', data.error);
                alert('Failed to update wishlist');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
</body>
</html>
