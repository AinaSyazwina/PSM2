<?php 
include 'navigaLib.php';
include 'config.php';  // Ensure database configuration is properly set
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    header('Location: index.php');
    exit;
}

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
    <title>Box Details and Reviews</title>
    <style>
        .box-details {
            display: flex;
            margin-bottom: 20px;
        }
        .box-details img {
            width: 350px;
            height: 500px;
            margin-right: 20px;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .box-details img:hover {
            transform: scale(1.05);
        }
        .box-info {
            flex-grow: 1;
            max-width: 600px;
        }
        .box-title {
            font-size: 24px;
            color: #333;
            margin: 0 0 20px 0;
            font-weight: bold;
        }
        .box-description {
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
    <h1>Box Details</h1>
    <?php
    $BoxSerialNum = isset($_GET['BoxSerialNum']) ? $_GET['BoxSerialNum'] : 0;
    $uploadDir = '';  // Ensure this path is correct

    // Check if the box is in the user's wishlist
    $wishlistQuery = $conn->prepare("SELECT * FROM wishlistbox WHERE memberID = ? AND BoxSerialNum = ?");
    $wishlistQuery->bind_param("ss", $memberID, $BoxSerialNum);
    $wishlistQuery->execute();
    $wishlistResult = $wishlistQuery->get_result();
    $isLoved = $wishlistResult->num_rows > 0;

    function getRatingCount($stars, $boxSerialNum, $conn) {
        $query = "SELECT COUNT(*) as count FROM reviewbox r
                  INNER JOIN issuebox ib ON r.issueBoxID = ib.issueBoxID
                  WHERE r.rating = ? AND ib.BoxSerialNum = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $stars, $boxSerialNum);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    function getAverageRating($boxSerialNum, $conn) {
        $query = "SELECT AVG(rating) as avg_rating FROM reviewbox r
                  INNER JOIN issuebox ib ON r.issueBoxID = ib.issueBoxID
                  WHERE ib.BoxSerialNum = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $boxSerialNum);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return round($row['avg_rating'], 1);
    }

    // Fetch box details
    $boxQuery = "SELECT b.BoxSerialNum, b.category, b.color, b.DateCreate, b.status, b.Boxpicture, b.BookQuantity
                 FROM boxs b
                 WHERE b.BoxSerialNum = ?";
    $stmt = $conn->prepare($boxQuery);
    $stmt->bind_param("s", $BoxSerialNum);
    $stmt->execute();
    $boxResult = $stmt->get_result();

    if ($boxResult->num_rows > 0) {
        while ($box = $boxResult->fetch_assoc()) {
            $averageRating = getAverageRating($box['BoxSerialNum'], $conn);
            $filledStars = round($averageRating);
            $emptyStars = 5 - $filledStars;
            $filledStarsHtml = str_repeat("★", $filledStars);
            $emptyStarsHtml = str_repeat("☆", $emptyStars);
            $totalReviews = getRatingCount(1, $box['BoxSerialNum'], $conn) + getRatingCount(2, $box['BoxSerialNum'], $conn) + getRatingCount(3, $box['BoxSerialNum'], $conn) + getRatingCount(4, $box['BoxSerialNum'], $conn) + getRatingCount(5, $box['BoxSerialNum'], $conn);

            echo "<div class='box-details'>";
            echo "<img src='" . $uploadDir . $box['Boxpicture'] . "' alt='Box Picture'>";
            echo "<div class='box-info'>";
            echo "<div class='rating'><span>{$averageRating}</span><span class='stars'>{$filledStarsHtml}{$emptyStarsHtml}</span></div>";
            echo "<h1 class='box-title'>" . htmlspecialchars($box['BoxSerialNum']) . "</h1>";
            echo "<p class='box-description'>This is the {$box['BoxSerialNum']} box with a capacity of holding {$box['BookQuantity']} books. The box is a {$box['category']} type with a color of {$box['color']}. It was created on {$box['DateCreate']} and its current status is {$box['status']}.</p>";
            echo "<p class='box-description'>Ideal for organizing and keeping your book collection in pristine condition, this box is a must-have for avid readers and book collectors. Make sure to handle with care to maintain its quality and durability.</p>";
            echo "<i class='far fa-heart love-icon" . ($isLoved ? " loved" : "") . "' data-box-id='{$box['BoxSerialNum']}'></i>";
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
                $ratingCount = getRatingCount($i, $box['BoxSerialNum'], $conn);
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
            $reviewQuery = "SELECT r.*, reg.fullName, reg.role, reg.memberID, reg.picture FROM reviewbox r
                            INNER JOIN register reg ON r.memberID = reg.memberID
                            INNER JOIN issuebox ib ON r.issueBoxID = ib.issueBoxID
                            WHERE ib.BoxSerialNum = ? AND r.isReview = 1";
            $reviewStmt = $conn->prepare($reviewQuery);
            $reviewStmt->bind_param("s", $box['BoxSerialNum']);
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
                echo "<p>No reviews available for this box.</p>";
            }
        }
    } else {
        echo "<p>No box details found.</p>";
    }
    ?>
</div>

<script>
    document.querySelectorAll('.love-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const boxSerialNum = this.getAttribute('data-box-id');
            const isLoved = this.classList.toggle('loved');
            updateWishlist(boxSerialNum, isLoved);
        });
    });

    function updateWishlist(boxSerialNum, isLoved) {
        fetch('Supdate_wishlistbox.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ BoxSerialNum: boxSerialNum, isLoved })
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
