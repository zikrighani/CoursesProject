<?php
include '../dbconfig.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = '';
$messageType = '';

// Fetch current user data
$query = "SELECT * FROM users WHERE userID = '$userID'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: ../login.php");
    exit();
}

// Get user's total bookings count
$bookingQuery = "SELECT COUNT(*) as total_bookings FROM reservations WHERE userID = '$userID'";
$bookingResult = mysqli_query($conn, $bookingQuery);
$bookingData = mysqli_fetch_assoc($bookingResult);
$totalBookings = $bookingData['total_bookings'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = mysqli_real_escape_string($conn, trim($_POST['fullName']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $_POST['phone'])); // Remove non-numeric characters
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validation
    $errors = array();
    
    if (empty($fullName)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($phone) || strlen($phone) < 10) {
        $errors[] = "Valid phone number is required";
    }
    
    // Check if email already exists for other users
    $emailCheckQuery = "SELECT userID FROM users WHERE email = '$email' AND userID != '$userID'";
    $emailCheckResult = mysqli_query($conn, $emailCheckQuery);
    if (mysqli_num_rows($emailCheckResult) > 0) {
        $errors[] = "Email address is already taken by another user";
    }
    
    // Password validation if provided
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) {
            $errors[] = "Current password is required to change password";
        } else {
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }
        
        if (empty($newPassword)) {
            $errors[] = "New password is required";
        } elseif (strlen($newPassword) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match";
        }
    }
    
    if (empty($errors)) {
        // Update user information
        $updateQuery = "UPDATE users SET fullName = '$fullName', email = '$email', phone = '$phone'";
        
        // Add password update if provided
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery .= ", password = '$hashedPassword'";
        }
        
        $updateQuery .= " WHERE userID = '$userID'";
        
        if (mysqli_query($conn, $updateQuery)) {
            // Update session data if needed
            $_SESSION['fullName'] = $fullName;
            $_SESSION['email'] = $email;
            
            // Refresh user data
            $query = "SELECT * FROM users WHERE userID = '$userID'";
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
            
            $message = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating profile: " . mysqli_error($conn);
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}

// Format phone number for display
function formatPhoneNumber($phone) {
    if (strlen($phone) >= 10) {
        // Assuming Malaysian format
        if (substr($phone, 0, 2) == '60') {
            return '+' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . '-' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        } else {
            return '+60 ' . substr($phone, 1, 2) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }
    }
    return $phone;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="Crony Karaoke - Profile Update">

  <title>Profile Update - Crony Karaoke</title>
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
    .profile-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .profile-header {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      padding: 30px;
      text-align: center;
    }
    .profile-content {
      padding: 40px;
    }
    .info-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 25px;
      margin-bottom: 30px;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #e9ecef;
    }
    .info-row:last-child {
      border-bottom: none;
    }
    .info-label {
      font-weight: 600;
      color: #495057;
    }
    .info-value {
      color: #212529;
    }
    .form-section {
      margin-bottom: 35px;
    }
    .form-section h3 {
      color: #493d9e;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e9ecef;
    }
    .form-control {
      border: 1px solid #ced4da;
      border-radius: 6px;
      padding: 12px 15px;
      font-size: 14px;
    }
    .form-control:focus {
      border-color: #493d9e;
      box-shadow: 0 0 0 0.2rem rgba(73, 61, 158, 0.25);
    }
    .btn-primary {
      background: #493d9e;
      border-color: #493d9e;
      padding: 12px 30px;
      border-radius: 6px;
      font-weight: 500;
    }
    .btn-primary:hover {
      background: #3d3486;
      border-color: #3d3486;
    }
    .btn-secondary {
      background: #6c757d;
      border-color: #6c757d;
      padding: 12px 30px;
      border-radius: 6px;
      font-weight: 500;
    }
    .btn-outline-secondary {
      border-color: #6c757d;
      color: #6c757d;
      padding: 12px 30px;
      border-radius: 6px;
      font-weight: 500;
    }
    .alert {
      border-radius: 8px;
      border: none;
      padding: 15px 20px;
    }
    .password-note {
      background: #e7f3ff;
      border: 1px solid #bee5eb;
      border-radius: 6px;
      padding: 15px;
      margin-bottom: 20px;
    }
    .form-buttons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid #e9ecef;
    }
  </style>
</head>
<body>
  
