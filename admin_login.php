<?php 
include('dbconnect.php');
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    // Redirect to user dashboard if logged in as a regular user
    if ($_SESSION['status'] != 1) {
        header("Location: admin_dashboard.php");
        exit();
    }
} elseif (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $query = "SELECT * FROM admin_signup WHERE admin_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['admin_email'] = $user['admin_email'];
        $_SESSION['status_id'] = $user['status_id'];

        // Redirect to user dashboard if logged in as a regular user
        if ($_SESSION['status'] != 1) {
            header("Location: admin_dashboard.php");
            exit();
        }
    } else {
        $error_message = "Invalid email or password";
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $query = "SELECT * FROM admin_signup WHERE admin_email = '$email' AND password = '$password'";
    $result = mysqli_query(mysql: $con, query: $query);
    $fetch = mysqli_fetch_assoc($result);

    if (mysqli_num_rows($result) > 0) {
        $status = $fetch['status_id'];
        if ($status == 1) {
            $_SESSION['admin_email'] = $fetch['admin_email'];
            $_SESSION['status_id'] = $fetch['status_id'];
            header("location: admin_dashboard.php");
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
        <link rel="stylesheet" href="admin_login.css?=<?php echo time(); ?>">
    </head>
    <body>
    <section>
                <img src="image/K.jpg"  alt="" class="image" width="250px" height="190px">   
                        <div class="form-value">
                            <form action="" method="post">
                                <h2>Welcome Back, Admin!</h2>
                            
                                <div class="inputbox">
                                    <ion-icon name="mail-outline"></ion-icon>
                                    <input type="email" id="email" name="email" required>
                                    <label for="email">Email</label>
                                </div>
                                <div class="inputbox">
                                
                                    <input type="password" id="password" name="password" required>
                                    <label for="password">Password</label>
                                </div>
                                <div class="forget">
                                    <label for=""><input type="checkbox">Remember Me</label>
                                </div>
                                <div class="button-sub">
                                    <button type="submit" name="login">Log in</button>
                                </div>
                                               <?php
                if (isset($error_message)) {
                    echo "<p style='color:red;'>$error_message</p>";
                }
                ?>
            </div>
        </section>

        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    </body>
    </html>