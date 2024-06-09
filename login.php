<?php
   /* session_start();
    $con = mysqli_connect("localhost", "root", "", "library") or die("Couldn't connect");

    if(isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT * FROM register WHERE username='$username' AND pwd='$password'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $role = $row['role']; 
            
            $_SESSION['username'] = $username;
            if ($role == 'admin') {
                header("Location: dashboard.php");
            } else if ($role == 'student') {
                header("Location: HomeStu.php");
            }
            exit(); 
        } else {
            
            $error = "Invalid username or password.";
        }
    }
    */
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

        h1{
            font-size: 25px;
        }
        .image-section {
            width: 50%;
            background-image: url('pic/login1.jpg'); 
            background-size: cover;
            background-position: center;
        }
        .header-content {
            display: flex;
            align-items: center;
        }
        .header-img {
            width: 70px; 
            margin-right: 5px; 
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 15px; 
        }
        .field input {
            margin-top: 10px;
            margin-bottom: 5px;
        } 
    </style>
</head>
<body>
    <div class="background">
        <div class="container">
            <div class="form-box">
                <header>
                    <div class="header-content">
                        <img src="pic/logo.png" alt="Header Image" class="header-img">
                        <h1>BookPanda & GrabBook Management System</h1>
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
