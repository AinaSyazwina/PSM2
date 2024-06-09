<?php 
include 'config.php';
$uploadDir = 'uploads/'; 
$errors = []; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $BoxSerialNum = $_POST['BoxSerialNum'];

    $query = "SELECT * FROM boxs WHERE BoxSerialNum = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $BoxSerialNum);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $boxData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$boxData) {
        echo "Box not found.";
        exit;
    }

    $category = $_POST['category'];
    $DateCreate = $_POST['DateCreate'];
    $BookQuantity = $_POST['BookQuantity'];
    $color = $_POST['color'];
    $status = $_POST['status'];

    $pictureToUse = $boxData['Boxpicture'];

    // Handle file upload
    if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
        $pic = $_FILES['pic'];
        $fileName = time() . '_' . basename($pic['name']);
        $picturePath = $uploadDir . $fileName;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($pic['tmp_name'], $picturePath)) {
            $pictureToUse = mysqli_real_escape_string($conn, $picturePath);

            if (!empty($boxData['Boxpicture']) && $boxData['Boxpicture'] !== $pictureToUse) {
                unlink($boxData['Boxpicture']);
            }
        } else {
            $errors['pic'] = 'There was an error uploading the file.';
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
}
?>
