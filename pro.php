<?php
    session_start();
    require_once(dirname(__FILE__).'/php/security.php');    
    if (!valid_session()) {
      unset_all();
      header("Location: ./login.php");
      die();
    } 
    $uid = my_user_id();

    //let's get all pages by our user_id
    my_mysql_connect();
    $query = "SELECT start, end, total, vat, currency, tid FROM orders WHERE user_id='$uid' ORDER BY id DESC;";
    $result = mysql_query($query) or die('Error 1');
    $count = mysql_num_rows($result);
    $rows = array();
    if ($count > 0) {        
        while ($row = mysql_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    require_once(dirname(__FILE__).'/php/payments.php');

    if (sizeof($_POST) > 0 && (isset($_POST['sub']) || isset($_POST['pay']))) {
      proceed_paypal($uid, $subtotal, $vat, $vatp, $total);
    } else if (sizeof($_POST) > 0 && isset($_POST['free_pro'])) {
      $ret = start_pro_trial($uid);
    }
    
    $is_pro = is_pro_user($uid);
    $is_pro_trial_user = is_pro_trial_user($uid);
    

  $post_parameterized = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
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

            <?php 
            if ($is_pro_trial_user == -1 && $is_pro == 0) { ?>
                <div class="col-sm-12">
                  <div class="col-lg-5 center">
                      <div class="panel panel-inverse">
                        <div class="panel-heading">
                          <h3 class="panel-title" style="color:white">Try Clicktrait PRO for free</h3>
                        </div>
                        <div class="panel-body">
                          <ul class="list-group"  style="color:black; font-size:15px">
                            <li class="list-group-item">
                              <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; Upgrade your account to PRO status for 30 days for free!
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; No credit card and no additional information required.
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; After 30 days you may continue using Clicktrait free or upgrade to PRO.
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; The VIP membership is not included in this PRO trial.
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; You may upgrade to the paid PRO plan at any time.
                            </li>    
                          </ul>  
                            <div style="width:100%;text-align:center;">
                              <form action="<?php echo $post_parameterized; ?>" method="POST" style="display: inline-block;">
                                <button class="btn btn-success waves-effect waves-light btn-lg" type="submit"> <span>Start your PRO trial</span> <i class="fa fa-rocket m-l-5"></i> </button>    
                                <input type="hidden" name="free_pro">
                              </form>
                            </div>
                          
                          
                        </div>                    
                      </div>
                    </div>
                </div>
              <?php
                }
              ?>

          
            <div class="col-sm-12">

                <div class="col-lg-5 center">
                  <div class="panel panel-primary">
                    <div class="panel-heading">
                      <h3 class="panel-title">Clicktrait PRO</h3>
                    </div>
                    <div class="panel-body">
                      <p style="font-size:19px;color:black">
                        <?php 
                          if ($is_pro) {
                            $due = date('d M Y', strtotime($rows[0]['end']));
                            $days = ceil(( strtotime($rows[0]['end'])-time() )/86400);
                            echo 
                            '<strong style="color:green;">Your account is on PRO level.</strong><br>
                            Your membership is due ' .($days==1?'today.':'in '.$days.' days, on ' . $due . '.') ;
                          } else {
                            echo 'You are currently on the free plan.<br>
                            <strong>Upgrade to PRO and go unlimited!</strong>';
                          }
                        ?>
                     </p>
                    </div>
                    <?php if (!$is_pro) { ?>
                    <ul class="list-group"  style="color:black; font-size:15px">                                          
                        <li class="list-group-item">
                          <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; Add an unlimited amount of websites and pages.
                        </li>
                        <li class="list-group-item">
                          <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; Analyze and track 1000 visitors per group.
                        </li>
                        <li class="list-group-item">
                          <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; Become a VIP member on our community forum.
                        </li>
                        <li class="list-group-item">
                          <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; Get lifetime access to all VIP material and content.
                        </li>  
                        <li class="list-group-item">
                          <i class="fa fa-check-circle" style="color:#5d9cec"></i>&nbsp; Receive first-class support.
                        </li>                      
                    </ul>
                    <div class="panel-body">                          
                      <div class="form-group m-t-0 m-b-40">              
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
                        <form class="form-horizontal m-t-20" action="<?php echo $post_parameterized; ?>" method="POST">
                          <div class="col-xs-12 m-t-20">
                            <button name="sub" class="btn btn-success text-uppercase waves-effect waves-light w-sm btn-lg" type="submit" title="Create an automatic monthly payment plan.">
                              Subscribe 
                            </button>
      
                            <button name="pay" class="btn btn-inverse text-uppercase waves-effect waves-light w-sm btn-lg" type="submit" title="No contract, decide when you pay.">
                              Pay 
                            </button>                       
                          </div>
                        </form>

                        <div class="col-xs-12 m-t-20">
                          <img src="https://clicktrait.com/ab/images/pp_cc.png" style="max-width:100%;opacity:0.8" height="68" />
                        </div>
                      </div>
                      <div class="form-group">
                      <div class="col-xs-12">
                        <h4>By purchasing Clicktrait PRO you agree to our <a href="https://clicktrait.com/terms" target="_blank">terms</a> and <a href="https://clicktrait.com/privacy" target="_blank">privacy policy</a>.</h4>
                      </div>
                      </div>
                    </div>
                    <?php } ?>
                  </div>
                </div>

                <div class="col-lg-7 center">
                  
                  <div class="panel panel-primary">
                    <div class="panel-heading">
                      <h3 class="panel-title">Payments History</h3>
                    </div>
                    <div class="panel-body">

                    <?php if (count($rows) > 0) { ?>
                      <div class="pad-btm form-inline m-b-20">                    
                        <div class="row">
                          <div class="col-sm-12 text-xs-center text-right">
                            <div class="form-group">
                              <input id="demo-input-search2" type="text" placeholder="Search" class="form-control  input-sm" autocomplete="off">
                            </div>
                          </div>
                        </div>
                      </div>

                      <table id="demo-foo-addrow" class="table table-striped m-b-0 table-hover toggle-circle" data-page-size="7">
                        <thead>
                          <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Total paid</th>
                            <th>VAT</th>
                            <th>Transaction ID</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                            foreach ($rows as $index => $kv) {
                              echo '
                                <tr>
                                  <td>'.date('d M Y', strtotime($kv['start'])).'</td>
                                  <td>'.date('d M Y', strtotime($kv['end'])).'</td>
                                  <td>'. number_format($kv['total'],2) .' '.$kv['currency'].'</td>
                                  <td>'. number_format($kv['vat'],2) .' '.$kv['currency'].'</td>
                                  <td>'.$kv['tid'].'</td>
                                </tr>
                              ';
                            }
                          ?>                          
                        </tbody>
                        <tfoot>
                          <tr>
                            <td colspan="6">
                              <div class="text-right">
                                <ul class="pagination pagination-split m-t-30"></ul>
                              </div>
                            </td>
                          </tr>
                        </tfoot>
                      </table>

                    </div>
                    <?php } else {
                      echo '<span>No payments found.</span>';
                      } ?>
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


</body>

</html>