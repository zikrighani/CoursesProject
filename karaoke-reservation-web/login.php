<?php
include 'dbconfig.php'; // your database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs and sanitize
    $email = trim($_POST['email']);
    $password = $_POST['password'];  // plain text from form

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Query user by email
        $sql = "SELECT userID, fullName, password, role FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            // Verify password (assuming password_hash stored)
            if (password_verify($password, $user['password'])) {
                // Login success: set session variables
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['fullName'] = $user['fullName'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: user/user_dashboard.php");
                }
                exit;
            } else {
                $error = "Incorrect email or password.";
            }
        } else {
            $error = "Incorrect email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html  >
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="generator" content="Mobirise v6.0.5, mobirise.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="">
  
  <title>Login</title>
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="assets/animatecss/animate.css">
  <link rel="stylesheet" href="assets/socicon/css/styles.css">
  <link rel="stylesheet" href="assets/theme/css/style.css">
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>
  <link rel="preload" as="style" href="assets/mobirise/css/mbr-additional.css?v=MVjKJ1"><link rel="stylesheet" href="assets/mobirise/css/mbr-additional.css?v=MVjKJ1" type="text/css">

  
  
  
</head>
<body>
  
  <section data-bs-version="5.1" class="header15 cid-uLCs2vR839" id="header15-26">	
	<div class="container">
		<div class="row justify-content-center">
			<div class="card col-12 col-lg-12">
				<div class="card-wrapper wrap">
					<div class="card-box align-center">
						<h1 class="card-title mbr-fonts-style mb-4 display-1">
							<strong>Welcome Back</strong></h1>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="form5 cid-uLCs2w5fcu" id="form02-27">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <h3 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                        <strong>Login</strong></h3>
                    <h4 class="mbr-section-subtitle mbr-fonts-style align-center mb-0 mt-4 display-7">Doesn't Have an Account, <a href="register.php" class="text-primary">register Here</a></h4>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8 mx-auto mbr-form" data-form-type="formoid">
                <form action="" method="POST" class="mbr-form form-with-styler" data-form-title="Form Name">
                    <div class="row">
                        <?php if (!empty($error)) : ?>
                            <div class="alert alert-danger col-12" style="color: #fff; background-color: #dc3545; border-color: #dc3545;">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dragArea row">
                        <div class="col-md col-sm-12 form-group mb-3" data-for="email">
                            <input type="email" name="email" placeholder="E-mail" data-form-field="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" id="email-form02-27" required>
                        </div>
                        <div class="col-12 form-group mb-3" data-for="password">
                            <input type="password" name="password" placeholder="Password" data-form-field="password" class="form-control" id="password-form02-27" required>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 align-center mbr-section-btn">
                            <button type="submit" class="btn btn-primary display-7">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section data-bs-version="5.1" class="footer3 cid-uLCs2wg56P" once="footers" id="footer03-28" style="padding-top: 40px; padding-bottom: 50px;">

    <div class="container">
        <div class="row">
            <div class="row-links">
                <ul class="header-menu">
                  
                <li class="header-menu-item mbr-fonts-style display-5"><a href="index.html#contacts01-1r" class="text-white text-primary">Contact</a></li><li class="header-menu-item mbr-fonts-style display-5"><a href="index.html#header18-1n" class="text-white text-primary">Home</a></li><li class="header-menu-item mbr-fonts-style display-5"><a href="register.php" class="text-white text-primary">Register</a></li><li class="header-menu-item mbr-fonts-style display-5"><a href="login.php" class="text-white text-primary">Login</a></li></ul>
              </div>

            <div class="col-12 mt-4">
                <div class="social-row">
                    <div class="soc-item">
                        <a href="https://mobiri.se/" target="_blank">
                            <span class="mbr-iconfont socicon socicon-facebook display-7"></span>
                        </a>
                    </div>
                    <div class="soc-item">
                        <a href="https://mobiri.se/" target="_blank">
                            <span class="mbr-iconfont socicon-instagram socicon"></span>
                        </a>
                    </div>
                                   
                </div>
            </div>
            <div class="col-12 mt-5">
                <p class="mbr-fonts-style copyright display-7">
                    © Copyright 2025 Crony Karaoke - All Rights Reserved
                </p>
            </div>
        </div>
    </div>
</section>
<script src="assets/web/assets/jquery/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/smoothscroll/smooth-scroll.js"></script>
<script src="assets/dropdown/js/script.min.js"></script>
<script src="assets/touchswipe/jquery.touch-swipe.min.js"></script>
<script src="assets/theme/js/script.js"></script>
<script src="assets/formoid/formoid.min.js"></script>
<script src="assets/ytplayer/index.js"></script>
<script src="assets/ytplayer/script.js"></script>
<script src="assets/ytplayer/vimeo_player.js"></script>
<script src="assets/ytplayer/youtube_player.js"></script>
  
  <input name="animation" type="hidden">
  </body>
</html>