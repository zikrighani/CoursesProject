<?php
// login.php

// --- START: Session INI settings (MUST be BEFORE session_start()) ---
// Set session cookie to be accessible only via HTTP (prevents JavaScript access)
ini_set('session.cookie_httponly', 1);

// Set session cookie to be sent only over secure (HTTPS) connections
// IMPORTANT: Only uncomment this if your site is running entirely on HTTPS!
// For development on XAMPP (HTTP), keep this commented out to avoid session issues.
// ini_set('session.cookie_secure', 1);

// Enable strict session mode (prevents session fixation attacks)
ini_set('session.use_strict_mode', 1);
// --- END: Session INI settings ---

// Start session
// This should be the first session-related function call after ini_set()
session_start();

// Regenerate session ID for security (should happen once at session start or successful login)
// If this page is loaded with an existing session (e.g., user is already logged in),
// regenerating it here prevents session fixation attacks.
session_regenerate_id(true);

include 'dbconfig.php'; // your database connection

// Check if user is already logged in and redirect
if (isset($_SESSION['userID']) && isset($_SESSION['role'])) {
    // Redirect based on existing role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/admin_dashboard.php");
    } else {
        header("Location: user/user_home.php");
    }
    exit();
}

// Rate limiting variables (simple implementation)
$max_attempts = 5;
$lockout_time = 900; // 15 minutes in seconds

// Initialize error variable
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs and sanitize
    $email = trim($_POST['email']);
    $password = $_POST['password']; // plain text from form

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Use a consistent key for rate limiting based on email
        // Using `md5($email)` helps obscure the email in session keys
        $attempt_key = 'login_attempts_' . md5($email);
        $lockout_key = 'lockout_until_' . md5($email);
        
        // Check if user is locked out
        if (isset($_SESSION[$lockout_key]) && $_SESSION[$lockout_key] > time()) {
            $remaining_time = $_SESSION[$lockout_key] - time();
            $minutes = ceil($remaining_time / 60);
            $error = "Too many failed attempts. Please try again in $minutes minutes.";
        } else {
            // Clear lockout if time has passed
            if (isset($_SESSION[$lockout_key]) && $_SESSION[$lockout_key] <= time()) {
                unset($_SESSION[$lockout_key]);
                unset($_SESSION[$attempt_key]);
            }
            
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT userID, fullName, password, role FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    
                    // Verify password (assuming password_hash stored)
                    if (password_verify($password, $user['password'])) {
                        // Login success: clear any failed attempts
                        unset($_SESSION[$attempt_key]);
                        unset($_SESSION[$lockout_key]);
                        
                        // Regenerate session ID again after successful login
                        // This helps prevent session fixation, even if a new one was just generated above.
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['userID'] = $user['userID'];
                        $_SESSION['fullName'] = $user['fullName'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['login_time'] = time();
                        $_SESSION['last_activity'] = time();
                        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; // Use null coalescing for safety
                        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                        
                        // Redirect based on role
                        if ($user['role'] == 'admin') {
                            header("Location: admin/admin_dashboard.php");
                        } else {
                            header("Location: user/user_home.php");
                        }
                        exit();
                    } else {
                        // Password incorrect - increment failed attempts
                        $attempts = isset($_SESSION[$attempt_key]) ? $_SESSION[$attempt_key] : 0;
                        $attempts++;
                        $_SESSION[$attempt_key] = $attempts;
                        
                        if ($attempts >= $max_attempts) {
                            $_SESSION[$lockout_key] = time() + $lockout_time;
                            $error = "Too many failed attempts. Account locked for 15 minutes.";
                        } else {
                            $remaining = $max_attempts - $attempts;
                            $error = "Incorrect email or password. You have $remaining attempts remaining.";
                        }
                    }
                } else {
                    // User not found - still increment attempts to prevent email enumeration
                    $attempts = isset($_SESSION[$attempt_key]) ? $_SESSION[$attempt_key] : 0;
                    $attempts++;
                    $_SESSION[$attempt_key] = $attempts;
                    
                    if ($attempts >= $max_attempts) {
                        $_SESSION[$lockout_key] = time() + $lockout_time;
                        $error = "Too many failed attempts. Account locked for 15 minutes.";
                    } else {
                        $remaining = $max_attempts - $attempts;
                        // Generic error message to prevent revealing if email exists
                        $error = "Incorrect email or password. You have $remaining attempts remaining.";
                    }
                }
                
                $stmt->close();
            } else {
                $error = "Database query error. Please try again later.";
            }
        }
    }
}

