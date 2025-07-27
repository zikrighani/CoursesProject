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

$completed_reservation = $_SESSION['completed_reservation'];
$bookingReference = '#CK' . str_pad($completed_reservation['reservationID'], 5, '0', STR_PAD_LEFT);

// Function to format room type display
function formatRoomType($roomType) {
    return htmlspecialchars($roomType) . " Room";
}

// Clear the completed reservation from session after displaying
// (We'll keep it for this page load, but could clear it after)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="description" content="Crony Karaoke - Payment Complete">
    <title>Payment Complete - Crony Karaoke</title>
    
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
        .success-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .success-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-title {
            color: #10b981;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .success-subtitle {
            color: var(--text-gray);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .booking-details {
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }
        
        .booking-reference {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .booking-reference h4 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 2px;
        }
        
        .booking-reference p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: var(--text-gray);
            font-weight: 500;
        }
        
        .detail-value {
            color: var(--text-white);
            font-weight: 600;
        }
        
        .detail-value.amount {
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .status-badge {
            background: #10b981;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .confirmation-notice {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .confirmation-notice h5 {
            color: #3b82f6;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .confirmation-notice p {
            color: var(--text-gray);
            margin: 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(124, 108, 255, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary-custom {
            background: var(--bg-dark);
            color: var(--text-white);
            border: 1px solid var(--border-color);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-secondary-custom:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .success-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .booking-details {
                padding: 1.5rem;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-primary-custom,
            .btn-secondary-custom {
                width: 100%;
                text-align: center;
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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
                <h1 class="page-title">Booking Confirmed!</h1>
                <p class="page-subtitle">Your karaoke session has been successfully reserved</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="success-card animate-fade-in">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        
                        <h3 class="success-title">Payment Successful!</h3>
                        <p class="success-subtitle">
                            Your karaoke room has been booked. We're looking forward to welcoming you!
                        </p>
                        
                        <div class="booking-reference">
                            <p>Your Booking Reference</p>
                            <h4><?php echo $bookingReference; ?></h4>
                        </div>
                        
                        <div class="booking-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-row">
                                        <span class="detail-label">Room Type:</span>
                                        <span class="detail-value"><?php echo formatRoomType($completed_reservation['roomType']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Date:</span>
                                        <span class="detail-value"><?php echo date('F j, Y', strtotime($completed_reservation['reservationDate'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Time:</span>
                                        <span class="detail-value">
                                            <?php echo date('g:i A', strtotime($completed_reservation['startTime'])); ?> - 
                                            <?php echo date('g:i A', strtotime($completed_reservation['endTime'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-row">
                                        <span class="detail-label">Payment Method:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($completed_reservation['paymentMethod']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Amount Paid:</span>
                                        <span class="detail-value amount">RM <?php echo number_format($completed_reservation['totalPrice'], 2); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Status:</span>
                                        <span class="status-badge">PAID</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="confirmation-notice">
                            <h5>
                                <i class="fas fa-envelope"></i>
                                Confirmation Email Sent
                            </h5>
                            <p>A confirmation email has been sent to your registered email address with all booking details.</p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-center" style="color: var(--text-gray);">
                                <i class="fas fa-info-circle me-1"></i>
                                Please present your booking reference upon arrival at our venue.
                            </p>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="booking.php" class="btn-primary-custom">
                                <i class="fas fa-calendar-alt me-2"></i>
                                View My Bookings
                            </a>
                            <a href="make_reservation.php" class="btn-secondary-custom">
                                <i class="fas fa-plus me-2"></i>
                                Book Another Room
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-buttons">
                <a href="user_home.php" class="btn btn-secondary">
                    <i class="fas fa-home me-1"></i>
                    Back to Home
                </a>
                <a href="../logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
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
        // Clear the completed reservation from session after 30 seconds
        // This prevents users from refreshing and seeing the same confirmation
        setTimeout(function() {
            fetch('clear_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({action: 'clear_completed_reservation'})
            });
        }, 30000);
    </script>

    <input name="animation" type="hidden">
</body>
</html>