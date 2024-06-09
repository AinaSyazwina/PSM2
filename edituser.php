<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
</head>
<body>
<?php include 'navigation.php'; ?>
<?php
include 'config.php';
$memberID = isset($_GET['memberID']) ? $_GET['memberID'] : '';

if (!$memberID) {
    echo "Invalid Member ID";
    exit;
}

$query = "SELECT * FROM register WHERE memberID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $memberID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

if (!$userData) {
    echo "No user found with that ID.";
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<form action="updatedituser.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="memberID" value="<?php echo htmlspecialchars($memberID); ?>">

    <div class="form-group">
        <label for="fullname">Name:</label>
        <input type="text" name="fullname" id="fullname"
               value='<?php echo htmlspecialchars($userData['fullname'] ?? ''); ?>'required>
    </div>

    <div class="form-group">
        <label for="IC">Identification Number:</label>
        <input type="text" name="IC" id="IC"
               value='<?php echo htmlspecialchars($userData['IC'] ?? ''); ?>'readonly>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="text" name="email" id="email"
       value='<?php echo htmlspecialchars($userData['email'] ?? ''); ?>' required>

    </div>

    <div class="form-group">
    <label for="birthdate">Birth Date:</label>
    <input type="date" name="birthdate" id="birthdate"
           value='<?php echo htmlspecialchars($userData['birthdate'] ?? ''); ?>' required>
</div>

    <div class="form-group">
        <label for="class">Class:</label>
        <input type="text" name="class" id="class"
               value='<?php echo htmlspecialchars($userData['class'] ?? ''); ?>'>
    </div>

    <div class="formradio">
       <p style="margin-bottom: 10px;">Please select the role</p>
 
         <input type="radio" id="admin" name="role" value="admin" <?php echo ($userData['role'] == 'admin') ? 'checked' : ''; ?> required>
         <label for="admin">Administrator</label><br>
          <input type="radio" id="LibPre" name="role" value="LibPre" <?php echo ($userData['role'] == 'LibPre') ? 'checked' : ''; ?> required>
         <label for="LibPre">Library Prefect</label><br>
         <input type="radio" id="student" name="role" value="student" <?php echo ($userData['role'] == 'student') ? 'checked' : ''; ?> required>
         <label for="student">Student</label>
    </div>

    <div class="formradio">
        <p>Status:</p>
        <input type="radio" id="active" name="status" value="active" <?php echo ($userData['status'] == 'active') ? 'checked' : ''; ?>>
        <label for="active">Active</label><br>
        <input type="radio" id="inactive" name="status" value="inactive" <?php echo ($userData['status'] == 'inactive') ? 'checked' : ''; ?>>
        <label for="inactive">Inactive</label>
    </div>

    <div class="form-group">
    <label for="picture">User Picture:</label>
    <input type="file" name="picture" id="picture">
    <?php if (!empty($userData['picture'])): ?>
        <img src="<?php echo htmlspecialchars($userData['picture']); ?>" alt="Current User Image" style="max-width: 100px; max-height: 100px;">
        <!-- Hidden field to store the current image path -->
        <input type="hidden" name="currentPicture" value="<?php echo htmlspecialchars($userData['picture']); ?>">
    <?php endif; ?>
</div>


<br>  
<hr>
<br>
    <input type="checkbox" id="clarify" name="clarify" value="clarify" <?php echo ($userData['clarify'] == 'clarify') ? 'checked' : ''; ?> required>
         <label for="clarify"> I hereby confirm the details in registration form</label><br>


    <div class="addBtn">
        <input type="submit" name="updateBtn" value="Update">
        <a href="manageuser.php"><button type="button">Cancel</button></a>
    </div>
</form>

</body>
</html>
