<?php

include '../dbconfig.php'; // your database connection
session_start();

$username = $_SESSION['fullName'];

?>

<!DOCTYPE html>
<html>
<head>
  <!-- Site made with Mobirise Website Builder v6.0.5, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="generator" content="Mobirise v6.0.5, mobirise.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="Crony Karaoke - User Dashboard">

  <title>User Dashboard</title>
  <link rel="stylesheet" href="../assets/web/assets/mobirise-icons2/mobirise2.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="../assets/animatecss/animate.css">
  <link rel="stylesheet" href="../assets/dropdown/css/style.css">
  <link rel="stylesheet" href="../assets/socicon/css/styles.css">
  <link rel="stylesheet" href="../assets/theme/css/style.css">
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>
  <link rel="preload" as="style" href="../assets/mobirise/css/mbr-additional.css?v=f0jscm"><link rel="stylesheet" href="../assets/mobirise/css/mbr-additional.css?v=f0jscm" type="text/css">

  <style>
    .dashboard-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 30px;
    }
    .dashboard-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .dashboard-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: #ffffff;
    }
    .welcome-banner {
      background-color: #149dcc;
      color: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .card-primary {
      background: linear-gradient(45deg, #149dcc, #2fcef5);
    }
    .card-info {
      background: linear-gradient(45deg, #55b4d4, #91d7e8);
    }
    .card-danger {
      background: linear-gradient(45deg, #dc3545, #ff6b7d);
    }
    .card-secondary {
      background: linear-gradient(45deg, #6c757d, #9fa6ac);
    }
    .btn-cancel {
        background-color: #dc3545;
        color: #fff;
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }
    .btn-cancel:hover {
        background-color: #a71d2a; /* Darker red on hover */
        color: #fff;
    }
    .btn-invoice {
        background-color: #493d9e;
        color: #fff;
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }
    .btn-invoice:hover {
        background-color: #b2a5ff; /* Darker red on hover */
        color: #fff;
    }
  </style>
</head>
<body>
  
<section data-bs-version="5.1" class="menu menu2 cid-uLC4xntJah" once="menu" id="menu02-1m">
    <nav class="navbar navbar-dropdown navbar-fixed-top navbar-expand-lg">
        <div class="container">
            <div class="navbar-brand">
                <span class="navbar-logo">
                    <a href="user_dashboard.php">
                        <img src="../assets/images/cronykaraoke-1.webp" alt="Crony Karaoke Logo" style="height: 3rem;">
                    </a>
                </span>
                <span class="navbar-caption-wrap"><a class="navbar-caption text-black text-primary display-4" href="user_dashboard.php#top">Crony<br>Karaoke</a></span>
            </div>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#navbarSupportedContent" data-bs-target="#navbarSupportedContent" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav nav-dropdown ms-auto me-auto" style="margin-right:2rem;" data-app-modern-menu="true">
                    <li class="nav-item">
                        <a class="nav-link link text-black text-primary display-4" href="make_reservation.php">Book Room</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link link text-black text-primary display-4" href="user_dashboard.php#newsletter-promotions">Newsletter</a>
                    </li>
                </ul>
                <div class="navbar-buttons mbr-section-btn d-flex align-items-center gap-2">
                    <a href="mailto:helper@cronykaraoke.com" class="btn btn-link p-0" title="Email Helper">
                        <span class="mbr-iconfont mobi-mbri-letter mobi-mbri" style="font-size:1.5rem;color:#149dcc;"></span>
                    </a>
                    <a href="tel:+60165014332" class="btn btn-link p-0" title="Call Helper">
                        <span class="mbr-iconfont mobi-mbri-phone mobi-mbri" style="font-size:1.5rem;color:#149dcc;"></span>
                    </a>
                    <a class="btn btn-primary display-4 ms-2" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</section>
<section data-bs-version="5.1" class="header15 cid-uLCs2vR839" id="header18-1n" style="padding-top: 145px; padding-bottom: 30px; background: #edefeb;">	
    <div class="container">
        <div class="row justify-content-center">
            <div class="card col-12 col-lg-12" style="background: transparent; border: none;">
                <div class="card-wrapper wrap">
                    <div class="card-box align-center text-center">
                        <h1 class="card-title mbr-fonts-style mb-4 display-3" style="color: #fff;">
                            <strong>Welcome Back, </strong><?php echo htmlspecialchars($username); ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section data-bs-version="5.1" class="features6 cid-uLCcr1YAXA" id="dashboard-cards" style="padding-top: 30px; padding-bottom: 60px; background: #edefeb;">
    <div class="container">
        <div class="row">
            <!-- Profile Update Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card" style="background: linear-gradient(45deg, #493d9e, #8571ff);">
                    <div class="card-body text-center p-4">
                        <div class="dashboard-icon">
                            <span class="mbr-iconfont mobi-mbri-user mobi-mbri"></span>
                        </div>
                        <h4 class="card-title mbr-fonts-style display-7 text-white">
                            <strong>Profile Update</strong>
                        </h4>
                        <p class="card-text mbr-fonts-style display-7 text-white">
                            Update your email, phone number, and password
                        </p>
                        <div class="mbr-section-btn mt-3"> <!--dis--> 
                            <a href="profile_update.php" class="btn btn-white display-3 disabled" role="button" aria-disabled="true" style="color: #DDDDDD !important;">Update Profile</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Make Reservation Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card" style="background: linear-gradient(45deg, #493d9e, #8571ff);">
                    <div class="card-body text-center p-4">
                        <div class="dashboard-icon">
                            <span class="mbr-iconfont mobi-mbri-calendar mobi-mbri"></span>
                        </div>
                        <h4 class="card-title mbr-fonts-style display-7 text-white">
                            <strong>Make Reservation</strong>
                        </h4>
                        <p class="card-text mbr-fonts-style display-7 text-white">
                            Book a room, select date and time
                        </p>
                        <div class="mbr-section-btn mt-3">
                            <a href="make_reservation.php" class="btn btn-white display-3" style="color: #000 !important;">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cancel Reservation Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card" style="background: linear-gradient(45deg, #493d9e, #8571ff);">
                    <div class="card-body text-center p-4">
                        <div class="dashboard-icon">
                            <span class="mbr-iconfont mobi-mbri-close mobi-mbri"></span>
                        </div>
                        <h4 class="card-title mbr-fonts-style display-7 text-white">
                            <strong>Cancel Reservation</strong>
                        </h4>
                        <p class="card-text mbr-fonts-style display-7 text-white">
                            Cancel your existing bookings
                        </p>
                        <div class="mbr-section-btn mt-3">
                            <a href="cancel_reservation.php" class="btn btn-white display-3 disabled" role="button" aria-disabled="true" style="color: #DDDDDD !important;">Cancel Booking</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- View Invoice Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card" style="background: linear-gradient(45deg, #493d9e, #8571ff);">
                    <div class="card-body text-center p-4">
                        <div class="dashboard-icon">
                            <span class="mbr-iconfont mobi-mbri-file mobi-mbri"></span>
                        </div>
                        <h4 class="card-title mbr-fonts-style display-7 text-white">
                            <strong>View Invoice</strong>
                        </h4>
                        <p class="card-text mbr-fonts-style display-7 text-white">
                            See your invoice and payment details
                        </p>
                        <div class="mbr-section-btn mt-3">
                            <a href="invoice.php" class="btn btn-white display-3 disabled" role="button" aria-disabled="true" style="color: #DDDDDD !important;">View Invoice</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section data-bs-version="5.1" class="features6 cid-uLCcr1YAXA" id="upcoming-bookings" style="padding-top: 30px; padding-bottom: 90px; background: #edefeb;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                        <strong>Your Upcoming Bookings</strong>
                    </h4>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php
    include '../dbconfig.php'; // Update with your actual DB connection file
    $userID = $_SESSION['userID'];
    $today = date('Y-m-d');

    $query = "SELECT r.reservationID, rm.roomName, r.reservationDate, r.startTime, r.endTime, r.status
              FROM reservations r
              JOIN rooms rm ON r.roomID = rm.roomID
              WHERE r.userID = ? AND r.reservationDate >= ?
              ORDER BY r.reservationDate ASC, r.startTime ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userID, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $reservationID = $row['reservationID'];
        $roomName = $row['roomName'];
        $date = date("d M Y", strtotime($row['reservationDate']));
        $startTime = date("H:i", strtotime($row['startTime']));
        $duration = round((strtotime($row['endTime']) - strtotime($row['startTime'])) / 3600, 1); // in hours
        $status = ucfirst($row['status']);

        echo "<tr>
                <td>{$roomName}</td>
                <td>{$date}</td>
                <td>{$startTime}</td>
                <td>{$duration} hour(s)</td>
                <td><span class='badge' style='background-color: " . ($status == 'Confirmed' ? '#28a745' : ($status == 'Cancelled' ? '#dc3545' : '#ffc107')) . "; color: #fff;'>{$status}</span></td>
                <td>";

        if ($row['status'] != 'cancelled' && $row['reservationDate'] >= $today) {
            echo "<a href='cancel_reservation.php?id={$reservationID}' class='btn-cancel btn btn-sm me-1'>Cancel</a>";
        } else {
            echo "<span class='text-muted me-1'>N/A</span>";
        }

        echo    "<a href='view_invoice.php?id={$reservationID}' class='btn-invoice btn btn-sm me-1'>Invoice</a>
                </td>
              </tr>";
    }

    $stmt->close();
?>
</tbody>

            </table>
        </div>
    </div>
</section>

<section data-bs-version="5.1" class="features6 cid-uLCcr1YAXA" id="newsletter-promotions" style="padding-top: 50px; padding-bottom: 70px; background: #fff2af;">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-12 text-center">
                <h2 class="mbr-section-title mbr-fonts-style align-center display-2">
                    <strong>Latest Promotions & Announcements</strong>
                </h2>
                <p class="mbr-text mbr-fonts-style mb-0 display-7 text-center">Don't miss out on our exclusive deals and updates!</p>
            </div>
        </div>

        <div class="row">
            <!-- Promo 1 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card p-3 h-100 shadow" style="border-radius: 20px; background: #fff;">
                    <div class="card-body text-center">
                        <h5 class="card-title"><strong>🔥 10% Off for Early Bookings!</strong></h5>
                        <p class="card-text">Reserve your room at least 3 days in advance and enjoy 10% off your total fee.</p>
                        <span class="badge bg-success">Limited Time</span>
                    </div>
                </div>
            </div>

            <!-- Promo 2 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card p-3 h-100 shadow" style="border-radius: 20px; background: #fff;">
                    <div class="card-body text-center">
                        <h5 class="card-title"><strong>🎤 New VIP Room Added!</strong></h5>
                        <p class="card-text">Try our luxurious VIP room with enhanced sound and lighting effects at RM50/hour.</p>
                        <span class="badge bg-warning text-dark">Ended</span>
                    </div>
                </div>
            </div>

            <!-- Promo 3 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card p-3 h-100 shadow" style="border-radius: 20px; background: #fff;">
                    <div class="card-body text-center">
                        <h5 class="card-title"><strong>📢 Refer & Earn!</strong></h5>
                        <p class="card-text">Refer a friend and both of you will receive RM5 promo code send to your email.</p>
                        <span class="badge bg-primary">Ongoing</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card p-4 shadow" style="border-radius: 20px; background:hsl(0, 0.00%, 100.00%);">
                    <div class="card-body text-center">
                        <h5 class="card-title"><strong>📍 We're Expanding!</strong></h5>
                        <p class="card-text">Exciting news! We're opening soon in <strong>Penang</strong> and <strong>Johor</strong>. Stay tuned for updates and launch promos!</p>
                        <span class="badge bg-info text-dark">Coming Soon</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- <footer style="background:#493d9e; padding-top: 20px; padding-bottom: 20px;">
    <div class="container text-center">
        <a href="../logout.php" class="btn btn-light btn-sm">Logout</a>
        <p class="mbr-fonts-style mb-0 mt-2" style="color:#fff; font-size:0.9rem;">
            © 2025 Crony Karaoke
        </p>
    </div>
</footer> -->

<section data-bs-version="5.1" class="footer3 cid-uLCpCfgtNL" once="footers" id="footer03-22" style="padding-top: 40px; padding-bottom: 0px;">

    <div class="container">
        <div class="row">
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <div class="container text-center">
                    <a href="../logout.php" class="btn btn-light btn-sm">Logout</a>
                    </div>
            <div class="col-12 mt-5">
                <p class="mbr-fonts-style copyright display-8">
                    © Copyright 2025 Crony Karaoke - All Rights Reserved
                </p>
            </div>
        </div>
    </div>
</section>

  <script src="../assets/web/assets/jquery/jquery.min.js"></script>
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/smoothscroll/smooth-scroll.js"></script>
  <script src="../assets/dropdown/js/script.min.js"></script>
  <script src="../assets/touchswipe/jquery.touch-swipe.min.js"></script>
  <script src="../assets/theme/js/script.js"></script>
  <script src="../assets/formoid/formoid.min.js"></script>

  <input name="animation" type="hidden">
</body>
</html>