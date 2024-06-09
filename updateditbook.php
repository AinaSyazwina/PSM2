 <?php /*
include 'config.php';
$errors = []; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming these inputs are part of your form
    $ISBN = $_POST['ISBN'];
    $author1 = $_POST['author1'];
    $author2 = $_POST['author2'];
    $Title = $_POST['Title'];
    $PublishDate = $_POST['PublishDate'];
    $PublicPlace = $_POST['PublicPlace'];
    $Copy = $_POST['Copy'];
    $genre = $_POST['genre'];
    $PageNum = $_POST['PageNum'];
    $DateReceived = $_POST['DateReceived'];
    $Price = $_POST['Price'];
    $book_acquisition = $_POST['book_acquisition'];
 

    if (isset($_POST['Price'])) {
        $Price = $_POST['Price'];
        if (!is_numeric($Price) || $Price <= 0) {
            $errors['Price'] = 'Price must be a positive number.';
        }
    }
    if (count($errors) === 0) {
    // Update book details
    $updateBookQuery = "UPDATE books SET 
                        author1 = '$author1', 
                        author2 = '$author2', 
                        Title = '$Title',
                        PublishDate = '$PublishDate',
                        PublicPlace = '$PublicPlace',
                        Copy = '$Copy',
                        genre = '$genre',
                        PageNum = '$PageNum',
                        DateReceived = '$DateReceived',
                        Price = '$Price',
                        book_acquisition = '$book_acquisition' 
                        WHERE ISBN = '$ISBN'";
    mysqli_query($conn, $updateBookQuery);

    
     // Update existing box associations
     if (isset($_POST['existingBoxSerialNums'])) {
        foreach ($_POST['existingBoxSerialNums'] as $index => $BoxSerialNum) {
            $CopyCount = $_POST['existingCopyCounts'][$index];
            $updateBoxQuery = "UPDATE book_distribution SET CopyCount = '$CopyCount' 
                               WHERE ISBN = '$ISBN' AND BoxSerialNum = '$BoxSerialNum'";
            mysqli_query($conn, $updateBoxQuery);
        }
    }

    // Insert new box associations
    if (isset($_POST['newBoxSerialNums'])) {
        foreach ($_POST['newBoxSerialNums'] as $index => $newBoxSerialNum) {
            if ($newBoxSerialNum != '') {
                $newCopyCount = $_POST['newCopyCounts'][$index];
                $insertBoxQuery = "INSERT INTO book_distribution (ISBN, BoxSerialNum, CopyCount) 
                                   VALUES ('$ISBN', '$newBoxSerialNum', '$newCopyCount')";
                mysqli_query($conn, $insertBoxQuery);
            }
        }
    }
}

    mysqli_close($conn);
    header("Location: managebook.php?success=true");
    exit();
}
*/?> 
