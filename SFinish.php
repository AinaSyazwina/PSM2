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

// Query to get the completed loyalty card details
$queryCard = "SELECT * FROM loyalty_cards WHERE memberID = ? AND stamps >= 10 ORDER BY cardID DESC LIMIT 1";
$stmt = $conn->prepare($queryCard);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$card = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Challenges</title>
    <link rel="stylesheet" href="Cssfile/challenge.css">
</head>
<body>
    <div class="container-padding">
        <div class="header-section">
            <div class="header-text">
                <h2>Completed Challenges</h2>
            </div>

            <div class="button-container">
                <a href="SChallenge.php">Currently</a>
                <a href="SFinish.php" class="active">Finish</a>
            </div>

            <div class="section-title">Finish</div>
            <div class="loyalty-card-container">
                <?php if ($card): ?>
                    <div class="loyalty-card">
                        <?php
                        for ($i = 0; $i < 10; $i++) {
                            $class = "bubble" . ($i + 1);
                            echo "<span class='stamped $class'></span>";
                        }
                        ?>
                    </div>
                    <div class="toggle-container">
                        <label class="toggle-switch">
                            <input type="checkbox" id="requestToggle" <?php echo $card['requested'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                            Already request
                        </label>
                    </div>
                <?php else: ?>
                    <p>No completed cards.</p>
                <?php endif; ?>
            </div>
        </div>

        <script>
            document.getElementById('requestToggle').addEventListener('change', function () {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'SupdateLoyaltyCard.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        alert('Request status updated');
                    }
                };
                xhr.send('memberID=<?php echo $memberID; ?>&requested=' + (this.checked ? 1 : 0));
            });
        </script>
    </body>
    </html>
