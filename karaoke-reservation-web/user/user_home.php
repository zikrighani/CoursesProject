<?php
include '../dbconfig.php';
require_once '../session_security.php'; // This includes the function definitions and header settings

// Start session if not already started (redundant if session_security.php always starts, but safe)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- START: MANUAL SESSION VALIDATION FOR USER HOME ---
// This mimics the robust checks in your admin_dashboard.php, but for users.
if (!isset($_SESSION['userID']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'user' || // IMPORTANT: Check for 'user' role here
    empty($_SESSION['userID'])) {

    // Clear any existing session data if invalid
    session_unset();
    session_destroy();

    header("Location: ../login.php?msg=session_expired"); // Redirect to login page
    exit();
}

// Optional: Regenerate session ID for security (from admin_dashboard's logic)
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}
// --- END: MANUAL SESSION VALIDATION FOR USER HOME ---


$username = $_SESSION['fullName'] ?? 'Guest';
$userID = $_SESSION['userID'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="description" content="Crony Karaoke - User Dashboard">
    <title>Dashboard - Crony Karaoke</title>

    <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">

    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/animatecss/animate.css">
    <link rel="stylesheet" href="../assets/theme/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>

    <link rel="stylesheet" href="style.css">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

</head>

<body>
    <script>
        // Prevent back button access after logout
        (function() {
            if (window.history && window.history.pushState) {
                window.addEventListener('load', function() {
                    // Push a dummy state to prevent going back
                    window.history.pushState('preventBack', null, '');

                    // Listen for popstate (back button)
                    window.addEventListener('popstate', function(event) {
                        // Check if user is still logged in by making a quick session check
                        fetch('../check_session.php') // Corrected path for check_session.php
                            .then(response => response.json())
                            .then(data => {
                                // Assuming check_session.php returns 'valid' for session status
                                // If check_session.php returns 'loggedIn' as in admin's script, use that
                                if (!data.valid) { // Adjusted based on check_session.php's 'valid' key
                                    // If not logged in, redirect to login
                                    window.location.href = '../login.php';
                                } else {
                                    // If still logged in, push state again to prevent going back
                                    window.history.pushState('preventBack', null, '');
                                }
                            })
                            .catch(() => {
                                // On error, assume not logged in or connection issue
                                window.location.href = '../login.php';
                            });
                    });
                });
            }
        })();

        // Disable right-click and common keyboard shortcuts (optional security measure)
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        document.addEventListener('keydown', function(e) {
            // Disable F12, Ctrl+Shift+I, Ctrl+U, Ctrl+Shift+C
            if (e.keyCode === 123 ||
                (e.ctrlKey && e.shiftKey && e.keyCode === 73) ||
                (e.ctrlKey && e.keyCode === 85) ||
                (e.ctrlKey && e.shiftKey && e.keyCode === 67)) {
                e.preventDefault();
                return false;
            }
        });

        // Confirm logout function
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Check session periodically
        setInterval(function() {
            fetch('../check_session.php') // Corrected path for check_session.php
                .then(response => response.json())
                .then(data => {
                    // Assuming check_session.php returns 'valid' for session status
                    // If check_session.php returns 'loggedIn' as in admin's script, use that
                    if (!data.valid) { // Adjusted based on check_session.php's 'valid' key
                        alert('Your session has expired. You will be redirected to login.');
                        window.location.href = '../login.php';
                    }
                })
                .catch(() => {
                    // Session check failed, redirect to login
                    window.location.href = '../login.php';
                });
        }, 300000); // Check every 5 minutes (300000 ms)
    </script>


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

    <main class="main-content">
        <div class="container">
            <div class="welcome-header">
                <h1 class="welcome-title">
                    Welcome back, <span class="username-highlight"><?php echo htmlspecialchars($username); ?></span>
                </h1>
                <p class="welcome-subtitle">Manage your bookings and explore our karaoke services</p>
            </div>

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

    <script src="../assets/web/assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/smoothscroll/smooth-scroll.js"></script>
    <script src="../assets/theme/js/script.js"></script>
</body>
</html>