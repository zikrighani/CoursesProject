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
$query = "SELECT * FROM users WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: ../login.php");
    exit();
}

// Get user's total bookings count
$bookingQuery = "SELECT COUNT(*) as total_bookings FROM reservations WHERE userID = ?";
$bookingStmt = $conn->prepare($bookingQuery);
$bookingStmt->bind_param("i", $userID);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();
$bookingData = $bookingResult->fetch_assoc();
$totalBookings = $bookingData['total_bookings'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = trim($_POST['fullName']);
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validation
    $errors = array();
    
    if (empty($fullName)) {
        $errors[] = "Full name is required";
    }
    
    // Password validation if provided
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) {
            $errors[] = "Current password is required to change password";
        } else {
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
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET fullName = ?, password = ? WHERE userID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssi", $fullName, $hashedPassword, $userID);
        } else {
            $updateQuery = "UPDATE users SET fullName = ? WHERE userID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $fullName, $userID);
        }
        
        if ($updateStmt->execute()) {
            // Update session data
            $_SESSION['fullName'] = $fullName;
            
            // Refresh user data
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            $message = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating profile";
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="description" content="Crony Karaoke - Profile Update">
  <title>Profile Update - Crony Karaoke</title>
  
  <!-- Favicon -->
  <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
  
  <!-- External Stylesheets -->
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/animatecss/animate.css">
  <link rel="stylesheet" href="../assets/theme/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>

  <style>
    /* Global Styles */
    :root {
      --primary-color: #7c6cff;
      --primary-light: #afa5f5;
      --bg-dark: #000;
      --bg-card: #181818;
      --text-white: #fff;
      --text-gray: #bbb;
      --text-dark-gray: #999;
      --border-color: rgba(255, 255, 255, 0.1);
      --shadow: 0 2px 8px rgba(0,0,0,0.08);
      --error-color: #ff3860;
      --success-color: #23d160;
      --warning-color: #ffdd57;
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-white);
      font-family: 'Inter Tight', sans-serif;
      min-height: 100vh;
    }

    /* Navigation */
    .navbar {
      background: rgba(0,0,0,0.95) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 0;
    }

    .navbar-brand {
      font-weight: 700;
      color: var(--text-white) !important;
      display: flex;
      align-items: center;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .navbar-nav .nav-link {
      color: var(--text-white) !important;
      font-weight: 500;
      margin: 0 10px;
      transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
      color: var(--primary-color) !important;
    }

    .btn-logout {
      background: linear-gradient(135deg, var(--error-color), #ff6b7d);
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-logout:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 56, 96, 0.3);
      color: white;
      text-decoration: none;
    }

    /* Main Content */
    .main-content {
      padding: 120px 0 80px 0;
      min-height: calc(100vh - 200px);
    }

    .welcome-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .welcome-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--text-white);
      margin-bottom: 0.5rem;
    }

    .welcome-subtitle {
      font-size: 1.1rem;
      color: var(--text-gray);
      margin-bottom: 2rem;
    }

    /* Profile Container */
    .table-container {
      background: var(--bg-card);
      border-radius: 16px;
      border: 1px solid var(--border-color);
      overflow: hidden;
      margin-bottom: 3rem;
    }

    .profile-section {
      padding: 2rem;
    }

    .section-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-white);
      margin-bottom: 2rem;
    }

    /* Current Info Display */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .info-item {
      background: rgba(255, 255, 255, 0.03);
      padding: 1rem;
      border-radius: 8px;
      border: 1px solid var(--border-color);
    }

    .info-label {
      font-size: 0.85rem;
      color: var(--text-gray);
      margin-bottom: 0.25rem;
      text-transform: uppercase;
      font-weight: 600;
    }

    .info-value {
      color: var(--text-white);
      font-weight: 500;
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      color: var(--text-white);
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: block;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      color: var(--text-white);
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(124, 108, 255, 0.25);
      color: var(--text-white);
    }

    .form-control:disabled {
      background: rgba(255, 255, 255, 0.02);
      border-color: var(--border-color);
      color: var(--text-gray);
      cursor: not-allowed;
    }

    .form-control::placeholder {
      color: var(--text-dark-gray);
    }

    .form-text {
      color: var(--text-gray);
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    /* Locked Field */
    .locked-field {
      position: relative;
    }

    .lock-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-gray);
      font-size: 0.9rem;
    }

    .locked-field .form-control {
      padding-right: 40px;
    }

    /* Buttons */
    .card-button {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
    }

    .card-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(124, 108, 255, 0.3);
      color: white;
      text-decoration: none;
    }

    .btn-secondary {
      background: var(--text-dark-gray);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      background: var(--text-gray);
      color: white;
      text-decoration: none;
    }

    .btn-outline-secondary {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-gray);
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-white);
      text-decoration: none;
    }

    .btn-warning {
      background: linear-gradient(135deg, var(--warning-color), #ffeb3b);
      color: #333;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 600;
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .btn-warning:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(255, 221, 87, 0.3);
      color: #333;
      text-decoration: none;
    }

    /* Alerts */
    .alert {
      border-radius: 8px;
      border: none;
      padding: 1rem 1.25rem;
      margin-bottom: 2rem;
    }

    .alert-success {
      background: rgba(35, 209, 96, 0.1);
      color: var(--success-color);
      border: 1px solid rgba(35, 209, 96, 0.2);
    }

    .alert-danger {
      background: rgba(255, 56, 96, 0.1);
      color: var(--error-color);
      border: 1px solid rgba(255, 56, 96, 0.2);
    }

    /* Password Note */
    .password-note {
      background: rgba(124, 108, 255, 0.1);
      border: 1px solid rgba(124, 108, 255, 0.2);
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .password-note small {
      color: var(--primary-light);
    }

    /* Form Actions */
    .form-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
      margin-top: 2rem;
    }

    /* Footer */
    .footer {
      background: var(--bg-dark);
      color: var(--text-gray);
      padding: 40px 0 20px 0;
      border-top: 1px solid var(--border-color);
      margin-top: auto;
      text-align: center;
    }

    .footer-info {
      font-size: 0.95rem;
    }

    .footer-info a {
      color: var(--primary-color);
      text-decoration: none;
    }

    .footer-info a:hover {
      text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .welcome-title {
        font-size: 2rem;
      }
      
      .profile-section {
        padding: 1.5rem;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
      
      .form-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
      
      .main-content {
        padding: 100px 0 60px 0;
      }
    }

    @media (max-width: 576px) {
      .welcome-title {
        font-size: 1.75rem;
      }
      
      .profile-section {
        padding: 1.25rem;
      }
    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="user_home.php">
        <img src="../assets/images/cronykaraoke-1.webp" alt="Crony Karaoke Logo">
        <span>Crony Karaoke</span>
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto me-auto">
          <li class="nav-item">
            <a class="nav-link" href="user_home.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="make_reservation.php">Book Room</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mailto:helper@cronykaraoke.com">Support</a>
          </li>
        </ul>
        <a class="btn-logout" href="../logout.php">
          <i class="fas fa-sign-out-alt me-1"></i>
          Logout
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <!-- Welcome Header -->
      <div class="welcome-header">
        <h1 class="welcome-title">Update Profile</h1>
        <p class="welcome-subtitle">Keep your information up to date</p>
      </div>

      <!-- Success/Error Messages -->
      <?php if (!empty($message)): ?>
      <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-danger'; ?>" role="alert">
        <strong><?php echo $messageType == 'success' ? 'Success!' : 'Error!'; ?></strong> <?php echo $message; ?>
      </div>
      <?php endif; ?>

      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="table-container">
            <!-- Current Information -->
            <div class="profile-section">
              <h3 class="section-title">Current Information</h3>
              <div class="info-grid">
                <div class="info-item">
                  <div class="info-label">Full Name</div>
                  <div class="info-value"><?php echo htmlspecialchars($user['fullName']); ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">Email Address</div>
                  <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">Phone Number</div>
                  <div class="info-value"><?php echo formatPhoneNumber($user['phone']); ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">Total Bookings</div>
                  <div class="info-value"><?php echo $totalBookings; ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">Member Since</div>
                  <div class="info-value"><?php echo date('M Y', strtotime($user['createdAt'])); ?></div>
                </div>
              </div>
            </div>

            <!-- Update Form -->
            <div class="profile-section">
              <h3 class="section-title">Update Information</h3>
              
              <form method="POST">
                <!-- Personal Information -->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="fullName" class="form-label">Full Name</label>
                      <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['fullName']); ?>" required>
                    </div>
                  </div>
                </div>

                <!-- Locked Fields -->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email" class="form-label">Email Address</label>
                      <div class="locked-field">
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <i class="fas fa-lock lock-icon"></i>
                      </div>
                      <div class="form-text">Email changes require verification</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="phone" class="form-label">Phone Number</label>
                      <div class="locked-field">
                        <input type="tel" class="form-control" id="phone" value="<?php echo formatPhoneNumber($user['phone']); ?>" disabled>
                        <i class="fas fa-lock lock-icon"></i>
                      </div>
                      <div class="form-text">Phone changes require verification</div>
                    </div>
                  </div>
                </div>

                <!-- Request Change Button -->
                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <a href="request_change.php" class="btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Request Email/Phone Change
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Security Settings -->
                <h4 class="section-title">Security Settings</h4>
                <div class="password-note">
                  <small><strong>Note:</strong> Leave password fields blank if you don't want to change your password.</small>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="currentPassword" class="form-label">Current Password</label>
                      <input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Enter current password">
                      <div class="form-text">Required only if changing password</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="newPassword" class="form-label">New Password</label>
                      <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter new password" minlength="8">
                      <div class="form-text">At least 8 characters</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="confirmPassword" class="form-label">Confirm New Password</label>
                      <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                  <a href="user_home.php" class="btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Home
                  </a>
                  <div>
                    <button type="reset" class="btn-outline-secondary me-2">
                      <i class="fas fa-undo me-2"></i>
                      Reset
                    </button>
                    <button type="submit" class="card-button">
                      <i class="fas fa-save me-2"></i>
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
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-info">
        <p class="mb-1">© 2025 Crony Karaoke — Sing. Laugh. Repeat.</p>
        <p class="mb-1">Level 2, Lot 18, Plaza Sentral, Kuala Lumpur, Malaysia</p>
        <p class="mb-0">
          <a href="mailto:kl_info@cronykaraoke.com">kl_info@cronykaraoke.com</a>
        </p>
        <p class="mb-0">Powered by CronyTech</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="../assets/web/assets/jquery/jquery.min.js"></script>
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/smoothscroll/smooth-scroll.js"></script>
  <script src="../assets/theme/js/script.js"></script>

  <script>
    // Password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', function() {
      var newPassword = document.getElementById('newPassword').value;
      var confirmPassword = this.value;
      
      if (newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
      } else {
        this.setCustomValidity('');
      }
    });

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
</body>
</html>