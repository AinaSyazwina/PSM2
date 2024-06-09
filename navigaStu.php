<?php include 'chatbot.php'; ?>
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$memberID = $_SESSION['memberID'];
$stmt = $conn->prepare("SELECT picture FROM register WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$uploadDir = '';  
$userImage = !empty($user['picture']) ? $uploadDir . $user['picture'] : 'pic/default-avatar.png';

$stmt->close();

// Function to insert or update user activity
function updateUserActivity($conn, $memberID) {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO user_activity (memberID, date) VALUES (?, ?) ON DUPLICATE KEY UPDATE date = VALUES(date)");
    $stmt->bind_param("ss", $memberID, $today); // Corrected bind_param to use string
    $stmt->execute();
    $stmt->close();
}

// Function to calculate the streak
function calculateStreak($conn, $memberID) {
    $query = "SELECT date FROM user_activity WHERE memberID = ? ORDER BY date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $memberID); // Corrected bind_param to use string
    $stmt->execute();
    $result = $stmt->get_result();
    $dates = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $streak = 0;
    $currentDate = new DateTime();
    foreach ($dates as $date) {
        $activityDate = new DateTime($date['date']);
        $diff = $currentDate->diff($activityDate)->days;
        if ($diff == $streak) {
            $streak++;
        } else {
            break;
        }
    }
    return $streak;
}

updateUserActivity($conn, $memberID);
$streak = calculateStreak($conn, $memberID);

function fetchNotifications($conn, $memberID) {
    $twoWeeksAgo = date('Y-m-d', strtotime('-2 weeks'));
    $oneWeekAgo = date('Y-m-d', strtotime('-1 week'));
    $oneMonthAgo = date('Y-m-d', strtotime('-1 month'));
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $now = new DateTime();
    $notifications = [];

    $bookQuery = "SELECT 
                  'Book' as type, 
                  m.fullname, 
                  i.issueID as itemID, 
                  i.DueDate, 
                  b.ISBN
                  FROM issuebook i
                  JOIN register m ON i.memberID = m.memberID
                  JOIN books b ON i.bookID = b.book_acquisition
                  WHERE (i.DueDate = '$tomorrow' OR i.DueDate < '$today')
                  AND i.ReturnDate IS NULL
                  AND i.DueDate >= '$oneMonthAgo'
                  AND i.memberID = '$memberID'";

    $bookResult = mysqli_query($conn, $bookQuery);
    while ($row = mysqli_fetch_assoc($bookResult)) {
        $dueDate = new DateTime($row['DueDate']);
        $interval = $now->diff($dueDate);
        $timeElapsed = formatTimeElapsed($interval);
        $notifications[] = [
            "message" => "{$row['fullname']}'s {$row['type']} (ISBN: {$row['ISBN']}) is " . 
                         ($row['DueDate'] == $tomorrow ? 'due tomorrow.' : 'overdue.'),
            "timeElapsed" => $timeElapsed
        ];
    }

    $boxQuery = "SELECT 'Box' as type, i.issueBoxID as itemID, i.DueDate
                 FROM issuebox i
                 WHERE i.DueDate >= '$oneWeekAgo' AND i.DueDate <= '$today'
                       AND i.ReturnDate IS NULL
                       AND i.memberID = '$memberID'";  
    $boxResult = mysqli_query($conn, $boxQuery);
    while ($row = mysqli_fetch_assoc($boxResult)) {
        $dueDate = new DateTime($row['DueDate']);
        $interval = $now->diff($dueDate);
        $timeElapsed = formatTimeElapsed($interval);
        $notifications[] = [
            "message" => "{$row['type']} (ID: {$row['itemID']}) is " . ($row['DueDate'] == $today ? "due today." : "overdue."),
            "timeElapsed" => $timeElapsed
        ];
    }

    $recentBooksQuery = "SELECT 'Book' as type, bookID, Title, DateReceived
                         FROM books
                         WHERE DateReceived >= '$twoWeeksAgo'";
    $recentBooksResult = mysqli_query($conn, $recentBooksQuery);
    while ($row = mysqli_fetch_assoc($recentBooksResult)) {
        $dateReceived = new DateTime($row['DateReceived']);
        $interval = $now->diff($dateReceived);
        $timeElapsed = formatTimeElapsed($interval);
        $notifications[] = [
            "message" => "{$row['type']} (ID: {$row['bookID']}) titled '{$row['Title']}' was added recently.",
            "timeElapsed" => $timeElapsed
        ];
    }

    $recentBoxesQuery = "SELECT 'Box' as type, BoxSerialNum, DateCreate
                         FROM boxs
                         WHERE DateCreate >= '$twoWeeksAgo'";
    $recentBoxesResult = mysqli_query($conn, $recentBoxesQuery);
    while ($row = mysqli_fetch_assoc($recentBoxesResult)) {
        $dateCreate = new DateTime($row['DateCreate']);
        $interval = $now->diff($dateCreate);
        $timeElapsed = formatTimeElapsed($interval);
        $notifications[] = [
            "message" => "{$row['type']} (ID: {$row['BoxSerialNum']}) was added recently.",
            "timeElapsed" => $timeElapsed
        ];
    }

    return $notifications;
}

function formatTimeElapsed($interval) {
    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
    } elseif ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
    } elseif ($interval->d > 0) {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
    } elseif ($interval->h > 0) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
    } elseif ($interval->i > 0) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
    } else {
        return $interval->s . ' second' . ($interval->s > 1 ? 's' : '');
    }
}

