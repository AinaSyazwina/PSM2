<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="Cssfile/styleforgot.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="image-container">
                <img src="pic/lock1.png" alt="Lock Image" />
            </div>
            <h2>Forgot Password</h2>
            <p class="form-description">Enter your email and we'll send you a link to get back into your account.</p>
            <form id="forgotPasswordForm">
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="form-actions">
                    <input type="submit" value="Submit">
                    <input type="button" value="Cancel" onclick="goToIndexPage()">
                </div>
            </form>
        </div>
    </div>

    <script>
document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission
    const email = document.getElementById('email').value;
    const formData = new FormData();
    formData.append('email', email);

    fetch('forgotpwd.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Handle different responses based on the server's reply
        if (data.includes('Email has been sent')) {
            alert('Check your email for updating your password.');
        } else {
            alert(data); // Show the error message or different response from the server
        }
    })
    .catch(error => {
        alert('Error sending email.');
        console.error('Error:', error);
    });
});

function goToIndexPage() {
    window.location.href = 'index.php';
}
</script>

</body>
</html>
