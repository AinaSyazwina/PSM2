<?php
include 'navigaStu.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

include 'config.php';

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT memberID FROM register WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$memberID = $user['memberID'];
$view = isset($_GET['view']) ? $_GET['view'] : 'currently';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge & Rewards</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container-padding {
            padding: 20px;
        }
        .header-section {
            background-color: #e7f3ff; /* Light blue background */
            padding: 40px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 10px;
        }
        .header-section .header-text {
            max-width: 60%;
        }
        .header-section h2 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .header-section p {
            font-size: 18px;
            margin-bottom: 0;
        }
        .header-section img {
            max-width: 30%;
            height: auto;
        }
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .button-container a {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            margin: 0 10px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .button-container a.active, .button-container a:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }
        .loyalty-card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px; /* Adjusted margin to move it down */
        }
        .loyalty-card {
            position: relative;
            width: 450px; /* Adjust width for smaller size */
            height: 250px; /* Adjust height for smaller size */
            background: url('pic/Brown Minimalist Loyalty Card.png') no-repeat center center;
            background-size: cover;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .loyalty-card span {
            display: inline-block;
            width: 60px; /* Adjust size */
            height: 60px; /* Adjust size */
            position: absolute;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0); /* Transparent background */
        }
        .bubble1 { top: 95px; left: 69px; }
        .bubble2 { top: 95px; left: 135px; }
        .bubble3 { top: 95px; left: 190px; }
        .bubble4 { top: 95px; left: 255px; }
        .bubble5 { top: 95px; left: 318px; }
        .bubble6 { top: 154px; left: 69px; }
        .bubble7 { top: 154px; left: 135px; }
        .bubble8 { top: 154px; left: 190px; }
        .bubble9 { top: 154px; left: 255px; }
        .bubble10 { top: 154px; left: 318px; }
        .stamped {
            background-color: rgba(0, 0, 0, 0); /* Transparent background */
            position: relative;
        }
        .stamped:after {
            content: '\2713'; /* Checkmark icon */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #9e1b32; /* Dark red color for the checkmark */
            font-size: 36px; /* Slightly smaller size */
            font-weight: bold;
        }
        .toggle-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .section-title {
            font-size: 24px;
            font-weight: bold;
            margin-top: 30px;
            text-align: center;
            color: #333;
        }
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            padding-top: 100px; /* Location of the box */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .modal-header i {
            font-size: 50px; /* Make the checkmark icon larger */
            color: #28a745; /* Keep the green color for the checkmark */
            display: block;
            text-align: center;
            margin: 0 auto 10px auto; /* Center the icon and add margin at the bottom */
        }
        .modal-body p {
            font-size: 16px;
            margin: 10px 0;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-success {
            background-color: #2e2185;
            color: white;
        }
        .btn-success:hover {
            background-color: #241a65;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            margin-left: 10px; /* Add gap between buttons */
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .success-message {
            display: none;
            text-align: center;
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-padding">
        <div class="header-section">
            <div class="header-text">
                <h2>Challenge & Rewards</h2>
                <p>Borrow and return books 10 times to be eligible to participate in a lucky draw by SK Kamunting library. After 10 stamps, don't forget to give your name to the librarian or library prefects.</p>
            </div>
            <img src="pic/award_14661356.png" alt="Reward Program">
        </div>

        <div class="button-container">
            <a href="SChallenge.php?view=currently" class="<?php echo ($view == 'currently') ? 'active' : ''; ?>">Currently</a>
            <a href="SChallenge.php?view=finish" class="<?php echo ($view == 'finish') ? 'active' : ''; ?>">Finish</a>
        </div>

        <?php
        if ($view == 'currently') {
            echo '<div class="section-title">Currently</div>';
            echo '<div class="loyalty-card-container">';
            echo '<div class="loyalty-card">';

            // Query to get the number of books returned by the member
            $queryBooksReturned = "SELECT COUNT(*) as Returned FROM issuebook WHERE memberID = ? AND ReturnDate IS NOT NULL";
            $stmt = $conn->prepare($queryBooksReturned);
            $stmt->bind_param('s', $memberID);
            $stmt->execute();
            $result = $stmt->get_result();
            $booksReturned = $result->fetch_assoc()['Returned'];

            // Determine current card start point based on total books returned
            $currentCardStart = (int)($booksReturned / 10) * 10;
            $booksInCurrentCard = $booksReturned - $currentCardStart;

            for ($i = 0; $i < 10; $i++) {
                $class = "bubble" . ($i + 1);
                if ($i < $booksInCurrentCard) {
                    echo "<span class='stamped $class'></span>";
                } else {
                    echo "<span class='$class'></span>";
                }
            }

            echo '</div></div>';

        } else if ($view == 'finish') {
            // Query to get the finished loyalty card details
            $queryFinishedCard = "SELECT stamps, eligible, requested FROM loyalty_cards WHERE memberID = ?";
            $stmt = $conn->prepare($queryFinishedCard);
            $stmt->bind_param('s', $memberID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            echo '<div class="section-title">Finish</div>';
            if ($row) {
                echo '<div class="loyalty-card-container">';
                echo '<div class="loyalty-card">';

                for ($i = 0; $i < 10; $i++) {
                    $class = "bubble" . ($i + 1);
                    echo "<span class='stamped $class'></span>";
                }

                echo '</div></div>';

                echo '<div class="toggle-container">';
                echo '<label class="toggle-switch">';
                echo '<input type="checkbox" id="requestNewCard" ' . ($row['requested'] ? 'checked disabled' : '') . '>';
                echo '<span class="slider"></span>';
                echo '</label>';
                echo '<span style="margin-left: 10px;">Already request</span>';
                echo '</div>';
            } else {
                echo '<div class="section-title">No finished loyalty cards found for this user.</div>';
            }

            $stmt->close();
        }
        ?>
        <div class="success-message" id="successMessage">Successfully toggled</div>
    </div>

    <!-- Modal for confirmation -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p><strong>Confirm request</strong></p>
                <p>Are you sure you have already confirmed with the library prefects or librarian?</p>
            </div>
            <div class="modal-footer">
                <button id="confirmBtn" class="btn btn-success">Yes</button>
                <button id="cancelBtn" class="btn btn-secondary">No</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('requestNewCard').addEventListener('change', function() {
            var isChecked = this.checked;
            if (isChecked) {
                // Show confirmation modal
                var modal = document.getElementById("confirmationModal");
                modal.style.display = "block";

                // When the user clicks on <span> (x), close the modal
                document.getElementsByClassName("close")[0].onclick = function() {
                    modal.style.display = "none";
                }

                // When the user clicks on "No", close the modal and uncheck the toggle
                document.getElementById("cancelBtn").onclick = function() {
                    modal.style.display = "none";
                    document.getElementById('requestNewCard').checked = false;
                }

                // When the user clicks on "Yes", close the modal and send AJAX request
                document.getElementById("confirmBtn").onclick = function() {
                    modal.style.display = "none";

                    // AJAX call to update the backend
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'SupdateLoyaltyCard.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            document.getElementById('requestNewCard').disabled = true;
                            document.getElementById('successMessage').style.display = 'block'; // Show success message
                        }
                    };
                    xhr.send('memberID=<?php echo $memberID; ?>&eligible=1');
                }

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                        document.getElementById('requestNewCard').checked = false;
                    }
                }
            }
        });
    </script>
</body>
</html>
