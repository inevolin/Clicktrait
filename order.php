<?php

	session_start();
	require_once(dirname(__FILE__).'/php/security.php');
	require_once(dirname(__FILE__).'/php/mail/mail.php');
	require_once(dirname(__FILE__).'/php/payments.php');
	$err = null;



    if (sizeof($_POST) > 0 && (isset($_POST['sub']) || isset($_POST['pay']))) {  
		$uid = register_new_user();
		if ($uid > 0) {
			$email = trim($_POST['email']);
			success_login($email,true);
			proceed_paypal($uid, $subtotal, $vat, $vatp, $total);
		} else {
			$err = $uid; // it'll return error message.
		}

	}

	if ( $err == null && (isset($_COOKIE["abcro"]) && strlen($_COOKIE["abcro"]) > 0) || (isset($_SESSION['abcro']) && strlen($_SESSION['abcro']) > 0) ) {				
		if (valid_session()) {
			//just in case they are already logged in, let's make sure & redirect them to index.php
        	header("Location: ./index.php");
			die();
		}        
	}

	$post_parameterized = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Sign up for a PRO account and start optimizing your conversion rate. Our big data driven A/B and MultiVariate testing tools at your fingertips.">
		<meta name="author" content="Clicktrait">
		<link rel="shortcut icon" href="assets/images/favicon_1.ico">
		<title>Clicktrait PRO order form | A/B and MultiVariate Testing Platform</title>

		<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />

        <style>
        	@media (min-width: 1200px) {
        		.custom-border {
        			border-left:1px solid #EEE;
        		}
        	}
        </style>

        <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <script src="assets/js/modernizr.min.js"></script>


	</head>
	<body>
		<div class="account-pages"></div>
		<div class="clearfix"></div>
		
		
		<div class="container-alt">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1">
					<div class="wrapper-page signup-signin-page">
						<div class="card-box">
							<div class="panel-heading">
								<h3 class="text-center"><img src="./images/logo-b.png"/></h3>
							</div>
			
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-3"></div>
															
									<div class="col-lg-6">
										<div class="p-20">
											<h4>Sign up for Clicktrait <strong>PRO</strong></h4>
											<form class="form-horizontal m-t-20" action="<?php echo $post_parameterized; ?>" method="POST">
												
												<div class="form-group ">
													<div class="col-xs-12">
														<input name="name" class="form-control" type="text" required="" placeholder="Your first name*" value="<?php if ($err != null && isset($_POST['name'])) echo $_POST['name']; ?>">
													</div>
												</div>										

												<div class="form-group ">
													<div class="col-xs-12">
														<input name="email" class="form-control" type="email" required="" placeholder="Your business email*" value="<?php if ($err != null && isset($_POST['email'])) echo $_POST['email']; ?>">
													</div>
												</div>										
						
												<div class="form-group">
													<div class="col-xs-12">
														<input name="pass" class="form-control" type="password" required="" placeholder="Choose a safe password*" value="<?php if ($err != null && isset($_POST['pass'])) echo $_POST['pass']; ?>">
													</div>
												</div>												
						
												<div class="form-group">
													<div class="col-xs-12">
														<div class="checkbox checkbox-primary">
															<input name="toc" id="checkbox-signup" type="checkbox" required="" <?php if ($err != null && isset($_POST['toc'])) echo 'checked=""'; ?>>
															<label for="checkbox-signup">I accept the <a href="https://clicktrait.com/terms" target="_blank">terms</a> and <a href="https://clicktrait.com/privacy" target="_blank">privacy policy</a>.</label>
														</div>
													</div>
												</div>

												<?php if ($err != null) {?>
													<div class="col-lg-12">														
														<div class="form-group m-t-20 m-b-0">
															<div class="alert alert-danger" style="text-align:center">
																<?php echo $err; ?>
															</div>
														</div>														
													</div>
												<?php } ?>
						
												<div class="form-group m-t-40 m-b-40">							
													<div class="col-xs-12 text-right">
														<div class="col-xs-9">
															<h4>Subtotal:</h4>
														</div>
														<div class="col-xs-3">
															<h4>$<?php echo number_format($subtotal,2); ?></h4>
														</div>
													</div>
													<div class="col-xs-12 text-right">
														<div class="col-xs-9">
															<h4>VAT (<?php echo $country. ($vatp==null?'':" $vatp%"); ?>):</h4>
														</div>
														<div class="col-xs-3">
															<h4>$<?php echo number_format($vat,2); ?></h4>
														</div>
													</div>
													<div class="col-xs-12 text-right">
														<div class="col-xs-9">
															<h3>Total:</h3>
														</div>
														<div class="col-xs-3">
															<h3>$<?php echo number_format($total,2); ?></h3>
														</div>													
													</div>
												</div>

												<div class="form-group text-right">
													<div class="col-xs-12 m-t-20">
														<button name="sub" class="btn btn-success text-uppercase waves-effect waves-light w-sm btn-lg" type="submit" title="Create an automatic monthly payment plan.">
							                              Subscribe 
							                            </button>
							      
							                            <button name="pay" class="btn btn-inverse text-uppercase waves-effect waves-light w-sm btn-lg" type="submit" title="No contract, decide when you pay.">
							                              Pay 
							                            </button>     
													</div>

													<div class="col-xs-12 m-t-20">
														<img src="https://clicktrait.com/ab/images/pp_cc.png" style="max-width:100%;opacity:0.8" height="68" />
													</div>

												</div>
												
											</form>
										</div>
									</div>

									<div class="col-lg-3"></div>
								</div>
								
							</div>
						</div>
						<a href="https://clicktrait.com/">Home</a> | <a href="http://forum.clicktrait.com/" target="_blank">Forum</a> | <a href="http://forum.clicktrait.com/topic/15-clicktrait-support/" target="_blank">Support</a> | <a href="https://clicktrait.com/ab/signup.php">Free sign up</a> | <a href="https://clicktrait.com/ab/login.php">Login</a> | 2016 &copy; Clicktrait.com
					</div>
				</div>
			</div>
		</div>
		

		<script>
			var resizefunc = [];
		</script>

		<!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/detect.js"></script>
        <script src="assets/js/fastclick.js"></script>
        <script src="assets/js/jquery.slimscroll.js"></script>
        <script src="assets/js/jquery.blockUI.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/wow.min.js"></script>
        <script src="assets/js/jquery.nicescroll.js"></script>
        <script src="assets/js/jquery.scrollTo.min.js"></script>


        <script src="assets/js/jquery.core.js"></script>
        <script src="assets/js/jquery.app.js"></script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-74899014-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');

fbq('init', '495074397331093');
fbq('track', "PageView");</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=495074397331093&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
	</body>
</html>