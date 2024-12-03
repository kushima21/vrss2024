<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection

// Check if a user is logged in based on session variables
if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email']; // Retrieve logged-in user's email from session variable

    // Prepare and execute query to fetch user details based on logged-in email
    $fetchadata = "SELECT * FROM signup WHERE email = ?";
    $stmt = $con->prepare($fetchadata);
    $stmt->bind_param("s", $loggedInEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data

        // Always display the user dashboard for users
        if ($status_id == 2) {
            // Code to display the user dashboard goes here
           
            // Further code to render the user dashboard can be placed here
        } else if ($status_id == 1) {
            // If the status_id indicates admin, redirect to the admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Handle any other roles, if necessary
            echo "Access denied. You are not authorized to view this page.";
        }
    } else {
        // If no user data is found, redirect to login.php
        header("Location: login.php");
        exit();
    }
} else {
    // If no email session is set, redirect to login.php
    header("Location: login.php");
    exit();
}

      // 1. Check if the user has any unviewed approved reservations
      $checkReservationQuery = "SELECT * FROM reservation_request WHERE user_id = ? AND status IN ('Approved', 'Traveling') AND viewed_reservation = 0";
      $stmt2 = $con->prepare($checkReservationQuery);
      $stmt2->bind_param("i", $id);
      $stmt2->execute();
      $reservationResult = $stmt2->get_result();
      $hasApprovedReservation = mysqli_num_rows($reservationResult) > 0;

      // 2. If the user clicks the link, update reservation to mark it as viewed
      if (isset($_GET['viewed'])) {
          // Update reservation to mark it as viewed
          $updateReservationQuery = "UPDATE reservation_request SET viewed_reservation = 1 WHERE user_id = ? AND status = 'Approved' AND viewed_reservation = 0";
          $stmt3 = $con->prepare($updateReservationQuery);
          $stmt3->bind_param("i", $id);
          $stmt3->execute();

          // Redirect to the transaction history page to ensure the red dot disappears
          header("Location: user_main_transaction_history.php");
          exit();
      }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_dashboard.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
<body>
<div class="user--main--container">
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
<div class="topbar">
        <div class="logo">
            <h2 ><i class="fa-solid fa-key" ></i>VRRS</h2>
        </div>
        <div class="nav-container">
            <button class="nav-toggle" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
  </button>
  <div class="nav-links" id="navLinks">
    <ul>
      <li><a href="user_dashboard.php"><i class="fas fa-home"></i> <span class="link-text">Home</span></a></li>
      <li><a href="user_about.php"><i class="fas fa-info-circle"></i> <span class="link-text">About</span></a></li>
      <li><a href="user_reservation.php"><i class="fas fa-calendar-check"></i> <span class="link-text">Reservation</span></a></li>
      <li><a href="user_contact.php"><i class="fas fa-envelope"></i> <span class="link-text">Contact</span></a></li>
      <li><a href="user_services.php"><i class="fas fa-concierge-bell"></i> <span class="link-text">Services</span></a></li>
      <li><a href="user_search.php"><i class="fas fa-magnifying-glass"></i><span class="link-text">Search</span></a></li>    
    </ul>
  </div>
</div>
<div class="mode">
    <div class="moon-sun" id="toggle-switch">
        <i class="fa-solid fa-moon" id="moon"></i>
        <i class="fa-solid fa-sun" id="sun"></i> <!-- Corrected this line -->
    </div>
