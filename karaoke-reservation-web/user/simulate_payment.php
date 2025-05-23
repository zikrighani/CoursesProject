<!DOCTYPE html>
<html>
<head>
  <!-- Site made with Mobirise Website Builder v6.0.5, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="generator" content="Mobirise v6.0.5, mobirise.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
  <meta name="description" content="Crony Karaoke - Processing Payment">

  <title>Payment Processing - Crony Karaoke</title>
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
  <link rel="preload" as="style" href="../assets/mobirise/css/mbr-additional.css?v=f0jscm">
  <link rel="stylesheet" href="../assets/mobirise/css/mbr-additional.css?v=f0jscm" type="text/css">

  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .content-wrapper {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 4rem 0;
      background: #edefeb;
    }
    .payment-processing-card {
      background: white;
      border-radius: 10px;
      padding: 3rem;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 500px;
      width: 100%;
    }
    .payment-header {
      margin-bottom: 2rem;
    }
    .spinner {
      width: 80px;
      height: 80px;
      border: 8px solid #f3f3f3;
      border-top: 8px solid #493d9e;
      border-radius: 50%;
      animation: spin 1.5s linear infinite;
      margin: 0 auto 2rem;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .processing-text {
      color: #333;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .processing-subtext {
      color: #666;
      margin-bottom: 2rem;
    }
    .page-header {
      background: linear-gradient(45deg, #493d9e, #8571ff);
      color: white;
      padding: 60px 0;
      margin-bottom: 0;
    }
  </style>
</head>
<body>

<section class="page-header" style="padding-top: 40px; padding-bottom: 20px; margin-bottom: 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 class="display-3 fw-bold">Processing Your Payment</h1>
                <p class="lead">Please wait while we secure your reservation</p>
            </div>
        </div>
    </div>
</section>

<div class="content-wrapper d-flex justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100%;">
            <div class="col-md-6 d-flex justify-content-center align-items-center">
                <div class="payment-processing-card">
                    <h3 class="processing-text">Processing your payment...</h3>
                    <p class="processing-subtext">Please don't close this window. You'll be redirected automatically when the process is complete.</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section data-bs-version="5.1" class="footer3 cid-uLCpCfgtNL" once="footers" id="footer03-22" style="padding-top: 0px; padding-bottom: 30px;">

    <div class="container">
        <div class="row">
            <div class="col-12 content-head">
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

<script>
    // Animate progress bar
    let progress = 0;
    const progressBar = document.querySelector('.progress-bar');
    
    const interval = setInterval(function() {
        progress += 5;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        
        if (progress >= 100) {
            clearInterval(interval);
            // Redirect after progress reaches 100%
            setTimeout(function() {
                window.location.href = "payment_done.php";
            }, 500);
        }
    }, 150); // Slightly faster to complete in about 3 seconds
</script>

</body>
</html>