<?php
include 'navigation.php'; // Assuming navigation.php handles session start and validation

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit;
}

include 'config.php'; 
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}
$username = $_SESSION['username'];

$query = $conn->prepare("SELECT fullname, IC, email, birthdate, memberID, username, pwd, role, picture FROM register WHERE username = ?");
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
    <title>My Profile</title>
    <link rel="stylesheet" href="Cssfile/profile.css"> <!-- Make sure the CSS path is correct -->
</head>
<body>
<div class="profile-container">
    <div class="profile-sidebar">
        <!-- Display the user image here -->
        <img src="<?= $userImage ?>" alt="Profile Picture" class="profile-picture">
        <h2>Welcome, <?= htmlspecialchars($user['username']) ?></h2>
        <p>Glad to have you back!</p>
    </div>
    <div class="profile-main">
        <h1 class="profile-title">My Profile</h1>
        <div class="info-container"><strong>Name:</strong> <?= htmlspecialchars($user['fullname']) ?></div>
        <div class="info-container"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
        <div class="info-container"><strong>Birthday:</strong> <?= date('d M Y', strtotime(htmlspecialchars($user['birthdate']))) ?></div>
        <div class="info-container"><strong>Member ID:</strong> <?= htmlspecialchars($user['memberID']) ?></div>
        <div class="info-container"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></div>
        <div class="info-container"><strong>Password:</strong> ******* </div>
        <div class="info-container"><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></div>
    </div>
</div>

</body>
</html>
