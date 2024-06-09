<?php
include 'navigaLib.php'; // Include student navigation

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    header('Location: index.php');
    exit;
}

include 'config.php';

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT * FROM register WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

$uploadDir = ''; 
$userImage = !empty($user['picture']) ? $uploadDir . htmlspecialchars($user['picture']) : 'pic/default-avatar.png';

$query->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="Cssfile/css/profile.css"> <!-- Ensure the path is correct -->
</head>
<body>
<div class="container">
    <h1>My Profile</h1>
    <div class="profile-header">
        <img src="<?= $userImage ?>" alt="Profile Picture" class="profile-picture">
        <div class="profile-summary">
            <h1><?= htmlspecialchars($user['fullname']) ?></h1>
            <p class="title"><?= htmlspecialchars($user['role']) ?></p>
            
        </div>
    </div>
    <div class="profile-about">
        <h2>About</h2>
        <p>Hello, my name is <?= htmlspecialchars($user['fullname']) ?>, and this is my profile.</p>
    </div>
    <div class="profile-details">
        <h2>Profile Details</h2>
        <div class="details-row">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="details-row">
            <p><strong>Birthday:</strong> <?= date('d M Y', strtotime(htmlspecialchars($user['birthdate']))) ?></p>
        </div>
        <div class="details-row">
            <p><strong>Member ID:</strong> <?= htmlspecialchars($user['memberID']) ?></p>
        </div>
        <div class="details-row">
            <p><strong>Class:</strong> <?= htmlspecialchars($user['class']) ?></p>
        </div>
        <div class="details-row">
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        </div>
        <div class="details-row">
            <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        </div>
        <div class="details-row">
            <p><strong>Status:</strong> <?= htmlspecialchars($user['status']) ?></p>
        </div>
    </div>
</div>
</body>
</html>
