<?php
    session_start();
    require_once(dirname(__FILE__).'/php/security.php');    
    if (!valid_session()) {
      unset_all();
      header("Location: ./login.php");
      die();
    } 

    
    $uid = my_user_id();

    //check if admin
    if (!is_admin($uid)) {
      header("Location: ./index.php?msg=denied");
    }

    //let's get all pages
    my_mysql_connect();
    $query = "SELECT p.id, p.url, p.domain, p.added, (SELECT u.email from users u where u.id=p.user_id) email, (SELECT count(*) from campaigns c where c.page_id=p.id) campaigns, (SELECT count(*) from campaigns c where c.page_id=p.id and c.status=1) any_active from pages p;";
    //print$query; die;
    $result = mysql_query($query) or die('Error 1');
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
            <div class="col-sm-12">

              <h4 class="page-title m-b-20">Manage your pages</h4>

              <div class="card-box">
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
                      <th data-sort-ignore="true" class="min-width"></th>
                      <th>Email</th>
                      <th>Page</th>
                      <th>Domain</th>                      
                      <th>#Campaigns</th>
                      <th>Active</th>
                      <th>Added</th>                      
                    </tr>
                  </thead>
                  <tbody>

                  <?php
                      foreach ($rows as $index => $row) {
                      //id, url, domain, added, #campaigns, any_active
                      $active = $row["any_active"] > 0 ? '<span class="label label-table label-success">Active</span>' : '<span class="label label-table label-inverse">Idle</span>';
                      print '
                        <tr>
                          <td style="display:none;">0</td>
                          <td style="text-align: center;">
                            <a class="btn btn-danger btn-xs btn-icon fa fa-times" style="font-size:20px" title="delete page and campaigns" href="./index.php?rm=' . $row["id"] . '" onclick="return confirm(\'Are you sure?\')"></a>                        
                            <a class="btn btn-primary btn-xs btn-icon fa fa-wrench" style="font-size:20px" title="manage campaigns" href="./campaigns.php?p=' . $row["id"] . '"></a>
                          </td>
                          <td>' . $row["email"] . '</td>
                          <td>' . $row["url"] . '</td>
                          <td>' . $row["domain"] . '</td>                          
                          <td>' . $row["campaigns"] . '</td>                      
                          <td>' . $active . '</td>
                          <td>' . $row["added"] . '</td>
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

                  <div class="pad-btm form-inline m-b-20">
                    <div class="row">
                      <div class="col-sm-6 text-xs-center text-left">
                        <form id="todo-form" role="form" class="m-t-0" action="index.php" method="post">
                          <div class="row">
                            <div class="col-sm-6 todo-inputbar">
                              <input name="page" type="text" style="width:100%" id="todo-input-text" name="todo-input-text" class="form-control" placeholder="http://domain.com/new_page.html">
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