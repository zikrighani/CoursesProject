<?php
include 'dbconfig.php';
session_start();

$name = $email = $phone = $password = "";
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["fullName"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $phone = mysqli_real_escape_string($conn, $_POST["phone"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already exists. Please use another.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($conn, "INSERT INTO users (fullName, email, phone, password) VALUES ('$name', '$email', '$phone', '$hashedPassword')");
            if ($insert) {
                $success = "Registration successful. You can now <a href='login.php'>login</a>.";
                $name = $email = $phone = $password = "";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html  >
<head>
  <!-- Site made with Mobirise Website Builder v6.0.5, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="generator" content="Mobirise v6.0.5, mobirise.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="">
  
  
  <title>Register</title>
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="assets/animatecss/animate.css">
  <link rel="stylesheet" href="assets/socicon/css/styles.css">
  <link rel="stylesheet" href="assets/theme/css/style.css">
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>
  <link rel="preload" as="style" href="assets/mobirise/css/mbr-additional.css?v=XAcbcX"><link rel="stylesheet" href="assets/mobirise/css/mbr-additional.css?v=XAcbcX" type="text/css">

  
  
  
</head>
<body>
  
  <section data-bs-version="5.1" class="header15 cid-uLCqqM0bTW" id="header15-24">
	

	
	
	<div class="container">
		<div class="row justify-content-center">
			<div class="card col-12 col-lg-12">
				<div class="card-wrapper wrap">
					<div class="card-box align-center">
						<h1 class="card-title mbr-fonts-style mb-4 display-1">
							<strong>Register As Our Member</strong></h1>
						
						
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section data-bs-version="5.1" class="form5 cid-uLCqdcIcif" id="form02-23">
    
    
    <div class="container">
        <div class="row justify-content-center">
            <?php if (!empty($error)): ?>
                <div class="alert text-center col-12" style="background-color: #ff3860; color: #fff;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success text-center col-12">
                    <?php echo $success; // allow HTML for link ?>
                </div>
            <?php endif; ?>
            <div class="col-12 content-head">
                <div class="mbr-section-head mb-5">
                    <h3 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                        <strong>Register</strong>
                    </h3>
                    <h4 class="mbr-section-subtitle mbr-fonts-style align-center mb-0 mt-4 display-7">
                        Already have an account? <a href="login.php" class="text-primary">Login here</a>
                    </h4>
                </div>
            </div>
        </div>
        <form action="" method="POST" class="mbr-form form-with-styler">
            <div class="dragArea row">
                <div class="col-md-6 col-sm-12 form-group mb-3">
                    <input type="text" name="fullName" placeholder="Full Name" class="form-control" required value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="col-md-6 col-sm-12 form-group mb-3">
                    <input type="email" name="email" placeholder="E-mail" class="form-control" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="col-md-6 col-sm-12 form-group mb-3">
                    <input type="tel" name="phone" placeholder="Phone" class="form-control" required value="<?php echo htmlspecialchars($phone); ?>">
                </div>
                <div class="col-md-6 col-sm-12 form-group mb-3">
                    <input type="password" name="password" placeholder="Password" class="form-control" required>
                </div>
                <div class="col-12 align-center mbr-section-btn">
                    <button type="submit" class="btn btn-primary display-7 w-100">Register Now</button>
                </div>
            </div>
        </form>
    </div>

    </div>
</section>

<section data-bs-version="5.1" class="footer3 cid-uLCpCfgtNL" once="footers" id="footer03-22" style="padding-top: 40px; padding-bottom: 50px;">

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