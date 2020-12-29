<?php

    session_start();
    require_once(dirname(__FILE__).'/php/security.php');    
    if (!valid_session()) {
      unset_all();
      header("Location: ./login.php");
      die();
    } 


    // is 'p' & 'c' parameter set? 
    $uid = my_user_id();
    $is_pro = is_pro_user($uid);
    $is_pro_trial_user = is_pro_trial_user($uid);

    $cid = null; //campaign id
    $pid = null; //page id
    $purl = null; //page url
    $permission = null; // 7=owner ; 1=editor ; 0=spectator

    if (sizeof($_GET) > 0 && isset($_GET['p']) && $_GET['p'] >= 0 && isset($_GET['c']) && $_GET['c'] >= 0 ) {
      $pid = $_GET['p'];
      $cid = $_GET['c'];

      //do we have access to this page???!!!
      $permission = get_page_permission($uid,$pid) ;
      if ($permission == 0) {
        header("Location: ./campaigns.php?msg=denied&p=" . $pid);
        die("0");  
      }

      //get page URL by id
      my_mysql_connect();
      $query = "SELECT p.url from pages p WHERE p.id='$pid';";
      $result = mysql_query($query);
      $count = mysql_num_rows($result);
      if ($count == 1) {       
        $row = mysql_fetch_row($result);
        $purl = $row[0];        
      } else {
          header("Location: ./campaigns.php?msg=denied&p=" . $pid);
          die("1");  
      }

    } else if ($pid == null || $purl == null) {
      header("Location: ./index.php?msg=denied");
      die("2");
    }


  //load vars for javascript below.
  $cname = null;
  $screens = null;
  my_mysql_connect();
  $query = "SELECT c.name name, r.value results, c.screenshots sh from campaigns c LEFT JOIN results r ON r.campaign_id=c.id WHERE c.id='$cid' ORDER BY r.id DESC LIMIT 0,1;";
  $result = mysql_query($query);
  $count = mysql_num_rows($result);
  $data = null;
  $totalSessions = 0;
  $totalEvents = 0;   
  if ($count == 1) {     
    $row = mysql_fetch_assoc($result);
    $cname = $row["name"];
    $data = json_decode($row["results"]);
    $screens = json_decode($row["sh"]);
    $totalSessions = $data->total_sessions;
    $totalEvents = $data->total_events;
    $data = (array)current((array)($data->json));
  }

  //header('Content-type: application/x-javascript');
  //print json_encode($screens, JSON_PRETTY_PRINT);die;
  
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Clicktrait">
    <link rel="shortcut icon" href="assets/images/favicon_1.ico">
    <title>A/B and MultiVariate Testing Platform | Free trial | Clicktrait</title>

  <link href="assets/plugins/c3/c3.min.css" rel="stylesheet" type="text/css"  />

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

            <div class="col-sm-12">

              <h4 class="page-title">Campaign results</h4>
              <ol class="breadcrumb">
                <li>
                  <a href="index.php">Pages</a>
                </li>
                <li>
                  <a href="campaigns.php?p=<?php echo $pid; ?>">Campaigns</a>
                </li>
                <li class="active">
                  Campaign "<?php echo $cname; ?>"
                </li>
              </ol>

              <div class="row">
                <div class="col-lg-6">
                  <div class="card-box">
                    
                    <?php
                      if ($data==null || (isset($data[0]) && $data[0] == false)) {
                        print "No results yet, please come back later <i class=\"fa fa-clock-o\"></i>";
                      } else {
                        print '<h4 class="m-t-0 m-b-30 header-title"><b>Data analysis</b></h4>';
                        $maxSessions = $is_pro || $is_pro_trial_user>0 ? 1000 : 100;
                        $maxSessions *= sizeof($data); //each group will receiver 1000 or 100 visitors; so we have to multiply by the number of groups we have.
                        $prc = number_format($totalSessions/$maxSessions*100,0);
                        print '<div class="progress progress-md">
                                  <div class="progress-bar progress-bar-'.($prc==100 ? 'success':'primary').' wow animated progress-animated" role="progressbar" aria-valuenow="'.$prc.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$prc.'%;">progress '.$prc.'%
                                  </div>
                              </div>';                                              
                        print "Visitors: <strong>$totalSessions/$maxSessions</strong><br>";
                        print "CTA clicks: <strong>$totalEvents</strong><br>";
                        print "<div style='text-align:center'><strong>Clicks distribution</strong></div><br>";
                      }
                    ?>

                    <div id="pie-chart"></div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="card-box">
                  <h4 class="m-t-0 m-b-30 header-title"><b>Inspect</b></h4>
                    <table class="table table-striped m-0">
                      <thead>
                        <tr>
                          <th width="100" style="text-align:center">Group</th>
                          <th width="150" style="text-align:center">Screenshot</th>
                          <th width="150" style="text-align:center">Heatmap</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>

                        <?php
                          if ($screens != null) {                            
                            foreach ($screens as $index => $obj) {
                              $obj = (array)$obj;
                              $img = '<a href="./php/crons/screens/uploads/'.$obj["file"].'" target="_blank">Open</a>';
                              $hmp = '<a href="'.$purl.'?ab-hmp=1&ab-sg='.$obj['group'].'&ab-sc='.$cid.'&ab-srv=1' .'" target="_blank">Open</a>';
                              print ' <tr>
                                        <td style="text-align:center" scope="row">'.$obj["group"].'</td>
                                        <td style="text-align:center">'.$img.'</td>
                                        <td style="text-align:center">'.$hmp.'</td>
                                        <td>&nbsp;</td>
                                      </tr>';
                            }
                          }
                        ?>

                      </tbody>
                    </table>
                  </div>
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



  <script type="text/javascript" src="assets/plugins/d3/d3.min.js"></script>
  <script type="text/javascript" src="assets/plugins/c3/c3.min.js"></script>
  <script>
    function RC() {
      return "#"+((1<<24)*Math.random()|0).toString(16);
    }
    !function($) {
      "use strict";
      var ChartC3 = function() {};
      ChartC3.prototype.init = function () { 
        c3.generate({
             bindto: '#pie-chart',
            data: {
                columns: [
                    <?php
                      if ($data==null || !isset($data[0])) {
                        foreach ($data as $sg => $obj) {
                          $d = round( $obj->perf , 2, PHP_ROUND_HALF_UP);
                          print "['$sg', $d],";
                        }
                      }
                    ?>
                ],
                type : 'pie'
            },
            color: {
              pattern: [<?php if ($data==null || !isset($data[0])) { foreach ($data as $sg => $obj) {print 'RC(),';} } ?>]
            },
            pie: {
            label: {
              show: false
            }
        }
        });
      },
      $.ChartC3 = new ChartC3, $.ChartC3.Constructor = ChartC3
    }(window.jQuery),
    function($) {
        "use strict";
        $.ChartC3.init()
    }(window.jQuery);

  </script>

</body>

</html>