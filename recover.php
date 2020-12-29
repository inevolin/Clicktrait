<?php
	session_start();
	require_once(dirname(__FILE__).'/php/security.php');
	$err = null;
	if (sizeof($_POST) > 0) {
		//login
		my_mysql_connect();
		$email = mysql_real_escape_string(trim($_POST['email']));       
		
		$query = "SELECT name from users where email='$email';";		
	    $result = mysql_query($query);
	    $count = mysql_num_rows($result);
	    if ($count == 1) {
	        $row = mysql_fetch_row($result);
	       
	        $pass = uniqid();
	        $pass_md5 = mb_convert_encoding($pass, "UTF-8");
	        $pass_md5 = md5($pass_md5);

	        $query = "UPDATE users SET password = '$pass_md5' WHERE email='$email';";
	        $result = mysql_query($query) or die('Error 1');

	        if ($result) {        	
		        $name = $row[0];
	            require_once(dirname(__FILE__).'/php/mail/mail.php');
	            send_mail_recover($email, $pass, $name);
	        }

    	} else {
        	//email not found
        	$err = "Email address not found.";
        }	
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
		<meta name="description" content="Recover your password for your account on our A/B and Multivariate testing platform.">
		<meta name="author" content="Clicktrait">
		<link rel="shortcut icon" href="assets/images/favicon_1.ico">
		<title>A/B and MultiVariate Testing Platform | Free trial | Clicktrait</title>

		<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />

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
		<div class="wrapper-page">
			<div class=" card-box">
				<div class="panel-heading">
					<h3 class="text-center"><img src="./images/logo-b.png"/></h3>
				</div>

				<div class="panel-body">
					<h4><b>Recover your password</b></h4>
					<form method="post" action="#" role="form" class="text-center">
						<div class="form-group m-b-0">
							<div class="input-group">
								<input name="email" type="email" class="form-control" placeholder="Enter Email" required="">
								<span class="input-group-btn">
									<button type="submit" class="btn btn-primary w-sm waves-effect waves-light">
										Reset
									</button> 
								</span>
							</div>
						</div>
						
						<?php if ($err != null) {?>
							<div class="form-group m-t-20 m-b-0">
								<div class="alert alert-danger" style="text-align:center">
									<?php echo $err; ?>
								</div>
							</div>
						<?php } else if (sizeof($_POST) > 0) { ?>
							<div class="form-group m-t-20 m-b-0">
								<div class="alert alert-success" style="text-align:center;color:green">
									Your new password has been sent.
								</div>
							</div>
						<?php } ?>
						<div class="form-group m-t-20 m-b-0">
							<div class="col-sm-12 text-right">
								<a href="login.php" class="text-primary"> Back to login</a>
							</div>
						</div>
					</form>
				</div>
			</div>
			
		<a href="https://clicktrait.com/">Home</a> | <a href="http://forum.clicktrait.com/" target="_blank">Forum</a> | <a href="http://forum.clicktrait.com/topic/15-clicktrait-support/" target="_blank">Support</a> | <a href="https://clicktrait.com/ab/order.php">Sign up</a> | 2016 &copy; Clicktrait.com
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

	</body>
</html>