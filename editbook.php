<?php
include 'config.php';
$uploadDir = 'uploads/'; // Ensure this directory exists with write permissions
$errors = [];
$pictureUpdated = false;

$ISBN = $_GET['ISBN'] ?? '';

if (empty($ISBN)) {
    echo 'Invalid book information.';
    exit;
}

$query = "SELECT * FROM books WHERE ISBN = '$ISBN'";
$result = mysqli_query($conn, $query);
$bookData = mysqli_fetch_assoc($result);

if (!$bookData) {
    echo 'Book data not found for ISBN: ' . $ISBN;
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $author1 = $_POST['author1'] ?? '';
    $author2 = $_POST['author2'] ?? '';
    $Title = $_POST['Title'] ?? '';
    $PublishDate = $_POST['PublishDate'] ?? '';
    $PublicPlace = $_POST['PublicPlace'] ?? '';
    $Copy = $_POST['Copy'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $PageNum = $_POST['PageNum'] ?? '';
    $DateReceived = $_POST['DateReceived'] ?? '';
    $Price = $_POST['Price'] ?? '';
    $book_acquisition = $_POST['book_acquisition'] ?? '';

    // Validation
    if (empty($author1)) {
        $errors['author1'] = 'Author 1 is required.';
    }
    if (empty($Title)) {
        $errors['Title'] = 'Title is required.';
    }
    if (empty($PublishDate)) {
        $errors['PublishDate'] = 'Publish Date is required.';
    }
    if (empty($PublicPlace)) {
        $errors['PublicPlace'] = 'Publication Place is required.';
    }
    if (empty($Copy)) {
        $errors['Copy'] = 'Copy is required.';
    } elseif (!ctype_digit($Copy)) {
        $errors['Copy'] = 'Copy must be an integer.';
    }
    if (empty($genre)) {
        $errors['genre'] = 'Genre is required.';
    }
    if (empty($PageNum)) {
        $errors['PageNum'] = 'Page Number is required.';
    } elseif (!ctype_digit($PageNum) || intval($PageNum) <= 0) {
        $errors['PageNum'] = 'Page Number must be a positive integer.';
    }
    if (empty($DateReceived)) {
        $errors['DateReceived'] = 'Date Received is required.';
    }
    if (empty($Price)) {
        $errors['Price'] = 'Price is required.';
    } elseif (!is_numeric($Price) || floatval($Price) <= 0) {
        $errors['Price'] = 'Price must be a positive number.';
    }

    // Handle picture upload
    if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024;
        $pic = $_FILES['pic'];
        $fileType = $pic['type'];
        $fileSize = $pic['size'];

        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
            $fileName = time() . '_' . basename($pic['name']);
            $picturePath = $uploadDir . $fileName;

            // Ensure the upload directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($pic['tmp_name'], $picturePath)) {
                $picturePath = mysqli_real_escape_string($conn, $picturePath);
                $pictureUpdated = true;
            } else {
                $errors['pic'] = 'There was an error uploading the file.';
            }
        } else {
            $errors['pic'] = 'Invalid file type or size exceeds the limit.';
        }
    }

    // If no errors, proceed to update the book data and box associations
    if (empty($errors)) {
        $updateQuery = "UPDATE books SET author1=?, author2=?, Title=?, PublishDate=?, PublicPlace=?, Copy=?, genre=?, PageNum=?, DateReceived=?, Price=?, book_acquisition=?";
        $updateParams = [$author1, $author2, $Title, $PublishDate, $PublicPlace, $Copy, $genre, $PageNum, $DateReceived, $Price, $book_acquisition];

        if ($pictureUpdated) {
            $updateQuery .= ", picture=?";
            $updateParams[] = $picturePath;
        }

        $updateQuery .= " WHERE ISBN=?";
        $updateParams[] = $ISBN;

        $stmt = mysqli_prepare($conn, $updateQuery);
        $types = str_repeat('s', count($updateParams));
        mysqli_stmt_bind_param($stmt, $types, ...$updateParams);
        $updateResult = mysqli_stmt_execute($stmt);

        if ($updateResult) {
            if ($pictureUpdated && !empty($bookData['picture']) && $bookData['picture'] !== $picturePath) {
                unlink($bookData['picture']);
            }

            // Update existing box associations
            if (isset($_POST['existingBoxSerialNums']) && isset($_POST['originalBoxSerialNums'])) {
                foreach ($_POST['originalBoxSerialNums'] as $index => $originalBoxSerialNum) {
                    $BoxSerialNum = $_POST['existingBoxSerialNums'][$index] ?? '';
                    $CopyCount = $_POST['existingCopyCounts'][$index] ?? '';

                    if (empty($BoxSerialNum) || empty($CopyCount)) {
                        // Remove the box association if left blank
                        $deleteBoxQuery = "DELETE FROM book_distribution WHERE ISBN = ? AND BoxSerialNum = ?";
                        $stmtDelete = mysqli_prepare($conn, $deleteBoxQuery);
                        mysqli_stmt_bind_param($stmtDelete, 'ss', $ISBN, $originalBoxSerialNum);
                        mysqli_stmt_execute($stmtDelete);
                        mysqli_stmt_close($stmtDelete);
                    } else {
                        // Update the existing association if not blank
                        $updateBoxQuery = "UPDATE book_distribution SET BoxSerialNum = ?, CopyCount = ? WHERE ISBN = ? AND BoxSerialNum = ?";
                        $stmtBox = mysqli_prepare($conn, $updateBoxQuery);
                        mysqli_stmt_bind_param($stmtBox, 'siss', $BoxSerialNum, $CopyCount, $ISBN, $originalBoxSerialNum);
                        mysqli_stmt_execute($stmtBox);
                        mysqli_stmt_close($stmtBox);
                    }
                }
            }

            // Insert new box associations
            if (isset($_POST['newBoxSerialNums'])) {
                foreach ($_POST['newBoxSerialNums'] as $index => $newBoxSerialNum) {
                    if ($newBoxSerialNum !== '') {
                        $newCopyCount = $_POST['newCopyCounts'][$index];

                        $insertBoxQuery = "INSERT INTO book_distribution (ISBN, BoxSerialNum, CopyCount) VALUES (?, ?, ?)";
                        $stmtInsert = mysqli_prepare($conn, $insertBoxQuery);
                        mysqli_stmt_bind_param($stmtInsert, 'ssi', $ISBN, $newBoxSerialNum, $newCopyCount);
                        mysqli_stmt_execute($stmtInsert);
                        mysqli_stmt_close($stmtInsert);
                    }
                }
            }

            header("Location: managebook.php?success=true");
            exit;
        } else {
            $errors['database'] = "Error updating record: " . mysqli_error($conn);
        }
    }
}

