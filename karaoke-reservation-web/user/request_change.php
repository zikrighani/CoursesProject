<?php 
 include '../dbconfig.php'; 
 session_start(); 


 // Check if user is logged in 
 if (!isset($_SESSION['userID'])) { 
    header("Location: ../login.php"); 
    exit(); 
 } 

 $userID = $_SESSION['userID']; 
 $message = ''; 
 $messageType = ''; 

 // Fetch current user data 
 $query = "SELECT * FROM users WHERE userID = ?"; 
 $stmt = $conn->prepare($query); 
 $stmt->bind_param("i", $userID); 
 $stmt->execute(); 
 $result = $stmt->get_result(); 
 $user = $result->fetch_assoc(); 

 if (!$user) { 
    header("Location: ../login.php"); 
    exit(); 
 } 

 // Check for existing pending requests 
 $checkQuery = "SELECT * FROM requestChanges WHERE userID = ? ORDER BY createdAt DESC LIMIT 1"; 
 $checkStmt = $conn->prepare($checkQuery); 
 $checkStmt->bind_param("i", $userID); 
 $checkStmt->execute(); 
 $checkResult = $checkStmt->get_result(); 
 $existingRequest = $checkResult->fetch_assoc(); 

 // Handle form submission 
 if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    $emailChange = isset($_POST['emailChange']) ? 'yes' : 'no'; 
    $phnoChange = isset($_POST['phnoChange']) ? 'yes' : 'no'; 
    $newEmail = trim($_POST['newEmail']); 
    $newPNo = trim($_POST['newPNo']); 
    
    // Validation 
    $errors = array(); 
    
    if ($emailChange == 'no' && $phnoChange == 'no') { 
        $errors[] = "Please select at least one field to change"; 
    } 
    
    if ($emailChange == 'yes') { 
        if (empty($newEmail)) { 
            $errors[] = "New email is required"; 
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) { 
            $errors[] = "Invalid email format"; 
        } elseif ($newEmail == $user['email']) { 
            $errors[] = "New email must be different from current email"; 
        } 
    } 
    
    if ($phnoChange == 'yes') { 
        if (empty($newPNo)) { 
            $errors[] = "New phone number is required"; 
        } elseif (!preg_match('/^[0-9]{10,12}$/', $newPNo)) { 
            $errors[] = "Phone number must be 10-12 digits"; 
        } elseif ($newPNo == $user['phone']) { 
            $errors[] = "New phone number must be different from current number"; 
        } 
    } 
    
    // Check for existing pending request 
    if ($existingRequest) { 
        $errors[] = "You already have a pending change request. Please wait for it to be processed."; 
    } 
    
    if (empty($errors)) { 
        // Set empty values for unchanged fields 
        if ($emailChange == 'no') $newEmail = ''; 
        if ($phnoChange == 'no') $newPNo = ''; 
        
        // Insert request into database 
        $insertQuery = "INSERT INTO requestChanges (userID, emailChange, phnoChange, newEmail, newPNo, createdAt) VALUES (?, ?, ?, ?, ?, NOW())"; 
        $insertStmt = $conn->prepare($insertQuery); 
        $insertStmt->bind_param("issss", $userID, $emailChange, $phnoChange, $newEmail, $newPNo); 
        
        if ($insertStmt->execute()) { 
            $message = "Change request submitted successfully! We will review your request and contact you within 2-3 business days."; 
            $messageType = "success"; 
            
            // Refresh to show the new request 
            $checkStmt->execute(); 
            $checkResult = $checkStmt->get_result(); 
            $existingRequest = $checkResult->fetch_assoc(); 
        } else { 
            $message = "Error submitting request. Please try again."; 
            $messageType = "error"; 
        } 
    } else { 
        $message = implode("<br>", $errors); 
        $messageType = "error"; 
    } 
 } 

 // Format phone number for display 
 function formatPhoneNumber($phone) { 
    if (strlen($phone) >= 10) { 
        if (substr($phone, 0, 2) == '60') { 
            return '+' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . '-' . substr($phone, 4, 3) . ' ' . substr($phone, 7); 
        } else { 
            return '+60 ' . substr($phone, 1, 2) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6); 
        } 
    } 
    return $phone; 
 } 
 ?> 

 <!DOCTYPE html> 
 <html lang="en"> 
 <head> 
   <meta charset="UTF-8"> 
   <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1"> 
   <meta name="description" content="Crony Karaoke - Request Change"> 
   <title>Request Change - Crony Karaoke</title> 
   
   <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon"> 
   
   <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css"> 
   <link rel="stylesheet" href="../assets/animatecss/animate.css"> 
   <link rel="stylesheet" href="../assets/theme/css/style.css"> 
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> 
   
   <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'"> 
   <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript> 

   <style> 
     /* Global Styles */ 
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

     /* Navigation */ 
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

     /* Main Content */ 
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

     /* Profile Container */ 
     .table-container { 
       background: var(--bg-card); 
       border-radius: 16px; 
       border: 1px solid var(--border-color); 
       overflow: hidden; 
       margin-bottom: 3rem; 
     } 

     .profile-section { 
       padding: 2rem; 
     } 

     .section-title { 
       font-size: 2rem; 
       font-weight: 700; 
       color: var(--text-white); 
       margin-bottom: 2rem; 
     } 

     /* Current Info Display */ 
     .info-grid { 
       display: grid; 
       grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
       gap: 1rem; 
       margin-bottom: 2rem; 
     } 

     .info-item { 
       background: rgba(255, 255, 255, 0.03); 
       padding: 1rem; 
       border-radius: 8px; 
       border: 1px solid var(--border-color); 
     } 

     .info-label { 
       font-size: 0.85rem; 
       color: var(--text-gray); 
       margin-bottom: 0.25rem; 
       text-transform: uppercase; 
       font-weight: 600; 
     } 

     .info-value { 
       color: var(--text-white); 
       font-weight: 500; 
     } 

     /* Form Styles */ 
     .form-group { 
       margin-bottom: 1.5rem; 
     } 

     .form-label { 
       color: var(--text-white); 
       font-weight: 600; 
       margin-bottom: 0.5rem; 
       display: block; 
     } 

     .form-control { 
       background: rgba(255, 255, 255, 0.05); 
       border: 1px solid var(--border-color); 
       color: var(--text-white); 
       border-radius: 8px; 
       padding: 12px 15px; 
       font-size: 14px; 
       transition: all 0.3s ease; 
     } 

     .form-control:focus { 
       background: rgba(255, 255, 255, 0.08); 
       border-color: var(--primary-color); 
       box-shadow: 0 0 0 0.2rem rgba(124, 108, 255, 0.25); 
       color: var(--text-white); 
     } 

     .form-control:disabled { 
       background: rgba(255, 255, 255, 0.02); 
       border-color: var(--border-color); 
       color: var(--text-gray); 
       cursor: not-allowed; 
     } 

     .form-control::placeholder { 
       color: var(--text-dark-gray); 
     } 

     .form-text { 
       color: var(--text-gray); 
       font-size: 0.875rem; 
       margin-top: 0.25rem; 
     } 

     /* Checkbox Styles */ 
     .form-check { 
       margin-bottom: 1rem; 
     } 

     .form-check-input { 
       background-color: rgba(255, 255, 255, 0.05); 
       border: 1px solid var(--border-color); 
       border-radius: 4px; 
     } 

     .form-check-input:checked { 
       background-color: var(--primary-color); 
       border-color: var(--primary-color); 
     } 

     .form-check-input:focus { 
       box-shadow: 0 0 0 0.2rem rgba(124, 108, 255, 0.25); 
     } 

     .form-check-label { 
       color: var(--text-white); 
       font-weight: 500; 
     } 

     /* Buttons */ 
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

     .btn-secondary { 
       background: var(--text-dark-gray); 
       color: white; 
       border: none; 
       padding: 12px 24px; 
       border-radius: 8px; 
       font-weight: 600; 
       text-decoration: none; 
       display: inline-block; 
       transition: all 0.3s ease; 
     } 

     .btn-secondary:hover { 
       background: var(--text-gray); 
       color: white; 
       text-decoration: none; 
     } 

     .btn-outline-secondary { 
       background: transparent; 
       border: 1px solid var(--border-color); 
       color: var(--text-gray); 
       padding: 12px 24px; 
       border-radius: 8px; 
       font-weight: 600; 
       text-decoration: none; 
       display: inline-block; 
       transition: all 0.3s ease; 
     } 

     .btn-outline-secondary:hover { 
       background: rgba(255, 255, 255, 0.05); 
       color: var(--text-white); 
       text-decoration: none; 
     } 

     /* Alerts */ 
     .alert { 
       border-radius: 8px; 
       border: none; 
       padding: 1rem 1.25rem; 
       margin-bottom: 2rem; 
     } 

     .alert-success { 
       background: rgba(35, 209, 96, 0.1); 
       color: var(--success-color); 
       border: 1px solid rgba(35, 209, 96, 0.2); 
     } 

     .alert-danger { 
       background: rgba(255, 56, 96, 0.1); 
       color: var(--error-color); 
       border: 1px solid rgba(255, 56, 96, 0.2); 
     } 

     .alert-warning { 
       background: rgba(255, 221, 87, 0.1); 
       color: var(--warning-color); 
       border: 1px solid rgba(255, 221, 87, 0.2); 
     } 

     /* Info Note */ 
     .info-note { 
       background: rgba(124, 108, 255, 0.1); 
       border: 1px solid rgba(124, 108, 255, 0.2); 
       border-radius: 8px; 
       padding: 1rem; 
       margin-bottom: 1.5rem; 
     } 

     .info-note small { 
       color: var(--primary-light); 
     } 

     /* Pending Request */ 
     .pending-request { 
       background: rgba(255, 221, 87, 0.1); 
       border: 1px solid rgba(255, 221, 87, 0.2); 
       border-radius: 8px; 
       padding: 1.5rem; 
       margin-bottom: 2rem; 
     } 

     .pending-title { 
       color: var(--warning-color); 
       font-weight: 700; 
       margin-bottom: 1rem; 
     } 

     /* Form Actions */ 
     .form-actions { 
       display: flex; 
       justify-content: space-between; 
       align-items: center; 
       padding-top: 1.5rem; 
       border-top: 1px solid var(--border-color); 
       margin-top: 2rem; 
     } 

     /* Footer */ 
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

     /* Responsive Design */ 
     @media (max-width: 768px) { 
       .welcome-title { 
         font-size: 2rem; 
       } 
       
       .profile-section { 
         padding: 1.5rem; 
       } 
       
       .info-grid { 
         grid-template-columns: 1fr; 
       } 
       
       .form-actions { 
         flex-direction: column; 
         gap: 1rem; 
         align-items: stretch; 
       } 
       
       .main-content { 
         padding: 100px 0 60px 0; 
       } 
     } 

     @media (max-width: 576px) { 
       .welcome-title { 
         font-size: 1.75rem; 
       } 
       
       .profile-section { 
         padding: 1.25rem; 
       } 
     } 
   </style> 
 </head> 

 <body> 
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

   <main class="main-content"> 
     <div class="container"> 
       <div class="welcome-header"> 
         <h1 class="welcome-title">Request Information Change</h1> 
         <p class="welcome-subtitle">Request changes to your email address or phone number</p> 
       </div> 

       <?php if (!empty($message)): ?> 
       <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-danger'; ?>" role="alert"> 
         <strong><?php echo $messageType == 'success' ? 'Success!' : 'Error!'; ?></strong> <?php echo $message; ?> 
       </div> 
       <?php endif; ?> 

       <div class="row justify-content-center"> 
         <div class="col-lg-10"> 
           <div class="table-container"> 
             <div class="profile-section"> 
               <h3 class="section-title">Current Information</h3> 
               <div class="info-grid"> 
                 <div class="info-item"> 
                   <div class="info-label">Email Address</div> 
                   <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div> 
                 </div> 
                 <div class="info-item"> 
                   <div class="info-label">Phone Number</div> 
                   <div class="info-value"><?php echo formatPhoneNumber($user['phone']); ?></div> 
                 </div> 
               </div> 
             </div> 

             <?php if ($existingRequest): ?> 
             <div class="profile-section"> 
               <div class="pending-request"> 
                 <h4 class="pending-title"> 
                   <i class="fas fa-clock me-2"></i> 
                   Pending Change Request 
                 </h4> 
                 <p class="mb-2">You have a pending change request submitted on <?php echo date('M d, Y', strtotime($existingRequest['createdAt'])); ?>.</p> 
                 <div class="row"> 
                   <?php if ($existingRequest['emailChange'] == 'yes'): ?> 
                   <div class="col-md-6"> 
                     <strong>Email Change:</strong> <?php echo htmlspecialchars($existingRequest['newEmail']); ?> 
                   </div> 
                   <?php endif; ?> 
                   <?php if ($existingRequest['phnoChange'] == 'yes'): ?> 
                   <div class="col-md-6"> 
                     <strong>Phone Change:</strong> <?php echo formatPhoneNumber($existingRequest['newPNo']); ?> 
                   </div> 
                   <?php endif; ?> 
                 </div> 
                 <p class="mt-2 mb-0"><small>Please wait for the current request to be processed before submitting a new one.</small></p> 
               </div> 
             </div> 
             <?php endif; ?> 

             <?php if (!$existingRequest): ?> 
             <div class="profile-section"> 
               <h3 class="section-title">Submit Change Request</h3> 
               
               <div class="info-note"> 
                 <small><strong>Important:</strong> Changes to email and phone number require verification. You will be contacted within 2-3 business days to complete the verification process.</small> 
               </div> 
               
               <form method="POST"> 
                 <div class="row"> 
                   <div class="col-md-6"> 
                     <div class="form-group"> 
                       <div class="form-check"> 
                         <input class="form-check-input" type="checkbox" id="emailChange" name="emailChange" onchange="toggleEmailField()"> 
                         <label class="form-check-label" for="emailChange"> 
                           Change Email Address 
                         </label> 
                       </div> 
                     </div> 
                     
                     <div class="form-group" id="emailGroup" style="display: none;"> 
                       <label for="newEmail" class="form-label">New Email Address</label> 
                       <input type="email" class="form-control" id="newEmail" name="newEmail" placeholder="Enter new email address"> 
                       <div class="form-text">Must be a valid email address</div> 
                     </div> 
                   </div> 
                   
                   <div class="col-md-6"> 
                     <div class="form-group"> 
                       <div class="form-check"> 
                         <input class="form-check-input" type="checkbox" id="phnoChange" name="phnoChange" onchange="togglePhoneField()"> 
                         <label class="form-check-label" for="phnoChange"> 
                           Change Phone Number 
                         </label> 
                       </div> 
                     </div> 
                     
                     <div class="form-group" id="phoneGroup" style="display: none;"> 
                       <label for="newPNo" class="form-label">New Phone Number</label> 
                       <input type="tel" class="form-control" id="newPNo" name="newPNo" placeholder="Enter new phone number (without +60)"> 
                       <div class="form-text">Enter 10-12 digits without country code</div> 
                     </div> 
                   </div> 
                 </div> 

                 <div class="form-actions"> 
                   <a href="profile_update.php" class="btn-secondary"> 
                     <i class="fas fa-arrow-left me-2"></i> 
                     Back to Profile 
                   </a> 
                   <div> 
                     <button type="reset" class="btn-outline-secondary me-2" onclick="resetForm()"> 
                       <i class="fas fa-undo me-2"></i> 
                       Reset 
                     </button> 
                     <button type="submit" class="card-button"> 
                       <i class="fas fa-paper-plane me-2"></i> 
                       Submit Request 
                     </button> 
                   </div> 
                 </div> 
               </form> 
             </div> 
             <?php else: ?> 
             <div class="profile-section"> 
               <div class="form-actions"> 
                 <a href="profile_update.php" class="btn-secondary"> 
                   <i class="fas fa-arrow-left me-2"></i> 
                   Back to Profile 
                 </a> 
               </div> 
             </div> 
             <?php endif; ?> 
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

   <script> 
     function toggleEmailField() { 
       var checkbox = document.getElementById('emailChange'); 
       var emailGroup = document.getElementById('emailGroup'); 
       var emailInput = document.getElementById('newEmail'); 
       
       if (checkbox.checked) { 
         emailGroup.style.display = 'block'; 
         emailInput.required = true; 
       } else { 
         emailGroup.style.display = 'none'; 
         emailInput.required = false; 
         emailInput.value = ''; 
       } 
     } 

     function togglePhoneField() { 
       var checkbox = document.getElementById('phnoChange'); 
       var phoneGroup = document.getElementById('phoneGroup'); 
       var phoneInput = document.getElementById('newPNo'); 
       
       if (checkbox.checked) { 
         phoneGroup.style.display = 'block'; 
         phoneInput.required = true; 
       } else { 
         phoneGroup.style.display = 'none'; 
         phoneInput.required = false; 
         phoneInput.value = ''; 
       } 
     } 

     function resetForm() { 
       document.getElementById('emailChange').checked = false; 
       document.getElementById('phnoChange').checked = false; 
       document.getElementById('emailGroup').style.display = 'none'; 
       document.getElementById('phoneGroup').style.display = 'none'; 
       document.getElementById('newEmail').required = false; 
       document.getElementById('newPNo').required = false; 
     } 

     // Phone number formatting 
     document.getElementById('newPNo').addEventListener('input', function() { 
       this.value = this.value.replace(/[^0-9]/g, ''); 
     }); 

     // Auto-hide alert messages after 5 seconds 
     <?php if (!empty($message)): ?> 
     setTimeout(function() { 
       var alert = document.querySelector('.alert'); 
       if (alert) { 
         alert.style.display = 'none'; 
       } 
     }, 5000); 
     <?php endif; ?> 
   </script> 
 </body> 
 </html>