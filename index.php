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

    //listen for new page being added
    $show_js_code = 0;
    $err = null;
    if (sizeof($_POST) > 0 && isset($_POST['page'])) {

      $cp = count_pages_by_user($uid);
      if (!$is_pro && $is_pro_trial_user<1 && $cp >= 2) {
        $err = 'Your account is limited to two pages max.';
      } else { 

        $url = trim($_POST['page']);
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
          $err = "Invalid URL format";
        } else {     
          my_mysql_connect();   
          $url = trim($_POST['page']);
          $parsedUrl = parse_url($url);
          $host = mysql_real_escape_string($parsedUrl['host']);          

          //let's make sure this user owns the domain
          $query = "SELECT distinct user_id from pages where domain='$host';";
          $result = mysql_query($query);
          $count = mysql_num_rows($result);
          $test_id = -1;
          if ($count == 1) {
          	$_GET["msg"] = "success";
            $row = mysql_fetch_row($result);
            $test_id = $row[0];          
          }
          if ($count==0 || strcmp($test_id,$uid)==0) {
            //if domain not used before yet OR it's our user id (we own it)
            $query = "INSERT into pages (user_id,url,domain) VALUES('$uid', '$url', '$host');";
            $result = mysql_query($query);
            if (!$result) {
              $err = "Page already added.";
            }
            $show_js_code = 1;
          } else {
            $err = "You are not the owner of this website.";
          }
        }
      }
    }

    //listen for page being deleted
    if (sizeof($_GET) > 0 && isset($_GET['rm']) && $_GET['rm'] >= 0) {
      my_mysql_connect();
      $pid = mysql_real_escape_string($_GET['rm']);
      //is it our page => delete page
      $query = "SELECT user_id from pages where id='$pid' AND user_id='$uid';";
      $result = mysql_query($query);
      $count = mysql_num_rows($result);
      if ($count == 1) {
        $query = "DELETE from pages where id='$pid' AND user_id='$uid';";
        $result = mysql_query($query);
        //finally delete all campaigns
        $query = "DELETE from campaigns where page_id='$pid';";
        $result = mysql_query($query);

        header("Location: ./index.php");
        die();
      } else {
        header("Location: ./index.php?msg=denied");
        die();
      }
    }

    //let's get all pages by our user_id
    my_mysql_connect();
    $query = "SELECT p.id, p.url, p.domain, date_format(p.added, '%M %d, %Y') added,
                    (SELECT u.email from users u where u.id=p.user_id) email,
                    (SELECT count(*) from campaigns c where c.page_id=p.id) campaigns,
                    (SELECT count(*) from campaigns c where c.page_id=p.id and c.status=1) any_active
                    FROM pages p
                    WHERE p.user_id='$uid' ";
    $query .= $is_pro || $is_pro_trial_user > 0? ' ;' : ' ORDER BY id DESC;'; //LIMIT 2
    $result = mysql_query($query) or die('Error 1');
    $count = mysql_num_rows($result);
    $rows = array();
    if ($count > 0) {        
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

              <h4 class="page-title m-b-20">Manage your pages</h4>

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
                        <th style="display:none;">ID</th>                      
                        <th data-sort-initial="true" style="width:50px">Status</th>
                        <th data-sort-ignore="true" style="width:30px"></th>
                        <th style="width:300px">Page</th>
                        <th style="width:150px">Domain</th>
                        <th style="width:150px;text-align:center">#Campaigns</th>                      
                        <th style="width:200px">Added</th>
                        <th data-sort-ignore="true" style="width:100px">Delete</th>
                      </tr>
                    </thead>
                    <tbody>

                    <?php
                        $j = 0;
                        foreach ($rows as $index => $row) {
                        	$j++;
  	                      //id, url, domain, added, #campaigns, any_active
  	                      $status = $row["any_active"] > 0 ? '<span class="label label-table label-success">Active</span>' : '<span class="label label-table label-inverse">Idle</span>';
  	                      $status = $is_pro || $is_pro_trial_user>0 || ($j <= 2) ? $status : '<span class="label label-table label-danger">Disabled</span>';

  	                      $url_rm = './index.php?rm=' . $row["id"] ;
  	                      $url_campaign = './campaigns.php?p=' . $row["id"];
  	                      $url_proceed = $is_pro || $is_pro_trial_user>0 || ($j <= 2) ? '<td><a href="' .$url_campaign . '">' . $row["url"] . '</a></td>' : '<td>' . $row["url"] . '</td>';
                          $wrench = $is_pro || $is_pro_trial_user>0 || ($j <= 2) ? '<td><a class="btn btn-primary btn-xs btn-icon fa fa-wrench" style="font-size:20px" title="manage campaigns" href="'.$url_campaign.'"></a></td>' : '<td>&nbsp;</td>';
  	                      print '
  	                        <tr>
  	                          <td style="display:none;">0</td>	                      
  	                          <td>' . $status . '</td>    
  	                          ' . $wrench . '
                              ' . $url_proceed . '
  	                          <td>' . $row["domain"] . '</td>
  	                          <td style="text-align:center">' . $row["campaigns"] . '</td>                      	                          
  	                          <td>' . $row["added"] . '</td>
  	                          <td>  	                            
                                <a class="btn btn-danger btn-xs btn-icon fa fa-times" style="font-size:20px" title="delete page and campaigns" href="'.$url_rm.'" onclick="return confirm(\'Are you sure?\')"></a>                        
  	                          </td>
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
                <?php } ?>

                  <div class="pad-btm form-inline m-b-20">
                	<div class="row">
                      <div class="col-sm-6 text-xs-center text-left">
                        <form id="todo-form" role="form" class="m-t-0" action="index.php" method="post">
	                		<div class="row">
	                        	<div class="col-sm-12">
                            <?php if (count($rows) > 0) { ?> 
	                        		<h3>Add a new page:</h3>
                            <?php } else { ?>
                              <h3>Add your first page:</h3>
                            <?php } ?>
	                    		</div>
	                		</div>
                          <div class="row">
                            <div class="col-sm-6 todo-inputbar">                            
                              <input name="page" type="text" style="width:100%" id="todo-input-text" class="form-control" placeholder="http://domain.com/new_page.html">
                            </div>
                            <div class="col-sm-2 todo-send">
                              <button class="btn-primary btn-md btn-block btn waves-effect waves-light" type="submit" id="todo-btn-submit">Add</button>
                            </div>
                          </div>
                        </form> 
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6 text-xs-center text-left">
                      <?php if ($err != null && isset($_POST['page'])) {?>
                          <div class="form-group m-t-20 m-b-0">
                            <div class="alert alert-danger" style="text-align:center">
                              <?php echo $err; ?>
                            </div>
                          </div>
                        <?php } ?>
                      </div>
                    </div>
                  </div>

                  <?php if ($show_js_code == 1) { ?>
                  	<div class="pad-btm form-inline m-b-20">
	                    <div class="row">
	                    	<div class="col-sm-12">
		                    	<div class="row">
		                        	<div class="col-sm-12">
		                        		<div class="alert alert-success" id="divAlert" style="color:green">
											<strong>Add this code in the header or footer of your page:</strong>
											<textarea style="width:100%;text-align:center;padding:10px 20px 0px 20px;"><?php require('./js/script_code.txt') ?></textarea>
										</div>
		                    		</div>
		                		</div>
	                    	</div>
	                	</div>
            		</div>
            		<?php } ?>
                    

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
	  if (sizeof($_GET) > 0 && isset($_GET['msg']) && $_GET['msg'] == "denied") {
	  	print '      <script src="assets/plugins/notifyjs/dist/notify.min.js"></script><script src="assets/plugins/notifications/notify-metro.js"></script>';
    	print "<script>$(document).ready(function() { $.Notification.autoHideNotify('error', 'top right', '".($_GET['msg'])."')  });</script>";
    }
  ?>

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');

fbq('init', '495074397331093');
fbq('track', "PageView");
fbq('track', 'CompleteRegistration');</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=495074397331093&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->

</body>

</html>