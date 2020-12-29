<?php
	session_start();
	require_once(dirname(__FILE__).'/php/security.php');
	require_once(dirname(__FILE__).'/php/mail/mail.php');
	$err = null;
	$suc = null;

	if (sizeof($_GET) > 0 && isset($_GET['verify']) && isset($_GET['id'])) {
		my_mysql_connect();
		$id = mysql_real_escape_string($_GET['id']);
		$verify = mysql_real_escape_string($_GET['verify']);
		$query = "UPDATE users SET verified=1 where id=$id AND md5(registered) = '$verify' and verified=0;";
        $result = mysql_query($query) or die('Error 0.1');
        $count = mysql_affected_rows();
        if ($count == 1) {
			$email = get_user_email($id);
        	success_login($email,true);
        	header('Location: ./login.php');
    		exit();
        } else {
        	$err = "Account already verified. Please login";
        }
	} else if (sizeof($_POST) > 0 && isset($_POST['login'])) {
		//login
		my_mysql_connect();

        $email = mysql_real_escape_string(trim($_POST['email']));
        $pass = mysql_real_escape_string(trim($_POST['pass']));
        $remember = isset($_POST['remember']) ? true : false;

        $pass = mb_convert_encoding($pass, "UTF-8");
        $pass = md5($pass);
        //die ($pass);

        $query = "SELECT password, verified, id from users where email='$email' LIMIT 0,1;";
        $result = mysql_query($query) or die('Error 1');
        $count = mysql_num_rows($result);

        if ($count == 1) {
            $row = mysql_fetch_row($result);
            $test_pass = $row[0];
            $verified = $row[1];
            if ($verified == "0") {
            	$id = $row[2];
            	$err = "Account not yet verified. <a href='https://clicktrait.com/ab/login.php?resend=1&id=$id'>Click here</a> to resend the email.";
            } else if (strcmp($test_pass, $pass) == 0) {
            	//ok
            	success_login($email, $remember);
            	header('Location: ./login.php');
    			exit();
            } else {
            	//wrong password
            	$err = "Invalid password";
            }
        } else {
        	//email not found
        	$err = "Email address not found";
        }

	} else if (sizeof($_GET) > 0 && isset($_GET['resend']) && isset($_GET['id'])) {
		activation_email($_GET['id']);
		$suc = "The activation email has been sent to your inbox.";
	}

	if ( $err == null && (isset($_COOKIE["abcro"]) && strlen($_COOKIE["abcro"]) > 0) || (isset($_SESSION['abcro']) && strlen($_SESSION['abcro']) > 0) ) {				
		if (valid_session()) {
			//just in case they are already logged in, let's make sure & redirect them to index.php
        	header("Location: ./index.php");
			die();
		}        
	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Login to start optimizing your conversion rate. Our big data driven A/B and MultiVariate testing tools at your fingertips.">
		<meta name="author" content="Clicktrait">
		<link rel="shortcut icon" href="assets/images/favicon_1.ico">
		<title>Login to Clicktrait's A/B and MultiVariate Testing Platform</title>

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
											<h4><b>Login</b></h4>
											<form class="form-horizontal m-t-20" action="login.php" method="POST">
												<div class="form-group ">
													<div class="col-xs-12">
														<input name="email" class="form-control" type="text" required="" placeholder="Your email address" value="<?php if ($err != null && isset($_POST['email']) && isset($_POST['login'])) echo $_POST['email']; ?>">
													</div>
												</div>
						
												<div class="form-group">
													<div class="col-xs-12">
														<input name="pass" class="form-control" type="password" required="" placeholder="Your password" value="<?php if ($err != null && isset($_POST['pass']) && isset($_POST['login'])) echo $_POST['pass']; ?>">
													</div>
												</div>
						
												<div class="form-group ">
													<div class="col-xs-12">
														<div class="checkbox checkbox-primary">
															<input name="remember" id="checkbox-signin" type="checkbox" <?php if ($err != null && isset($_POST['remember']) && isset($_POST['login'])) echo 'checked'; ?>>
															<label for="checkbox-signin"> Remember me </label>
														</div>
						
													</div>
												</div>
						
												<div class="form-group text-right m-t-20">
													<div class="col-xs-12">
														<button name="login" class="btn btn-primary text-uppercase waves-effect waves-light w-sm" type="submit">
															Log In
														</button>
													</div>
												</div>
												
												<div class="form-group m-t-20 m-b-0">
													<div class="col-sm-12 text-right">
														<a href="recover.php" class="text-primary"> Lost your password?</a>
													</div>
												</div>

											</form>
										</div>
									</div>
									<div class="col-lg-3"></div>									
								</div>
								<div class="row">

									<div class="col-lg-3"></div>
									<div class="col-lg-6">
										<?php if ($err != null) {?>
											<div class="form-group m-t-20 m-b-0">
												<div class="alert alert-danger" style="text-align:center">
													<?php echo $err; ?>
												</div>
											</div>
										<?php } else if ($suc != null) {?>
											<div class="form-group m-t-20 m-b-0">
												<div class="alert alert-success" style="text-align:center; color:green">
													<?php echo $suc; ?>
												</div>
											</div>
										<?php } ?>
									</div>
									<div class="col-lg-3"></div>
									
								</div>
								
							</div>
						</div>
						<a href="https://clicktrait.com/">Home</a> | <a href="http://forum.clicktrait.com/" target="_blank">Forum</a> | <a href="http://forum.clicktrait.com/topic/15-clicktrait-support/" target="_blank">Support</a> | <a href="https://clicktrait.com/ab/order.php">Clicktrait PRO</a> | <a href="https://clicktrait.com/ab/signup.php">Sign up</a> | 2016 &copy; Clicktrait.com
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

  <?php
	  if (sizeof($_GET) > 0 && isset($_GET['msg'])) {
	  	print '      <script src="assets/plugins/notifyjs/dist/notify.min.js"></script><script src="assets/plugins/notifications/notify-metro.js"></script>';
    	print "<script>$(document).ready(function() { $.Notification.autoHideNotify('error', 'top right', '".($_GET['msg'])."')  });</script>";
    }
  ?>

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