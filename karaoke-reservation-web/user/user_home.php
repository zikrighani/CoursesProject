<?php
include '../dbconfig.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['fullName'];
$userID = $_SESSION['userID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="description" content="Crony Karaoke - User Dashboard">
  <title>Dashboard - Crony Karaoke</title>
  
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
      --warning-color: #ffdd57;
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

    /* ==========================================================================
       MAIN CONTENT
       ========================================================================== */
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

    .username-highlight {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* ==========================================================================
       DASHBOARD CARDS
       ========================================================================== */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .dashboard-card {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 2rem;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      text-align: center;
    }

    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(124, 108, 255, 0.2);
      border-color: var(--primary-color);
    }

    .card-icon {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
    }

    .card-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--text-white);
      margin-bottom: 0.5rem;
    }

    .card-description {
      color: var(--text-gray);
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
    }

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

    /* ==========================================================================
       BOOKINGS TABLE
       ========================================================================== */
    .section-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-white);
      text-align: center;
      margin-bottom: 2rem;
    }

    .table-container {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 2rem;
      border: 1px solid var(--border-color);
      margin-bottom: 3rem;
      overflow-x: auto;
    }

    .table {
      color: var(--text-white);
      margin-bottom: 0;
    }

    .table thead th {
      border-bottom: 2px solid var(--border-color);
      background: transparent;
      color: var(--text-white);
      font-weight: 600;
      padding: 1rem 0.75rem;
    }

    .table tbody td {
      border-bottom: 1px solid var(--border-color);
      background: transparent;
      color: var(--text-gray);
      padding: 1rem 0.75rem;
    }

    .table tbody tr:hover {
      background: rgba(255, 255, 255, 0.02);
    }

    .status-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
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

    .action-btn {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      margin-right: 0.5rem;
    }

    .btn-cancel {
      background: var(--error-color);
      color: white;
    }

    .btn-cancel:hover {
      background: #ff1744;
      color: white;
      text-decoration: none;
    }

    .btn-detail {
      background: var(--primary-color);
      color: white;
    }

    .btn-detail:hover {
      background: var(--primary-light);
      color: white;
      text-decoration: none;
    }

    .no-bookings {
      text-align: center;
      padding: 3rem;
      color: var(--text-gray);
    }

    .no-bookings i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: var(--text-dark-gray);
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
      .welcome-title {
        font-size: 2rem;
      }
      
      .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .dashboard-card {
        padding: 1.5rem;
      }
      
      .table-container {
        padding: 1rem;
      }
      
      .main-content {
        padding: 100px 0 60px 0;
      }
    }

    @media (max-width: 576px) {
      .welcome-title {
        font-size: 1.75rem;
      }
      
      .dashboard-card {
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
            <a class="nav-link" href="make_reservation.php">Book Room</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#promotions">Promotions</a>
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
        <h1 class="welcome-title">
          Welcome back, <span class="username-highlight"><?php echo htmlspecialchars($username); ?></span>
        </h1>
        <p class="welcome-subtitle">Manage your bookings and explore our karaoke services</p>
      </div>

      <!-- Dashboard Cards -->
      <div class="dashboard-grid">
        <div class="dashboard-card">
          <div class="card-icon">
            <i class="fas fa-calendar-plus"></i>
          </div>
          <h3 class="card-title">Make Reservation</h3>
          <p class="card-description">Book a room, select date and time for your karaoke session</p>
          <a href="make_reservation.php" class="card-button">Book Now</a>
        </div>

        <div class="dashboard-card">
          <div class="card-icon">
            <i class="fas fa-user-edit"></i>
          </div>
          <h3 class="card-title">Update Profile</h3>
          <p class="card-description">Update your email, phone number, and password settings</p>
          <a href="profile_update.php" class="card-button">Update Profile</a>
        </div>

        <div class="dashboard-card">
          <div class="card-icon">
            <i class="fas fa-receipt"></i>
          </div>
          <h3 class="card-title">View Bookings</h3>
          <p class="card-description">See all your booking history and payment details</p>
          <a href="booking.php" class="card-button">View All</a>
        </div>

        <div class="dashboard-card">
          <div class="card-icon">
            <i class="fas fa-times-circle"></i>
          </div>
          <h3 class="card-title">Cancel Booking</h3>
          <p class="card-description">Cancel your existing reservations if needed</p>
          <a href="cancel_reservation.php" class="card-button">Manage Bookings</a>
        </div>
      </div>

      <!-- Upcoming Bookings -->
      <div class="bookings-section">
        <h2 class="section-title">Your Upcoming Bookings</h2>
        
        <div class="table-container">
          <?php
          $today = date('Y-m-d');
          $query = "SELECT r.reservationID, rm.roomName, r.reservationDate, r.startTime, r.endTime, r.status
                    FROM reservations r
                    JOIN rooms rm ON r.roomID = rm.roomID
                    WHERE r.userID = ? AND r.reservationDate >= ?
                    ORDER BY r.reservationDate ASC, r.startTime ASC";

          $stmt = $conn->prepare($query);
          $stmt->bind_param("is", $userID, $today);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0) {
          ?>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Room</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  while ($row = $result->fetch_assoc()) {
                    $reservationID = $row['reservationID'];
                    $roomName = $row['roomName'];
                    $date = date("d M Y", strtotime($row['reservationDate']));
                    $startTime = date("H:i", strtotime($row['startTime']));
                    $duration = round((strtotime($row['endTime']) - strtotime($row['startTime'])) / 3600, 1);
                    $status = strtolower($row['status']);
                    
                    $statusClass = 'status-pending';
                    if ($status == 'confirmed') $statusClass = 'status-confirmed';
                    if ($status == 'cancelled') $statusClass = 'status-cancelled';
                  ?>
                    <tr>
                      <td><?php echo htmlspecialchars($roomName); ?></td>
                      <td><?php echo $date; ?></td>
                      <td><?php echo $startTime; ?></td>
                      <td><?php echo $duration; ?>h</td>
                      <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                          <?php echo ucfirst($status); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($status != 'cancelled' && $row['reservationDate'] >= $today): ?>
                          <a href="cancel_reservation.php?id=<?php echo $reservationID; ?>" class="action-btn btn-cancel">
                            Cancel
                          </a>
                        <?php endif; ?>
                        <a href="view_invoice.php?id=<?php echo $reservationID; ?>" class="action-btn btn-detail">
                          Details
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          <?php 
          } else { 
          ?>
            <div class="no-bookings">
              <i class="fas fa-calendar-times"></i>
              <h4>No Upcoming Bookings</h4>
              <p>You don't have any upcoming reservations. Ready to book your next karaoke session?</p>
              <a href="make_reservation.php" class="card-button">Book Now</a>
            </div>
          <?php 
          }
          $stmt->close();
          ?>
        </div>
      </div>

      <!-- Promotions Section -->
      <div id="promotions" class="promotions-section">
        <h2 class="section-title">Latest Promotions</h2>
        
        <div class="dashboard-grid">
          <div class="dashboard-card">
            <div class="card-icon">
              <i class="fas fa-percentage"></i>
            </div>
            <h3 class="card-title">Early Bird Discount</h3>
            <p class="card-description">Book 3 days in advance and get 10% off your total booking fee</p>
            <span class="status-badge status-confirmed">Active</span>
          </div>

          <div class="dashboard-card">
            <div class="card-icon">
              <i class="fas fa-star"></i>
            </div>
            <h3 class="card-title">VIP Room Special</h3>
            <p class="card-description">Experience our luxury VIP room with premium sound system</p>
            <span class="status-badge status-cancelled">Ended</span>
          </div>

          <div class="dashboard-card">
            <div class="card-icon">
              <i class="fas fa-users"></i>
            </div>
            <h3 class="card-title">Refer & Earn</h3>
            <p class="card-description">Refer friends and both get RM5 promo codes via email</p>
            <span class="status-badge status-confirmed">Ongoing</span>
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
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="../assets/web/assets/jquery/jquery.min.js"></script>
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/smoothscroll/smooth-scroll.js"></script>
  <script src="../assets/theme/js/script.js"></script>
</body>
</html>