</div>

                            <div class="profile">
                                <div class="profileBtn">
                                    <span class="profile_text"> <?php echo $fname, $lname; ?></span>
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <ul class="profile_options">
                                    <li class="profile_option">
                                      <i class="fa-solid fa-user"></i>
                                        <a href="user_main_transaction_account.php"><span class="option_profile">Profile Modifications</span></a>
                                    </li>
                                    <li class="profile_option">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        <a href="user_main_transaction_history.php?viewed=true">
                                            <span class="option_profile">Transaction History</span>
                                            <?php if ($hasApprovedReservation): ?>
                                                <span class="notification-dot"></span> <!-- Red dot for unviewed approved reservations -->
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li class="profile_option">
                                          <i class="fa-solid fa-shield"></i>
                                          <a href="user_transaction_term_condition.php"><span class="option_profile">Terms and Conditions </span></a>
                                    </li>
                                    <li class="profile_option">
                                      <i class="fa-solid fa-right-from-bracket"></i>
                                        <a href="logout.php"><span class="option_profile">Logout</span></a>
                                    </li>
                                </ul>
                         </div>  
                    </div>     
                </div>         
        <main class="wrapper">
          <section class="hero">
            <div class="container">
              <div class="hero__image"></div>
      
              <div class="hero__text container--pall">
                    <h1>RENT EXCLUSIVE VAN</h1>
                    <p>
                        These rental VANS  are the keys to unlocking new adventures and unforgettable memories.
                        Renting a van is the ultimate way to take control of your travel.
                        Why limit yourself to one destination when you can rent a van and explore them all.
                    </p>
                    <a href="user_reservation.php" class="button hero__cta">Reserve Now</a>
                  </div>
                </div>
                <div class="feature__content container container--pall">
                  <div class="feature__intro">
                    <h2>Why choose VRS?</h2>
                      <p>
                      Van Reservation are the keys to unlocking new adventures and unforgettable memories. 
                      Renting a car is the ultimate way to take control of your travel. Why limit yourself to one destination when you can rent a car and explore them all.
                      </p>
                  </div>
          </section>
        </main>
  
          <div class="feature__grid">
            <div class="feature__item"> 
              <div class="feature__icon"><img src="img/Picture1.png" />
              </div>

              <div class="feature__title">Convenience</div>
              <div class="feature__description">
                <p>Van Reservation allow users to easily search and book a rental car from the comfort of their own home, without having to visit a physical rental location.</p>
              </div>
            </div>
  
            <div class="feature__item">
              <div class="feature__icon"><img src="img/Picture2.png" />
              </div>

              <div class="feature__title">Cost-effective</div>
              <div class="feature__description">
                <p>Van Reservation often offer competitive pricing, especially for those who book in advance.</p>
              </div>
            </div>
  
            <div class="feature__item">
              <div class="feature__icon"><img src="img/Picture3.png" />
              </div>

              <div class="feature__title">Variety</div>
              <div class="feature__description">
                <p>Van Reservation offer a wide range of car models and types for rent, which may not be available at local rental locations.</p>
              </div>
            </div>
  
            <div class="feature__item">
              <div class="feature__icon"><img src="img/Picture4.png" /></div>
              <div class="feature__title">Flexibility</div>
              <div class="feature__description">
                <p>Van Reservation typically offer flexible rental periods, allowing users to rent a car for as long as they need it.</p>
              </div>
            </div>
          </div>
        </div>
      

