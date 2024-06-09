<?php
include 'config.php';

$sort = $_GET['sort'] ?? 'all';
$uploadDir = ''; // Set the correct path to your images

// Start building your query
$query = "SELECT rv.*, b.category, b.Boxpicture, reg.fullName, reg.role, b.BoxSerialNum
          FROM reviewbox rv
          INNER JOIN issuebox ib ON rv.issueBoxID = ib.issueBoxID
          INNER JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
          INNER JOIN register reg ON rv.memberID = reg.memberID
          WHERE rv.isReview = 1";

// Apply filter based on rating if not 'all'
if ($sort !== 'all') {
    $rating = (int)$sort;  // Convert the sort parameter to an integer rating
    $query .= " AND rv.rating = $rating"; // Use 'rv' alias since the 'rating' column belongs to 'reviewbox'
}

$query .= " ORDER BY rv.DateReview DESC"; // Order results by review date

$result = $conn->query($query);
$output = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $picturePath = htmlspecialchars($uploadDir . ($row['Boxpicture'] ?? 'default.jpg')); // Provide a default image if none is found
        $output .= '<div class="review">';
        $output .= '<div class="left-side">';
        $output .= "<img src='{$picturePath}' alt='" . htmlspecialchars($row['BoxSerialNum']) . "' class='avatar'>";
        $output .= '<h3>' . htmlspecialchars($row['fullName']) . '</h3>';
        $output .= '<p>' . htmlspecialchars($row['role']) . '</p>';
        $output .= '</div>';
        $output .= '<div class="right-side">';
        $output .= '<div class="star-rating">' . str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']) . '</div>';
        $output .= '<h3>' . htmlspecialchars($row['BoxSerialNum']) . '</h3>';
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