// Fetch existing box associations after all processing is done
$boxQuery = "SELECT BoxSerialNum, CopyCount FROM book_distribution WHERE ISBN = '$ISBN'";
$boxResult = mysqli_query($conn, $boxQuery);
$existingBoxes = mysqli_fetch_all($boxResult, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">

    <style>
        .form-group .count-label {
            color: #333;
            display: block;
            margin-bottom: 5px;
            margin-top: 20px; /* Set the same margin-top as other labels */
            /* Add any other specific styles */
        }

        .error-messages {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php 
include 'config.php';
include 'navigation.php';

$ISBN = $_GET['ISBN'] ?? '';

if (empty($ISBN)) {
    echo 'Invalid book information.';
    exit;
}

$query = "SELECT * FROM books WHERE ISBN = '$ISBN'";

$result = mysqli_query($conn, $query);
$userData = mysqli_fetch_assoc($result);

// Fetch existing box associations
$boxQuery = "SELECT BoxSerialNum, CopyCount FROM book_distribution WHERE ISBN = '$ISBN'";
$boxResult = mysqli_query($conn, $boxQuery);
$existingBoxes = mysqli_fetch_all($boxResult, MYSQLI_ASSOC);

// Calculate the number of additional boxes that can be added (assuming a maximum of 3)
$numberOfAdditionalBoxes = 3 - count($existingBoxes);

mysqli_close($conn);
?>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const priceInput = document.getElementById('Price');
    const priceTooltip = document.getElementById('priceTooltip');
    priceInput.addEventListener('input', validatePrice);
    
    function validatePrice() {
        const priceValue = parseFloat(priceInput.value);
        if (isNaN(priceValue) || priceValue <= 0) {
            // Show the custom tooltip
            priceTooltip.style.visibility = 'visible';
            priceTooltip.style.opacity = '1';
        } else {
            // Hide the custom tooltip
            priceTooltip.style.visibility = 'hidden';
            priceTooltip.style.opacity = '0';
        }
    }
});
</script>

