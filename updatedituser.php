<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $memberID = $_POST['memberID'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $class = $_POST['class'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';
    $clarify = $_POST['clarify'] ?? '';
    $currentPicture = $_POST['currentPicture'] ?? ''; // Hidden field from the form

    $picturePath = $currentPicture; // Default to the existing picture path

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . basename($_FILES['picture']['name']);
        $tempPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['picture']['tmp_name'], $tempPath)) {
            $picturePath = htmlspecialchars($tempPath);
        } else {
            echo "Failed to upload file. Using existing image.";
            // Keep the existing image if the upload fails
        }
    }

    $sql = "UPDATE register SET fullname=?, email=?, birthdate=?, class=?, role=?, status=?, clarify=?, picture=? WHERE memberID=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssssss', $fullname, $email, $birthdate, $class, $role, $status, $clarify, $picturePath, $memberID);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manageuser.php?success=true");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
