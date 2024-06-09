<?php include 'navigation.php'; ?>
<?php 
include 'config.php';

$errors = []; 
$insert_successful = false;
$boxCapacities = [];
$uploadDir = 'uploads/'; 
$picturePath = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ISBN = $_POST['ISBN'] ?? '';
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
    $totalDistributedCopies = 0;

    // Validate fields
    if (!preg_match('/^\d{13}$/', $ISBN)) {
        $errors['ISBN'] = "Invalid ISBN format. Please enter a 13-digit number.";
    }

    if (!ctype_digit($Copy)) {
        $errors['Copy'] = "Copy must be an integer.";
    }

    if (!ctype_digit($PageNum) || intval($PageNum) <= 0) {
        $errors['PageNum'] = "Page Number must be a positive integer.";
    }

    if (!is_numeric($Price) || floatval($Price) <= 0) {
        $errors['Price'] = "Price must be a positive number.";
    }

    if (empty($genre)) {
        $errors['genre'] = "Genre is required.";
    }
    if (empty($PublishDate)) {
        $errors['PublishDate'] = "Publish Date is required.";
    }
    
    if (empty($DateReceived)) {
        $errors['DateReceived'] = "Date Received is required.";
    }

    if (!preg_match('/^\d{5}$/', $book_acquisition)) {
        $errors['book_acquisition'] = "Book Acquisition must be a 5-digit number.";
    }

    if (isset($_FILES['pic']) && $_FILES['pic']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'image/avif'];

        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        $pic = $_FILES['pic'];
        $fileType = $pic['type'];
        $fileSize = $pic['size'];

        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

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

    $totalDistributedCopies = 0;
    $boxCapacities = [];

    for ($i = 1; $i <= 3; $i++) {
        $BoxSerialNum = $_POST["BoxSerialNum$i"] ?? '';
        $CopyCount = $_POST["CopyCount$i"] ?? 0;

        if (!empty($BoxSerialNum) && $CopyCount > 0) {
            $box_check_query = "SELECT BoxSerialNum, BookQuantity FROM boxs WHERE BoxSerialNum = '$BoxSerialNum'";
            $box_check_result = mysqli_query($conn, $box_check_query);
            if ($box_row = mysqli_fetch_assoc($box_check_result)) {
                $boxCapacities[$BoxSerialNum] = $box_row['BookQuantity'];
                $totalDistributedCopies += $CopyCount;
            } else {
                $errors["BoxSerialNum$i"] = "Box Serial Number $i does not exist in the database.";
            }
        }
    }

    foreach ($boxCapacities as $serial => $BookQuantity) {
        $totalCopiesForBox = 0;
        for ($i = 1; $i <= 3; $i++) {
            if ($_POST["BoxSerialNum$i"] == $serial) {
                $totalCopiesForBox += $_POST["CopyCount$i"];
            }
        }
        if ($totalCopiesForBox > $BookQuantity) {
            $errors["BoxCapacity$serial"] = "The total number of copies exceeds the capacity for Box Serial Number $serial.";
        }
    }

    if ($totalDistributedCopies > $Copy) {
        $errors['BookCopy'] = "Total distributed copies exceed the book's total copies.";
    }

    // Check for existing ISBN, Book Acquisition number, or Title
    $ISBN = mysqli_real_escape_string($conn, $ISBN);
    $book_acquisition = mysqli_real_escape_string($conn, $book_acquisition);
    $Title = mysqli_real_escape_string($conn, $Title);

    $existing_data_query = "SELECT ISBN, book_acquisition, Title FROM books WHERE ISBN = '$ISBN' OR book_acquisition = '$book_acquisition' OR Title = '$Title'";
    $result = mysqli_query($conn, $existing_data_query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['ISBN'] === $ISBN) {
                $errors['ISBN'] = "This ISBN already exists in the database.";
            }
            if ($row['book_acquisition'] === $book_acquisition) {
                $errors['book_acquisition'] = "This Book Acquisition number already exists in the database.";
            }
            if ($row['Title'] === $Title) {
                $errors['Title'] = "This Title already exists in the database.";
            }
        }
    }

    // Only insert if no errors
    if (empty($errors)) {
        $author1 = mysqli_real_escape_string($conn, $author1);
        $author2 = mysqli_real_escape_string($conn, $author2);
        $PublishDate = mysqli_real_escape_string($conn, $PublishDate);
        $PublicPlace = mysqli_real_escape_string($conn, $PublicPlace);
        $Copy = mysqli_real_escape_string($conn, $Copy);
        $genre = mysqli_real_escape_string($conn, $genre);
        $PageNum = mysqli_real_escape_string($conn, $PageNum);
        $DateReceived = mysqli_real_escape_string($conn, $DateReceived);
        $Price = mysqli_real_escape_string($conn, $Price);

        try {
            $insert_query = "INSERT INTO books (ISBN, author1, author2, Title, PublishDate, PublicPlace, Copy, genre, PageNum, DateReceived, Price, book_acquisition, picture)
                             VALUES ('$ISBN', '$author1', '$author2', '$Title', '$PublishDate', '$PublicPlace', '$Copy', '$genre', '$PageNum', '$DateReceived', '$Price', '$book_acquisition', '$picturePath')";
            
            if (!mysqli_query($conn, $insert_query)) {
                throw new Exception(mysqli_error($conn), mysqli_errno($conn));
            }

            $insert_successful = true;

            $totalDistributedCopies = 0;

            for ($i = 1; $i <= 3; $i++) {
                $BoxSerialNum = $_POST["BoxSerialNum$i"] ?? '';
                $CopyCount = $_POST["CopyCount$i"] ?? 0;

                if (!empty($BoxSerialNum) && $CopyCount > 0) {
                    $box_check_query = "SELECT BoxSerialNum FROM boxs WHERE BoxSerialNum = '$BoxSerialNum'";
                    $box_check_result = mysqli_query($conn, $box_check_query);
                    if (mysqli_num_rows($box_check_result) == 0) {
                        $errors["BoxSerialNum$i"] = "Box Serial Number $i does not exist in the database.";
                        continue; 
                    }

                    $insert_distribution_query = "INSERT INTO book_distribution (ISBN, BoxSerialNum, CopyCount) VALUES ('$ISBN', '$BoxSerialNum', $CopyCount)";
                    if (!mysqli_query($conn, $insert_distribution_query)) {
                        $errors['distribution'] = "Error distributing copies: " . mysqli_error($conn);
                        break; 
                    } else {
                        $totalDistributedCopies += $CopyCount;
                    }
                }
            }

            if ($totalDistributedCopies > $Copy) {
                $errors['distribution'] = "Total distributed copies exceed the available copies.";
            }
        } catch (Exception $e) {
            if ($e->getCode() == 1062) {
                if (strpos($e->getMessage(), 'book_acquisition') !== false) {
                    $errors['book_acquisition'] = "This Book Acquisition number already exists in the database.";
                } elseif (strpos($e->getMessage(), 'ISBN') !== false) {
                    $errors['ISBN'] = "This ISBN already exists in the database.";
                } elseif (strpos($e->getMessage(), 'Title') !== false) {
                    $errors['Title'] = "This Title already exists in the database.";
                }
            } else {
                $errors['database'] = "Error: " . $e->getMessage();
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
     
    <style>
        .error-message1 {
    color: red;
    font-size: 14px;
    margin-top: 5px;
    display: block;
}
    </style>
    <script>
    function validateForm() {
        let isValid = true;

        // Validate ISBN
        let ISBNField = document.getElementById('ISBN');
        let ISBNValue = ISBNField.value.trim();
        let ISBNError = document.querySelector('.error-message[data-error="ISBN"]');

        if (!/^\d{13}$/.test(ISBNValue)) {
            ISBNError.textContent = "Invalid ISBN format. Please enter a 13-digit number.";
            isValid = false;
        } else {
            ISBNError.textContent = ''; 
        }

        // Validate Copy
        let copyField = document.getElementById('Copy');
        let copyValue = copyField.value.trim();
        let copyError = document.querySelector('.error-message[data-error="Copy"]');

        if (!Number.isInteger(Number(copyValue))) {
            copyError.textContent = "Copy must be an integer.";
            isValid = false;
        } else {
            copyError.textContent = ''; 
        }

        // Validate PageNum
        let pageNumField = document.getElementById('PageNum');
        let pageNumValue = pageNumField.value.trim();
        let pageNumError = document.querySelector('.error-message[data-error="PageNum"]');

        if (!/^\d+$/.test(pageNumValue) || parseInt(pageNumValue) <= 0) {
            pageNumError.textContent = "Page Number must be a positive integer.";
            isValid = false;
        } else {
            pageNumError.textContent = ''; 
        }

        // Validate Price
        let priceField = document.getElementById('Price');
        let priceValue = priceField.value.trim();
        let priceError = document.querySelector('.error-message[data-error="Price"]');

        if (isNaN(parseFloat(priceValue)) || parseFloat(priceValue) <= 0) {
            priceError.textContent = "Price must be a positive number.";
            isValid = false;
        } else {
            priceError.textContent = ''; 
        }

        // Validate Book Acquisition
        let bookAcqField = document.getElementById('book_acquisition');
        let bookAcqValue = bookAcqField.value.trim();
        let bookAcqError = document.querySelector('.error-message[data-error="book_acquisition"]');

        if (!/^\d{5}$/.test(bookAcqValue)) {
            bookAcqError.textContent = "Book Acquisition must be a 5-digit number.";
            isValid = false;
        } else {
            bookAcqError.textContent = ''; 
        }

        // Validate Genre
        let genreField = document.getElementById('genre');
        let genreValue = genreField.value.trim();
        let genreError = document.querySelector('.error-message[data-error="genre"]');

        if (genreValue === '') {
            genreError.textContent = "Genre is required.";
            isValid = false;
        } else {
            genreError.textContent = ''; 
        }

        return isValid;
    }

    function togglePopup() {
        document.getElementById("popup-1").classList.toggle("active");
    }

    function closePopup() {
        document.getElementById("popup-1").classList.remove("active");
    }

    function goBack() {
        window.location.href = 'managebook.php';
    }

    let inputTimer; 
    const debounceDuration = 500; 

    function handleISBNInput() {
        clearTimeout(inputTimer); 
        const isbnInput = document.getElementById('ISBN');
        const isbn = isbnInput.value.trim();

        if (isbn.length === 13) { 
            clearTimeout(inputTimer);
            fetchBookDetails(isbn); 
        } else {
            inputTimer = setTimeout(() => {
                if (isbn.length === 13) {
                    fetchBookDetails(isbn);
                }
            }, debounceDuration);
        }
    }

    function fetchBookDetails(isbn) {
        console.log("Fetching details for ISBN:", isbn);
        fetch(`https://www.googleapis.com/books/v1/volumes?q=isbn:${isbn}`)
            .then(response => response.json())
            .then(data => {
                if (data.totalItems > 0) {
                    displayBookDetails(data.items[0].volumeInfo);
                } else {
                    alert('No book found with that ISBN. Please enter details manually.');
                }
            })
            .catch(error => {
                console.error('Error fetching book details:', error);
            });
    }

    function displayBookDetails(book) {
        document.getElementById('Title').value = book.title || '';

        const authorInputs = [document.getElementById('author1'), document.getElementById('author2')];
        if (book.authors && book.authors.length) {
            book.authors.forEach((author, index) => {
                if (authorInputs[index]) {
                    authorInputs[index].value = author;
                }
            });
           
            for (let i = book.authors.length; i < authorInputs.length; i++) {
                authorInputs[i].value = '';
            }
        } else {
            authorInputs.forEach(input => input.value = '');
        }

        document.getElementById('PublishDate').value = book.publishedDate || '';
        document.getElementById('PublicPlace').value = book.publisher || '';
        document.getElementById('PageNum').value = book.pageCount || '';

        const genreSelect = document.getElementById('genre');
        if (book.categories && book.categories.length > 0) {
            const genreValue = book.categories[0]; 
            Array.from(genreSelect.options).forEach(option => {
                if (option.value === genreValue) {
                    option.selected = true;
                }
            });
        }

        if (book.imageLinks && book.imageLinks.thumbnail) {
            document.getElementById('bookImage').src = book.imageLinks.thumbnail;
        } else {
            document.getElementById('bookImage').src = 'placeholder-image.jpg'; 
        }

        document.getElementById('ISBN').value = book.industryIdentifiers && book.industryIdentifiers.find(id => id.type === 'ISBN_13')?.identifier || '';
    }
    </script>
</head>
<body>
<h1>Book Form</h1>
<form action="" method="post" id="bookform" onsubmit="return validateForm();" enctype="multipart/form-data">

<div class="form-group">
    <label for="book_acquisition" class="required">Book Acquisition:</label>
    <input type="text" name="book_acquisition" id="book_acquisition" required value="<?php echo isset($_POST['book_acquisition']) ? htmlspecialchars($_POST['book_acquisition']) : ''; ?>">
    <span class="error-message1" data-error="book_acquisition">
        <?php echo isset($errors['book_acquisition']) ? $errors['book_acquisition'] : ''; ?>
    </span>
</div>

<div class="form-group">
    <label for="ISBN" class="required">ISBN:</label>
    <input type="text" id="ISBN" name="ISBN" oninput="handleISBNInput()" placeholder="Enter or scan ISBN" required value="<?php echo isset($_POST['ISBN']) ? htmlspecialchars($_POST['ISBN']) : ''; ?>">
    <span class="error-message1" data-error="ISBN">
        <?php echo isset($errors['ISBN']) ? $errors['ISBN'] : ''; ?>
    </span>
</div>

<div class="form-group">
    <label for="author1" class="required">Author 1:</label>
    <input type="text" name="author1" id="author1" required value="<?php echo isset($_POST['author1']) ? htmlspecialchars($_POST['author1']) : ''; ?>">
</div>

<div class="form-group">
    <label for="author2">Author 2:</label>
    <input type="text" name="author2" id="author2" value="<?php echo isset($_POST['author2']) ? htmlspecialchars($_POST['author2']) : ''; ?>">
</div>

<div class="form-group">
    <label for="Title" class="required">Title:</label>
    <input type="text" name="Title" id="Title" required value="<?php echo isset($_POST['Title']) ? htmlspecialchars($_POST['Title']) : ''; ?>">
    <span class="error-message1" data-error="Title">
        <?php echo isset($errors['Title']) ? $errors['Title'] : ''; ?>
    </span>
</div>

<div class="form-group">
    <label for="PublishDate" class="required">Publish Date:</label>
    <input type="date" name="PublishDate" id="PublishDate" required value="<?php echo isset($_POST['PublishDate']) ? htmlspecialchars($_POST['PublishDate']) : ''; ?>">
    <span class="error-message" data-error="PublishDate">
        <?php echo isset($errors['PublishDate']) ? $errors['PublishDate'] : ''; ?>
    </span>
</div>

<div class="form-group">
    <label for="PublicPlace" class="required">Publication Place:</label>
    <input type="text" name="PublicPlace" id="PublicPlace" value="<?php echo isset($_POST['PublicPlace']) ? htmlspecialchars($_POST['PublicPlace']) : ''; ?>">
</div>

<div class="form-group">
    <label for="Copy" class="required">Copy:</label>
    <input type="text" name="Copy" id="Copy" required value="<?php echo isset($_POST['Copy']) ? htmlspecialchars($_POST['Copy']) : ''; ?>">
    <span class="error-message" data-error="Copy">
        <?php echo isset($errors['Copy']) ? $errors['Copy'] : ''; ?>
    </span>
</div>

<?php if (isset($errors['BookCopy'])): ?>
    <div class="error-message"><?php echo htmlspecialchars($errors['BookCopy']); ?></div>
<?php endif; ?>

<?php foreach ($boxCapacities as $serial => $BookQuantity): ?>
    <?php if (isset($errors["BoxCapacity$serial"])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors["BoxCapacity$serial"]); ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="form-group">
    <label for="genre" class="required">Genre:</label>
    <select name="genre" id="genre">
        <option value="" <?php echo (isset($_POST['genre']) && $_POST['genre'] == '') ? 'selected' : ''; ?>></option>
        <option value="Romance" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Romance') ? 'selected' : ''; ?>>Romance</option>
        <option value="Fiction" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Fiction') ? 'selected' : ''; ?>>Fiction</option>
        <option value="Non-Fiction" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Non-Fiction') ? 'selected' : ''; ?>>Non-Fiction</option>
        <option value="Mystery" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Mystery') ? 'selected' : ''; ?>>Mystery</option>
        <option value="FairyTale" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'FairyTale') ? 'selected' : ''; ?>>FairyTale</option>
        <option value="Action" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Action') ? 'selected' : ''; ?>>Action</option>
        <option value="Fantasy" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Fantasy') ? 'selected' : ''; ?>>Fantasy</option>
        <option value="Historical" <?php echo (isset($_POST['genre']) && $_POST['genre'] == 'Historical') ? 'selected' : ''; ?>>Historical</option>
    </select>
    <span class="error-message" data-error="genre"><?php echo $errors['genre'] ?? ''; ?></span>
</div>

<div class="form-group">
    <label for="PageNum" class="required">Page Number:</label>
    <input type="text" name="PageNum" id="PageNum" required value="<?php echo isset($_POST['PageNum']) ? htmlspecialchars($_POST['PageNum']) : ''; ?>">
    <span class="error-message" data-error="PageNum">
        <?php echo isset($errors['PageNum']) ? $errors['PageNum'] : ''; ?>
    </span>
</div>

<div class="form-group">
    <label for="DateReceived" class="required">Date Received:</label>
    <input type="date" name="DateReceived" id="DateReceived" required value="<?php echo isset($_POST['DateReceived']) ? htmlspecialchars($_POST['DateReceived']) : ''; ?>">
    <span class="error-message" data-error="DateReceived">
        <?php echo isset($errors['DateReceived']) ? $errors['DateReceived'] : ''; ?>
    </span>
</div>

<div class="form-group">
    <label for="Price" class="required">Price:</label>
    <input type="text" name="Price" id="Price" required value="<?php echo isset($_POST['Price']) ? htmlspecialchars($_POST['Price']) : ''; ?>">
    <span class="error-message" data-error="Price">
        <?php echo isset($errors['Price']) ? $errors['Price'] : ''; ?>
    </span>
</div>

<?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="form-group">
        <h3 class="box-header">Box <?php echo $i; ?></h3>
        <label for="BoxSerialNum<?php echo $i; ?>">Box Serial Number <?php echo $i; ?>:</label>
        <input type="text" name="BoxSerialNum<?php echo $i; ?>" id="BoxSerialNum<?php echo $i; ?>" value="<?php echo isset($_POST["BoxSerialNum$i"]) ? htmlspecialchars($_POST["BoxSerialNum$i"]) : ''; ?>">
        <?php if (isset($errors["BoxSerialNum$i"])): ?>
            <div class="error-message"><?php echo $errors["BoxSerialNum$i"]; ?></div>
        <?php endif; ?>

        <label for="CopyCount<?php echo $i; ?>" class="count-label">Book Count for Box <?php echo $i; ?>:</label>
        <input type="number" name="CopyCount<?php echo $i; ?>" id="CopyCount<?php echo $i; ?>" min="1" value="<?php echo isset($_POST["CopyCount$i"]) ? htmlspecialchars($_POST["CopyCount$i"]) : ''; ?>">
    </div>
<?php endfor; ?>

<div class="form-group">
    <label for="pic" class="required">Upload a picture:</label>
    <input type="file" name="pic" id="pic" required>
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
    document.getElementById('bookform').reset();
}

function goBack() {
    window.location.href = 'managebook.php';
}
</script>

</body>
</html>
