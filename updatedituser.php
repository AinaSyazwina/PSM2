<?php
include 'config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $memberID = $_POST['memberID'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $class = $_POST['class'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';
    $clarify = $_POST['clarify'] ?? '';
    $currentPicture = $_POST['currentPicture'] ?? ''; 

    $picturePath = $currentPicture; 
    $errors = [];

    // Fullname validation
    if (!preg_match("/^[a-zA-Z ]+$/", $fullname)) {
        $errors['fullname'] = "Name must contain only alphabetic characters and spaces.";
    }

    // Email validation
    if (!strpos($email, '@')) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if email already exists
        $email_check_query = "SELECT * FROM register WHERE email = ? AND memberID != ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $email_check_query);
        mysqli_stmt_bind_param($stmt, "ss", $email, $memberID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = "Email already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Username validation
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else {
        // Check if username already exists
        $username_check_query = "SELECT * FROM register WHERE username = ? AND memberID != ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $username_check_query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $memberID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $errors['username'] = "Username already exists.";
        }
        mysqli_stmt_close($stmt);
    }

    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['picture']['name']);
        $tempPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['picture']['tmp_name'], $tempPath)) {
            $picturePath = htmlspecialchars($tempPath);
        } else {
            echo "Failed to upload file. Using existing image.";
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE register SET fullname=?, username=?, email=?, birthdate=?, class=?, role=?, status=?, clarify=?, picture=? WHERE memberID=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssssss', $fullname, $username, $email, $birthdate, $class, $role, $status, $clarify, $picturePath, $memberID);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: manageuser.php?success=true");
            exit;
        } else {
            $_SESSION['errors'] = ["Error updating record: " . mysqli_error($conn)];
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['post_data'] = $_POST;
    }

    mysqli_close($conn);
    header("Location: edituser.php?memberID=$memberID");
    exit;
}
?>
