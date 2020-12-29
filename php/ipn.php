<?php

if (isset($_POST) && !empty($_POST)) {
	file_put_contents('./__ipn__.log', json_encode($_POST).','.PHP_EOL, FILE_APPEND | LOCK_EX);
}

require(dirname(__FILE__).'/log.php');
require(dirname(__FILE__).'/security.php');
require(dirname(__FILE__).'/mail/mail.php');

// [PRO] payments
if (isset($_POST['receiver_email']) && isset($_POST['txn_id']) && isset($_POST['payer_email']) && ($_POST['txn_type'] == 'web_accept' || $_POST['txn_type'] == 'subscr_payment')) {
	my_mysql_connect();
	
	$tid = mysql_real_escape_string(trim($_POST['txn_id']));
	$payermail = mysql_real_escape_string(trim($_POST['payer_email']));
	$fullname = mysql_real_escape_string(trim($_POST['first_name'] .' '. $_POST['last_name']));
	$currency = mysql_real_escape_string(trim($_POST['mc_currency']));

	if ( isset($_POST['payment_status']) && $_POST['payment_status'] == 'Completed' && $_POST['payment_type'] == 'echeck' ) {
		return; //we'll accept echeck payments before receiving the funds, if they don't get delivered after 3 wdays, we'll cancel license.
	}

	if (isset($_POST['custom'])) {		
		$json = (array)json_decode($_POST['custom']);	
		$uid = mysql_real_escape_string($json["uid"]);
		

		$total = mysql_real_escape_string(trim($json["total"]));
		$subtotal = mysql_real_escape_string(trim($json["subtotal"]));
		$vat = mysql_real_escape_string(trim($json["vat"]));

		//if they insta-buy, then let's verify their account.
		$query = "UPDATE users SET verified=1 WHERE id='$uid' AND verified=0;"; 
		mysql_query($query);
		
		$query = "INSERT INTO orders (user_id, end, total,subtotal,vat, currency, tid, name_pp, email_pp) VALUES ('$uid', date_add(now(), interval 30 DAY), $total,$subtotal,$vat, '$currency', '$tid', '$fullname', '$payermail');";
		mysql_query($query);
		$order_id = mysql_insert_id();

		$email = ($json["email"]);
		send_mail_pro_purchase($fullname, $email); //to given account
		if (strcmp($email, $payermail) != 0) {
			send_mail_pro_purchase($fullname, $payermail); //to paypal account
		}
		$msg = "$payermail ($fullname) $total $currency [ $tid ]" ;
		send_mail_from_server($msg,$msg,'Clicktrait PRO purchase','admin@clicktrait.com','Ilya');
		notify_affs_order($uid, $order_id, 'Someone just paid for Clicktrait PRO. Congratulations!', 'Affiliates: Clicktrait PRO');

		require_once("/home/MyMailerLite.php");
		subscribe($fullname, $payermail, 5242119);

	}	

}


?>
<html>
<head>
<meta name="robots" content="noindex">
</head>
<body>

Wrong page.
</form>
</body>
</html>