<?php
    session_start();
    require_once(dirname(__FILE__).'/php/security.php');    
    if (!valid_session()) {
      unset_all();
      header("Location: ./login.php");
      die();
    } 

    
    // is 'p' parameter set? And do we have permission to this page?
    $uid = my_user_id();
    $is_pro = is_pro_user($uid);
    $is_pro_trial_user = is_pro_trial_user($uid);
    
    $pid = null; //page id
    $purl = null; //page url
    $permission = null;

    if (sizeof($_GET) > 0 && isset($_GET['p']) && $_GET['p'] >= 0 ) {
      $pid = $_GET['p'];

      //do we have access to this page???!!!
      $permission = get_page_permission($uid,$pid) ;
      if ($permission == 0) {
        header("Location: ./index.php?msg=denied");
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
          header("Location: ./index.php?msg=denied");
          die("1");  
      }

    } else if ($pid == null || $purl == null) {
      header("Location: ./index.php?msg=denied");
      die("2");
    }


    //listen for new campaign being added.
    $err = null;
    if (sizeof($_POST) > 0 && isset($_POST['name']) && $permission != 0) {  
      $cp = count_campaigns_by_page($pid);
      /*if (!$is_pro && $cp >= 2) {
        $err = 'Your account is limited to two campaigns max.';
      } else*/ {           
        my_mysql_connect();
        $name = mysql_real_escape_string(trim($_POST['name']));        
        $query = "INSERT into campaigns (user_id,page_id,name,page_url) VALUES('$uid', '$pid', '$name', '$purl');";
        $result = mysql_query($query);
        if (!$result) {
          $err = "Something went wrong, please <a href='mailto:contact@clicktrait.com'>contact us</a>.";
        } else {
          $key = mysql_insert_id();
          header("Location: ./editor.php?c=" . $key . "&p=" . $pid);
          die();
        } 
      }     
    }

    //listen for campaign being deleted
    //owner may delete any campaign ; editors only theirs.
    if (sizeof($_GET) > 0 && isset($_GET['rm']) && $permission != 0) {
      my_mysql_connect();
      $cid = mysql_real_escape_string($_GET['rm']);      
      $query = "DELETE from campaigns where id='$cid' AND page_id='$pid';";
      $result = mysql_query($query);  
      header("Location: ./campaigns.php?p=" . $pid);
      die();
    }

    //listen for campaign started.
    //owner may start any campaign ; editors only theirs.
    if (sizeof($_GET) > 0 && isset($_GET['start']) && $permission != 0) {
      my_mysql_connect();
      $cid = mysql_real_escape_string($_GET['start']);      
      $query = "UPDATE campaigns SET status=1 where id='$cid' AND page_id='$pid';";
      $result = mysql_query($query);      
      header("Location: ./campaigns.php?p=" . $pid);
      die();
    }

      //listen for campaign paused.
    //owner may start any campaign ; editors only theirs.
    if (sizeof($_GET) > 0 && isset($_GET['pause']) && $permission != 0) {
      my_mysql_connect();
      $cid = mysql_real_escape_string($_GET['pause']);      
      $query = "UPDATE campaigns SET status=2 where id='$cid' AND page_id='$pid';";
      $result = mysql_query($query);    
      header("Location: ./campaigns.php?p=" . $pid);
      die();
    }

    //let's get all campaigns by our page_id
    my_mysql_connect();
    $query = "SELECT id, name, status, json, created, ended FROM campaigns WHERE page_id='$pid' order by id desc;";
    $result = mysql_query($query) or die('Error c1');
    $count = mysql_num_rows($result);
    $rows = array();
    if ($count > 0)
    {        
        while ($row = mysql_fetch_assoc($result)) {
            $rows[] = $row;
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

          <?php include('notifications.php'); ?>

            <div class="col-sm-12">

              <h4 class="page-title">Manage your campaigns for <a href="<?php echo $purl; ?>"><?php echo $purl; ?></a></h4>
              <ol class="breadcrumb">
                <li>
                  <a href="index.php">Pages</a>
                </li>
                <li class="active">
                  campaigns
                </li>
              </ol>

              <div class="card-box">

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
                        <th data-sort-initial="true" style="display:none;">ID</th>
                        <th style="width:100px">Status</th>
                        <th data-sort-ignore="true" style="width:200px">Actions</th>
                        <th>Name</th>
                        <th  style="width:200px">Created</th>
                        <th style="width:200px">Finished</th>   
                        <th data-sort-ignore="true" style="width:100px">Delete</th>                   
                      </tr>
                    </thead>
                    <tbody>

                    <?php
                        $j = 0;
                        foreach ($rows as $index => $row) {
                          $j++;
                        //id, name, status, created, ended
                        //Starting can only happen from editor.php page                           
                        $actionPlayPause = '';   
                        $editor = '<a style="font-size:20px" title="view campaign" class="btn btn-primary btn-xs btn-icon fa fa-eye" href="./editor.php?c=' . $row["id"] . '&p='.$pid.'"></a>';
                        $delete = '<a style="font-size:20px" title="delete campaign" class="btn btn-danger btn-xs btn-icon fa fa-times" href="./campaigns.php?rm=' . $row["id"] . '&p='.$pid.'" onclick="return confirm(\'Are you sure?\')"></a>';
                        switch ( $row["status"] ) {
                          case 0:                        
                              $status = '<span class="label label-table label-inverse">Idle</span>';
                              if (strlen($row['json']) > 0) {
                                //this campaign should beconfigured
                              	$startConfirmIfIdle = 'onclick="return confirm(\'Once you start this campaign, you can not edit it.\nAre you sure?\')"';
                                $actionPlayPause = '<a style="font-size:20px" title="start campaign" class="btn btn-success btn-xs btn-icon fa fa-play-circle-o" href="./campaigns.php?start=' . $row["id"] . '&p='.$pid.'" '.$startConfirmIfIdle.'></a>';
                              }    
                              $editor = '<a style="font-size:20px" title="edit campaign" class="btn btn-primary btn-xs btn-icon fa fa-wrench" href="./editor.php?c=' . $row["id"] . '&p='.$pid.'"></a>';                        
                              break;
                          case 1:
                              $status = '<span class="label label-table label-success">Active</span>';
                              $actionPlayPause = '<a style="font-size:20px" title="pause campaign" class="btn btn-warning btn-xs btn-icon fa fa-pause-circle-o" href="./campaigns.php?pause=' . $row["id"] . '&p='.$pid.'"></a>';
                              break;
                          case 2:                        
                              $status = '<span class="label label-table label-warning">Paused</span>';
                              $actionPlayPause = '<a style="font-size:20px" title="start campaign" class="btn btn-success btn-xs btn-icon fa fa-play-circle-o" href="./campaigns.php?start=' . $row["id"] . '&p='.$pid.'" ></a>';
                              break;
                          case 3:
                              $status = '<span class="label label-table label-purple">Finished</span>';
                              break;
                        }

                        print '
                          <tr>
                            <td style="display:none;">0</td>
                            <td>' . $status . '</td>
                            <td style="text-align: left;">                            
                              ' . ( ($permission == 7 || $permission == 1) ? $editor : '') . '
                              <a style="font-size:20px" title="analyze campaign" class="btn btn-default btn-xs btn-icon fa fa-pie-chart" href="./stats.php?c=' . $row["id"] . '&p='.$pid.'"></a>
                              ' . ( ($permission == 7 || $permission == 1) ? $actionPlayPause : '') . '
                            </td>
                            <td>' . $row["name"] . '</td>
                            <td>' . $row["created"] . '</td>
                            <td>' . $row["ended"] . '</td>     
                            <td>' . ( ($permission == 7 || $permission == 1) ? $delete : '') . '</td>                                           
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
                <?php } ?>



                </table>

                  <div class="pad-btm form-inline m-b-20">
                    <div class="row">
                      <div class="col-sm-6 text-xs-center text-left">
                        <form id="todo-form" role="form" class="m-t-0" action="campaigns.php<?php echo "?p=$pid"; ?>" method="post">
                          <div class="row">
                            <div class="col-sm-12">
                              <?php if (count($rows) > 0) { ?> 
                              <h3>Create a new campaign:</h3>
                            <?php } else { ?>
                              <h3>Create your first campaign:</h3>
                            <?php } ?>
                          </div>
                        </div>
                          <div class="row">
                            <div class="col-sm-6 todo-inputbar">
                              <input name="name" type="text" style="width:100%" id="todo-input-text" name="todo-input-text" class="form-control" placeholder="Give your campaign a name">
                            </div>
                            <div class="col-sm-3 todo-send">
                              <button class="btn-primary btn-md btn-block btn waves-effect waves-light" type="submit" id="todo-btn-submit">Create</button>
                            </div>
                          </div>
                        </form> 
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6 text-xs-center text-left">
                      <?php if ($err != null && isset($_POST['name'])) {?>
                          <div class="form-group m-t-20 m-b-0">
                            <div class="alert alert-danger" style="text-align:center">
                              <?php echo $err; ?>
                            </div>
                          </div>
                        <?php } ?>
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