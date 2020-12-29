
<div class="topbar">
    <div class="topbar-left">
        <div class="text-center">
            <a href="index.php" class="logo" style="text-transform: none">
                <i class="icon-c-logo fa fa-mouse-pointer"></i>
                <img src="./images/logo-w-xs.png"/>
            </a>
        </div>
    </div>
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="">
                <div class="pull-left">
                    <button class="button-menu-mobile open-left waves-effect">
                            <i class="md md-menu"></i>
                        </button>
                    <span class="clearfix"></span>
                </div>                
                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">
                        <a href="" class="dropdown-toggle profile waves-effect" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-cog"></i></a>
                        <ul class="dropdown-menu dropdown-menu-animate drop-menu-right">
                            <li><a href="index.php?logout=1"><i class="ti-power-off m-r-5"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
                <?php if ($is_pro) { ?>
                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">
                        <span class="btn btn-default" style="line-height:28px; margin-top:10px;margin-right:20px;cursor:default"><i style="color:white" class="fa fa-trophy m-l-5"></i> PRO  </span>    
                    </li>
                </ul>
                <?php } else if ($is_pro_trial_user > 0) { ?>
                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">
                        
                        <span class="btn btn-default" style="line-height:28px; margin-top:10px;margin-right:20px;cursor:default"> <i style="color:white" class="fa fa-trophy m-l-5"></i> PRO trial: <?php echo ($is_pro_trial_user . ' day') . ($is_pro_trial_user==1? "" : "s");  ?> left </span>    
                    </li>
                </ul>
                <?php } else if ($is_pro_trial_user == 0) { ?>
                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">                    
                        <a class="btn btn-success waves-effect waves-light" href="pro.php" style="line-height:40px; margin-top:10px;margin-right:20px"> <span style="color:white">UPGRADE</span> <i  style="color:white" class="fa fa-rocket m-l-5"></i> </a>    
                    </li>
                </ul>
                <?php } else if ($is_pro_trial_user == -1) { ?>
                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">                    
                        <a class="btn btn-success waves-effect waves-light" href="pro.php" style="line-height:40px; margin-top:10px;margin-right:20px"> <span style="color:white">Try PRO for 30 days</span> <i  style="color:white" class="fa fa-rocket m-l-5"></i> </a>    
                    </li>
                </ul>
                <?php } ?>
            </div>
        </div>
    </div>
</div>