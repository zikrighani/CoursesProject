<?php
session_start();
require_once '../dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

// Check if there's a pending reservation
if (!isset($_SESSION['pending_reservation'])) {
    header("Location: make_reservation.php");
    exit();
}

$pending_reservation = $_SESSION['pending_reservation'];

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $error_message = "Please select a payment method.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Ensure date is in correct format (YYYY-MM-DD)
            $reservationDate = $pending_reservation['reservationDate'];
            
            // If date is in different format, convert it
            if (!empty($reservationDate)) {
                $dateObj = DateTime::createFromFormat('Y-m-d', $reservationDate);
                if (!$dateObj) {
                    // Try other common formats
                    $dateObj = DateTime::createFromFormat('m/d/Y', $reservationDate);
                    if (!$dateObj) {
                        $dateObj = DateTime::createFromFormat('d/m/Y', $reservationDate);
                    }
                }
                
                if ($dateObj) {
                    $reservationDate = $dateObj->format('Y-m-d');
                } else {
                    throw new Exception("Invalid date format");
                }
            }
            
            // Insert reservation into database
            $insertReservationQuery = "INSERT INTO reservations (userID, roomID, reservationDate, startTime, endTime, totalPrice, status, addInfo) 
                                      VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?)";
            
            $stmt = mysqli_prepare($conn, $insertReservationQuery);
            
            mysqli_stmt_bind_param($stmt, "iisssds", 
                $pending_reservation['userID'],
                $pending_reservation['roomID'],
                $reservationDate,
                $pending_reservation['startTime'],
                $pending_reservation['endTime'],
                $pending_reservation['totalPrice'],
                $pending_reservation['specialRequests']
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create reservation: " . mysqli_error($conn));
            }
            
            // Get the reservation ID
            $reservationID = mysqli_insert_id($conn);
            
            // Insert payment record
            $insertPaymentQuery = "INSERT INTO payments (reservationID, paymentMethod, amountPaid, paymentStatus) 
                                  VALUES (?, ?, ?, 'paid')";
            
            $paymentStmt = mysqli_prepare($conn, $insertPaymentQuery);
            mysqli_stmt_bind_param($paymentStmt, "isd", 
                $reservationID,
                $payment_method,
                $pending_reservation['totalPrice']
            );
            
            if (!mysqli_stmt_execute($paymentStmt)) {
                throw new Exception("Failed to record payment: " . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Store reservation details for confirmation page
            $_SESSION['completed_reservation'] = [
                'reservationID' => $reservationID,
                'roomType' => $pending_reservation['roomType'],
                'reservationDate' => $reservationDate,
                'startTime' => $pending_reservation['startTime'],
                'endTime' => $pending_reservation['endTime'],
                'totalPrice' => $pending_reservation['totalPrice'],
                'paymentMethod' => $payment_method
            ];
            
            // Clear pending reservation
            unset($_SESSION['pending_reservation']);
            
            // Redirect to processing page
            header("Location: payment_processing.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            $error_message = "Payment processing failed: " . $e->getMessage();
            error_log("Payment processing error: " . $e->getMessage());
        }
    }
}

// Get package details based on the room type (package name)
$packageQuery = "SELECT * FROM packages WHERE packageName = ?";
$packageStmt = mysqli_prepare($conn, $packageQuery);
mysqli_stmt_bind_param($packageStmt, "s", $pending_reservation['roomType']);
mysqli_stmt_execute($packageStmt);
$packageResult = mysqli_stmt_get_result($packageStmt);
$package = mysqli_fetch_assoc($packageResult);

// If package is not found, set default values to prevent errors
if (!$package) {
    $package = [
        'packageName' => $pending_reservation['roomType'] ?? 'Unknown',
        'pricePerHour' => 0
    ];
}

