<?php

	$con = null;
	function my_mysql_connect() {
		global $con;
		if (!$con) {
			require(dirname(__FILE__).'/log.php');
			$con = mysql_connect($dbhost, $dbuser, $dbpass) or die('database error');
	        mysql_select_db($dbname) or die('database error');
        }
	}

	function valid_session() {
		if (checkLogout()) {return false;}
		
		if ((isset($_COOKIE["abcro"]) && strlen($_COOKIE["abcro"]) > 0) || (isset($_SESSION['abcro']) && strlen($_SESSION['abcro']) > 0)) {
			my_mysql_connect();
	        $sesid = mysql_real_escape_string(   (isset($_COOKIE["abcro"]) && strlen($_COOKIE["abcro"]) > 0) ? $_COOKIE["abcro"] : $_SESSION['abcro'] );
			$query = "SELECT session from users where session='$sesid' AND verified=1;";
	        $result = mysql_query($query) or die('Error s1');
	        $count = mysql_num_rows($result);

	        if ($count == 1) {
	        	$_SESSION['abcro'] = $sesid;
	        	return true;
	        } else {
	        	unset_all();
	        }
		} 
		return false;	
	}

	function checkLogout() {
		return (isset($_GET['logout']));
	}

	function unset_all() {
		setcookie("abcro", "", time()-9999999, '/ab/');
    	unset($_SESSION['abcro']);
	}

	function activation_email($id) {
		my_mysql_connect();
		$id = mysql_real_escape_string($id);
		$query = "SELECT name,registered,email from users where id=$id;";
		$result = mysql_query($query) or die('Error 2');
        $row = mysql_fetch_row($result);
        $name = $row[0];
        $link = 'https://clicktrait.com/ab/login.php?id='.$id.'&verify='.md5($row[1]);
        $email = $row[2];        
		send_mail_welcome($name,$email,$link);
	}	

	function register_new_user() { //return user id
		//register
		$err = null;
		my_mysql_connect();
		$email = mysql_real_escape_string(trim($_POST['email']));
        $name = mysql_real_escape_string(trim($_POST['name']));
        $pass = mysql_real_escape_string(trim($_POST['pass']));
        $accept = isset($_POST['toc']) ? true : false;
        if (!$accept) {
        	$err = 'You have to accept the Terms and Conditions.';
        	return $err;
        } else {
        	$pass = mb_convert_encoding($pass, "UTF-8");
        	$pass = mysql_real_escape_string(md5($pass));
        	$sesid = uniqid();

			$query = "INSERT into users (email, name, password, session, verified) VALUES('$email', '$name', '$pass', '$sesid', 0);";
	        $result = mysql_query($query) or $err = 'Email address is already in use.';
	        if ($result) {
	        	$uid = mysql_insert_id();
	        	activation_email($uid);
	        	send_mail_from_server("$email ($name)","$email ($name)",'Clicktrait new free user','admin@clicktrait.com','Ilya');
		        mark_user_to_aff_and_notify($uid, $email);       		    	
	    		
		        require_once("/home/MyMailerLite.php");
				subscribe($name, $email, 5242119);

				return $uid;
			}
		}
		return $err;
	}

	function success_login($email, $remember) {
		my_mysql_connect();
		$email = mysql_real_escape_string($email);
		$sesid = uniqid();
		$query = "UPDATE users SET session='$sesid' where email='$email';";
        $result = mysql_query($query) or die('Error 2');
        $count = mysql_affected_rows();
        if ($count == 1) {
	        $_SESSION['abcro'] = $sesid;
	    	if ($remember == true) {            					        
		        setcookie("abcro", $sesid, time()+604800, '/ab/'); // 7 day cookief
	    	}	    	
    	}
	}

	function my_user_id() {
		my_mysql_connect();
        $sesid = mysql_real_escape_string(   (isset($_COOKIE["abcro"]) && strlen($_COOKIE["abcro"]) > 0) ? $_COOKIE["abcro"] : $_SESSION['abcro'] );

		$query = "SELECT id from users where session='$sesid' LIMIT 0,1;";
        $result = mysql_query($query) or die('Error s1');
        $count = mysql_num_rows($result);

        if ($count == 1) {
        	$row = mysql_fetch_row($result);
            $uid = $row[0];
            return $uid;
        }
	}

	function get_user_email($uid) {
		my_mysql_connect();
		$uid = mysql_real_escape_string($uid);
		$query = "SELECT email from users where id='$uid' ;";
        $result = mysql_query($query) or die('Error gue1');
        $count = mysql_num_rows($result);
        if ($count == 1) {
        	$row = mysql_fetch_row($result);
        	return $row[0];
        }
        return null;
	}
	function get_user_name($uid) {
		my_mysql_connect();
		$query = "SELECT name from users where id='$uid' ;";
        $result = mysql_query($query) or die('Error gun1');
        $count = mysql_num_rows($result);
        if ($count == 1) {
        	$row = mysql_fetch_row($result);
        	return $row[0];
        }
        return null;
	}


	function is_admin($uid) {
		my_mysql_connect();
		$query = "SELECT type from users where id='$uid' and type='1' LIMIT 0,1;";
        $result = mysql_query($query) or die('Error s1');
        $count = mysql_num_rows($result);

        if ($count == 1) {
        	return true;
        }
        return false;
	}

	function get_page_permission($uid, $pid) {
		// 7=owner  1=editor  0=denied		
		my_mysql_connect();
		$query = "SELECT url from pages WHERE (user_id='$uid' AND id='$pid');";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count == 1) {			
        	return 1;
		} else if (is_admin($uid)) {	        	
			return 7;
    	} else {
			return 0;
		}		
	}

	function is_pro_user($uid) {
		my_mysql_connect();
		$query = "SELECT * from orders WHERE (user_id='$uid' AND (now() BETWEEN start AND end) );";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count >= 1) {			
        	return 1;
		} else if (is_admin($uid)) {	        	
			return 1;
    	} else {
			return 0;
		}		
	}

	function was_pro_user($uid) {
		my_mysql_connect();
		$query = "SELECT * from orders WHERE (user_id='$uid');";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count >= 1) {			
        	return 1;
		} else {
			return 0;
		}		
	}
	function is_pro_trial_user($uid) {
		my_mysql_connect();
		$query = "SELECT DATEDIFF(DATE_ADD(date_started, INTERVAL 30 DAY),now()) as remaining from pro_trial WHERE (user_id='$uid');";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		
		if ($count >= 1) {
			$i = mysql_fetch_array($result);
			$days = $i[0];
			return $days > 0 ? $days : 0; //return how many days left [ result will be >0 if still on PRO plan, otherwise 0=expired ]
		} else {
			//if the user ever was PRO in the past (because they paid); don't allow them to become trial-PRO.

			$allow_trial = false;			
			if(!$allow_trial) {
				//NO PRO TRIAL ALLOWED:
				return 0;
			} else {
				// PRO 30 DAY TRIAL CODE:
				if (was_pro_user($uid)) {return 0;}
				return -1; //never been pro trial
			}
		}		
	}

	function start_pro_trial($uid) {
		$is_pro_user = is_pro_user($uid);
		$is_pro_trial_user = is_pro_trial_user($uid);
		if ($is_pro_trial_user == -1 && $is_pro_user == 0) { 		
			my_mysql_connect();
	    	$query = "INSERT into pro_trial (user_id) VALUES('$uid');";
	    	$result = mysql_query($query);		        	
	    	return $result;
	    } else if ($is_pro_trial_user == 0) {
	    	return "Your PRO trial plan has expired.";
	    } else if ($is_pro_trial_user > 0) {
	    	return "You are already on the PRO trial plan.";
	    } else if ($is_pro_user > 0) {
	    	return "You are on the PRO plan already.";
	    }
	}

	function is_affiliate($uid) {
		my_mysql_connect();
		$query = "SELECT id from affs where user_id='$uid';";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count >= 1) {
			$i = mysql_fetch_array($result);
			$id = $i[0];
			return $id;
		} else {
			return 0;
		}		
	}
	function get_affiliate_data($uid) {
		my_mysql_connect();
		$query = "SELECT name, email_paypal from affs join users u on u.id = affs.user_id where user_id='$uid';";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count >= 1) {
			$i = mysql_fetch_array($result);
			return $i;
		} else {
			return null;
		}		
	}


	function count_pages_by_user($uid) {
		my_mysql_connect();
		$query = "SELECT * from pages WHERE (user_id='$uid');";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		return $count;
	}

	function count_campaigns_by_page($pid) {
		my_mysql_connect();
		$query = "SELECT * from campaigns WHERE (page_id='$pid');";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		return $count;
	}

	function notify_affs($new_uid, $msg, $title) { //when trial sign up
		my_mysql_connect();
		$query = "SELECT r.email, r.name from users r JOIN affs a on a.user_id = r.id JOIN aff_users u on u.aff_id = a.id and u.user_id='$new_uid';";
	    $result = mysql_query($query);
	    $count = mysql_num_rows($result);
	    if ($count == 1) {
	        $row = mysql_fetch_row($result);
	        $email = $row[0];
	        $name = $row[1];
	        send_mail($msg,$msg,$title,$email,$name);
	        return true;
	    }	
	    return false;
	}
	function notify_affs_order($uid, $order_id, $msg, $title) { //when pro payment		
		if (notify_affs($uid,$msg,$title)) {
			my_mysql_connect();
			$query = "INSERT INTO aff_paid (order_id,date_paid) VALUES ('$order_id', NULL);"; //date_paid --> when WE have paid our affiliate their commission.
			mysql_query($query);
		}
	}

	function mark_user_to_aff_and_notify($new_uid, $email) {
		if (isset($_COOKIE["clicktrait-aff-track"])) {
	    	$aff = mysql_real_escape_string($_COOKIE["clicktrait-aff-track"]);
	    	$query = "INSERT into aff_users (user_id, aff_id) VALUES('$new_uid', '$aff');";
	    	$result = mysql_query($query);		        	
	    	notify_affs($new_uid,"Someone just signed up for a Clicktrait free account. Congratulations!", 'Affiliates: Clicktrait new free user');
	    }
	}


?>