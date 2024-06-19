<?php 
include 'config.php';

$errors = []; 
$insert_successful = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $BoxSerialNum = $_POST['BoxSerialNum'] ?? '';
    $category= $_POST['category'] ?? '';
    $DateCreate = $_POST['DateCreate'] ?? '';
    $BookQuantity = $_POST['BookQuantity'] ?? '';
    $color = $_POST['color'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!preg_match('/^\d{13}$/', $BoxSerialNum)) {
        $errors['BoxSerialNum'] = "Invalid BoxSerialNum format. Please enter a 13-digit number.";
    }

    if (!ctype_digit($BookQuantity)) {
        $errors['BookQuantity'] = "BookQuantity must be an integer.";
    }

    // Validate color based on category
    if (($category == 'BookPanda' && $color != 'Pink') || ($category == 'GrabBook' && $color != 'Green')) {
        $errors['color'] = "For $category, only " . ($category == 'BookPanda' ? 'Pink' : 'Green') . " color is allowed.";
    }

    $uploadDir = 'uploads/'; // Directory to save the uploaded files
    $picturePath = ''; // Initialize the variable for picture path

    if (isset($_FILES['pic'])) {
        $pic = $_FILES['pic'];
        if ($pic['error'] === UPLOAD_ERR_OK) {
            $fileType = $pic['type'];
            $fileSize = $pic['size'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5 MB

            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
                $fileName = time() . '_' . basename($pic['name']);
                $picturePath = $uploadDir . $fileName;
                if (move_uploaded_file($pic['tmp_name'], $picturePath)) {
                    $picturePath = mysqli_real_escape_string($conn, $picturePath);
                } else {
                    $errors['pic'] = 'There was an error uploading the file.';
                }
            } else {
                $errors['pic'] = 'Invalid file type or size exceeds the limit.';
            }
        }
    }

    if (empty($errors)) {
        $BoxSerialNum = mysqli_real_escape_string($conn, $BoxSerialNum);

        // Check if the ISBN or book_acquisition already exists in the database
        $existing_data_query = "SELECT BoxSerialNum FROM boxs WHERE BoxSerialNum = '$BoxSerialNum'";
        $result = mysqli_query($conn, $existing_data_query);

        if (mysqli_num_rows($result) > 0) {
            $errors['BoxSerialNum'] = "This BoxSerialNum already exists in the database.";
        } else {
            $category = mysqli_real_escape_string($conn, $category);
            $DateCreate = mysqli_real_escape_string($conn, $DateCreate);
            $BookQuantity = mysqli_real_escape_string($conn, $BookQuantity);
            $color = mysqli_real_escape_string($conn, $color);
            $status = mysqli_real_escape_string($conn, $status);

            $insert_query = "INSERT INTO boxs (BoxSerialNum, category, DateCreate, BookQuantity, color, status, Boxpicture)
                             VALUES ('$BoxSerialNum', '$category', '$DateCreate', '$BookQuantity', '$color', '$status', '$picturePath')";

            if (mysqli_query($conn, $insert_query)) {
                $insert_successful = true;
            } else {
                $errors['database'] = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">

    <script>
    function validateForm() {
        let isValid = true;

        // Validate Category
        let categoryField = document.getElementById('category');
        let categoryValue = categoryField.value;
        let categoryError = document.querySelector('.error-message[data-error="category"]');

        if (categoryValue === '') {
            categoryError.textContent = "Please select a category.";
            isValid = false;
        } else {
            categoryError.textContent = ''; 
        }

        // Validate Color
        let colorField = document.getElementById('color');
        let colorValue = colorField.value;
        let colorError = document.querySelector('.error-message[data-error="color"]');

        if (colorValue === '') {
            colorError.textContent = "Please select a color.";
            isValid = false;
        } else {
            colorError.textContent = ''; 
        }

        // Validate Status
        let statusField = document.getElementById('status');
        let statusValue = statusField.value;
        let statusError = document.querySelector('.error-message[data-error="status"]');

        if (statusValue === '') {
            statusError.textContent = "Please select a status.";
            isValid = false;
        } else {
            statusError.textContent = ''; 
        }

        // Validate BoxSerialNum
        let BoxSerialNumField = document.getElementById('BoxSerialNum');
        let BoxSerialNumValue = BoxSerialNumField.value.trim();
        let BoxSerialNumError = document.querySelector('.error-message[data-error="BoxSerialNum"]');

        if (!/^\d{13}$/.test(BoxSerialNumValue)) {
            BoxSerialNumError.textContent = "Invalid BoxSerialNum format. Please enter a 13-digit number.";
            isValid = false;
        } else {
            BoxSerialNumError.textContent = ''; 
        }

        // Validate BookQuantity
        let BookQuantityField = document.getElementById('BookQuantity');
        let BookQuantityValue = BookQuantity.value.trim();
        let BookQuantityError = document.querySelector('.error-message[data-error="BookQuantity"]');

        if (!/^\d+$/.test(BookQuantityValue) || parseInt(BookQuantityValue) <= 0) {
            BookQuantityError.textContent = "Book Quantity must be a positive integer.";
            isValid = false;
        } else {
            BookQuantityError.textContent = ''; 
        }

        // Validate color based on category
        if (categoryValue === 'BookPanda' && colorValue !== 'Pink') {
            colorError.textContent = "For BookPanda, only Pink color is allowed.";
            isValid = false;
        } else if (categoryValue === 'GrabBook' && colorValue !== 'Green') {
            colorError.textContent = "For GrabBook, only Green color is allowed.";
            isValid = false;
        } else {
            colorError.textContent = ''; 
        }

        return isValid;
    }

    function togglePopup() {
        document.getElementById("popup-1").classList.toggle("active");
    }

    function closePopup() {
        document.getElementById("popup-1").classList.remove("active");
    }
    </script>
</head>
<body>
<?php include 'navigation.php'; ?>
<h1>Box Form</h1>

<form action="" method="post" id="boxform" onsubmit="return validateForm();" enctype="multipart/form-data">
    <div class="form-group">
        <label for="BoxSerialNum"  class="required">Box Serial Number:</label>
        <input type="text" name="BoxSerialNum" id="BoxSerialNum" required>
        <span class="error-message" data-error="BoxSerialNum">
        <?php echo isset($errors['BoxSerialNum']) ? $errors['BoxSerialNum'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="category"  class="required">Category:</label>
        <select name="category" id="category">
            <option value="BookPanda">BookPanda</option>
            <option value="GrabBook">GrabBook</option>
        </select>
        <span class="error-message" data-error="category"></span>
    </div>

    <div class="form-group" >
        <label for="DateCreate"  class="required">Date Created:</label>
        <input type="date" name="DateCreate" id="DateCreate" required>
    </div>

    <div class="form-group">
        <label for="BookQuantity"  class="required">Book Quantity:</label>
        <input type="text" name="BookQuantity" id="BookQuantity" required>
        <span class="error-message" data-error="BookQuantity">
        <?php echo isset($errors['BookQuantity']) ? $errors['BookQuantity'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="color"  class="required">Color:</label>
        <select name="color" id="color">
            <option value="Pink">Pink</option>
            <option value="Green">Green</option>
        </select>
        <span class="error-message" data-error="color">
        <?php echo isset($errors['color']) ? $errors['color'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="status"  class="required">Status:</label>
        <select name="status" id="status">
            <option value="Open(For Issue)">Open(For Issue)</option>
            <option value="Close(For Issue)">Close(For Issue)</option>
        </select>
        <span class="error-message" data-error="status"></span>
    </div>

    <div class="form-group">
        <label for="pic"  class="required">Box Picture:</label>
        <input type="file" name="pic" id="pic">
        <?php if (!empty($errors['pic'])): ?>
            <div class="error-message"><?php echo $errors['pic']; ?></div>
        <?php endif; ?>
    </div>

    <div class="addBtn">
        <input type="submit" name="saveBtn" id="saveBtn">
        <input type="reset" name="ClearBtn" id="ClearBtn" onclick="clearForm()">
        <button type="button" onclick="goBack()">Previous</button>
    </div>
</form>

<?php if ($insert_successful): ?>
    <div class="popup" id="popup-1">
        <div class="overlay"></div>
        <div class="content">
            <div class="alert-icon">
                <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
            </div>
            <h1>Saved Successfully</h1>
            <p>Your data was saved!</p>
            <div class="close-btn" onclick="closePopup()">&times;</div>
        </div>
    </div>
    <script>togglePopup();</script>
<?php endif; ?>

<script>
    function clearForm() {
        document.getElementById('boxform').reset();
    }

    function goBack() {
        window.location.href = 'managebox.php';
    }
</script>
</body>
</html>
