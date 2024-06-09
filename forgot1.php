<?php
session_start();
include 'config.php';

$token = $_GET['token'] ?? '';
$tokenError = '';
$passwordError = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $passwordError = "Passwords do not match.";
    } else {
      
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (isset($_SESSION['user_email'])) {
            $email = $_SESSION['user_email'];

            $updateSql = "UPDATE register SET pwd = ? WHERE email = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param('ss', $hashedPassword, $email);
            if ($stmt->execute()) {
                $successMessage = "Your password has been updated successfully.";
            } else {
                $passwordError = "Failed to update the password: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $passwordError = "Session error or invalid access.";
        }
    }
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $tokenHash = hash('sha256', $token);

    $query = "SELECT email FROM register WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();
        $_SESSION['user_email'] = $data['email']; 
    } else {
        $tokenError = 'Invalid or expired token.';
    }
    $stmt->close();
}

if (!empty($tokenError)) {
    echo "<p class='error'>$tokenError</p>";
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="Cssfile/styleforgot.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="image-container">
                <img src="pic/lock1.png" alt="Lock Image" />
            </div>
            <h2>Reset Password</h2>
            <?php if (!empty($successMessage)): ?>
    <p class="success" style="text-align: center;"><?php echo $successMessage; ?></p>
    <input type="button" class="action-button" value="Back to Homepage" onclick="window.location.href='index.php';">
            <?php else: ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?token=<?php echo htmlspecialchars($token); ?>" method="post">
                <div class="input-group">
                    <label for="password">New Password:</label>
                    <input type="password" name="password" id="password" required>
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <?php if (!empty($passwordError)): ?>
                    <div class="error"><?php echo $passwordError; ?></div>
                <?php endif; ?>
                <div class="form-actions">
                    <input type="submit" name="submit" value="Submit">
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