// Set headers to prevent caching of login page (these can be here as they are HTTP headers, not session INI)
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="description" content="Login to Crony Karaoke - Access your account to book private karaoke rooms">
  <title>Login - Crony Karaoke</title>
  
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  
  <link rel="shortcut icon" href="assets/images/cronykaraoke.webp" type="image/x-icon">
  
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/animatecss/animate.css">
  <link rel="stylesheet" href="assets/theme/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>

  <style>
    /* ==========================================================================
    GLOBAL STYLES
    ========================================================================== */
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
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-white);
      font-family: 'Inter Tight', sans-serif;
      min-height: 100vh;
    }

    /* ==========================================================================
    NAVIGATION
    ========================================================================== */
    .navbar {
      background: rgba(0,0,0,0.95) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border-color);
    }

    .navbar-brand {
      font-weight: 700;
      color: var(--text-white) !important;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    /* ==========================================================================
    MAIN CONTENT
    ========================================================================== */
    .main-content {
      padding: 120px 0 80px 0;
      min-height: calc(100vh - 200px);
    }

    .login-container {
      max-width: 500px;
      margin: 0 auto;
    }

    .page-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .page-title {
      font-size: 3rem;
      font-weight: 800;
      color: var(--text-white);
      margin-bottom: 1rem;
      background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .page-subtitle {
      font-size: 1.1rem;
      color: var(--text-gray);
      margin-bottom: 2rem;
    }

    /* ==========================================================================
    FORM STYLES
    ========================================================================== */
    .form-card {
      background: var(--bg-card);
      border-radius: 20px;
      padding: 2.5rem;
      border: 1px solid var(--border-color);
      backdrop-filter: blur(10px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      color: var(--text-white);
      padding: 15px 20px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(124, 108, 255, 0.25);
      color: var(--text-white);
    }

    .form-control::placeholder {
      color: var(--text-dark-gray);
    }

    .form-label {
      color: var(--text-white);
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: block;
    }

    /* ==========================================================================
    BUTTONS
    ========================================================================== */
    .btn-primary-custom {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
      color: white;
      border: none;
      padding: 15px 30px;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
      width: 100%;
      font-size: 1rem;
      cursor: pointer;
    }

    .btn-primary-custom:hover {
      transform: translateY(-2px);
      background: linear-gradient(135deg, var(--primary-light), var(--primary-light));
      color: white;
      text-decoration: none;
      box-shadow: 0 8px 25px rgba(124, 108, 255, 0.3);
    }

    .btn-primary-custom:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    /* ==========================================================================
    ALERTS
    ========================================================================== */
    .alert {
      border-radius: 12px;
      padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      border: none;
      font-weight: 500;
    }

    .alert-error {
      background-color: rgba(255, 56, 96, 0.1);
      color: var(--error-color);
      border: 1px solid rgba(255, 56, 96, 0.3);
    }

    .alert-success {
      background-color: rgba(35, 209, 96, 0.1);
      color: var(--success-color);
      border: 1px solid rgba(35, 209, 96, 0.3);
    }

    .register-link {
      text-align: center;
      margin-top: 1.5rem;
      color: var(--text-gray);
    }

    .register-link a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
    }

    .register-link a:hover {
      color: var(--primary-light);
      text-decoration: underline;
    }

    /* ==========================================================================
    FOOTER
    ========================================================================== */
    .footer {
      background: var(--bg-dark);
      color: var(--text-gray);
      padding: 40px 0 20px 0;
      border-top: 1px solid var(--border-color);
      margin-top: auto;
    }

    .footer-nav {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .footer-nav a {
      color: var(--text-white);
      margin: 0 15px;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-nav a:hover {
      color: var(--primary-color);
    }

    .social-links {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .social-links a {
      color: var(--text-white);
      margin: 0 10px;
      font-size: 1.3rem;
      transition: color 0.3s ease;
    }

    .social-links a:hover {
      color: var(--primary-color);
    }

    .footer-info {
      text-align: center;
      font-size: 0.95rem;
    }

    .footer-info a {
      color: var(--primary-color);
      text-decoration: none;
    }

    .footer-info a:hover {
      text-decoration: underline;
    }

    /* ==========================================================================
    RESPONSIVE DESIGN
    ========================================================================== */
    @media (max-width: 768px) {
      .page-title {
        font-size: 2.5rem;
      }
      
      .form-card {
        padding: 2rem 1.5rem;
        margin: 0 1rem;
      }
      
      .main-content {
        padding: 100px 0 60px 0;
      }
      
      .footer-nav a {
        margin: 0 8px;
        font-size: 0.9rem;
      }
    }

    @media (max-width: 576px) {
      .page-title {
        font-size: 2rem;
      }
      
      .form-card {
        padding: 1.5rem;
      }
    }

    /* Additional validation styles */
    .form-control.is-valid {
      border-color: var(--success-color);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2323d160' d='m2.3 6.73.7-.4 1.6-1.6c.2-.2.4-.2.6 0 .2.2.2.4 0 .6L3.7 7.33c-.2.2-.4.2-.6 0L1.7 6.03c-.2-.2-.2-.4 0-.6.2-.2.4-.2.6 0z'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 16px;
    }
    
    .form-control.is-invalid {
      border-color: var(--error-color);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ff3860'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6.4-.4.4.4M6.6 7.4l-.4.4-.4-.4m.4-1.8v1.8'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 16px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.html">
        <img src="assets/images/cronykaraoke-1.webp" alt="Crony Karaoke Logo">
        <span>Crony Karaoke</span>
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.html#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="index.html#testimonials">Testimonials</a></li>
          <li class="nav-item"><a class="nav-link" href="index.html#contact">Contact</a></li>
        </ul>
        <a class="btn btn-primary-custom ms-3" href="register.php" style="width: auto; padding: 8px 20px; font-size: 0.9rem;">Register</a>
      </div>
    </div>
  </nav>

  <main class="main-content">
    <div class="container">
      <div class="login-container">
        <div class="page-header">
          <h1 class="page-title">Welcome Back</h1>
          <p class="page-subtitle">Sign in to your account and continue your karaoke journey</p>
        </div>

        <div class="form-card">
          <?php if (!empty($error)): ?>
            <div class="alert alert-error">
              <i class="fas fa-exclamation-circle me-2"></i>
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['msg']) && $_GET['msg'] == 'session_expired'): ?>
            <div class="alert alert-error">
               <i class="fas fa-sign-in-alt me-2"></i>
               Your session has expired. Please enter your login details again to continue.
           </div>
          <?php endif; ?>

            <!-- NEW: Session timeout notification -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'session_timeout'): ?>
              <div class="alert alert-error">
                <i class="fas fa-clock me-2"></i>
                Your session has timed out due to inactivity. Please login again.
              </div>
            <?php endif; ?>

          <?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
            <div class="alert alert-error">
              <i class="fas fa-clock me-2"></i>
              Your session has expired. Please login again.
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i>
              You have been successfully logged out.
            </div>
          <?php endif; ?>

          <form action="" method="POST" novalidate>
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input 
                type="email" 
                id="email"
                name="email" 
                class="form-control" 
                placeholder="Enter your email address"
                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                required
                maxlength="255"
                autocomplete="email"
              >
            </div>
            
            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <input 
                type="password" 
                id="password"
                name="password" 
                class="form-control" 
                placeholder="Enter your password"
                required
                maxlength="255"
                autocomplete="current-password"
              >
            </div>

            <button type="submit" class="btn-primary-custom" id="loginBtn">
              <i class="fas fa-sign-in-alt me-2"></i>
              Sign In
            </button>
          </form>

          <div class="register-link">
            Don't have an account? <a href="register.php">Create one here</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <nav class="footer-nav">
        <a href="index.html#about">About Us</a>
        <a href="index.html#testimonials">Testimonials</a>
        <a href="index.html#contact">Contact</a>
        <a href="register.php">Register</a>
        <a href="login.php">Book Now</a>
      </nav>
      
      <div class="social-links">
        <a href="https://www.facebook.com/CronyKaraoke" target="_blank" aria-label="Facebook">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://www.instagram.com/CronyKaraoke" target="_blank" aria-label="Instagram">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="https://www.twitter.com/CronyKaraoke" target="_blank" aria-label="Twitter">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="mailto:kl_info@cronykaraoke.com" aria-label="Email">
          <i class="fas fa-envelope"></i>
        </a>
      </div>
      
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

  <script src="assets/web/assets/jquery/jquery.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/smoothscroll/smooth-scroll.js"></script>
  <script src="assets/theme/js/script.js"></script>

  <script>
    // Form validation enhancement
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      const inputs = form.querySelectorAll('input[required]');
      const loginBtn = document.getElementById('loginBtn');
      
      // Add real-time validation
      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          validateField(this);
        });
        
        input.addEventListener('input', function() {
          if (this.classList.contains('is-invalid')) {
            validateField(this);
          }
        });
      });
      
      form.addEventListener('submit', function(e) {
        let isValid = true;
        inputs.forEach(input => {
          if (!validateField(input)) {
            isValid = false;
          }
        });
        
        if (!isValid) {
          e.preventDefault();
        } else {
          // Disable button to prevent double submission
          loginBtn.disabled = true;
          loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
        }
      });
      
      function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        // Remove previous validation classes
        field.classList.remove('is-valid', 'is-invalid');
        
        if (field.hasAttribute('required') && value === '') {
          isValid = false;
        } else if (field.type === 'email' && value !== '' && !isValidEmail(value)) {
          isValid = false;
        } else if (field.type === 'password' && value !== '' && value.length < 1) {
          // You might want a stronger password validation here, e.g., minimum length
          isValid = false;
        }
        
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        return isValid;
      }
      
      function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      }
    });

    // Prevent back button caching
    window.addEventListener('pageshow', function(event) {
      if (event.persisted) {
        window.location.reload();
      }
    });

    // Clear form data when page is about to be unloaded
    window.addEventListener('beforeunload', function() {
      const form = document.querySelector('form');
      if (form) {
        form.reset();
      }
    });
  </script>
</body>
</html>