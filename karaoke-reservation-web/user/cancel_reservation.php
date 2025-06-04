<?php
include '../dbconfig.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
    $reservationID = $_POST['reservation'];
    $reason = $_POST['reason'] ?? '';
    $additional_comments = $_POST['additional_comments'] ?? '';
    
    // Combine reason and additional comments
    $cancellation_info = '';
    if (!empty($reason)) {
        $cancellation_info = "Cancellation Reason: " . $reason;
    }
    if (!empty($additional_comments)) {
        if (!empty($cancellation_info)) {
            $cancellation_info .= "\nAdditional Comments: " . $additional_comments;
        } else {
            $cancellation_info = "Additional Comments: " . $additional_comments;
        }
    }
    
    try {
        // Start transaction to ensure both reservation and payment are updated together
        $conn->begin_transaction();
        
        // Verify that the reservation belongs to the current user and can be cancelled
        // Also check cancellation time rules (24 hours before)
        $verify_sql = "SELECT r.*, ro.roomName, p.packageName,
                      TIMESTAMPDIFF(HOUR, NOW(), CONCAT(r.reservationDate, ' ', r.startTime)) as hours_until_booking
                      FROM reservations r 
                      JOIN rooms ro ON r.roomID = ro.roomID 
                      JOIN packages p ON ro.packageID = p.packageID 
                      WHERE r.reservationID = ? AND r.userID = ? AND r.status IN ('confirmed', 'pending')";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("ii", $reservationID, $userID);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Reservation not found or cannot be cancelled.");
        }
        
        $reservation_data = $result->fetch_assoc();
        
        // Check if reservation is in the past
        if ($reservation_data['hours_until_booking'] < 0) {
            throw new Exception("Cannot cancel past reservations.");
        }
        
        // Check 24-hour cancellation policy for confirmed bookings
        if ($reservation_data['status'] == 'confirmed' && $reservation_data['hours_until_booking'] < 24) {
            throw new Exception("Cannot cancel confirmed reservations less than 24 hours before the booking time.");
        }
        
        // Update the reservation status to cancelled and save cancellation info
        $update_reservation_sql = "UPDATE reservations SET status = 'cancelled', addInfo = ? WHERE reservationID = ? AND userID = ?";
        $update_reservation_stmt = $conn->prepare($update_reservation_sql);
        $update_reservation_stmt->bind_param("sii", $cancellation_info, $reservationID, $userID);
        
        if (!$update_reservation_stmt->execute()) {
            throw new Exception("Failed to cancel reservation. Please try again.");
        }
        
        // Update the payment status to 'refunded' for this reservation if payment exists
        $update_payment_sql = "UPDATE payments SET paymentStatus = 'refunded' WHERE reservationID = ? AND paymentStatus IN ('paid', 'pending')";
        $update_payment_stmt = $conn->prepare($update_payment_sql);
        $update_payment_stmt->bind_param("i", $reservationID);
        $update_payment_stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        $success_message = "Your reservation has been cancelled successfully. Refund will be processed within 3-5 business days.";
        
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Handle direct reservation ID from URL (like from booking.php)
$selected_reservation_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Fetch user's active reservations (confirmed or pending) that are in the future only
$sql = "SELECT r.*, ro.roomName, p.packageName, p.pricePerHour,
               TIMESTAMPDIFF(HOUR, r.startTime, r.endTime) as duration,
               TIMESTAMPDIFF(HOUR, NOW(), CONCAT(r.reservationDate, ' ', r.startTime)) as hours_until_booking,
               pay.paymentStatus, pay.paymentMethod
        FROM reservations r 
        JOIN rooms ro ON r.roomID = ro.roomID 
        JOIN packages p ON ro.packageID = p.packageID 
        LEFT JOIN payments pay ON r.reservationID = pay.reservationID
        WHERE r.userID = ? AND r.status IN ('confirmed', 'pending') 
        AND CONCAT(r.reservationDate, ' ', r.startTime) > NOW()
        ORDER BY r.reservationDate ASC, r.startTime ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$reservations = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="Crony Karaoke - Cancel Reservation">

  <title>Cancel Reservation - Crony Karaoke</title>
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
    .booking-card {
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin-bottom: 25px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      position: relative;
    }
    .booking-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .booking-card.selected {
      border: 3px solid #dc3545;
      background: #fff5f5;
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
    }
    .booking-header {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    .booking-reference {
      font-size: 1.3rem;
      font-weight: 700;
      color: rgb(255, 255, 255);
      margin-bottom: 5px;
    }
    .booking-status {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    .status-confirmed {
      background-color: #28a745;
      color: white;
    }
    .status-pending {
      background-color: #ffc107;
      color: #212529;
    }
    .booking-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    .detail-item {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      text-align: center;
    }
    .detail-label {
      font-size: 0.9rem;
      color: #6c757d;
      margin-bottom: 5px;
      font-weight: 500;
    }
    .detail-value {
      font-size: 1.1rem;
      font-weight: 600;
      color: #333;
    }
    .page-header {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      padding: 60px 0;
      margin-bottom: 40px;
    }
    .cancel-summary {
      background: linear-gradient(45deg, #dc3545, #ff6b7d);
      color: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      position: sticky;
      top: 100px;
    }
    .btn-cancel {
      background: #dc3545;
      border-color: #dc3545;
      color: white;
      padding: 12px 25px;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .btn-cancel:hover {
      background: #c82333;
      border-color: #bd2130;
      color: white;
      transform: translateY(-1px);
    }
    .btn-primary {
      background: #493d9e;
      border-color: #493d9e;
    }
    .btn-primary:hover {
      background: #3d3486;
      border-color: #3d3486;
    }
    .alert {
      margin-bottom: 20px;
      border-radius: 10px;
    }
    .form-section {
      margin-bottom: 40px;
    }
    .cancellation-reason {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .no-reservations {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .no-reservations .mbr-iconfont {
      font-size: 4rem;
      color: #ccc;
      margin-bottom: 20px;
    }
    .selection-radio {
      position: absolute;
      top: 15px;
      right: 15px;
      transform: scale(1.2);
    }
    .cannot-cancel-warning {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 8px;
      padding: 10px 15px;
      margin-top: 15px;
      font-size: 0.9rem;
      color: #856404;
    }
    .payment-info {
      background: #e7f3ff;
      border: 1px solid #bee5eb;
      border-radius: 5px;
      padding: 10px;
      margin-top: 10px;
      font-size: 0.9rem;
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
                        <a class="nav-link link text-black text-primary display-4" href="booking.php">View Bookings</a>
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

<section class="page-header" style="padding-top: 150px; padding-bottom: 40px; margin-bottom: 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-3 fw-bold">Cancel Your Reservation</h1>
                <p class="lead">Select the upcoming booking you want to cancel and provide a reason</p>
            </div>
        </div>
    </div>
</section>

<section data-bs-version="5.1" id="cancel-reservation" style="padding-top: 50px; padding-bottom: 90px; background: #edefeb;">
    <div class="container">
        <!-- Alert Messages -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if ($reservations->num_rows > 0): ?>
        <form id="cancelForm" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="cancel_reservation">
            <div class="row g-4 align-items-stretch">
            <!-- Step 1: Choose a Reservation to Cancel -->
            <div class="col-lg-7 d-flex flex-column">
                <div class="form-section flex-fill" style="background: #fff; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 28px 22px;">
                <div class="d-flex align-items-center mb-4" style="gap: 10px;">
                    <span style="font-size:1.5rem; color:#493d9e;">①</span>
                    <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#493d9e; line-height:1;">
                    Choose a Reservation to Cancel
                    </h2>
                </div>
                <div id="reservations-list">
                    <div class="row g-3 flex-column">
                    <?php 
                    $reservations->data_seek(0);
                    while ($reservation = $reservations->fetch_assoc()): 
                        $bookingReference = '#CK' . str_pad($reservation['reservationID'], 5, '0', STR_PAD_LEFT);
                        $formattedDate = date('F j, Y', strtotime($reservation['reservationDate']));
                        $formattedStartTime = date('g:i A', strtotime($reservation['startTime']));
                        $formattedEndTime = date('g:i A', strtotime($reservation['endTime']));
                        $createdDate = date('F j, Y', strtotime($reservation['createdAt']));
                        $duration_text = $reservation['duration'] == 1 ? "1 hour" : $reservation['duration'] . " hours";
                        $total_price = "RM " . number_format($reservation['totalPrice'], 2);
                        $canCancel = true;
                        $cancelWarning = '';
                        if ($reservation['status'] == 'confirmed' && $reservation['hours_until_booking'] < 24) {
                        $canCancel = false;
                        $cancelWarning = 'Cannot cancel confirmed bookings less than 24 hours before booking time.';
                        } elseif ($reservation['hours_until_booking'] < 0) {
                        $canCancel = false;
                        $cancelWarning = 'This booking has already passed.';
                        }
                    ?>
                    <div class="col-12">
                        <div class="booking-card mb-2 py-3 px-3 <?php echo !$canCancel ? 'opacity-50' : ''; ?>" 
                         style="padding:16px 13px; font-size:0.97rem; min-height: 0;"
                         <?php if ($canCancel): ?>
                         onclick="selectReservation('<?php echo $reservation['reservationID']; ?>', '<?php echo htmlspecialchars($reservation['packageName'] . ' (' . $reservation['roomName'] . ')'); ?>', '<?php echo $formattedDate; ?>', '<?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?>', '<?php echo $duration_text; ?>', '<?php echo $total_price; ?>', '<?php echo $bookingReference; ?>')"
                         <?php endif; ?>>
                        
                        <?php if ($canCancel): ?>
                        <input class="form-check-input selection-radio" type="radio" name="reservation" 
                               id="res-<?php echo $reservation['reservationID']; ?>" 
                               value="<?php echo $reservation['reservationID']; ?>" 
                               <?php echo ($selected_reservation_id == $reservation['reservationID']) ? 'checked' : ''; ?>
                               required>
                        <?php endif; ?>
                        
                        <div class="booking-header py-2 px-3" style="padding:10px 12px;">
                            <div class="row align-items-center">
                            <div class="col-8 d-flex flex-wrap align-items-center" style="gap: 10px;">
                                <div class="booking-reference" style="font-size:1.1rem;"><?php echo $bookingReference; ?></div>
                                <span style="font-size:0.95rem; color:#fff; opacity:0.85;">|</span>
                                <h6 class="mb-0" style="font-size:1rem;"><?php echo $reservation['packageName']; ?> Room (<?php echo $reservation['roomName']; ?>)</h6>
                            </div>
                            <div class="col-4 text-end">
                                <span class="booking-status status-<?php echo $reservation['status']; ?>" style="font-size:0.8rem;">
                                <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </div>
                            </div>
                            <div class="row">
                            <div class="col-12">
                                <p class="mb-0" style="opacity: 0.9; font-size:0.92rem;">Booked on <?php echo $createdDate; ?></p>
                            </div>
                            </div>
                        </div>
                        
                        <div class="booking-details" style="gap:10px; margin-bottom:10px; grid-template-columns: 1fr 1fr;">
                            <div class="detail-item" style="padding:10px;">
                            <div class="detail-label" style="font-size:0.85rem;">Date</div>
                            <div class="detail-value" style="font-size:1rem;"><?php echo $formattedDate; ?></div>
                            </div>
                            <div class="detail-item" style="padding:10px;">
                            <div class="detail-label" style="font-size:0.85rem;">Time</div>
                            <div class="detail-value" style="font-size:1rem;"><?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?></div>
                            </div>
                            <div class="detail-item" style="padding:10px;">
                            <div class="detail-label" style="font-size:0.85rem;">Duration</div>
                            <div class="detail-value" style="font-size:1rem;"><?php echo $duration_text; ?></div>
                            </div>
                            <div class="detail-item" style="padding:10px;">
                            <div class="detail-label" style="font-size:0.85rem;">Refund</div>
                            <div class="detail-value" style="font-size:1rem;"><?php echo $total_price; ?></div>
                            </div>
                        </div>
                                        
                                        <?php if ($reservation['paymentStatus'] && $reservation['paymentMethod']): ?>
                                        <div class="payment-info" style="font-size:0.9em; padding:7px;">
                                            <strong>Payment:</strong> <?php echo ucfirst($reservation['paymentMethod']); ?> - 
                                            Status: <?php echo ucfirst($reservation['paymentStatus']); ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!$canCancel): ?>
                                        <div class="cannot-cancel-warning" style="font-size:0.92em;">
                                            <strong>Cannot Cancel:</strong> <?php echo $cancelWarning; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <div class="invalid-feedback" style="margin-top:10px;">
                            Please choose a reservation to cancel.
                        </div>
                    </div>
                </div>

                <!-- Step 2: Tell Us Why (Optional) -->
                <div class="col-lg-5 d-flex flex-column">
                    <div class="form-section cancellation-reason" style="background: #fff; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 28px 22px; width:100%; max-width: 480px; margin-left:auto; margin-right:auto;">
                        <div class="d-flex align-items-center mb-4" style="gap: 10px;">
                            <span style="font-size:1.5rem; color:#493d9e;">②</span>
                            <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#493d9e; line-height:1;">
                                Tell Us Why (Optional)
                            </h2>
                        </div>
                        <div class="mb-3">
                            <label for="reason-select" class="form-label">Reason for cancellation</label>
                            <select class="form-select mb-3" id="reason-select" name="reason">
                                <option value="" selected>Select a reason (optional)</option>
                                <option value="Change of plans">Change of plans</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Double booking">Double booking</option>
                                <option value="Health issues">Health issues</option>
                                <option value="Weather conditions">Weather conditions</option>
                                <option value="Other">Other</option>
                            </select>
                            <textarea class="form-control" id="additional-comments" name="additional_comments" rows="4" 
                                      placeholder="Additional comments (optional)..."></textarea>
                        </div>
                    </div>
                    <!-- Cancellation Summary (directly below step 2, not sticky) -->
                    <div class="cancel-summary mt-4" style="position:static; margin-top:24px; width:100%; max-width: 480px; margin-left:auto; margin-right:auto;">
                        <h3 class="mb-4 text-white text-center"><strong>Cancellation Summary</strong></h3>
                        <div id="cancel-summary-details" class="w-100">
                            <p><strong>Reference:</strong> <span id="cancel-reference">Not selected</span></p>
                            <p><strong>Room:</strong> <span id="cancel-room">Not selected</span></p>
                            <p><strong>Date:</strong> <span id="cancel-date">Not selected</span></p>
                            <p><strong>Time:</strong> <span id="cancel-time">Not selected</span></p>
                            <p><strong>Duration:</strong> <span id="cancel-duration">Not selected</span></p>
                            <hr style="border-color: rgba(255,255,255,0.3);">
                            <h5 class="text-white text-center"><strong>Refund Amount: <span id="cancel-refund">RM 0.00</span></strong></h5>
                            <small class="text-light d-block text-center mb-4">*Refund will be processed within 3-5 business days</small>
                        </div>
                        <button type="submit" class="btn btn-light btn-lg w-100 btn-cancel">
                            <span class="mbr-iconfont mobi-mbri-close mobi-mbri me-2"></span>
                            Cancel Reservation
                        </button>
                    </div>
                </div>
 
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <style>
        @media (max-width: 991.98px) {
            .cancel-summary {
                position: static !important;
                margin-top: 24px;
            }
            .form-section, .cancellation-reason {
                margin-bottom: 0 !important;
            }
            .col-12.col-md-6 {
                margin-bottom: 0.5rem;
            }
        }
        @media (max-width: 767.98px) {
            .form-section, .cancellation-reason {
                padding: 18px 8px !important;
            }
            .cancel-summary {
                padding: 18px 8px !important;
            }
        }
        </style>
        <?php else: ?>
        <div class="no-reservations">
            <span class="mbr-iconfont mobi-mbri-search mobi-mbri"></span>
            <h3>No Upcoming Reservations</h3>
            <p class="text-muted">You don't have any upcoming reservations that can be cancelled.</p>
            <a href="make_reservation.php" class="btn btn-primary me-2">Make New Booking</a>
            <a href="user_home.php" class="btn btn-outline-primary">Back to Home</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section data-bs-version="5.1" class="footer3 cid-uLCpCfgtNL" once="footers" id="footer03-22" style="padding-top: 40px; padding-bottom: 0px;">
    <div class="container">
        <div class="row">
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <div class="container text-center">
                        <a href="booking.php" class="btn btn-light btn-sm">View All Bookings</a>
                        <a href="user_home.php" class="btn btn-light btn-sm">Back to Home</a>
                        <a href="../logout.php" class="btn btn-light btn-sm">Logout</a>
                    </div>
                </div>
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
    // Reservation selection
    function selectReservation(id, room, date, time, duration, total, reference) {
        // Clear all selections
        document.querySelectorAll('.booking-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Select the clicked reservation
        event.currentTarget.classList.add('selected');
        document.querySelector(`input[value="${id}"]`).checked = true;
        
        // Update cancellation summary
        updateCancelSummary(reference, room, date, time, duration, total);
    }

    // Update cancellation summary
    function updateCancelSummary(reference, room, date, time, duration, total) {
        document.getElementById('cancel-reference').textContent = reference || "Not selected";
        document.getElementById('cancel-room').textContent = room || "Not selected";
        document.getElementById('cancel-date').textContent = date || "Not selected";
        document.getElementById('cancel-time').textContent = time || "Not selected";
        document.getElementById('cancel-duration').textContent = duration || "Not selected";
        document.getElementById('cancel-refund').textContent = total || "RM 0.00";
    }

    // Auto-select if reservation ID is provided in URL
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($selected_reservation_id): ?>
        const selectedCard = document.querySelector('input[value="<?php echo $selected_reservation_id; ?>"]');
        if (selectedCard) {
            selectedCard.closest('.booking-card').click();
        }
        <?php endif; ?>
        
        // Form validation
        const form = document.getElementById('cancelForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                const selectedReservation = document.querySelector('input[name="reservation"]:checked');
                
                if (!selectedReservation) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Show validation message
                    document.querySelector('.invalid-feedback').style.display = 'block';
                    
                    // Scroll to reservations list
                    document.getElementById('reservations-list').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    
                    return false;
                }
                
                // Confirm cancellation
                const reference = document.getElementById('cancel-reference').textContent;
                const room = document.getElementById('cancel-room').textContent;
                const refund = document.getElementById('cancel-refund').textContent;
                
                const confirmMessage = `Are you sure you want to cancel reservation ${reference} for ${room}?\n\nRefund amount: ${refund}\n\nThis action cannot be undone.`;
                
                if (!confirm(confirmMessage)) {
                    event.preventDefault();
                    return false;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                submitBtn.disabled = true;
                
                // Re-enable button after 10 seconds in case of issues
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            });
        }
        
        // Hide validation message when reservation is selected
        document.querySelectorAll('input[name="reservation"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelector('.invalid-feedback').style.display = 'none';
            });
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-success')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    });
    
    // Prevent double submission
    let formSubmitted = false;
    document.getElementById('cancelForm')?.addEventListener('submit', function() {
        if (formSubmitted) {
            event.preventDefault();
            return false;
        }
        formSubmitted = true;
    });
</script>

</body>
</html>