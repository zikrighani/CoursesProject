<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

// Check if there's a completed reservation
if (!isset($_SESSION['completed_reservation'])) {
    header("Location: make_reservation.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="description" content="Crony Karaoke - Processing Payment">
    <title>Payment Processing - Crony Karaoke</title>
    
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

    <link rel="stylesheet" href="../assets/make_reservation.css">
    
    <style>
        .processing-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .spinner {
            width: 80px;
            height: 80px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
            margin: 0 auto 2rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .processing-title {
            color: var(--text-white);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .processing-subtitle {
            color: var(--text-gray);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .progress {
            height: 12px;
            background-color: var(--bg-dark);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 6px;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            color: var(--text-gray);
            font-size: 0.9rem;
        }
        
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-gray);
            font-size: 0.85rem;
        }
        
        .security-badge i {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .processing-card {
                padding: 2rem;
                margin: 1rem;
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
                        <a class="nav-link" href="user_home.php#promotions">Promotions</a>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Processing Your Payment</h1>
                <p class="page-subtitle">Please wait while we secure your karaoke room reservation</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="processing-card">
                        <div class="spinner"></div>
                        <h3 class="processing-title">Processing Payment...</h3>
                        <p class="processing-subtitle">
                            Please don't close this window or navigate away. 
                            You'll be redirected automatically when the process is complete.
                        </p>
                        
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%;" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="progress-text">
                            Processing... <span id="progress-text">0%</span>
                        </div>
                        
                        <div class="security-badges">
                            <div class="security-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>SSL Secured</span>
                            </div>
                            <div class="security-badge">
                                <i class="fas fa-lock"></i>
                                <span>Encrypted</span>
                            </div>
                            <div class="security-badge">
                                <i class="fas fa-check-circle"></i>
                                <span>PCI Compliant</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">© 2025 Crony Karaoke — Sing. Laugh. Repeat.</p>
            <p class="mb-0">Powered by CronyTech</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../assets/web/assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/smoothscroll/smooth-scroll.js"></script>
    <script src="../assets/theme/js/script.js"></script>

    <script>
        // Animate progress bar
        let progress = 0;
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.getElementById('progress-text');
        
        const interval = setInterval(function() {
            progress += Math.random() * 10 + 5; // Random increment between 5-15
            if (progress > 100) progress = 100;
            
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressText.textContent = Math.round(progress) + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                progressText.textContent = 'Complete!';
                
                // Show completion message
                setTimeout(function() {
                    document.querySelector('.processing-title').textContent = 'Payment Successful!';
                    document.querySelector('.processing-subtitle').textContent = 'Redirecting to confirmation page...';
                }, 500);
                
                // Redirect after progress reaches 100%
                setTimeout(function() {
                    window.location.href = "payment_done.php";
                }, 2000);
            }
        }, 200); // Update every 200ms for smoother animation
    </script>

    <input name="animation" type="hidden">
</body>
</html>