<form action="" method="post" enctype="multipart/form-data">

    <input type="hidden" name="ISBN" value="<?php echo htmlspecialchars($ISBN); ?>">

    <div class='form-group'>
        <label for='book_acquisition' class="required">Book Acquisition:</label>
        <input type='text' name='book_acquisition' id='book_acquisition' value='<?php echo htmlspecialchars($userData['book_acquisition'] ?? ''); ?>' readonly>
    </div>

    <div class='form-group'>
        <label for='ISBN' class="required">ISBN:</label>
        <input type='text' name='ISBN' id='ISBN' value='<?php echo htmlspecialchars($userData['ISBN'] ?? ''); ?>' readonly>
    </div>

    <div class='form-group'>
        <label for='author1' class="required">Author 1:</label>
        <input type='text' name='author1' id='author1' value='<?php echo htmlspecialchars($userData['author1'] ?? ''); ?>'>
        <?php if (isset($errors['author1'])): ?>
            <div class="error-messages"><?php echo $errors['author1']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='author2' >Author 2:</label>
        <input type='text' name='author2' id='author2' value='<?php echo htmlspecialchars($userData['author2'] ?? ''); ?>'>
    </div>

    <div class='form-group'>
        <label for='Title' class="required">Title:</label>
        <input type='text' name='Title' id='Title' value='<?php echo htmlspecialchars($userData['Title'] ?? ''); ?>'>
        <?php if (isset($errors['Title'])): ?>
            <div class="error-messages"><?php echo $errors['Title']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='PublishDate' class="required">Publish Date:</label>
        <input type='date' name='PublishDate' id='PublishDate' value='<?php echo htmlspecialchars($userData['PublishDate'] ?? ''); ?>'>
        <?php if (isset($errors['PublishDate'])): ?>
            <div class="error-messages"><?php echo $errors['PublishDate']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='PublicPlace' class="required">Publication Place:</label>
        <input type='text' name='PublicPlace' id='PublicPlace' value='<?php echo htmlspecialchars($userData['PublicPlace'] ?? ''); ?>'>
        <?php if (isset($errors['PublicPlace'])): ?>
            <div class="error-messages"><?php echo $errors['PublicPlace']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='Copy' class="required">Copy:</label>
        <input type='text' name='Copy' id='Copy' value='<?php echo htmlspecialchars($userData['Copy'] ?? ''); ?>'>
        <?php if (isset($errors['Copy'])): ?>
            <div class="error-messages"><?php echo $errors['Copy']; ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="genre" class="required">Genre:</label>
        <select name="genre" id="genre">
            <option value=""<?php echo ($userData['genre'] ?? '') === '' ? ' selected' : ''; ?>></option>
            <option value="Romance" <?php echo ($userData['genre'] ?? '') === 'Romance' ? ' selected' : ''; ?>>Romance</option>
            <option value="Fiction" <?php echo ($userData['genre'] ?? '') === 'Fiction' ? ' selected' : ''; ?>>Fiction</option>
            <option value="Non-Fiction" <?php echo ($userData['genre'] ?? '') === 'Non-Fiction' ? ' selected' : ''; ?>>Non-Fiction</option>
            <option value="Mystery" <?php echo ($userData['genre'] ?? '') === 'Mystery' ? ' selected' : ''; ?>>Mystery</option>
            <option value="FairyTale" <?php echo ($userData['genre'] ?? '') === 'FairyTale' ? ' selected' : ''; ?>>FairyTale</option>
            <option value="Action" <?php echo ($userData['genre'] ?? '') === 'Action' ? ' selected' : ''; ?>>Action</option>
            <option value="Fantasy" <?php echo ($userData['genre'] ?? '') === 'Fantasy' ? ' selected' : ''; ?>>Fantasy</option>
            <option value="Historical" <?php echo ($userData['genre'] ?? '') === 'Historical' ? ' selected' : ''; ?>>Historical</option>
        </select>
        <?php if (isset($errors['genre'])): ?>
            <div class="error-messages"><?php echo $errors['genre']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='PageNum' class="required">Page Number:</label>
        <input type='text' name='PageNum' id='PageNum' value='<?php echo htmlspecialchars($userData['PageNum'] ?? ''); ?>'>
        <?php if (isset($errors['PageNum'])): ?>
            <div class="error-messages"><?php echo $errors['PageNum']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='DateReceived' class="required">Date Received:</label>
        <input type='date' name='DateReceived' id='DateReceived' value='<?php echo htmlspecialchars($userData['DateReceived'] ?? ''); ?>'readonly>
        <?php if (isset($errors['DateReceived'])): ?>
            <div class="error-messages"><?php echo $errors['DateReceived']; ?></div>
        <?php endif; ?>
    </div>

    <div class='form-group'>
        <label for='Price' class="required">Price:</label>
        <input type='text' name='Price' id='Price' value='<?php echo htmlspecialchars($userData['Price'] ?? ''); ?>' class="<?php echo isset($errors['Price']) ? 'input-error' : ''; ?>">
        <?php if (isset($errors['Price'])): ?>
            <div class="error-messages"><?php echo $errors['Price']; ?></div>
        <?php endif; ?>
    </div>

    <!-- Existing Box Associations -->
    <?php foreach ($existingBoxes as $index => $box): ?>
        <div class="form-group">
            <label for="existingBoxSerialNums[<?php echo $index; ?>]">Box Serial Number (Used):</label>
            <input type="hidden" name="originalBoxSerialNums[]" value="<?php echo htmlspecialchars($box['BoxSerialNum']); ?>">
            <input type="text" name="existingBoxSerialNums[]" value="<?php echo htmlspecialchars($box['BoxSerialNum']); ?>">

            <label for="existingCopyCounts[<?php echo $index; ?>]" class="count-label">Copy Count:</label>
            <input type="number" name="existingCopyCounts[]" value="<?php echo htmlspecialchars($box['CopyCount']); ?>" min="1">
        </div>
    <?php endforeach; ?>

    <!-- New Box Associations -->
    <?php for ($i = 1; $i <= $numberOfAdditionalBoxes; $i++): ?>
        <div class="form-group">
            <label for="newBoxSerialNum<?php echo $i; ?>">New Box Serial Number:</label>
            <input type="text" name="newBoxSerialNums[]">

            <label for="newCopyCount<?php echo $i; ?>" class="count-label">New Copy Count:</label>
            <input type="number" name="newCopyCounts[]" min="1">
        </div>
    <?php endfor; ?>

    <!-- Book Image Upload -->
    <div class="form-group">
        <label for="pic">Book Picture:</label>
        <input type="file" name="pic" id="pic">
        <?php if (!empty($bookData['picture'])): ?>
            <div class="current-picture">
                <img src="<?php echo htmlspecialchars($bookData['picture']); ?>" alt="Book Image" style="max-width: 100px; max-height: 100px;">
            </div>
        <?php endif; ?>
        <?php if (!empty($errors['pic'])): ?>
            <div class="error-messages"><?php echo $errors['pic']; ?></div>
        <?php endif; ?>
    </div>

    <div class='addBtn'>
        <input type='submit' name='updateBtn' value='Update'>
        <a href='managebook.php'><button type='button'>Cancel</button></a>
    </div>
</form>
</body>
</html>
