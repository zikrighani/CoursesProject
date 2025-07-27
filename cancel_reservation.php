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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="description" content="Crony Karaoke - Cancel Reservation">
  <title>Cancel Reservation - Crony Karaoke</title>
  
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
  <link rel="stylesheet" href="../assets/make_reservation.css">
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
            <a class="nav-link" href="booking.php">View Bookings</a>
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
        <h1 class="welcome-title">Cancel Your Reservation</h1>
        <p class="welcome-subtitle">Select the upcoming booking you want to cancel and provide a reason</p>
      </div>

      <!-- Alert Messages -->
      <?php if (!empty($success_message)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if ($reservations->num_rows > 0): ?>
      <form id="cancelForm" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="cancel_reservation">
        
        <!-- Step 1: Choose Reservation -->
        <div class="bookings-section">
          <h2 class="section-title">
            <span class="step-number">1</span>
            Choose a Reservation to Cancel
          </h2>
          
          <div class="table-container">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th width="50"></th>
                    <th>Booking Ref</th>
                    <th>Room</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Refund</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $reservations->data_seek(0);
                  while ($reservation = $reservations->fetch_assoc()): 
                    $bookingReference = '#CK' . str_pad($reservation['reservationID'], 5, '0', STR_PAD_LEFT);
                    $formattedDate = date('d M Y', strtotime($reservation['reservationDate']));
                    $formattedStartTime = date('H:i', strtotime($reservation['startTime']));
                    $formattedEndTime = date('H:i', strtotime($reservation['endTime']));
                    $duration_text = $reservation['duration'] . "h";
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
                    
                    $statusClass = 'status-pending';
                    if ($reservation['status'] == 'confirmed') $statusClass = 'status-confirmed';
                  ?>
                  <tr class="<?php echo !$canCancel ? 'table-secondary' : ''; ?>" 
                      <?php if ($canCancel): ?>
                      onclick="selectReservation('<?php echo $reservation['reservationID']; ?>', '<?php echo htmlspecialchars($bookingReference); ?>', '<?php echo htmlspecialchars($reservation['packageName'] . ' (' . $reservation['roomName'] . ')'); ?>', '<?php echo $formattedDate; ?>', '<?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?>', '<?php echo $duration_text; ?>', '<?php echo $total_price; ?>')"
                      style="cursor: pointer;"
                      <?php endif; ?>>
                    <td>
                      <?php if ($canCancel): ?>
                      <input class="form-check-input" type="radio" name="reservation" 
                             id="res-<?php echo $reservation['reservationID']; ?>" 
                             value="<?php echo $reservation['reservationID']; ?>" 
                             <?php echo ($selected_reservation_id == $reservation['reservationID']) ? 'checked' : ''; ?>
                             required>
                      <?php else: ?>
                      <i class="fas fa-ban text-muted"></i>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?php echo $bookingReference; ?></strong>
                      <?php if (!$canCancel): ?>
                      <br><small class="text-warning"><?php echo $cancelWarning; ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($reservation['packageName'] . ' (' . $reservation['roomName'] . ')'); ?></td>
                    <td><?php echo $formattedDate; ?></td>
                    <td><?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?></td>
                    <td><?php echo $duration_text; ?></td>
                    <td>
                      <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo ucfirst($reservation['status']); ?>
                      </span>
                    </td>
                    <td><?php echo $total_price; ?></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
            <div class="invalid-feedback d-block" style="display: none !important;">
              Please choose a reservation to cancel.
            </div>
          </div>
        </div>

        <!-- Step 2: Cancellation Reason -->
        <div class="bookings-section">
          <h2 class="section-title">
            <span class="step-number">2</span>
            Tell Us Why (Optional)
          </h2>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="reason-select" class="form-label">Reason for cancellation</label>
                <select class="form-select" id="reason-select" name="reason">
                  <option value="" selected>Select a reason (optional)</option>
                  <option value="Change of plans">Change of plans</option>
                  <option value="Emergency">Emergency</option>
                  <option value="Double booking">Double booking</option>
                  <option value="Health issues">Health issues</option>
                  <option value="Weather conditions">Weather conditions</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="additional-comments" class="form-label">Additional comments</label>
                <textarea class="form-control" id="additional-comments" name="additional_comments" rows="3" 
                          placeholder="Additional comments (optional)..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Cancellation Summary -->
        <div class="bookings-section">
          <h2 class="section-title">
            <span class="step-number">3</span>
            Cancellation Summary
          </h2>
          
          <div class="dashboard-card" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;">
            <div id="cancel-summary-details">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Reference:</strong> <span id="cancel-reference">Not selected</span></p>
                  <p><strong>Room:</strong> <span id="cancel-room">Not selected</span></p>
                  <p><strong>Date:</strong> <span id="cancel-date">Not selected</span></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Time:</strong> <span id="cancel-time">Not selected</span></p>
                  <p><strong>Duration:</strong> <span id="cancel-duration">Not selected</span></p>
                  <p><strong>Refund Amount:</strong> <span id="cancel-refund">RM 0.00</span></p>
                </div>
              </div>
              <hr style="border-color: rgba(255,255,255,0.3);">
              <p class="text-center mb-3"><small>*Refund will be processed within 3-5 business days</small></p>
              <div class="text-center">
                <button type="submit" class="btn btn-light btn-lg">
                  <i class="fas fa-times-circle me-2"></i>
                  Cancel Reservation
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>

      <?php else: ?>
      <div class="no-bookings">
        <i class="fas fa-calendar-times"></i>
        <h4>No Upcoming Reservations</h4>
        <p>You don't have any upcoming reservations that can be cancelled.</p>
        <a href="make_reservation.php" class="card-button me-2">Make New Booking</a>
        <a href="user_home.php" class="card-button">Back to Home</a>
      </div>
      <?php endif; ?>
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
    // Reservation selection
    function selectReservation(id, reference, room, date, time, duration, total) {
      // Clear all selections
      document.querySelectorAll('tbody tr').forEach(row => {
        row.classList.remove('table-active');
      });
      
      // Select the clicked reservation
      event.currentTarget.classList.add('table-active');
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
      const selectedInput = document.querySelector('input[value="<?php echo $selected_reservation_id; ?>"]');
      if (selectedInput) {
        selectedInput.closest('tr').click();
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
            
            // Scroll to table
            document.querySelector('.table-container').scrollIntoView({
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
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
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
      
      // Auto-dismiss success alerts after 5 seconds
      setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(alert => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
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

  <style>
    .step-number {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2rem;
      height: 2rem;
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      border-radius: 50%;
      font-weight: 600;
      margin-right: 0.75rem;
      font-size: 0.9rem;
    }

    .table tbody tr {
      transition: all 0.2s ease;
    }

    .table tbody tr:hover:not(.table-secondary) {
      background-color: var(--bs-light);
      cursor: pointer;
    }

    .table tbody tr.table-active {
      background-color: #e3f2fd;
      border-left: 4px solid #007bff;
    }

    .form-check-input {
      transform: scale(1.2);
    }

    .alert {
      border-radius: 15px;
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .dashboard-card {
      transition: transform 0.2s ease;
    }

    .dashboard-card:hover {
      transform: translateY(-2px);
    }

    .btn-light {
      background: white;
      border: 2px solid white;
      color: #333;
      font-weight: 600;
      transition: all 0.2s ease;
    }

    .btn-light:hover {
      background: transparent;
      border-color: white;
      color: white;
    }
  </style>

</body>
</html>