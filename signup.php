<?php
include('dbconnect.php');

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";

if (isset($_POST['submit'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $pnum = mysqli_real_escape_string($con, $_POST['pnum']);
    $password = $_POST['password'];

    // Default status
    $defaultStatus = 2;

    $savedata = "INSERT INTO signup (id, fname, lname, email, pnum, password, status_id)
                VALUES ('', '$fname', '$lname', '$email', '$pnum', '$password', '$defaultStatus')";

    if (mysqli_query($con, $savedata)) {
        $message = "success"; // Set success message
    } else {
        $message = "error"; // Set error message
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="signup.css?= <?php echo time(); ?>">
    <link rel="stylesheet" href="float.css?= <?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<section>
    <div class="signup--main--container">
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
        <div class="signup--main--container--box">
            <div class="img--container"><img src="img/sign3.svg" alt=""></div>
            <div class="form--container">
                <div class="head">
                    <h1>Create VRRS Account</h1>
                    <p>One Account for everyday use of this website.</p>
                    <p style="color:red">Learn More.</p>
                </div>
                <div class="form--container--box">
                    <form id="signupForm" action="" method="post">
                        <div class="single-input">
                            <input type="text" name="fname" id="fname" placeholder="First Name" value="" required>
                        </div>
                        <div class="single-input">
                            <input type="text" name="lname" id="lname" placeholder="Last Name" value="" required>
                        </div>   
                        <div class="single-input">
                            <input type="text" name="email" id="email" placeholder="Email" value="" required>
                        </div>
                        <div class="single-input">
                            <input type="number" name="pnum" id="pnum" placeholder="Phone Number" value="" required>  
                        </div>
                        <div class="single-input">
                            <input type="password" name="password" id="password" placeholder="Password" value="" required>
                        </div>
                        <div class="forget">
                            <label>
                                <input type="checkbox" required>
                                By signing up in the VRRS, you agree to the Memorandum of Agreement and acknowledge that you have read the policy and agreement.
                            </label>          
                        </div>
                        <div class="button-sub">
                            <button type="submit" name="submit">Submit</button> 
                        </div>
                        <br>  
                        <div class="account-have">
                            <a href="index.php">Go back Home!</a>
                            <a href="login.php">Have an account?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Display SweetAlert based on PHP feedback
    const message = "<?php echo $message; ?>";
    if (message === "success") {
        Swal.fire({
            icon: 'success',
            title: 'Account Created',
            text: 'You successfully created your account!',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php'; // Redirect on success
            }
        });
    } else if (message === "error") {
        Swal.fire({
            icon: 'error',
            title: 'Account Creation Failed',
            text: 'There was an error creating your account. Please try again.',
            confirmButtonText: 'OK'
        });
    }
</script>
</body>
</html>
