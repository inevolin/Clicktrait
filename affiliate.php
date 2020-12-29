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
    $is_aff_id = is_affiliate($uid);

    //let's get all pages by our user_id
    my_mysql_connect();
    $query = "
      SELECT
        (select count(data) from meta where key1=1 and key2='$is_aff_id') total_clicks,
        sum(case when date_paid is not null then subtotal else 0 end) total_paid,
        sum(case when refunded = 0 then subtotal end) total_revenue,
        count(distinct o.user_id) pro_signups,
        count(distinct u.id) free_signups,
        count(case when o.refunded = 1 then 1 end) refunds
      FROM aff_users u    
      left join orders o    on    o.user_id=u.user_id
      left join aff_paid p  on    p.order_id=o.id
      where u.aff_id='$is_aff_id'
    ";
    $result = mysql_query($query) or die('Error 1');
    $count = mysql_num_rows($result);
    $aff_stats = mysql_fetch_assoc($result);

    $err = null;
    if (sizeof($_POST) > 0 && isset($_POST['ppemail'])) {
          my_mysql_connect();
          $ppe = mysql_real_escape_string(trim($_POST['ppemail']));          
          if (!filter_var($ppe, FILTER_VALIDATE_EMAIL)) {
            $err = "Invalid email format";
          } else {
            $query = "UPDATE affs set email_paypal='$ppe' where user_id = $uid;";
            $result = mysql_query($query);
            if (!$result) {
              $err = "Something went wrong.";
            }
          }
    }

    //application request
    if (isset($_POST['details'])) {
      $text = (nl2br(trim($_POST['details'])));        
      $store_text = mysql_real_escape_string($text);
      $query = "INSERT into meta (key1,key2,data) VALUES (0,$uid,'$store_text')";
      mysql_query($query);
      require_once(dirname(__FILE__).'/php/mail/mail.php');
      $uname = get_user_name($uid);
      $uemail = get_user_email($uid);
      $msg="User id: $uid<br>User name: $uname<br>User email: $uemail<br><br>----<br>" . $text . '<br>----<br><br>';
      send_mail_from_server($msg,$msg,"Clicktrait: affiliate request","ilya@clicktrait.com","admin");
    }


    //for paypal email addr
    $aff_data = $is_aff_id > 0 ? get_affiliate_data($uid) : null;

  
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Clicktrait">
    <link rel="shortcut icon" href="assets/images/favicon_1.ico">
    <title>Affiliate | Clicktrait</title>
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
          <?php include('notifications.php'); ?>
          </div>

          <?php if ($err != null && isset($_POST['ppemail'])) {?>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group m-t-20 m-b-0">
                  <div class="alert alert-danger" style="text-align:center">
                    <?php echo $err; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>

            <?php if (!$is_aff_id>0 && !isset($_POST['details'])) { ?>
              <div class="row">
                <div class="col-sm-6">      
                  <h3>Earn 30% from every monthly payment!</h3><br>
                </div>
              </div>

              <div class="row">   
                <div class="col-sm-6">                
                  <div class="panel panel-color panel-danger">
                    <div class="panel-heading">
                      <h3 class="panel-title">Application form</h3>
                    </div>
                    <div class="panel-body">
                      <div class="col-sm-12 m-b-20">   
                      <p>
                        You're currently not an affiliate for Clicktrait.<br>
                        Use the form below to apply. We will review your application and reply to you in 1-2 days.
                      </p>
                      </div>
                      <div class="col-sm-12">   

                      <form class="form-horizontal" role="form" method="POST" action="affiliate.php">                                                                                                         
                        <div class="form-group">
                            <label class="col-md-12">How do you exactly intend to promote Clicktrait?<br>If you have website(s) then kindly include the URL(s).</label>
                            <div class="col-md-12 m-b-20">
                                <textarea class="form-control" required="" name="details" rows="5"></textarea>
                            </div>
                            <div class="col-sm-12 m-b-20">
                              <div class="checkbox checkbox-primary">
                                <input name="toc" id="checkbox-signup" type="checkbox" required="">
                                <label for="checkbox-signup">I have read and understood the <a href="http://forum.clicktrait.com/topic/44-affiliate-system-terms-and-conditions/" target="_blank">terms &amp; rules</a>.</label>
                              </div>
                            </div>
                            <div class="col-sm-9 ">
                              <button type="submit" class="btn btn-info waves-effect waves-light">Submit</button>
                            </div>
                        </div>                      
                      </form>
                      </div>

                    </div>
                  </div>
                </div>  
              </div>
            <?php } else if (isset($_POST['details'])) { ?>
              <div class="row">          
                <div class="col-lg-4">
                  <div class="panel panel-border panel-success">
                    <div class="panel-heading">
                      <h3 class="panel-title">Application confirmation</h3>
                    </div>
                    <div class="panel-body">
                      <p>
                        Thank you,<br>
                        your application request has been received.<br>
                        We will reply to you in 1-2 days.
                      </p>
                    </div>
                  </div>
                </div>          
              </div>

            <?php } else { ?>       
            <?php $data = get_affiliate_data($uid); ?>  


              <div class="row">
                <div class="col-sm-12"><h3>Welcome back,  <?php echo $data['name']; ?>!</h3></div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="row">                       
                    <div class="col-md-12 col-lg-6">
                      <div class="widget-bg-color-icon card-box fadeInDown animated">
                          <div class="bg-icon bg-custom pull-left">
                              <i class="md md-attach-money text-white"></i>
                          </div>
                          <div class="text-right">
                              <h3 class="text-dark"><b class="counter">$<?php echo number_format($aff_stats['total_revenue'] == null ? 0 : $aff_stats['total_revenue'],2, '.', ''); ?></b></h3>
                              <p class="text-muted">Total Revenue</p>
                          </div>
                          <div class="clearfix"></div>
                      </div>
                    </div>
                    <div class="col-md-12 col-lg-6">
                        <div class="widget-bg-color-icon card-box fadeInDown animated">
                            <div class="bg-icon bg-warning pull-left">
                                <i class="md md-add-shopping-cart text-white"></i>
                            </div>
                            <div class="text-right">
                                <h3 class="text-dark"><b class="counter"><?php echo $aff_stats['pro_signups']; ?></b></h3>
                                <p class="text-muted">Total PRO sign-ups</p>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>                    
                  </div>

                  <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <div class="widget-bg-color-icon card-box fadeInDown animated">
                            <div class="bg-icon bg-danger pull-left">
                                <i class="md md-arrow-forward text-white"></i>
                            </div>
                            <div class="text-right">
                                <h3 class="text-dark"><b class="counter"><?php echo $aff_stats['total_clicks']; ?></b></h3>
                                <p class="text-muted">Total clicks</p>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6">
                      <div class="widget-bg-color-icon card-box fadeInDown animated">
                          <div class="bg-icon bg-warning pull-left">
                              <i class="md md-group text-white"></i>
                          </div>
                          <div class="text-right">
                              <h3 class="text-dark"><b class="counter"><?php echo $aff_stats['free_signups']; ?></b></h3>
                              <p class="text-muted">Total free sign-ups</p>
                          </div>
                          <div class="clearfix"></div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12 col-lg-6">
                      <div class="widget-bg-color-icon card-box fadeInDown animated">
                          <div class="bg-icon bg-info pull-left">
                              <i class="md md-payment text-white"></i>
                          </div>
                          <div class="text-right">
                              <h3 class="text-dark"><b class="counter">$<?php echo number_format($aff_stats['total_paid'] == null ? 0 : $aff_stats['total_paid'],2, '.', ''); ?></b></h3>
                              <p class="text-muted">Total payments received</p>
                          </div>
                          <div class="clearfix"></div>
                      </div>
                    </div>
                    <div class="col-md-12 col-lg-6">
                      <div class="widget-bg-color-icon card-box fadeInDown animated">
                          <div class="bg-icon bg-info pull-left">
                              <i class="md md-payment text-white"></i>
                          </div>
                          <div class="text-right">
                              <h3 class="text-dark"><b class="counter"><?php echo $aff_stats['refunds']; ?></b></h3>
                              <p class="text-muted">Refunds</p>
                          </div>
                          <div class="clearfix"></div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                        <div class="panel panel-color panel-primary">
                          <div class="panel-heading">
                            <h3 class="panel-title">Your details</h3>
                          </div>
                          <div class="panel-body">
                            <form class="form-horizontal" role="form">                                                                                  
                              <div class="form-group">
                                <label class="col-md-3 control-label">Affiliate URL</label>
                                  <div class="col-md-9">
                                      <input type="text" class="form-control" readonly="" value="https://clicktrait.com/track.php?next=index&amp;aid=<?php echo $is_aff_id; ?>">
                                  </div>
                              </div>     
                            </form>
                            <hr>
                            <form class="form-horizontal" role="form" method="POST" action="affiliate.php">
                              <div class="form-group">
                                  <label for="inputEmail3" class="col-sm-3 control-label">PayPal email address</label>
                                  <div class="col-sm-6">
                                    <input type="email" class="form-control" name="ppemail" required="" id="inputEmail3" placeholder="john.doe@gmail.com" value="<?php echo $aff_data[1] ?>">
                                  </div>
                              </div>
                              <div class="form-group m-b-0">
                                  <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-info waves-effect waves-light">Save</button>
                                  </div>
                              </div>
                          </form>

                          <div class="col-md-12 m-t-40">
                            <a href="http://forum.clicktrait.com/topic/44-affiliate-system-terms-and-conditions/" target="_blank">Terms and Conditions</a>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>

                </div>
              </div>

            <?php } ?>

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
	  if (sizeof($_GET) > 0 && isset($_GET['msg']) && $_GET['msg'] == "denied") {
	  	print '      <script src="assets/plugins/notifyjs/dist/notify.min.js"></script><script src="assets/plugins/notifications/notify-metro.js"></script>';
    	print "<script>$(document).ready(function() { $.Notification.autoHideNotify('error', 'top right', '".($_GET['msg'])."')  });</script>";
    }
  ?>

</body>

</html>