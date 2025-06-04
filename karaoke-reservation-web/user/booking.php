<?php
session_start();
include '../dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get booking statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
        SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(CASE WHEN p.paymentStatus = 'refunded' THEN p.amountPaid ELSE 0 END) as total_refunded,
        SUM(CASE WHEN r.status = 'cancelled' AND p.paymentStatus = 'paid' THEN p.amountPaid ELSE 0 END) as pending_refund
    FROM reservations r
    LEFT JOIN payments p ON r.reservationID = p.reservationID
    WHERE r.userID = ?
";

$stmt = $conn->prepare($statsQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get all bookings with related information
$bookingsQuery = "
    SELECT 
        r.reservationID,
        r.reservationDate,
        r.startTime,
        r.endTime,
        r.totalPrice,
        r.status as reservationStatus,
        r.addInfo,
        r.createdAt,
        ro.roomName,
        pk.packageName,
        pk.pricePerHour,
        p.paymentStatus,
        p.paymentMethod,
        p.amountPaid,
        p.paymentDate,
        TIMESTAMPDIFF(HOUR, r.startTime, r.endTime) as duration
    FROM reservations r
    JOIN rooms ro ON r.roomID = ro.roomID
    JOIN packages pk ON ro.packageID = pk.packageID
    LEFT JOIN payments p ON r.reservationID = p.reservationID
    WHERE r.userID = ?
    ORDER BY r.reservationDate DESC, r.startTime DESC
";

$stmt = $conn->prepare($bookingsQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="Crony Karaoke - View Your Bookings">

  <title>View Bookings - Crony Karaoke</title>
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
    }
    .booking-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
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
      color:rgb(255, 255, 255);
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
    .status-cancelled {
      background-color: #dc3545;
      color: white;
    }
    .status-completed {
      background-color: #17a2b8;
      color: white;
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
    .action-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .btn-action {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s ease;
      white-space: nowrap;
    }
    .btn-primary-action {
      background-color: #493d9e;
      color: #fff;
    }
    .btn-primary-action:hover {
      background-color: #3d3486;
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-secondary-action {
      background-color: #6c757d;
      color: #fff;
    }
    .btn-secondary-action:hover {
      background-color: #545b62;
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-danger-action {
      background-color: #dc3545;
      color: #fff;
    }
    .btn-danger-action:hover {
      background-color: #c82333;
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-success-action {
      background-color: #28a745;
      color: #fff;
    }
    .btn-success-action:hover {
      background-color: #218838;
      color: #fff;
      transform: translateY(-1px);
    }
    .page-header {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      padding: 60px 0;
      margin-bottom: 40px;
    }
    .filter-section {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    .stats-card {
      background: linear-gradient(45deg, #149dcc, #2fcef5);
      color: white;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 20px;
    }
    .stats-number {
      font-size: 2rem;
      font-weight: bold;
      margin-bottom: 5px;
    }
    .stats-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .empty-state .mbr-iconfont {
      font-size: 4rem;
      color: #ccc;
      margin-bottom: 20px;
    }
    .payment-info {
      background: #e7f3ff;
      border: 1px solid #bee5eb;
      border-radius: 5px;
      padding: 10px;
      margin-top: 10px;
      font-size: 0.9rem;
    }
    .cannot-cancel-text {
      font-size: 0.8rem;
      color: #6c757d;
      font-style: italic;
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

<section class="page-header" style="padding-top: 150px; padding-bottom: 40px; margin-bottom: 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-3 fw-bold">Your Bookings</h1>
                <p class="lead">View and manage all your karaoke room reservations</p>
            </div>
        </div>
    </div>
</section>

<section data-bs-version="5.1" id="view-bookings" style="padding-top: 50px; padding-bottom: 90px; background: #edefeb;">
    <div class="container">
        
        <!-- Stats Cards -->
        <div class="row mb-4 justify-content-center">
            <div class="col-md-3">
            <div class="stats-card h-100 d-flex flex-column justify-content-center align-items-center" style="background: linear-gradient(135deg, #f5f6fa, #e9ecef); color: #222; min-height: 120px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div class="stats-number" style="color: #28a745;">RM <?php echo number_format($stats['total_refunded'], 2); ?></div>
                <div class="stats-label" style="color: #555;">Total Refunded</div>
            </div>
            </div>
            <div class="col-md-3">
            <div class="stats-card h-100 d-flex flex-column justify-content-center align-items-center" style="background: linear-gradient(135deg, #f5f6fa, #e9ecef); color: #222; min-height: 120px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div class="stats-number" style="color: #fd7e14;">RM <?php echo number_format($stats['pending_refund'], 2); ?></div>
                <div class="stats-label" style="color: #555;">Pending Refund</div>
            </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="d-flex justify-content-center">
            <div class="filter-section" style="display: inline-block; padding: 12px 18px; border-radius: 8px; margin-bottom: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); background: #f8f9fa; width: auto; min-width: 0; max-width: 100%;">
                <form class="row g-2 align-items-center flex-nowrap justify-content-center" style="flex-wrap: nowrap !important;" onsubmit="applyFilters(); return false;">
                    <div class="col-auto d-flex align-items-center" style="gap: 8px;">
                        <label for="statusFilter" class="form-label mb-0" style="font-size: 0.95rem; min-width: 55px;">Status</label>
                        <select class="form-select form-select-sm" id="statusFilter" style="font-size: 0.95rem; min-width: 120px;">
                            <option value="">All</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-center" style="gap: 8px;">
                        <label for="dateFrom" class="form-label mb-0" style="font-size: 0.95rem; min-width: 38px;">From</label>
                        <input type="date" class="form-control form-control-sm" id="dateFrom" style="font-size: 0.95rem; min-width: 120px;">
                    </div>
                    <div class="col-auto d-flex align-items-center" style="gap: 8px;">
                        <label for="dateTo" class="form-label mb-0" style="font-size: 0.95rem; min-width: 22px;">To</label>
                        <input type="date" class="form-control form-control-sm" id="dateTo" style="font-size: 0.95rem; min-width: 120px;">
                    </div>
                    <div class="col-auto d-flex align-items-center" style="gap: 8px;">
                        <div class="btn-group-vertical d-block d-md-none w-100" role="group" aria-label="Filter and Clear">
                            <button type="submit" class="btn btn-primary btn-sm mb-1">
                                <span class="mbr-iconfont mobi-mbri-search mobi-mbri me-1"></span>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                Clear
                            </button>
                        </div>
                        <div class="btn-group d-none d-md-flex" role="group" aria-label="Filter and Clear">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="mbr-iconfont mobi-mbri-search mobi-mbri me-1"></span>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings List -->
        <div id="bookings-container">
            <?php if ($bookings->num_rows > 0): ?>
            <div class="row">
            <?php 
                while ($booking = $bookings->fetch_assoc()): 
                $currentDate = new DateTime();
                $bookingDate = new DateTime($booking['reservationDate']);
                $isPastBooking = $bookingDate < $currentDate;

                // Determine display status
                $displayStatus = $booking['reservationStatus'];
                if ($displayStatus == 'confirmed' && $isPastBooking) {
                    $displayStatus = 'completed';
                }

                // Format dates and times
                $formattedDate = date('F j, Y', strtotime($booking['reservationDate']));
                $formattedStartTime = date('g:i A', strtotime($booking['startTime']));
                $formattedEndTime = date('g:i A', strtotime($booking['endTime']));
                $createdDate = date('F j, Y', strtotime($booking['createdAt']));
                
                // Generate booking reference like payment_done.php
                $bookingReference = '#CK' . str_pad($booking['reservationID'], 5, '0', STR_PAD_LEFT);
            ?>
                <div class="col-md-6 mb-4 d-flex">
                <div class="booking-card flex-fill" data-status="<?php echo $booking['reservationStatus']; ?>" data-date="<?php echo $booking['reservationDate']; ?>">
                    <div class="booking-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                        <div class="booking-reference"><?php echo $bookingReference; ?></div>
                        <h5 class="mb-1"><?php echo $booking['packageName']; ?> Room (<?php echo $booking['roomName']; ?>)</h5>
                        <p class="mb-0" style="opacity: 0.9;">Booked on <?php echo $createdDate; ?></p>
                        </div>
                        <div class="col-4 text-end">
                        <span class="booking-status status-<?php echo $displayStatus; ?>">
                            <?php 
                            echo ucfirst($displayStatus);
                            if ($booking['reservationStatus'] == 'pending' && !$booking['paymentStatus']) {
                            echo ' Payment';
                            }
                            ?>
                        </span>
                        </div>
                    </div>
                    </div>
                    
                    <div class="booking-details">
                    <div class="detail-item">
                        <div class="detail-label">Date</div>
                        <div class="detail-value"><?php echo $formattedDate; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Time</div>
                        <div class="detail-value"><?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Duration</div>
                        <div class="detail-value"><?php echo $booking['duration']; ?> hour<?php echo $booking['duration'] > 1 ? 's' : ''; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><?php echo $booking['reservationStatus'] == 'cancelled' ? 'Refund Amount' : 'Total Amount'; ?></div>
                        <div class="detail-value">RM <?php echo number_format($booking['totalPrice'], 2); ?></div>
                    </div>
                    </div>
                    
                    <?php if ($booking['paymentStatus'] && $booking['paymentMethod']): ?>
                    <div class="payment-info">
                        <strong>Payment Info:</strong> <?php echo ucfirst($booking['paymentMethod']); ?> - 
                        Status: <?php echo ucfirst($booking['paymentStatus']); ?>
                        <?php if ($booking['paymentDate']): ?>
                        (<?php echo date('M j, Y', strtotime($booking['paymentDate'])); ?>)
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mt-3 align-items-end">
                    <div class="col-md-7">
                        <?php if (!empty($booking['addInfo'])): ?>
                        <h6><strong>
                            <?php echo $booking['reservationStatus'] == 'cancelled' ? 'Cancellation Details:' : 'Special Requests:'; ?>
                        </strong></h6>
                        <p class="text-muted mb-2" style="font-size: 0.9rem;"><?php echo nl2br(htmlspecialchars($booking['addInfo'])); ?></p>
                        <?php else: ?>
                        <h6><strong>Special Requests:</strong></h6>
                        <p class="text-muted mb-2" style="font-size: 0.9rem;">No special requests.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        <div class="action-buttons">
                        <?php if ($booking['paymentStatus']): ?>
                            <a href="view_invoice.php?id=<?php echo $booking['reservationID']; ?>" class="btn-action btn-secondary-action">
                            <?php echo $booking['reservationStatus'] == 'cancelled' ? 'Refund Receipt' : 'View Invoice'; ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($booking['reservationStatus'] == 'pending'): ?>
                            <?php if (!$booking['paymentStatus']): ?>
                            <a href="complete_payment.php?id=<?php echo $booking['reservationID']; ?>" class="btn-action btn-success-action">Pay Now</a>
                            <?php endif; ?>
                            <a href="cancel_reservation.php?id=<?php echo $booking['reservationID']; ?>" class="btn-action btn-danger-action">Cancel</a>
                        <?php elseif ($booking['reservationStatus'] == 'confirmed' && !$isPastBooking): ?>
                            <?php 
                            $bookingDateTime = new DateTime($booking['reservationDate'] . ' ' . $booking['startTime']);
                            $hoursUntilBooking = ($bookingDateTime->getTimestamp() - $currentDate->getTimestamp()) / 3600;
                            ?>
                            <?php if ($hoursUntilBooking > 24): ?>
                            <a href="cancel_reservation.php?id=<?php echo $booking['reservationID']; ?>" class="btn-action btn-danger-action">Cancel</a>
                            <?php else: ?>
                            <span class="cannot-cancel-text">Cannot cancel (< 24hrs)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            <?php endwhile; ?>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <span class="mbr-iconfont mobi-mbri-search mobi-mbri"></span>
                <h4>No bookings found</h4>
                <p class="text-muted">You haven't made any bookings yet. <a href="make_reservation.php">Make your first booking</a>!</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Empty State for filtered results (hidden by default) -->
        <div id="empty-state" class="empty-state" style="display: none;">
            <span class="mbr-iconfont mobi-mbri-search mobi-mbri"></span>
            <h4>No bookings found</h4>
            <p class="text-muted">Try adjusting your filters or <a href="make_reservation.php">make a new booking</a>.</p>
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
    // Filter functionality
    function applyFilters() {
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const bookingCards = document.querySelectorAll('.booking-card');
        let visibleCount = 0;
        
        bookingCards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            const cardDate = card.getAttribute('data-date');
            
            let showCard = true;
            
            // Status filter
            if (statusFilter && cardStatus !== statusFilter) {
                showCard = false;
            }
            
            // Date range filter
            if (dateFrom && cardDate < dateFrom) {
                showCard = false;
            }
            
            if (dateTo && cardDate > dateTo) {
                showCard = false;
            }
            
            if (showCard) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show empty state if no bookings match filters
        const emptyState = document.getElementById('empty-state');
        if (visibleCount === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }
    }

    // Clear filters
    function clearFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        
        const bookingCards = document.querySelectorAll('.booking-card');
        bookingCards.forEach(card => {
            card.style.display = 'block';
        });
        
        document.getElementById('empty-state').style.display = 'none';
    }

    // Set date input constraints
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        const oneYearAgo = new Date();
        oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
        const minDate = oneYearAgo.toISOString().split('T')[0];
        
        document.getElementById('dateFrom').setAttribute('min', minDate);
        document.getElementById('dateFrom').setAttribute('max', today);
        document.getElementById('dateTo').setAttribute('min', minDate);
        document.getElementById('dateTo').setAttribute('max', today);
    });
  </script>

  <input name="animation" type="hidden">
</body>
</html>