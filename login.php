<?php 
include('dbconnect.php');
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    // Redirect to user dashboard if logged in as a regular user
    if ($_SESSION['status'] != 2) {
        header("Location: user_dashboard.php");
        exit();
    }
} elseif (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $query = "SELECT * FROM signup WHERE email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $user['email'];
        $_SESSION['status'] = $user['status_id'];

        // Redirect to user dashboard if logged in as a regular user
        if ($_SESSION['status'] != 2) {
            header("Location: user_dashboard.php");
            exit();
        }
    } else {
        $error_message = "Invalid email or password";
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $query = "SELECT * FROM signup WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($con, $query);
    $fetch = mysqli_fetch_assoc($result);

    if (mysqli_num_rows($result) > 0) {
        $status = $fetch['status_id'];
        if ($status == 2) {
            $_SESSION['email'] = $fetch['email'];
            $_SESSION['status'] = $fetch['status_id'];
            header("location: user_dashboard.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css?=<?php echo time(); ?>">
</head>
<body>
    <section>

    <div class="wrapper">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
        <a href="#">
        <img src="img/log.svg" alt="Login Image" class="image">
        </a>
        <div class="form-value">
            <form action="" method="post">
                <h2>Login</h2>
                <div class="inputbox">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="email" id="email" name="email" required placeholder=" ">
                    <label for="email">Email</label>
                </div>
                <div class="inputbox">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Password</label>
                </div>
                <div class="forget">
                    <label><input type="checkbox">Remember Me</label>
                    <a href="#">Forget Password</a>
                </div>
                <div class="button-sub">
                    <button type="submit" name="login">Log in</button>
                </div>
                <div class="register">
                    <p>Don't have an account? <a href="signup.php">Register</a></p>
                </div>
            </form>

            <?php
            if (isset($error_message)) {
                echo "<p style='color:red;'>$error_message</p>";
            }
            ?>
        </div>
    </section>

    <style>
        .inputbox {
            position: relative;
            margin: 20px 0;
        }

        .inputbox input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            outline: none;
            border-radius: 5px;
            transition: 0.3s;
        }

        .inputbox label {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            pointer-events: none;
            transition: 0.3s;
        }

        .inputbox input:focus ~ label,
        .inputbox input:not(:placeholder-shown) ~ label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #333;
        }
    </style>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>