$notifications = fetchNotifications($conn, $memberID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Kamunting</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Cssfile/navStu.css">
    <style>
        /* Notification Bell with White Outline and Badge */
        .notification-bell-container {
            position: relative;
            margin-right: 20px;
        }

        box-icon#notification-bell {
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            border-radius: 50%;
            padding: 2px;
        }

        .notification-bell-container::after {
            content: "<?php echo count($notifications); ?>";
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            font-size: 12px;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Notification Dropdown Styles */
        .notification-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            border-radius: 8px;
            display: none;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .notification-dropdown.active {
            display: block;
        }
        
        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-header h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item .icon {
            font-size: 20px;
            margin-right: 10px;
        }
        
        .notification-item .icon.alert {
            color: #ffc107;
        }
        
        .notification-item .content1 {
            flex: 1;
        }
        
        .notification-item .content1 h5 {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        
        .notification-item .content1 p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #777;
        }
        
        .notification-footer {
            padding: 10px;
            text-align: center;
        }
        
        .notification-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        
        /* Flashcard Styles */
        .flashcard {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: #fff3e0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            animation: fadeInOut 5s ease-in-out;
            z-index: 2000;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0; }
            10%, 90% { opacity: 1; }
        }

        .flashcard img {
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }

        .flashcard-content {
            font-size: 16px;
            color: #333;
        }

        .flashcard-content h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #000;
        }
    </style>
</head>
<body>
<header>
    <a href="#" class="brand">SK Kamunting</a>
    <div class="navigation">
        <div class="navigation-items">
            <a href="HomeStu.php">Home</a>
            <a href="SInsight.php">Insight</a>
            <div class="dropdown">
                <a href="#" class="dropbtn">Issue Record <i class='bx bx-chevron-down'></i></a>
                <div class="dropdown-content">
                    <a href="SCheckIssue.php">Book</a>
                    <a href="SCheckBoxIssue.php">Box</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="dropbtn">Fine Records <i class='bx bx-chevron-down'></i></a>
                <div class="dropdown-content">
                    <a href="SCheckFine.php">Book</a>
                    <a href="SCheckFineBox.php">Box</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="dropbtn">Review <i class='bx bx-chevron-down'></i></a>
                <div class="dropdown-content">
                    <a href="SReview.php">Book</a>
                    <a href="SBoxReview.php">Box</a>
                </div>
            </div>
            <a href="SList.php">Book</a>
            <a href="SListBox.php">Box</a>
            <a href="SGuide.php">Guides & Tips</a>
        </div>
    </div>

    <!-- User info with dynamic image and notification bell -->
    <div class="userinfo">
        <div class="notification-bell-container">
            <box-icon name='bell' id="notification-bell" onclick="toggleDropdown()"></box-icon>
        </div>
        <div class="dropdown">
            <button class="dropbtn">
                <img src="<?= htmlspecialchars($userImage) ?>" alt="Profile" class="profile-pic">
            </button>
            <div class="dropdown-content">
                <a href="SProfile.php">My Profile</a>
                <a href="SWishlist.php">Wishlist</a>
                <a href="SQuote.php">Quote</a>
                <a href="SChallenge.php">Challenge & rewards</a>
                <a href="SFAQ.php">FAQ</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <!-- Notification Dropdown -->
        <div class="notification-dropdown" id="notification-dropdown">
            <div class="notification-header">
                <h4>You have <?= count($notifications) ?> new notifications</h4>
            </div>
            <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                <div class="notification-item">
                    <i class='bx bxs-bell-ring icon alert'></i>
                    <div class="content1">
                        <h5><?= $notification['message'] ?></h5>
                        <p><?= $notification['timeElapsed'] ?> ago</p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($notifications) > 3): ?>
                <div class="notification-footer">
                    <a id="show-more" onclick="showAllNotifications()">Show all notifications</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Flashcard for Active Streak -->
<?php if ($streak > 0 && isset($_SESSION['show_flashcard']) && $_SESSION['show_flashcard']): ?>
    <div class="flashcard">
        <img src="uploads/fire_7549368.png" alt="Fire Icon">
        <div class="flashcard-content">
            <h4>New achievement earned!</h4>
            <p>You hit a <?= $streak ?>-day active user streak!</p>
        </div>
    </div>
    <?php unset($_SESSION['show_flashcard']); // Reset the session variable ?>
<?php endif; ?>

<script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        dropdown.classList.toggle('active');
    }

    function showAllNotifications() {
        const dropdownContent = document.getElementById('notification-dropdown');
        dropdownContent.innerHTML = `
            <div class="notification-header">
                <h4>You have <?= count($notifications) ?> new notifications</h4>
            </div>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <i class='bx bxs-bell-ring icon alert'></i>
                    <div class="content1">
                        <h5><?= $notification['message'] ?></h5>
                        <p><?= $notification['timeElapsed'] ?> ago</p>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="notification-footer">
                <a onclick="showLessNotifications()">Show less</a>
            </div>
        `;
    }

    function showLessNotifications() {
        const dropdownContent = document.getElementById('notification-dropdown');
        dropdownContent.innerHTML = `
            <div class="notification-header">
                <h4>You have <?= count($notifications) ?> new notifications</h4>
            </div>
            <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                <div class="notification-item">
                    <i class='bx bxs-bell-ring icon alert'></i>
                    <div class="content1">
                        <h5><?= $notification['message'] ?></h5>
                        <p><?= $notification['timeElapsed'] ?> ago</p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($notifications) > 3): ?>
                <div class="notification-footer">
                    <a id="show-more" onclick="showAllNotifications()">Show all notifications</a>
                </div>
            <?php endif; ?>
        `;
    }

    // Hide flashcard after 1 min
    setTimeout(function() {
        const flashcard = document.querySelector('.flashcard');
        if (flashcard) {
            flashcard.style.display = 'none';
        }
    }, 60000);
</script>
</body>
</html>