<section data-bs-version="5.1" class="menu menu2 cid-uLC4xntJah" once="menu" id="menu02-1m">
    <nav class="navbar navbar-dropdown navbar-fixed-top navbar-expand-lg">
        <div class="container">
            <div class="navbar-brand">
                <span class="navbar-logo">
                    <a href="user_home.php">
                        <img src="../assets/images/cronykaraoke-1.webp" alt="Crony Karaoke Logo" style="height: 3rem;">
                    </a>
                </span>
                <span class="navbar-caption-wrap"><a class="navbar-caption text-black text-primary display-4" href="user_home.php">Crony<br>Karaoke</a></span>
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
                        <a class="nav-link link text-black text-primary display-4" href="user_home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link link text-black text-primary display-4" href="make_reservation.php">Book Room</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link link text-black text-primary display-4" href="user_home.php#newsletter-promotions">Newsletter</a>
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

<section data-bs-version="5.1" id="profile-update-form" style="padding-top: 150px; padding-bottom: 90px; background: #edefeb;">
    <div class="container">
        <!-- Success/Error Messages -->
        <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <strong><?php echo $messageType == 'success' ? 'Success!' : 'Error!'; ?></strong> <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="profile-container">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <h1 class="mb-2">Update Profile</h1>
                        <p class="mb-0">Keep your information up to date</p>
                    </div>

                    <div class="profile-content">
                        <!-- Current Information Display -->
                        <div class="info-section">
                            <h3>Current Information</h3>
                            <div class="info-row">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['fullName']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email Address</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value"><?php echo formatPhoneNumber($user['phone']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total Bookings</span>
                                <span class="info-value"><?php echo $totalBookings; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Member Since</span>
                                <span class="info-value"><?php echo date('M Y', strtotime($user['createdAt'])); ?></span>
                            </div>
                        </div>

                        <!-- Update Form -->
                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Personal Information Section -->
                            <div class="form-section">
                                <h3>Personal Information</h3>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fullName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['fullName']); ?>" required>
                                        <div class="invalid-feedback">Please provide a valid full name.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        <div class="invalid-feedback">Please provide a valid email address.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                        <div class="invalid-feedback">Please provide a valid Malaysian phone number.</div>
                                        <div class="form-text">Format: +60123456789 or 0123456789</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Section -->
                            <div class="form-section">
                                <h3>Security Settings</h3>
                                <div class="password-note">
                                    <small><strong>Note:</strong> Leave password fields blank if you don't want to change your password.</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="currentPassword" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Enter current password">
                                        <div class="form-text">Required only if changing password</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter new password" minlength="8">
                                        <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                                        <div class="invalid-feedback">Passwords do not match.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-buttons">
                                <a href="user_home.php" class="btn btn-secondary">
                                    <span class="mbr-iconfont mobi-mbri-arrow-prev mobi-mbri me-2"></span>
                                    Back to Home
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-secondary me-2">
                                        <span class="mbr-iconfont mobi-mbri-refresh mobi-mbri me-2"></span>
                                        Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="mbr-iconfont mobi-mbri-save mobi-mbri me-2"></span>
                                        Update Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section data-bs-version="5.1" class="footer3 cid-uLCpCfgtNL" once="footers" id="footer03-22" style="padding-top: 40px; padding-bottom: 0px;">
    <div class="container">
        <div class="row">
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <div class="container text-center">
                        <a href="user_home.php" class="btn btn-light btn-sm">Back to Home</a>
                        <a href="../logout.php" class="btn btn-light btn-sm">Logout</a>
                    </div>
                <div class="col-12 mt-5">
                    <p class="mbr-fonts-style copyright display-8">
                        © Copyright 2025 Crony Karaoke - All Rights Reserved
                    </p>
                </div>
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
    // Form validation
    (function () {
        'use strict'
        
        var forms = document.querySelectorAll('.needs-validation')
        
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

    // Password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', function() {
        var newPassword = document.getElementById('newPassword').value
        var confirmPassword = this.value
        
        if (newPassword !== confirmPassword) {
            this.setCustomValidity('Passwords do not match')
        } else {
            this.setCustomValidity('')
        }
    })

    // Phone number formatting
    document.getElementById('phone').addEventListener('input', function(e) {
        var value = e.target.value.replace(/\D/g, '')
        
        // Handle Malaysian phone numbers
        if (value.startsWith('60')) {
            // Already has country code
            e.target.value = value
        } else if (value.startsWith('0')) {
            // Local number, add country code
            e.target.value = '6' + value
        } else if (value.length > 0) {
            // Assume it's missing the leading 0
            e.target.value = '601' + value
        }
    })

    // Auto-hide alert messages after 5 seconds
    <?php if (!empty($message)): ?>
    setTimeout(function() {
        var alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
    <?php endif; ?>
  </script>

  <input name="animation" type="hidden">
</body>
</html>