<!--feature-->


      
<!--footers-->

      <footer class="footer">
        <div class="container">
          <a class="footer__logo" href="#"></a>
  
          <div class="footer__social">
            <a href="https://www.facebook.com/jm.hondrada.7">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                <title>Facebook</title>
                <path
                  fill="#FFF"
                  d="M18.896 0H1.104C.494 0 0 .494 0 1.104v17.793C0 19.506.494 20 1.104 20h9.58v-7.745H8.076V9.237h2.606V7.01c0-2.583 1.578-3.99 3.883-3.99 1.104 0 2.052.082 2.329.119v2.7h-1.598c-1.254 0-1.496.597-1.496 1.47v1.928h2.989l-.39 3.018h-2.6V20h5.098c.608 0 1.102-.494 1.102-1.104V1.104C20 .494 19.506 0 18.896 0z"
                />
              </svg>
            </a>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20">
                <title>YouTube</title>
                <path
                  fill="#FFF"
                  d="M10.333 0c-5.522 0-10 4.478-10 10 0 5.523 4.478 10 10 10 5.523 0 10-4.477 10-10 0-5.522-4.477-10-10-10zm3.701 14.077c-1.752.12-5.653.12-7.402 0C4.735 13.947 4.514 13.018 4.5 10c.014-3.024.237-3.947 2.132-4.077 1.749-.12 5.651-.12 7.402 0 1.898.13 2.118 1.059 2.133 4.077-.015 3.024-.238 3.947-2.133 4.077zM8.667 8.048l4.097 1.949-4.097 1.955V8.048z"
                />
              </svg>
            </a>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="18">
                <title>Twitter</title>
                <path
                  fill="#FFF"
                  d="M20.667 2.797a8.192 8.192 0 01-2.357.646 4.11 4.11 0 001.804-2.27 8.22 8.22 0 01-2.606.996A4.096 4.096 0 0014.513.873c-2.649 0-4.595 2.472-3.997 5.038a11.648 11.648 0 01-8.457-4.287 4.109 4.109 0 001.27 5.478A4.086 4.086 0 011.47 6.59c-.045 1.901 1.317 3.68 3.29 4.075a4.113 4.113 0 01-1.853.07 4.106 4.106 0 003.834 2.85 8.25 8.25 0 01-6.075 1.7 11.616 11.616 0 006.29 1.843c7.618 0 11.922-6.434 11.662-12.205a8.354 8.354 0 002.048-2.124z"
                />
              </svg>
            </a>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                <title>Pinterest</title>
                <path
                  fill="#FFF"
                  d="M10 0C4.478 0 0 4.477 0 10c0 4.237 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.182-.78 1.172-4.97 1.172-4.97s-.299-.6-.299-1.486c0-1.39.806-2.428 1.81-2.428.852 0 1.264.64 1.264 1.408 0 .858-.545 2.14-.828 3.33-.236.995.5 1.807 1.48 1.807 1.778 0 3.144-1.874 3.144-4.58 0-2.393-1.72-4.068-4.177-4.068-2.845 0-4.515 2.135-4.515 4.34 0 .859.331 1.781.745 2.281a.3.3 0 01.069.288l-.278 1.133c-.044.183-.145.223-.335.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.965-.525-2.291-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.937.29 1.931.446 2.962.446 5.522 0 10-4.477 10-10S15.522 0 10 0z"
                />
              </svg>
            </a>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20">
                <title>Instagram</title>
                <path
                  fill="#FFF"
                  d="M10.333 1.802c2.67 0 2.987.01 4.042.059 2.71.123 3.976 1.409 4.1 4.099.048 1.054.057 1.37.057 4.04 0 2.672-.01 2.988-.058 4.042-.124 2.687-1.386 3.975-4.099 4.099-1.055.048-1.37.058-4.042.058-2.67 0-2.986-.01-4.04-.058-2.717-.124-3.976-1.416-4.1-4.1-.048-1.054-.058-1.37-.058-4.041 0-2.67.01-2.986.058-4.04.124-2.69 1.387-3.977 4.1-4.1 1.054-.048 1.37-.058 4.04-.058zm0-1.802C7.618 0 7.278.012 6.211.06 2.579.227.56 2.242.394 5.877.345 6.944.334 7.284.334 10s.011 3.057.06 4.123c.166 3.632 2.181 5.65 5.816 5.817 1.068.048 1.408.06 4.123.06 2.716 0 3.057-.012 4.124-.06 3.628-.167 5.651-2.182 5.816-5.817.049-1.066.06-1.407.06-4.123s-.011-3.056-.06-4.122C20.11 2.249 18.093.228 14.458.06 13.39.01 13.049 0 10.333 0zm0 4.865a5.135 5.135 0 100 10.27 5.135 5.135 0 000-10.27zm0 8.468a3.333 3.333 0 110-6.666 3.333 3.333 0 010 6.666zm5.339-9.87a1.2 1.2 0 10-.001 2.4 1.2 1.2 0 000-2.4z"
                />
              </svg>
            </a>
          </div>
          
  <!--Footers-->
          <div class="footer__links col1">
            <a href="about.html">About Us</a>
            <a href="#">Contact</a>
            <a href="#">Blog</a>
          </div>
  
          <div class="footer__links col2">
            <a href="#">Offer</a>
            <a href="#">Support</a>
            <a href="#">Privacy Policy</a>
          </div>
  
    
        <div class="attribution">
          &copy; Van_Reservation. All Rights Reserved.
        </div>
      </footer>
      <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
      <script src="user.js"></script>
      <script src="darkmode.js"></script>
      <script>
        function toggleNav() {
  const navLinks = document.getElementById("navLinks");
  navLinks.classList.toggle("show");
}
      </script>
</div>
</body>
</html>