// Payment methods configuration
$payment_methods = [
    'Credit Card' => ['icon' => '💳', 'desc' => 'Visa, MasterCard, Amex'],
    'Debit Card' => ['icon' => '💳', 'desc' => 'Bank debit cards'],
    'Online Banking' => ['icon' => '🏦', 'desc' => 'FPX, Internet Banking'],
    'E-Wallet' => ['icon' => '📱', 'desc' => 'Touch \'n Go, GrabPay']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="description" content="Crony Karaoke - Payment">
    <title>Payment - Crony Karaoke</title>
    
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
        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .payment-method {
            background: var(--bg-dark);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .payment-method:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(124, 108, 255, 0.2);
        }
        
        .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(124, 108, 255, 0.1);
            box-shadow: 0 0 20px rgba(124, 108, 255, 0.2);
        }
        
        .payment-method input[type="radio"] {
            display: none;
        }
        
        .payment-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .payment-name {
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 0.25rem;
        }
        
        .payment-desc {
            color: var(--text-gray);
            font-size: 0.85rem;
        }
        
        .booking-summary {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .booking-summary h4 {
            color: var(--text-white);
            margin-bottom: 1rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .summary-label {
            color: var(--text-gray);
        }
        
        .summary-value {
            color: var(--text-white);
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-back {
            background: var(--bg-card);
            color: var(--text-white);
            border: 1px solid var(--border-color);
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            text-decoration: none;
        }
        
        .btn-pay {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(124, 108, 255, 0.3);
        }
        
        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .payment-methods-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-back, .btn-pay {
                width: 100%;
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
                <h1 class="page-title">Complete Your Payment</h1>
                <p class="page-subtitle">Secure your karaoke room reservation with your preferred payment method</p>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Booking Summary -->
                    <div class="form-section">
                        <h2 class="section-header">
                            <span class="step-number">1</span>
                            Booking Summary
                        </h2>
                        
                        <div class="booking-summary">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="summary-item">
                                        <span class="summary-label">Room Type:</span>
                                        <span class="summary-value"><?php echo htmlspecialchars($package['packageName']); ?> Room</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Date:</span>
                                        <span class="summary-value"><?php echo date('F j, Y', strtotime($pending_reservation['reservationDate'])); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Time:</span>
                                        <span class="summary-value">
                                            <?php echo date('g:i A', strtotime($pending_reservation['startTime'])); ?> - 
                                            <?php echo date('g:i A', strtotime($pending_reservation['endTime'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="summary-item">
                                        <span class="summary-label">Duration:</span>
                                        <span class="summary-value"><?php echo $pending_reservation['duration']; ?> hour(s)</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Rate:</span>
                                        <span class="summary-value">RM <?php echo number_format($package['pricePerHour'], 2); ?>/hour</span>
                                    </div>
                                    <?php if (isset($pending_reservation['earlyBirdDiscount']) && $pending_reservation['earlyBirdDiscount'] > 0): ?>
                                    <div class="summary-item">
                                        <span class="summary-label">Early Bird Discount:</span>
                                        <span class="summary-value text-warning">-RM <?php echo number_format($pending_reservation['earlyBirdDiscount'], 2); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="summary-item">
                                        <span class="summary-label">Total Amount:</span>
                                        <span class="summary-value">RM <?php echo number_format($pending_reservation['totalPrice'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($pending_reservation['specialRequests'])): ?>
                                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                    <div class="summary-item">
                                        <span class="summary-label">Special Requests:</span>
                                        <span class="summary-value"><?php echo htmlspecialchars($pending_reservation['specialRequests']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Payment Method Selection -->
                    <div class="form-section">
                        <h2 class="section-header">
                            <span class="step-number">2</span>
                            Select Payment Method
                        </h2>
                        
                        <form method="POST" id="paymentForm" class="needs-validation" novalidate>
                            <div class="payment-methods-grid">
                                <?php foreach ($payment_methods as $method => $details): ?>
                                    <label class="payment-method" for="<?php echo strtolower(str_replace(' ', '_', $method)); ?>">
                                        <input type="radio" name="payment_method" value="<?php echo $method; ?>" id="<?php echo strtolower(str_replace(' ', '_', $method)); ?>" required>
                                        <div class="payment-icon"><?php echo $details['icon']; ?></div>
                                        <div class="payment-name"><?php echo $method; ?></div>
                                        <div class="payment-desc"><?php echo $details['desc']; ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="make_reservation.php" class="btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Booking
                                </a>
                                <button type="submit" class="btn-pay">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Pay RM <?php echo number_format($pending_reservation['totalPrice'], 2); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Payment Security Info -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h3 class="summary-title">
                            <i class="fas fa-shield-alt me-2"></i>
                            Secure Payment
                        </h3>
                        
                        <div class="text-center mb-3">
                            <i class="fas fa-lock" style="font-size: 3rem; opacity: 0.7;"></i>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">SSL Encrypted</span>
                            <span class="summary-value"><i class="fas fa-check text-success"></i></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">PCI Compliant</span>
                            <span class="summary-value"><i class="fas fa-check text-success"></i></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Instant Confirmation</span>
                            <span class="summary-value"><i class="fas fa-check text-success"></i></span>
                        </div>
                        
                        <div class="discount-notice">
                            <i class="fas fa-info-circle me-1"></i>
                            Your payment is processed securely. You will receive a confirmation email after successful payment.
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
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../assets/web/assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/smoothscroll/smooth-scroll.js"></script>
    <script src="../assets/theme/js/script.js"></script>

    <script>
        // Handle payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                // Add selected class to clicked method
                this.classList.add('selected');
                
                // Enable the pay button
                document.querySelector('.btn-pay').disabled = false;
            });
        });

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedPayment) {
                e.preventDefault();
                e.stopPropagation();
                
                // Show validation message
                const firstPaymentMethod = document.querySelector('.payment-method');
                firstPaymentMethod.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Add shake animation to payment methods
                document.querySelectorAll('.payment-method').forEach(method => {
                    method.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        method.style.animation = '';
                    }, 500);
                });
            }
            
            this.classList.add('was-validated');
        });

        // Disable pay button initially
        document.querySelector('.btn-pay').disabled = true;

        // Add shake animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    </script>

    <input name="animation" type="hidden">
</body>
</html>