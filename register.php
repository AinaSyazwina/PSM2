<?php include 'navigation.php'; ?>
<?php
include 'config.php';

$errors = [];
$insert_successful = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST['fullname'] ?? '';
    $IC = $_POST['IC'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $memberID = $_POST['memberID'] ?? '';
    $class = $_POST['class'] ?? '';
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';
    $clarify = $_POST['clarify'] ?? '';
    $hashed_password = $_POST['hashed_password'] ?? '';

    // Validation for IC
    if (strlen($IC) !== 12) {
        $errors['IC'] = "IC should be 12 digits.";
    } else {
        
        $IC_check_query = "SELECT * FROM register WHERE IC = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $IC_check_query);
        mysqli_stmt_bind_param($stmt, "s", $IC);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $errors['IC'] = "IC already registered.";
        }
    }

    // Validation for Member ID
    if (strlen($memberID) !== 8) {
        $errors['memberID'] = "Member ID should be 8 characters.";
    } else {
        
        $memberID_check_query = "SELECT * FROM register WHERE memberID = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $memberID_check_query);
        mysqli_stmt_bind_param($stmt, "s", $memberID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $errors['memberID'] = "Member ID already registered.";
        }
    }

    // Check for unique username
    if (empty($errors['username'])) {
        $username = $_POST['username'];
        $user_check_query = "SELECT * FROM register WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $user_check_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $errors['username'] = "Username already exists.";
        }
    }

    // Check if passwords match
    $pwd = $_POST['pwd'];
    $confirm_pwd = $_POST['confirm_pwd'];
    if ($pwd !== $confirm_pwd) {
        $errors['password'] = "Passwords do not match.";
    }

    // Email Validation
  // Email Validation
if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$|^([a-zA-Z0-9._%+-]+@localhost)$/", $email)) {
    $errors['email'] = "Invalid email format.";
} else {
    // Check if email already exists
    $email_check_query = "SELECT * FROM register WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $email_check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $errors['email'] = "Email already registered.";
    }
}


    // Validate the status field
    if (empty($status)) {
        $errors['status'] = "Status must be selected.";
    }

     // Picture upload handling
  // Picture upload handling
