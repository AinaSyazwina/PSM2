<?php include 'navigaLib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quote</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            padding-top: 80px; 
        }
        .container {
            width: 90%;
            max-width: 800px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            margin-top: 30px;
        }
        .icon {
            text-align: center;
            display: block;
            margin: 0 auto;
            font-size: 50px;
            color: #007bff;
            margin-bottom: 5px; 
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        p.description {
            text-align: center;
            font-size: 1.1em;
            color: #666;
            margin-bottom: 20px;
        }
        .quote-nav {
            text-align: center;
            margin-bottom: 20px;
        }
        .quote-nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .quote-nav a.active {
            color: #007bff;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            width: 100%;
            max-width: 600px;
            margin-top: 10px;
            font-weight: bold;
        }
        textarea, input[type="text"] {
            width: 100%;
            max-width: 600px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .success-message-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); 
        }
        .success-message, .error-message {
            display: none;
            position: fixed;
            z-index: 1001;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 80%;
            max-width: 400px;
        }
        .success-message .icon, .error-message .icon {
            font-size: 40px; 
            margin-bottom: 10px;
        }
        .success-message .icon {
            color: #28a745;
        }
        .error-message .icon {
            color: #dc3545;
        }
        .success-message h2, .error-message h2 {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
        }
        .success-message p, .error-message p {
            margin: 10px 0 0;
        }
    </style>
    <script>
        function showSuccessMessage() {
            var successMessageOverlay = document.getElementById('successMessageOverlay');
            var successMessage = document.getElementById('successMessage');
            successMessageOverlay.style.display = 'block';
            successMessage.style.display = 'block';

            setTimeout(function() {
                successMessageOverlay.style.display = 'none';
                successMessage.style.display = 'none';
            }, 1000); 
        }

        function showError(message) {
            var errorMessageOverlay = document.getElementById('successMessageOverlay');
            var errorMessage = document.getElementById('errorMessage');
            errorMessage.querySelector('p').textContent = message;
            errorMessageOverlay.style.display = 'block';
            errorMessage.style.display = 'block';

            setTimeout(function() {
                errorMessageOverlay.style.display = 'none';
                errorMessage.style.display = 'none';
            }, 3000); 
        }

        function saveQuote(event) {
            event.preventDefault(); 
            var form = document.getElementById('quoteForm');
            var formData = new FormData(form);

            fetch('LPQuote1.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    showSuccessMessage();
                    form.reset(); 
                } else if (data.trim() === 'error:exceed_limit') {
                    showError('You have exceeded the quote limit for today.');
                } else {
                    showError('Error: Could not save quote. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error: Could not save quote. Please try again.');
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <i class="fab fa-twitter icon"></i>
        <h1>Create Quote</h1>
        <p class="description">Write beautiful quotes to share with the community</p>
        <div class="quote-nav">
            <a href="LPQuote.php" class="active">Create a Quote</a>
            <a href="LPQuote3.php">Your Quotes</a>
            <a href="LPQuote2.php">View All Quotes</a>
        </div>
        <form id="quoteForm" method="POST" onsubmit="saveQuote(event)">
            <label for="quote">Quote:</label>
            <textarea name="quote" id="quote" rows="4" required></textarea>
            <label for="tags">Tags:</label>
            <input type="text" name="tags" id="tags">
            <input type="submit" value="Submit Quote">
        </form>
        <div id="successMessageOverlay" class="success-message-overlay"></div>
        <div id="successMessage" class="success-message">
            <i class="fas fa-check-circle icon"></i>
            <h2>Success!</h2>
            <p>Your quote has been submitted.</p>
        </div>
        <div id="errorMessage" class="error-message">
            <i class="fas fa-times-circle icon"></i>
            <h2>Error!</h2>
            <p></p>
        </div>
    </div>
</body>
</html>
