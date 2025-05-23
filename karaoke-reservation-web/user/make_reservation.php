<?php

include '../dbconfig.php'; // your database connection
session_start();

$username = $_SESSION['fullName'];

// Get available rooms
$roomsQuery = "SELECT * FROM rooms WHERE status = 'available' ORDER BY roomName";
$roomsResult = mysqli_query($conn, $roomsQuery);

// Handle form submission
if(isset($_POST['submit'])) {
    $roomId = $_POST['room'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = $_POST['duration'];
    $addons = isset($_POST['addons']) ? $_POST['addons'] : [];
    
    // Add reservation to database
    // This is just a placeholder - you would need to implement the actual reservation logic
    
    // Redirect to confirmation page or refresh with success message
    $_SESSION['success_message'] = "Reservation successfully booked!";
    header("Location: user_dashboard.php");
    exit();
}

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
  <meta name="description" content="Crony Karaoke - Make a Reservation">

  <title>Book a Room - Crony Karaoke</title>
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
    .reservation-form {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .room-card {
      border-radius: 10px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 20px;
      cursor: pointer;
    }
    .room-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .room-card.selected {
      border: 3px solid #493d9e;
    }
    .room-image {
      height: 180px;
      background-size: cover;
      background-position: center;
    }
    .room-details {
      padding: 15px;
    }
    .summary-card {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      border-radius: 10px;
      padding: 20px;
      height: 100%;
    }
    .form-section {
      margin-bottom: 30px;
    }
    .addon-item {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
    }
    .addon-item:hover {
      background: #e9ecef;
    }
    .addon-item.selected {
      background: #e0e7ff;
      border: 1px solid #493d9e;
    }
    .form-label {
      font-weight: 600;
      color: #333;
    }
    .date-time-section {
      background: #f9f9f9;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .btn-primary {
      background: #493d9e;
      border-color: #493d9e;
    }
    .btn-primary:hover {
      background: #3d3486;
      border-color: #3d3486;
    }
    .page-header {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      padding: 60px 0;
      margin-bottom: 40px;
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
                <span class="navbar-caption-wrap"><a class="navbar-caption text-black text-primary display-4" href="user_dashboard.php">Crony<br>Karaoke</a></span>
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
                        <a class="nav-link link text-black text-primary display-4" href="user_dashboard.php">Dashboard</a>
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

<section class="page-header" style="padding-top: 150px; padding-bottom: 40px; margin-bottom: 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-3 fw-bold">Book Your Karaoke Room</h1>
                <p class="lead">Select your preferred room, date, and time to reserve your spot</p>
            </div>
        </div>
    </div>
</section>

<section data-bs-version="5.1" id="reservation-form" style="padding-top: 50px; padding-bottom: 90px; background: #edefeb;">
    <div class="container">
        <form method="post" action="" class="needs-validation" novalidate>
            <!-- Step 1: Choose Room -->
            <div class="form-section">
                <h2 class="text-center mb-4"><strong>Step 1: Choose Your Room</strong></h2>
                <div class="row">
                    <!-- Room 1: Standard Room -->
                    <div class="col-md-4">
                        <div class="room-card" onclick="selectRoom('standard')">
                            <div class="room-image" style="background-image: url('../assets/images/standard-room.png');"></div>
                            <div class="room-details" style="background:rgb(247, 243, 248);">
                                <h4><strong>Standard Room</strong></h4>
                                <p>Perfect for small groups of 2-4 people</p>
                                <ul>
                                    <li>Basic sound system</li>
                                    <li>2 microphones</li>
                                    <li>40" TV screen</li>
                                </ul>
                                <h5 class="text-primary">RM 40/hour</h5>
                                <div class="form-check mt-3">
                                    <input class="form-check-input room-select" type="radio" name="room" id="standard-room" value="standard" required>
                                    <label class="form-check-label" for="standard-room">
                                        Select Room
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Room 2: Deluxe Room -->
                    <div class="col-md-4">
                        <div class="room-card" onclick="selectRoom('deluxe')">
                            <div class="room-image" style="background-image: url('../assets/images/deluxe-room.png');"></div>
                            <div class="room-details" style="background:rgb(247, 243, 248);">
                                <h4><strong>Deluxe Room</strong></h4>
                                <p>Great for medium groups of 5-8 people</p>
                                <ul>
                                    <li>Enhanced sound system</li>
                                    <li>4 microphones</li>
                                    <li>50" TV screen</li>
                                </ul>
                                <h5 class="text-primary">RM 65/hour</h5>
                                <div class="form-check mt-3">
                                    <input class="form-check-input room-select" type="radio" name="room" id="deluxe-room" value="deluxe" required>
                                    <label class="form-check-label" for="deluxe-room">
                                        Select Room
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Room 3: VIP Room -->
                    <div class="col-md-4">
                        <div class="room-card" onclick="selectRoom('vip')">
                            <div class="room-image" style="background-image: url('../assets/images/vip-room.png');"></div>
                            <div class="room-details" style="background:rgb(247, 243, 248);">
                                <h4><strong>VIP Room</strong></h4>
                                <p>Luxury experience for 8-12 people</p>
                                <ul>
                                    <li>Premium sound system</li>
                                    <li>6 microphones</li>
                                    <li>65" TV screen</li>
                                </ul>
                                <h5 class="text-primary">RM 99/hour</h5>
                                <div class="form-check mt-3">
                                    <input class="form-check-input room-select" type="radio" name="room" id="vip-room" value="vip" required>
                                    <label class="form-check-label" for="vip-room">
                                        Select Room
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 2: Choose Date and Time -->
            <div class="form-section date-time-section mt-5">
                <h2 class="text-center mb-4"><strong>Step 2: Select Date & Time</strong></h2>
                <div class="row">
                    <div class="row-md-4 d-flex align-items-stretch">
                        <div class="mb-3 w-100">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                            <div class="invalid-feedback">
                                Please select a valid date.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-stretch">
                        <div class="mb-3 w-100">
                            <label for="time" class="form-label">Time</label>
                            <select class="form-select" id="time" name="time" required>
                                <option value="" selected disabled>Select time</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="20:00">8:00 PM</option>
                                <option value="21:00">9:00 PM</option>
                                <option value="22:00">10:00 PM</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a time.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-stretch">
                        <div class="mb-3 w-100">
                            <label for="duration" class="form-label">Duration (hours)</label>
                            <select class="form-select" id="duration" name="duration" required>
                                <option value="" selected disabled>Select duration</option>
                                <option value="1">1 hour</option>
                                <option value="2">2 hours</option>
                                <option value="3">3 hours</option>
                                <option value="4">4 hours</option>
                                <option value="5">5 hours</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select duration.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 3: Choose Add-ons -->
            <!--<div class="form-section mt-5">
                <h2 class="text-center mb-4"><strong>Step 3: Select Add-ons (Optional)</strong></h2>
                <div class="row">
                    // Food Platter
                    <div class="col-md-4">
                        <div class="addon-item">
                            <div class="form-check">
                                <input class="form-check-input addon-select" type="checkbox" name="addons[]" id="food-platter" value="food-platter">
                                <label class="form-check-label" for="food-platter">
                                    <h5><strong>Food Platter</strong></h5>
                                    <p>Assorted finger foods and snacks</p>
                                    <p class="text-primary">+ RM 45</p>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    // Drink Package
                    <div class="col-md-4">
                        <div class="addon-item">
                            <div class="form-check">
                                <input class="form-check-input addon-select" type="checkbox" name="addons[]" id="drink-package" value="drink-package">
                                <label class="form-check-label" for="drink-package">
                                    <h5><strong>Drink Package</strong></h5>
                                    <p>Unlimited soft drinks for your session</p>
                                    <p class="text-primary">+ RM 35</p>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    // Extra Microphone
                    <div class="col-md-4">
                        <div class="addon-item">
                            <div class="form-check">
                                <input class="form-check-input addon-select" type="checkbox" name="addons[]" id="extra-mic" value="extra-mic">
                                <label class="form-check-label" for="extra-mic">
                                    <h5><strong>Extra Microphone</strong></h5>
                                    <p>Additional microphone for larger groups</p>
                                    <p class="text-primary">+ RM 10 each</p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>-->
            
            <!-- Step 4: Booking Summary -->
            <div class="row mt-5">
                <div class="col-md-8">
                    <div class="form-section">
                        <h2 class="mb-4"><strong>Step 4: Additional Information</strong></h2>
                        <div class="mb-3">
                            <label for="special-requests" class="form-label">Special Requests (Optional)</label>
                            <textarea class="form-control" id="special-requests" name="special_requests" rows="4" placeholder="Any special requests or arrangements..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card d-flex flex-column align-items-center">
                        <h3 class="mb-4 text-white"><strong>Booking Summary</strong></h3>
                        <div id="summary-details" class="w-100">
                            <p><strong>Room:</strong> <span id="summary-room">Not selected</span></p>
                            <p><strong>Date:</strong> <span id="summary-date">Not selected</span></p>
                            <p><strong>Time:</strong> <span id="summary-time">Not selected</span></p>
                            <p><strong>Duration:</strong> <span id="summary-duration">Not selected</span></p>
                            <p><strong>Add-ons:</strong> <span id="summary-addons">None</span></p>
                            <hr>
                            <h4 class="text-white"><strong>Total: <span id="summary-total">RM 0.00</span></strong></h4>
                        </div>
                        <button type="submit" name="submit" formaction="simulate_payment.php" class="btn btn-light btn-lg mt-4" style="width: 70%; display: block; margin-left: auto; margin-right: auto;">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<section data-bs-version="5.1" class="footer3 cid-uLCpCfgtNL" once="footers" id="footer03-22" style="padding-top: 40px; padding-bottom: 0px;">

    <div class="container">
        <div class="row">
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <div class="container text-center">
        <a href="user_dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
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

  <script>
    // Room selection
    function selectRoom(roomType) {
        // Clear all selections
        document.querySelectorAll('.room-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Select the clicked room
        document.getElementById(roomType + '-room').checked = true;
        document.getElementById(roomType + '-room').closest('.room-card').classList.add('selected');
        
        // Update summary
        updateSummary();
    }
    
    // Add-on selection
    document.querySelectorAll('.addon-select').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if(this.checked) {
                this.closest('.addon-item').classList.add('selected');
            } else {
                this.closest('.addon-item').classList.remove('selected');
            }
            updateSummary();
        });
    });
    
    // Date, time and duration change
    document.getElementById('date').addEventListener('change', updateSummary);
    document.getElementById('time').addEventListener('change', updateSummary);
    document.getElementById('duration').addEventListener('change', updateSummary);
    
    // Update booking summary
    function updateSummary() {
        // Room
        let roomElement = document.querySelector('input[name="room"]:checked');
        let roomName = "Not selected";
        let roomPrice = 0;
        
        if(roomElement) {
            switch(roomElement.value) {
                case 'standard':
                    roomName = "Standard Room";
                    roomPrice = 40;
                    break;
                case 'deluxe':
                    roomName = "Deluxe Room";
                    roomPrice = 65;
                    break;
                case 'vip':
                    roomName = "VIP Room";
                    roomPrice = 95;
                    break;
            }
        }
        
        document.getElementById('summary-room').textContent = roomName;
        
        // Date
        let date = document.getElementById('date').value;
        document.getElementById('summary-date').textContent = date ? new Date(date).toLocaleDateString() : "Not selected";
        
        // Time
        let time = document.getElementById('time').value;
        document.getElementById('summary-time').textContent = time ? time : "Not selected";
        
        // Duration
        let duration = document.getElementById('duration').value;
        document.getElementById('summary-duration').textContent = duration ? duration + " hour(s)" : "Not selected";
        
        // Add-ons
        let addons = [];
        let addonsTotal = 0;
        
        document.querySelectorAll('input[name="addons[]"]:checked').forEach(addon => {
            switch(addon.value) {
                case 'food-platter':
                    addons.push("Food Platter (RM 45)");
                    addonsTotal += 45;
                    break;
                case 'drink-package':
                    addons.push("Drink Package (RM 35)");
                    addonsTotal += 35;
                    break;
                case 'extra-mic':
                    addons.push("Extra Microphone (RM 10)");
                    addonsTotal += 10;
                    break;
            }
        });
        
        document.getElementById('summary-addons').textContent = addons.length > 0 ? addons.join(", ") : "None";
        
        // Total
        let total = duration && roomPrice ? (roomPrice * duration) + addonsTotal : 0;
        document.getElementById('summary-total').textContent = "RM " + total.toFixed(2);
    }
    
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all forms we want to apply validation to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
  </script>

  <input name="animation" type="hidden">
</body>
</html>