$picturePath = ''; // Initialize the variable for picture path
if (isset($_FILES['picture'])) {
    $pic = $_FILES['picture'];
    if ($pic['error'] === UPLOAD_ERR_OK) {
        $fileType = $pic['type'];
        $fileSize = $pic['size'];
        $allowedTypes = [
            'image/jpeg', 
            'image/png', 
            'image/gif',
            'image/bmp',
            'image/tiff',
            'image/webp'
        ];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
            $fileName = time() . '_' . basename($pic['name']);
            $uploadDir = 'uploads/';
            $picturePath = $uploadDir . $fileName;
            if (move_uploaded_file($pic['tmp_name'], $picturePath)) {
                $picturePath = mysqli_real_escape_string($conn, $picturePath);
            } else {
                $errors['picture'] = 'There was an error uploading the file.';
            }
        } else {
            $errors['picture'] = 'Invalid file type or size exceeds the limit.';
        }
    } else if ($pic['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['picture'] = 'No file uploaded.';
    } else {
        $errors['picture'] = 'File upload error.';
    }
}

    // If no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $IC = mysqli_real_escape_string($conn, $IC);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $birthdate = mysqli_real_escape_string($conn, $_POST['birthdate']);
        $memberID = mysqli_real_escape_string($conn, $memberID);
        $class = mysqli_real_escape_string($conn, $class);
        $username = mysqli_real_escape_string($conn, $username);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $clarify = isset($_POST['clarify']) ? mysqli_real_escape_string($conn, $_POST['clarify']) : '';

        $sql = "INSERT INTO register (fullname, IC, email, birthdate, memberID, class, username, pwd, role, clarify, status, picture)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssssss", $fullname, $IC, $email, $birthdate, $memberID, $class, $username, $hashed_password, $role, $clarify, $status, $picturePath);


        if (mysqli_stmt_execute($stmt)) {
            $insert_successful = true;
        } else {
            $errors['database'] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
   
    <script>
        function validateForm() {
            let isValid = true;

            // Validate IC
            let icField = document.getElementById('IC');
            let icValue = icField.value.trim();
            let icError = document.querySelector('.error-message[data-error="IC"]');

            if (icValue.length !== 12) {
                icError.textContent = "IC should be 12 digits.";
                isValid = false;
            } else {
                icError.textContent = '';
            }

            // Validate Member ID
            let memberIDField = document.getElementById('memberID');
            let memberIDValue = memberIDField.value.trim();
            let memberIDError = document.querySelector('.error-message[data-error="memberID"]');

            if (memberIDValue.length !== 8) {
                memberIDError.textContent = "Member ID should be 8 characters.";
                isValid = false;
            } else {
                memberIDError.textContent = '';
            }

            // Validate Password Match
            let passwordField = document.getElementById('pwd');
            let confirmPasswordField = document.getElementById('confirm_pwd');
            let passwordError = document.querySelector('.error-message[data-error="password"]');
            let passwordValue = passwordField.value;
            let confirmPasswordValue = confirmPasswordField.value;

            if (passwordValue !== confirmPasswordValue) {
                passwordError.textContent = "Passwords do not match.";
                isValid = false;
            } else {
                passwordError.textContent = '';
            }

            return isValid;
        }
        </script>
</head>
<body>


<h1>Registration Form</h1>
<form action="register.php" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">

    <div class="form-group">
        <label for="fullname" class="required">Name:</label>
        <input type="text" name="fullname" id="fullname" required>
    </div>

    <div class="form-group">
        <label for="IC" class="required">Identification Number:</label>
        <input type="text" name="IC" id="IC" required>
        <span class="error-message" data-error="IC">
        <?php echo isset($errors['IC']) ? $errors['IC'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="email" class="required">Email:</label>
        <input type="email" name="email" id="email" required>
        <span class="error-message" data-error="email">
    <?php echo isset($errors['email']) ? $errors['email'] : ''; ?>
    </span>
    </div>

    <div class="form-group">
        <label for="birthdate" class="required">Birth Date:</label>
        <input type="date" name="birthdate" id="birthdate" required>
    </div>

    <div class="form-group">
        <label for="memberID" class="required">Member ID:</label>
        <input type="text" name="memberID" id="memberID" required>
        <span class="error-message" data-error="memberID">
        <?php echo isset($errors['memberID']) ? $errors['memberID'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="class" >Class:</label>
        <input type="text" name="class" id="class" >
    </div>


    <div class="form-group">
        <label for="username" class="required">Username:</label>
        <input type="text" name="username" id="username" required>
        <span class="error-message" data-error="username">
        <?php echo isset($errors['username']) ? $errors['username'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="pwd" class="required">Password:</label>
        <input type="password" name="pwd" id="pwd" required>
        <span class="error-message" data-error="password">
        <?php echo isset($errors['password']) ? $errors['password'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
        <label for="confirm_pwd" class="required">Confirm Password:</label>
        <input type="password" name="confirm_pwd" id="confirm_pwd" required>
        <span class="error-message" data-error="confirm_pwd">
        <?php echo isset($errors['confirm_pwd']) ? $errors['confirm_pwd'] : ''; ?>
        </span>
    </div>

    <div class="formradio">
    <p style="margin-bottom: 10px;" class="required">Please select the role</p>
 
     <input type="radio" id="admin" name="role" value="admin" required>
     <label for="admin">Administrator</label><br>
     <input type="radio" id="LibPre" name="role" value="LibPre" required>
    <label for="LibPre">Library Prefect</label><br>
    <input type="radio" id="student" name="role" value="student" required>
    <label for="student">Student</label>

     </div>

     <div class="formradio">
        <p class="required">Please select the status</p>
        <input type="radio" id="active" name="status" value="active" required>
        <label for="active">Active</label><br>
        <input type="radio" id="inactive" name="status" value="inactive" required>
        <label for="inactive">Inactive</label>
        <span class="error-message" data-error="status">
        <?php echo isset($errors['status']) ? $errors['status'] : ''; ?>
        </span>
    </div>

    <div class="form-group">
    <label for="picture" class="required">Profile Picture:</label>
    <input type="file" name="picture" id="picture" required>
    <?php if (!empty($errors['picture'])): ?>
        <div class="error-message"><?php echo $errors['picture']; ?></div>
    <?php endif; ?>
</div>


  <br>  

    <hr>
    <br>
    <input type="checkbox" id="clarify" name="clarify" value="clarify" require>
    <label for="clarify" class="required"> I hereby confirm the details in registration form</label><br>

    <div class="button">
        <input type="submit" class="btn" name="submit" value="Register">
    </div>
</form>

<?php if ($insert_successful): ?>
    <div class="popup" id="popup-1">
        <div class="overlay"></div>
        <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
            <h1>Registration Successful</h1>
            <p>Your registration was successful!</p>
            <div class="close-btn" onclick="closePopup()">&times;</div>
        </div>
    </div>
    <script>
        document.getElementById("popup-1").classList.add("active");
    </script>
<?php endif; ?>

<script>
    function closePopup() {
        document.getElementById("popup-1").classList.remove("active");
    }
</script>

</body>
</html>
