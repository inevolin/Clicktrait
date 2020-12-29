<?php
    session_start();
    require_once(dirname(__FILE__).'/php/security.php');    
    if (!valid_session()) {
      unset_all();
      header("Location: ./login.php");
      die();
    } 

    $uid = my_user_id();
    $is_pro = is_pro_user($uid);
    $is_pro_trial_user = is_pro_trial_user($uid);
    $email = get_user_email($uid);


    //listen for password change
    $err = null;
    $suc = null;
    if (sizeof($_POST) > 0 && isset($_POST['pass0'])) {
      
      $pass0 = trim($_POST['pass0']);
      $pass1 = mysql_real_escape_string(trim($_POST['pass1']));
      $pass2 = mysql_real_escape_string(trim($_POST['pass2']));
      if (strlen($pass0) <= 0) {
        $err = "Current password missing.";
      } else if (strlen($pass1) < 5) {
        $err = "The required password length is 5 characters or more.";
      } else if (strlen($pass2) <= 0 || strcmp($pass1, $pass2) != 0) {
        $err = "Passwords do not match.";
      }

      if ($err == null) {      
          $pass0_md5 = mb_convert_encoding($pass0, "UTF-8");
          $pass0_md5 = md5($pass0_md5);

          $query = "SELECT * from users where id = $uid and password = '$pass0_md5' LIMIT 1;";
          $result = mysql_query($query) or die('Error 1');
          $count = mysql_num_rows($result);
          if ($count == 1) {
            
            $newp = mb_convert_encoding($pass1, "UTF-8");
            $newp = md5($newp);
            $query = "UPDATE users set password='$newp' where id = $uid;";
            $result = mysql_query($query);
            if (!$result) {
              $err = "Something went wrong.";
            } else {
              $suc = "Password changed successfully!";
            }
          } else {
            $err = "Current password is incorrect.";
          }      
      }

    }
  
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Clicktrait">
    <link rel="shortcut icon" href="assets/images/favicon_1.ico">
    <title>A/B and MultiVariate Testing Platform | Free trial | Clicktrait</title>
  <!--Footable-->
  <link href="assets/plugins/footable/css/footable.core.css" rel="stylesheet">
  <link href="assets/plugins/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet" />

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

<body class="fixed-left">

  <!-- Begin page -->
  <div id="wrapper">

    <?php
      require_once(dirname(__FILE__).'/php/top_bar.php');
      require_once(dirname(__FILE__).'/php/left_sidebar.php');
    ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="content-page">
      <!-- Start content -->
      <div class="content">
        <div class="container">
          <div class="row">
            <div class="col-sm-6">
              <div class="card-box">
                <div class="row">
                  <div class="col-md-11">
                    <h4 class="m-t-0 header-title"><b>Change your password</b></h4>
                    <br>                      
                    <form class="form-horizontal" role="form" method="POST" action="account.php">
                      <div class="form-group">
                          <label for="pass0" class="col-sm-3 control-label">Current Password</label>
                          <div class="col-sm-9">
                            <input type="password" class="form-control" id="pass0" name="pass0" placeholder="Current Password">
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="pass1" class="col-sm-3 control-label">New Password</label>
                          <div class="col-sm-9">
                            <input type="password" class="form-control" id="pass1" name="pass1" placeholder="New Password">
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="pass2" class="col-sm-3 control-label">Repeat Password</label>
                          <div class="col-sm-9">
                            <input type="password" class="form-control" id="pass2" name="pass2" placeholder="Repeat Password">
                          </div>
                      </div>
                      <div class="form-group m-b-0">
                          <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-default waves-effect waves-light">Submit</button>
                          </div>
                      </div>
                      <?php if ($err != null && isset($_POST['pass0'])) {?>
                        <div class="form-group m-t-20 m-b-0">
                          <div class="alert alert-danger" style="text-align:center">
                            <?php echo $err; ?>
                          </div>
                        </div>
                      <?php } ?>
                      <?php if ($suc != null) {?>
                        <div class="form-group m-t-20 m-b-0">
                          <div class="alert alert-success" style="text-align:center;color:green;">
                            <?php echo $suc; ?>
                          </div>
                        </div>
                      <?php } ?>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="card-box">
                <div class="row">
                  <div class="col-md-11">
                    <form class="form-horizontal" role="form">                                                                                  
                      <div class="form-group">
                        <label class="col-md-3 control-label">Your email address</label>
                          <div class="col-md-6">
                              <input type="text" class="form-control" readonly="" value="<?php echo $email; ?>">
                          </div>
                      </div>     
                    </form>
                  </div>
                </div>
              </div>
            </div>


          </div>
        </div>
        <!-- container -->

      </div>
      <!-- content -->

<?php require_once(dirname(__FILE__).'/php/footer.php'); ?>

    </div>
    <!-- ============================================================== -->
    <!-- End Right content here -->
    <!-- ============================================================== -->



  </div>
  <!-- END wrapper -->

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

  <!--FooTable-->
  <script src="assets/plugins/footable/js/footable.all.min.js"></script>

  <script src="assets/plugins/bootstrap-select/dist/js/bootstrap-select.min.js" type="text/javascript"></script>

  <!--FooTable Example-->
  <script src="assets/pages/jquery.footable.js"></script>

  <?php
	  if (sizeof($_GET) > 0 && isset($_GET['msg'])) {
	  	print '      <script src="assets/plugins/notifyjs/dist/notify.min.js"></script><script src="assets/plugins/notifications/notify-metro.js"></script>';
    	print "<script>$(document).ready(function() { $.Notification.autoHideNotify('error', 'top right', '".($_GET['msg'])."')  });</script>";
    }
  ?>

</body>

</html>