<?php
include 'config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $BoxSerialNum = $_POST['BoxSerialNum'];
    $category = $_POST['category'];
    $DateCreate = $_POST['DateCreate'];
    $BookQuantity = $_POST['BookQuantity'];
    $color = $_POST['color'];
    $status = $_POST['status'];
    $pictureToUse = $_POST['existingPicture'];

    $errors = [];

    // Validation
    if (empty($BookQuantity) || !ctype_digit($BookQuantity)) {
        $errors['BookQuantity'] = 'Book Quantity is required and must be an integer.';
    }

    if ($category == 'BookPanda' && $color != 'Pink') {
        $errors['color'] = 'Invalid color for BookPanda. Only Pink is allowed.';
    } elseif ($category == 'GrabBook' && $color != 'Green') {
        $errors['color'] = 'Invalid color for GrabBook. Only Green is allowed.';
    }

    // Handle file upload
    if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
        $pic = $_FILES['pic'];
        $fileType = $pic['type'];
        $fileSize = $pic['size'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
            $fileName = time() . '_' . basename($pic['name']);
            $picturePath = $uploadDir . $fileName;

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($pic['tmp_name'], $picturePath)) {
                $pictureToUse = mysqli_real_escape_string($conn, $picturePath);
                // If new image is uploaded successfully, update the path and remove the old file if it's different
                if (!empty($boxData['Boxpicture']) && $boxData['Boxpicture'] !== $pictureToUse) {
                    unlink($boxData['Boxpicture']); // This deletes the old image
                }
            } else {
                $errors['pic'] = 'There was an error uploading the file.';
            }
        } else {
            $errors['pic'] = 'Invalid file type or size exceeds the limit.';
        }
    }

    if (empty($errors)) {
        $updateQuery = "UPDATE boxs SET category=?, DateCreate=?, BookQuantity=?, color=?, status=?, Boxpicture=? WHERE BoxSerialNum=?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "sssssss", $category, $DateCreate, $BookQuantity, $color, $status, $pictureToUse, $BoxSerialNum);
        $updateResult = mysqli_stmt_execute($stmt);

        if ($updateResult) {
            header("Location: managebox.php?success=true");
            exit;
        } else {
            $errors['database'] = "Error updating record: " . mysqli_error($conn);
        }
    }

    // Store errors and redirect back to edit page
    $_SESSION['errors'] = $errors;
    header("Location: editbox.php?BoxSerialNum=$BoxSerialNum");
    exit;
}
?>
