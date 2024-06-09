<?php

include 'config.php';

$memberID = $_GET['memberID'] ?? '';

if (empty($memberID)) {
    echo 'Invalid user information.';
    exit;
}

$query = "SELECT fullname, IC, email, birthdate, memberID, class, username, role, status FROM register WHERE memberID = '$memberID'";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Error executing query: " . mysqli_error($conn);
    exit;
}

if ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='book-Details'>";
    echo "<h2>User Details</h2>";

    echo "<p><strong>Name:</strong> " . htmlspecialchars($row['fullname']) . "</p>";
    echo "<p><strong>Identification Number:</strong> " . htmlspecialchars($row['IC']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";
    echo "<p><strong>Birthday Date:</strong> " . htmlspecialchars($row['birthdate']) . "</p>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($row['username']) . "</p>";
    echo "<p><strong>Member ID:</strong> " . htmlspecialchars($row['memberID']) . "</p>";
    echo "<p><strong>Class:</strong> " . htmlspecialchars($row['class']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($row['role']) . "</p>";
    echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";  
    echo "</div>";
} else {
    echo 'User data not found.';
}

mysqli_close($conn);
