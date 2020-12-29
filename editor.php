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
    


    // is 'p' & 'c' parameter set? And do we have permission to this page?

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

    //saving, only IF security & authentication checks passed above.
    //only owner & editor may save.
  if ( isset($_POST['vars']) && isset($_POST['url']) && strcmp($_POST['url'],$purl) == 0 ) {    
    $json = json_encode($_POST);
    $json = str_replace("ab-editor%3D1%26","",$json); //just in case relative URLs append the editor's parameters -- strip them.
    $json = str_replace("%3Fab-editor=1","",$json);
    //die($json);
    $store_json = mysql_real_escape_string($json);
    $query = "UPDATE campaigns SET json='$store_json' WHERE id='$cid';";
    $result = mysql_query($query);
    die( json_encode("success") );
  }


  //load vars for javascript below.
  $c_name = '';
  $editing_mode = false;
  $vars = array();
  my_mysql_connect();
  $query = "SELECT json, status, name from campaigns where id='$cid';";
  $result = mysql_query($query);
  $count = mysql_num_rows($result);
  if ($count == 1) {
    $row = mysql_fetch_row($result);
    $vars = $row[0] == null ? [] : json_decode($row[0]);    
    $editing_mode = $row[1]==null || $row[1] == 0 ? true : false;
    $c_name = $row[2];
  } else {
    $editing_mode = true;
  }

  $iframe_url =  $purl . ($editing_mode ? (strpos($purl, '?') === false ? '?ab-editor=1' : '&ab-editor=1'):'');

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

  <script src="assets/js/jquery.min.js"></script>

  <script>  	  	
    var purl = <?php echo "'$purl';"; ?>
    var cid = <?php echo "'$cid';"; ?>
    var pid = <?php echo "'$pid';"; ?>
    var Wpb_Reps = <?php echo count($vars) > 0 ? json_encode($vars) : '[]'; ?>;    
    var editing_mode = <?php  echo ($editing_mode ? 'true' : 'false'); ?>;
    var campaigns_page_url = '<?php echo "campaigns.php?p=$pid"; ?>';

  </script>
    <script src="js/editor.js?<?php echo time(); ?>"></script>

</head>

<body class="fixed-left" style="overflow-y:scroll">

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
            <div class="col-sm-12">

              <h4 class="page-title">Edit campaign "<?php echo $c_name ?>"</h4>
              <ol class="breadcrumb">
                <li>
                  <a href="index.php">Pages</a>
                </li>
                <li>
                  <a href="campaigns.php?p=<?php echo $pid; ?>">Campaigns</a>
                </li>
                <li class="active">
                  Editor
                </li>
              </ol>
            </div>
          </div>
          
          <div class="row">
            <div class="col-sm-12">
              <div class="card-box" style="min-height:100%;height:100%;">           

                    <div style="text-align:right;">
                      <a id="minus" href="#minus" class="fa fa-minus" style="cursor:pointer;float:right;margin:5px;" onclick="ifr_minus(this);"></a>
                      <a id="plus" href="#plus" class="fa fa-plus" style="cursor:pointer;float:right;margin:5px;" onclick="ifr_plus(this);"></a>                      
                      <span>Resize: &nbsp;</span>
                    </div>

                	<iframe id="ifr" class="ifrc" src="<?php echo $iframe_url; ?>" style="width:100%;"></iframe>
                	<br/>
                	<div style="width:100%;display:table;">	

		                <br><a id="self" href="#self" onclick="openNewWindow();" style="float:left;">Not working? Click here to open in a new window.</a>
	                  <script>
	                    function openNewWindow() {
	                      var openU = "<?php echo $purl . ($editing_mode ? (strpos($purl, '?') === false ? '?ab-editor=1' : '&ab-editor=1'):''); ?>";
	                      var param =  "width="+screen.width*.75+", height="+screen.height*.75+",scrollbars=yes";
	                      window.open(openU, "windowName", param);
	                    }
	                  </script>

                	</div>
              </div>
            </div>
          </div>
           
          <div class="row">
            <div class="col-sm-12">

              <div style="display:none;" class="alert alert-danger" id="divAlert">
                <p id="alertMsg"></p><br><br>
                <textarea style="width:100%;text-align:center;padding:10px 20px 0px 20px;"><?php require('./js/script_code.txt') ?></textarea>
              </div>

              <?php  if ($editing_mode) { ?> 
                  <button style="display:none;" id="btnSave" type="button" class="btn btn-success waves-effect waves-light" onclick="save( '<?php echo $purl; ?>','<?php echo $pid; ?>','<?php echo $cid; ?>' )">
                    <span class="btn-label"><i class="fa fa-check"></i></span>save
                  </button><br><br>
              <?php } ?>                  

              <?php  if ($editing_mode) { ?> 
                  <div class="alert alert-info" id="divLoading" style="color:black;max-width:400px;">
                    <strong>Please wait</strong> while we're loading some stuff :)
                  </div>     
               <?php } ?>     

              <div style="display:none;" id="editors"></div>
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

<!-- editor only -->
  <script src="assets/plugins/notifyjs/dist/notify.min.js"></script>
  <script src="assets/plugins/notifications/notify-metro.js"></script>
<!-- editor only -->

</body>
</html>