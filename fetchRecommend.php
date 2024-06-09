<?php
include 'config.php';  // Ensure this file includes your database connection settings

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    // Prepare statement to avoid SQL Injection
    $stmt = $conn->prepare("SELECT title, picture, genre FROM books WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
        echo json_encode($details);
    } else {
        echo json_encode(["error" => "No details found for this book"]);
    }
} else {
    echo json_encode(["error" => "Book title not provided"]);
}
?>
