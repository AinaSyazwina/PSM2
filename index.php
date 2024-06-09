<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "library") or die("Couldn't connect");

function fetchRecommendations($user_id) {
    $url = "http://localhost:5000/recommend/" . $user_id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!$response) {
        return ["Error" => "Failed to fetch data"];
    }
    $data = json_decode($response, true);
    return $data ?: ["Error" => "No recommendations available"];
}

function login($username, $role, $memberID, $picture) {
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['memberID'] = $memberID;
    $_SESSION['user_picture'] = $picture;

    $_SESSION['recommendations'] = fetchRecommendations($memberID);

    switch ($role) {
        case 'admin':
            header("Location: dashboard.php");
            exit();
        case 'student':
            header("Location: HomeStu.php");
            exit();
        case 'LibPre':
            header("Location: HomeLib.php");
            exit();
        default:
            exit('Unauthorized access.');
    }
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $submittedPassword = $_POST['password'];

    $stmt = $con->prepare("SELECT * FROM register WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $storedPassword = $row['pwd'];
        $role = $row['role'];
        $memberID = $row['memberID'];
        $picture = $row['picture'] ? "path/to/images/" . $row['picture'] : 'pic/default-avatar.png';
        $status = $row['status'];

        if ($status !== 'active') {
            $error = "Your account is inactive. Please contact support.";
        } else {
            if (preg_match('/^\$2y\$/', $storedPassword)) {
                if (password_verify($submittedPassword, $storedPassword)) {
                    login($username, $role, $memberID, $picture);
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                if ($submittedPassword === $storedPassword) {
                    login($username, $role, $memberID, $picture);
                } else {
                    $error = "Invalid username or password.";
                }
            }
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="Cssfile/style.css">
    <style>
        header {
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 19px;
        }
        h1 { font-size: 25px; }
        .image-section {
            width: 50%;
            background-image: url('pic/loginbg.jpg');
            background-size: cover;
            background-position: center;
        }
        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header-img {
            width: 150px;
            margin-right: 5px;
        }
        .error {
            height: 20px;
            color: red;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .field input {
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .form-box header h3 {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
            font-weight: 450;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="container">
            <div class="form-box">
                <header>
                    <div class="header-content">
                        <img src="pic/logo (2).png" alt="Header Image" class="header-img">
                        <h1>Welcome Back</h1>
                        <h3>Login into your account to continue</h3>
                    </div>
                </header>
                <form action="" method="post">
                    <div class="field input">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="field input">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="field">
                        <input type="submit" class="btn" name="submit" value="Login">
                    </div>
                    <?php if(isset($error)): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                </form>
                <div class="link">
                    Forget Password? <a href="forgot.php">Click here</a>
                </div>
            </div>
            <div class="image-section"></div>
        </div>
    </div>
</body>
</html>
