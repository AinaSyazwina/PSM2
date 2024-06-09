<?php
include 'config.php';

$sort = $_GET['sort'] ?? 'All'; // Fetch the sort criterion from the query parameter

$uploadDir = ''; // Specify the directory if the images are not in the same directory as this script.
$query = "SELECT * FROM books";

// Adding condition for sorting by genre or date
if ($sort !== 'All') {
    switch ($sort) {
        case 'Latest':
            $query .= " ORDER BY DateReceived DESC"; // Sort by newest first
            break;
        case 'Oldest':
            $query .= " ORDER BY DateReceived ASC"; // Sort by oldest first
            break;
        default:
            $query .= " WHERE genre = '" . mysqli_real_escape_string($conn, $sort) . "'";
            $query .= " ORDER BY Title"; // Default sorting by title within genre
            break;
    }
} else {
    $query .= " ORDER BY Title"; // Default sorting by title when 'All' is selected
}

$result = mysqli_query($conn, $query);

// Handling the display of books
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pictureSrc = !empty($row['picture']) ? $uploadDir . $row['picture'] : 'default.jpg';
        echo "<tr>";
        echo "<td><img src='" . htmlspecialchars($pictureSrc) . "' alt='Book Image' class='book-image'></td>";
        echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ISBN']) . "</td>";
        echo "<td>" . htmlspecialchars($row['author1']) . "</td>";
        echo "<td>" . htmlspecialchars($row['genre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DateReceived']) . "</td>";
        echo "<td>
              <div class='action-icons'>
                 <button class='eye-btn'><ion-icon name='eye-outline'></ion-icon></button>
                 <button class='edit-btn'><ion-icon name='create-outline'></ion-icon></button>
                 <button class='delete-btn'><ion-icon name='trash-outline'></ion-icon></button>
              </div>
          </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No books found</td></tr>";
}
?>
