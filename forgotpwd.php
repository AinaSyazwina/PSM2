<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    $checkEmail = "SELECT email FROM register WHERE email = ?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); 

    if ($stmt->num_rows == 0) {
        echo "No account associated with this email.";
    } else {
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); 

        $sql = "UPDATE register SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $token_hash, $expiry, $email);

        if ($stmt->execute()) {
            $to = $email;
            $subject = "Reset Your Password";
            $message = "
            <html>
            <head>
            <title>Password Reset Request</title>
            </head>
            <body>
            <h1>Password Reset Request</h1>
            <p>We have received a request to reset the password associated with this email address. If you did not initiate this request, please disregard this email, and no further action is required. The password reset link will automatically expire within 30 minutes for your security.</p>
            <p>If you requested this password reset, please click the link below to set up a new password:</p>
            <a href='http://localhost/library/forgot1.php?token=$token'>Set Up New Password</a>
            <p>If you encounter any issues with resetting your password, please contact our support team.</p>
            <p>Thank you for your attention to this matter.</p>
            <p>Regards,</p>
            <p>BookPanda and GrabBook Management System</p>
            </body>
            </html>
            ";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: <aina@localhost>' . "\r\n";
            $headers .= 'Cc: aina@localhost' . "\r\n";

            if (mail($to, $subject, $message, $headers)) {
                echo 'Email has been sent with password reset instructions.';
            } else {
                echo 'Failed to send email.';
            }
        } else {
            echo "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
