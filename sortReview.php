<?php
include 'config.php';

$sort = $_GET['sort'] ?? 'all';
$uploadDir = ''; // Ensure this path is correctly specified

$query = "SELECT r.*, b.Title AS BookTitle, b.picture, reg.fullName, reg.role 
          FROM reviews r 
          INNER JOIN issuebook ib ON r.issueID = ib.issueID
          INNER JOIN books b ON ib.bookID = b.book_acquisition
          INNER JOIN register reg ON r.memberID = reg.memberID
          WHERE r.isReview = 1";

if ($sort !== 'all') {
    $rating = (int)$sort;  // Use the sort parameter directly as an integer rating
    $query .= " AND r.rating = $rating";
}

$query .= " ORDER BY r.DateReview DESC";

$result = $conn->query($query);
$output = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uploadDir = ''; 
        $picture = htmlspecialchars($uploadDir . ($row['picture'] ?? 'default.jpg')); // Default image if not available
        $output .= '<div class="review">';
        $output .= '<div class="left-side">';
        $output .= "<img src='{$picture}' alt='" . htmlspecialchars($row['BookTitle']) . "' class='avatar'>";
        $output .= '<h3>' . htmlspecialchars($row['fullName']) . '</h3>';
        $output .= '<p>' . htmlspecialchars($row['role']) . '</p>';
        $output .= '</div>';
        $output .= '<div class="right-side">';
        $output .= '<div class="star-rating">' . str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']) . '</div>';
        $output .= '<h3 style="color: var(--blue2);">' . htmlspecialchars($row['BookTitle']) . '</h3>';
        $output .= '<p><strong>Date of Review:</strong> ' . htmlspecialchars($row['DateReview']) . '</p>';
        $output .= '<p><strong>Date of Review Edit:</strong> ' . htmlspecialchars($row['DateReviewEdit']) . '</p>';
        $output .= '<p><strong>Review:</strong> ' . nl2br(htmlspecialchars($row['review'])) . '</p>';
        $output .= '</div></div>';
    }
} else {
    $output = '<p>No reviews found.</p>';
}

echo $output;
?>
