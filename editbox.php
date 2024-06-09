<?php 
include 'config.php';
$uploadDir = 'uploads/'; // Directory where images will be uploaded
$errors = []; 

$BoxSerialNum = isset($_GET['BoxSerialNum']) ? htmlspecialchars($_GET['BoxSerialNum']) : '';

if (empty($BoxSerialNum)) {
    echo 'Invalid box information.';
    exit;
} else {
    // Fetch box data from the database
    $query = "SELECT * FROM boxs WHERE BoxSerialNum = '$BoxSerialNum'";
    $result = mysqli_query($conn, $query);
    $boxData = mysqli_fetch_assoc($result);
    if (!$boxData) {
        echo 'Box data not found for BoxSerialNum: ' . $BoxSerialNum;
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $DateCreate = $_POST['DateCreate'];
    $BookQuantity = $_POST['BookQuantity'];
    $color = $_POST['color'];
    $status = $_POST['status'];
    
    // Assume the old picture remains if no new picture is uploaded
    $picturePath = isset($_POST['existingPicture']) ? $_POST['existingPicture'] : null;

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
                $picturePath = mysqli_real_escape_string($conn, $picturePath);
                // If new image is uploaded successfully, update the path and remove the old file if it's different
                if (!empty($boxData['Boxpicture']) && $boxData['Boxpicture'] !== $picturePath) {
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
        mysqli_stmt_bind_param($stmt, "sssssss", $category, $DateCreate, $BookQuantity, $color, $status, $picturePath, $BoxSerialNum);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Box</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
</head>
<body>
<?php include 'navigation.php'; ?>


<form action="updateditbox.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="existingPicture" value="<?php echo htmlspecialchars($boxData['Boxpicture']); ?>">
    <input type="hidden" name="BoxSerialNum" value="<?php echo htmlspecialchars($boxData['BoxSerialNum']); ?>">

    <div class="form-group">
    <label for="category">Category:</label>
    <select name="category" id="category">
        <option value="BookPanda"<?php echo ($boxData['category'] ?? '') === 'BookPanda' ? ' selected' : ''; ?>>BookPanda</option>
        <option value="GrabBook" <?php echo ($boxData['category'] ?? '') === 'GrabBook' ? ' selected' : ''; ?>>GrabBook</option>
    </select>
</div>

<div class="form-group">
    <label for="DateCreate">Date Created:</label>
    <input type="date" name="DateCreate" id="DateCreate" value='<?php echo htmlspecialchars($boxData['DateCreate'] ?? ''); ?>'>
</div>

<div class="form-group">
    <label for="BookQuantity">Book Quantity:</label>
    <input type="text" name="BookQuantity" id="BookQuantity" value='<?php echo htmlspecialchars($boxData['BookQuantity'] ?? ''); ?>'>
</div>

<div class="form-group">
    <label for="color">Color:</label>
    <select name="color" id="color">
        <option value="Pink"<?php echo ($boxData['color'] ?? '') === 'Pink' ? ' selected' : ''; ?>>Pink</option>
        <option value="Green"<?php echo ($boxData['color'] ?? '') === 'Green' ? ' selected' : ''; ?>>Green</option>
    </select>
</div>

<div class="form-group">
    <label for="status">Status:</label>
    <select name="status" id="status">
        <option value="Open(For Issue)"<?php echo ($boxData['status'] ?? '') === 'Open(For Issue)' ? ' selected' : ''; ?>>Open(For Issue)</option>
        <option value="Close(For Issue)"<?php echo ($boxData['status'] ?? '') === 'Close(For Issue)' ? ' selected' : ''; ?>>Close(For Issue</option>
    </select>
</div>

<div class="form-group">
        <label for="pic">Box Picture:</label>
        <input type="file" name="pic" id="pic">
        <?php if (!empty($boxData['Boxpicture'])): ?>
            <img src="<?php echo htmlspecialchars($boxData['Boxpicture']); ?>" alt="Current Box Image" style="max-width: 100px; max-height: 100px;">
        <?php endif; ?>
        <?php if (!empty($errors['pic'])): ?>
            <div class="error-message"><?php echo $errors['pic']; ?></div>
        <?php endif; ?>
    </div>

    <div class="addBtn">
        <input type="submit" name="updateBtn" value="Update">
        <a href="managebox.php"><button type="button">Cancel</button></a>
    </div>
</form>

</body>
</html>
