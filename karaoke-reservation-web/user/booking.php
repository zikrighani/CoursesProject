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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="description" content="Crony Karaoke - View Your Bookings">
  <title>View Bookings - Crony Karaoke</title>
  
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

  <link rel="stylesheet" href="style.css">
  <style>

    /* ==========================================================================
       MAIN CONTENT
       ========================================================================== */
    .main-content {
      padding: 120px 0 80px 0;
      min-height: calc(100vh - 200px);
    }

    .page-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .page-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--text-white);
      margin-bottom: 0.5rem;
    }

    .page-subtitle {
      font-size: 1.1rem;
      color: var(--text-gray);
      margin-bottom: 2rem;
    }

    /* ==========================================================================
       STATS CARDS
       ========================================================================== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .stats-card {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 1.5rem;
      border: 1px solid var(--border-color);
      text-align: center;
      transition: all 0.3s ease;
    }

    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(124, 108, 255, 0.2);
      border-color: var(--primary-color);
    }

    .stats-number {
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
    }

    .stats-label {
      color: var(--text-gray);
      font-size: 0.9rem;
    }

    .stats-refunded { color: var(--success-color); }
    .stats-pending { color: var(--warning-color); }

    /* ==========================================================================
       FILTER SECTION
       ========================================================================== */
    .filter-section {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 1.5rem;
      border: 1px solid var(--border-color);
      margin-bottom: 2rem;
    }

    .filter-form {
      display: flex;
      gap: 1rem;
      align-items: end;
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .filter-label {
      color: var(--text-gray);
      font-size: 0.9rem;
      font-weight: 500;
    }

    .form-control, .form-select {
      background: var(--bg-dark);
      border: 1px solid var(--border-color);
      color: var(--text-white);
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
    }

    .form-control:focus, .form-select:focus {
      background: var(--bg-dark);
      border-color: var(--primary-color);
      color: var(--text-white);
      box-shadow: 0 0 0 0.2rem rgba(124, 108, 255, 0.25);
    }

    .btn-filter {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-filter:hover {
      background: var(--primary-light);
      color: white;
    }

    .btn-clear {
      background: transparent;
      color: var(--text-gray);
      border: 1px solid var(--border-color);
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-clear:hover {
      background: var(--border-color);
      color: var(--text-white);
    }

    /* ==========================================================================
       BOOKING CARDS
       ========================================================================== */
    .bookings-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .booking-card {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 1.5rem;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }

    .booking-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(124, 108, 255, 0.2);
      border-color: var(--primary-color);
    }

    .booking-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-color);
    }

    .booking-reference {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 0.25rem;
    }

    .booking-room {
      font-size: 1rem;
      color: var(--text-white);
      margin-bottom: 0.25rem;
    }

    .booking-created {
      font-size: 0.85rem;
      color: var(--text-gray);
    }

    .status-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-confirmed {
      background: rgba(35, 209, 96, 0.2);
      color: var(--success-color);
      border: 1px solid rgba(35, 209, 96, 0.3);
    }

    .status-cancelled {
      background: rgba(255, 56, 96, 0.2);
      color: var(--error-color);
      border: 1px solid rgba(255, 56, 96, 0.3);
    }

    .status-pending {
      background: rgba(255, 221, 87, 0.2);
      color: var(--warning-color);
      border: 1px solid rgba(255, 221, 87, 0.3);
    }

    .status-completed {
      background: rgba(124, 108, 255, 0.2);
      color: var(--primary-color);
      border: 1px solid rgba(124, 108, 255, 0.3);
    }

    .booking-details {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .detail-item {
      text-align: center;
      padding: 0.75rem;
      background: rgba(255, 255, 255, 0.02);
      border-radius: 8px;
    }

    .detail-label {
      font-size: 0.8rem;
      color: var(--text-gray);
      margin-bottom: 0.25rem;
    }

    .detail-value {
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--text-white);
    }

    .payment-info {
      background: rgba(124, 108, 255, 0.1);
      border: 1px solid rgba(124, 108, 255, 0.2);
      border-radius: 8px;
      padding: 0.75rem;
      margin: 1rem 0;
      font-size: 0.85rem;
      color: var(--text-gray);
    }

    .special-requests {
      margin: 1rem 0;
    }

    .special-requests h6 {
      font-size: 0.9rem;
      color: var(--text-white);
      margin-bottom: 0.5rem;
    }

    .special-requests p {
      font-size: 0.85rem;
      color: var(--text-gray);
      margin: 0;
    }

    .action-buttons {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      justify-content: flex-end;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--border-color);
    }

    .action-btn {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }

    .btn-primary-action {
      background: var(--primary-color);
      color: white;
    }

    .btn-primary-action:hover {
      background: var(--primary-light);
      color: white;
      text-decoration: none;
    }

    .btn-secondary-action {
      background: var(--text-dark-gray);
      color: white;
    }

    .btn-secondary-action:hover {
      background: var(--text-gray);
      color: white;
      text-decoration: none;
    }

    .btn-danger-action {
      background: var(--error-color);
      color: white;
    }

    .btn-danger-action:hover {
      background: #ff1744;
      color: white;
      text-decoration: none;
    }

    .btn-success-action {
      background: var(--success-color);
      color: white;
    }

    .btn-success-action:hover {
      background: #1e8e3e;
      color: white;
      text-decoration: none;
    }

    .cannot-cancel-text {
      font-size: 0.75rem;
      color: var(--text-gray);
      font-style: italic;
    }

    /* ==========================================================================
       EMPTY STATE
       ========================================================================== */
    .empty-state {
      text-align: center;
      padding: 3rem;
      background: var(--bg-card);
      border-radius: 16px;
      border: 1px solid var(--border-color);
    }

    .empty-state i {
      font-size: 3rem;
      color: var(--text-dark-gray);
      margin-bottom: 1rem;
    }

    .empty-state h4 {
      color: var(--text-white);
      margin-bottom: 1rem;
    }

    .empty-state p {
      color: var(--text-gray);
      margin-bottom: 1.5rem;
    }

    .empty-state a {
      color: var(--primary-color);
      text-decoration: none;
    }

    .empty-state a:hover {
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
      text-align: center;
    }

    .footer-info {
      font-size: 0.95rem;
    }

    .footer-buttons {
      margin-bottom: 1rem;
    }

    .footer-buttons .btn {
      margin: 0 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-light {
      background: var(--bg-card);
      color: var(--text-white);
      border: 1px solid var(--border-color);
    }

    .btn-light:hover {
      background: var(--border-color);
      color: var(--text-white);
      text-decoration: none;
    }

    /* ==========================================================================
       RESPONSIVE DESIGN
       ========================================================================== */
    @media (max-width: 768px) {
      .page-title {
        font-size: 2rem;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }
      
      .bookings-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .booking-details {
        grid-template-columns: 1fr;
        gap: 0.5rem;
      }
      
      .filter-form {
        flex-direction: column;
        align-items: stretch;
      }
      
      .main-content {
        padding: 100px 0 60px 0;
      }
    }

    @media (max-width: 576px) {
      .page-title {
        font-size: 1.75rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .booking-card {
        padding: 1rem;
      }
      
      .action-buttons {
        flex-direction: column;
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
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Your Bookings</h1>
        <p class="page-subtitle">View and manage all your karaoke room reservations</p>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stats-card">
          <div class="stats-number stats-refunded">RM <?php echo number_format($stats['total_refunded'], 2); ?></div>
          <div class="stats-label">Total Refunded</div>
        </div>
        <div class="stats-card">
          <div class="stats-number stats-pending">RM <?php echo number_format($stats['pending_refund'], 2); ?></div>
          <div class="stats-label">Pending Refund</div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="filter-section">
        <form class="filter-form" onsubmit="applyFilters(); return false;">
          <div class="filter-group">
            <label class="filter-label">Status</label>
            <select class="form-select" id="statusFilter">
              <option value="">All Status</option>
              <option value="confirmed">Confirmed</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label">From Date</label>
            <input type="date" class="form-control" id="dateFrom">
          </div>
          <div class="filter-group">
            <label class="filter-label">To Date</label>
            <input type="date" class="form-control" id="dateTo">
          </div>
          <div class="filter-group">
            <button type="submit" class="btn-filter">
              <i class="fas fa-search me-1"></i>Filter
            </button>
          </div>
          <div class="filter-group">
            <button type="button" class="btn-clear" onclick="clearFilters()">
              Clear
            </button>
          </div>
        </form>
      </div>

      <!-- Bookings List -->
      <div id="bookings-container">
        <?php if ($bookings->num_rows > 0): ?>
        <div class="bookings-grid">
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
            
            // Generate booking reference
            $bookingReference = '#CK' . str_pad($booking['reservationID'], 5, '0', STR_PAD_LEFT);
        ?>
          <div class="booking-card" data-status="<?php echo $booking['reservationStatus']; ?>" data-date="<?php echo $booking['reservationDate']; ?>">
            <div class="booking-header">
              <div>
                <div class="booking-reference"><?php echo $bookingReference; ?></div>
                <div class="booking-room"><?php echo $booking['packageName']; ?> Room (<?php echo $booking['roomName']; ?>)</div>
                <div class="booking-created">Booked on <?php echo $createdDate; ?></div>
              </div>
              <div>
                <span class="status-badge status-<?php echo $displayStatus; ?>">
                  <?php 
                  echo ucfirst($displayStatus);
                  if ($booking['reservationStatus'] == 'pending' && !$booking['paymentStatus']) {
                    echo ' Payment';
                  }
                  ?>
                </span>
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

            <div class="special-requests">
              <h6>
                <?php echo $booking['reservationStatus'] == 'cancelled' ? 'Cancellation Details:' : 'Special Requests:'; ?>
              </h6>
              <p>
                <?php echo !empty($booking['addInfo']) ? nl2br(htmlspecialchars($booking['addInfo'])) : 'No special requests.'; ?>
              </p>
            </div>

            <div class="action-buttons">
              <?php if ($booking['paymentStatus']): ?>
                <a href="view_invoice.php?id=<?php echo $booking['reservationID']; ?>" class="action-btn btn-secondary-action">
                  <?php echo $booking['reservationStatus'] == 'cancelled' ? 'Refund Receipt' : 'View Receipt'; ?>
                </a>
              <?php endif; ?>

              <?php if ($booking['reservationStatus'] == 'pending'): ?>
                <?php if (!$booking['paymentStatus']): ?>
                <a href="complete_payment.php?id=<?php echo $booking['reservationID']; ?>" class="action-btn btn-success-action">Pay Now</a>
                <?php endif; ?>
                <a href="cancel_reservation.php?id=<?php echo $booking['reservationID']; ?>" class="action-btn btn-danger-action">Cancel</a>
              <?php elseif ($booking['reservationStatus'] == 'confirmed' && !$isPastBooking): ?>
                <?php 
                $bookingDateTime = new DateTime($booking['reservationDate'] . ' ' . $booking['startTime']);
                $hoursUntilBooking = ($bookingDateTime->getTimestamp() - $currentDate->getTimestamp()) / 3600;
                ?>
                <?php if ($hoursUntilBooking > 24): ?>
                <a href="cancel_reservation.php?id=<?php echo $booking['reservationID']; ?>" class="action-btn btn-danger-action">Cancel</a>
                <?php else: ?>
                <div class="cannot-cancel-text">Cannot cancel within 24 hours</div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
        </div>
        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
          <i class="fas fa-calendar-times"></i>
          <h4>No Bookings Found</h4>
          <p>You haven't made any reservations yet. Ready to book your first karaoke session?</p>
          <a href="make_reservation.php">Make Your First Booking</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-buttons">
        <a href="user_home.php" class="btn btn-light">
          <i class="fas fa-home me-1"></i>Back to Dashboard
        </a>
        <a href="make_reservation.php" class="btn btn-primary-action">
          <i class="fas fa-plus me-1"></i>New Booking
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

  <!-- Scripts -->
  <script src="../assets/web/assets/jquery/jquery.min.js"></script>
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/smoothscroll/smooth-scroll.js"></script>
  <script src="../assets/theme/js/script.js"></script>

  <script>
    // Filter functionality
    function applyFilters() {
      const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
      const dateFrom = document.getElementById('dateFrom').value;
      const dateTo = document.getElementById('dateTo').value;
      
      const bookingCards = document.querySelectorAll('.booking-card');
      
      bookingCards.forEach(card => {
        let showCard = true;
        
        // Status filter
        if (statusFilter && !card.dataset.status.toLowerCase().includes(statusFilter)) {
          showCard = false;
        }
        
        // Date filter
        const cardDate = card.dataset.date;
        if (dateFrom && cardDate < dateFrom) {
          showCard = false;
        }
        if (dateTo && cardDate > dateTo) {
          showCard = false;
        }
        
        card.style.display = showCard ? 'block' : 'none';
      });
      
      // Check if any cards are visible
      const visibleCards = document.querySelectorAll('.booking-card[style="display: block;"], .booking-card:not([style*="display: none"])');
      const bookingsGrid = document.querySelector('.bookings-grid');
      const emptyState = document.querySelector('.empty-state');
      
      if (visibleCards.length === 0 && bookingsGrid) {
        if (!document.querySelector('.filter-empty-state')) {
          const filterEmptyState = document.createElement('div');
          filterEmptyState.className = 'empty-state filter-empty-state';
          filterEmptyState.innerHTML = `
            <i class="fas fa-search"></i>
            <h4>No Bookings Match Your Filters</h4>
            <p>Try adjusting your filter criteria to see more results.</p>
            <button onclick="clearFilters()" class="btn btn-primary-action">Clear Filters</button>
          `;
          bookingsGrid.parentNode.insertBefore(filterEmptyState, bookingsGrid.nextSibling);
        }
        document.querySelector('.filter-empty-state').style.display = 'block';
      } else {
        const filterEmptyState = document.querySelector('.filter-empty-state');
        if (filterEmptyState) {
          filterEmptyState.style.display = 'none';
        }
      }
    }
    
    function clearFilters() {
      document.getElementById('statusFilter').value = '';
      document.getElementById('dateFrom').value = '';
      document.getElementById('dateTo').value = '';
      
      const bookingCards = document.querySelectorAll('.booking-card');
      bookingCards.forEach(card => {
        card.style.display = 'block';
      });
      
      const filterEmptyState = document.querySelector('.filter-empty-state');
      if (filterEmptyState) {
        filterEmptyState.style.display = 'none';
      }
    }
    
    // Auto-apply filters when form inputs change
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFrom').addEventListener('change', applyFilters);
    document.getElementById('dateTo').addEventListener('change', applyFilters);
    
    // Confirmation for cancellation links
    document.querySelectorAll('.btn-danger-action').forEach(link => {
      link.addEventListener('click', function(e) {
        if (this.textContent.trim() === 'Cancel') {
          if (!confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
            e.preventDefault();
          }
        }
      });
    });
    
    // Smooth scroll for any anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
    
    // Add loading states to action buttons
    document.querySelectorAll('.action-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        if (this.href && !this.href.includes('#')) {
          this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
          this.style.pointerEvents = 'none';
        }
      });
    });
  </script>
</body>
</html>