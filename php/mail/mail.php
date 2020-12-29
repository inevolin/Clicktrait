<?php

	include('/home/smtp.php');

	function send_mail($message,$text_message,$subject,$to,$name) {
		
		global $CUST_EMAIL_PB;
		global $CUST_PASS_PB;

		require_once(dirname(__FILE__).'/class.phpmailer.php');

		$mail             = new PHPMailer();

		$body             = $message;

		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
		                                       // 1 = errors and messages
		                                       // 2 = messages only
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->Host       = "smtp.gmail.com"; // sets the SMTP server
		$mail->Port       = 465;                    // set the SMTP port for the GMAIL server
		$mail->Username   = $CUST_EMAIL_PB; // SMTP account username
		$mail->Password   = $CUST_PASS_PB;        // SMTP account password

		$mail->From 	  =	"ilya@clicktrait.com";//make sure to add this email address in your GMail & verify it.
		$mail->FromName   = "Ilya Nevolin";

		$mail->AddReplyTo("ilya@clicktrait.com","Ilya Nevolin");

		$mail->Subject    = $subject;

		$mail->AltBody    = $text_message; // text version

		$mail->MsgHTML($body);
		//$mail->SMTP 	  = 'tls';
		$mail->SMTPSecure = "ssl";

		$mail->AddAddress($to, $name);

		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment


		if(!$mail->Send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
			return false;
		} else {
			//echo "Message sent to: " . $to;
			return true;
		}
	}

	function send_mail_from_server($message,$text_message,$subject,$to,$name) {
				
		global $CUST_EMAIL_SERVER;
		global $CUST_PASS_SERVER;

		require_once(dirname(__FILE__).'/class.phpmailer.php');

		$mail             = new PHPMailer();

		$body             = $message;

		$mail->IsSMTP(); // telling the class to use SMTP
		                                       // 1 = errors and messages
		                                       // 2 = messages only
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->Host       = "smtp.gmail.com"; // sets the SMTP server
		$mail->Port       = 465;                    // set the SMTP port for the GMAIL server
		$mail->Username   = $CUST_EMAIL_SERVER; // SMTP account username
		$mail->Password   = $CUST_PASS_SERVER;        // SMTP account password

		$mail->From 	  =	"something@gmail.com";
		$mail->FromName   = "ClickTrait server";

		$mail->AddReplyTo("something@gmail.com","ClickTrait server");

		$mail->Subject    = $subject;

		$mail->AltBody    = $text_message; // text version

		$mail->MsgHTML($body);
		//$mail->SMTP 	  = 'tls';
		$mail->SMTPSecure = "ssl";
		$mail->SMTPDebug = 0;

		$mail->AddAddress($to, $name);

		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

		if(!$mail->Send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
			return false;
		} else {
		//echo "Message sent to: " . $to;
			return true;
		}
	}





function send_mail_recover($email, $password, $name)
{

	$subject = 'ClickTrait - Your new password.';

	//text
	$text_message = '
		Hello '.$fullname.',\r\n\r\n
		
		Your new password is: '.$password.' \r\n
		You can login here: http://clicktrait.com/ab/login.php .\r\n
		Feel free to contact us for any support, assistance or questions.\r\n\r\n

		Have a great day!\r\n
		Ilya Nevolin\r\n
		ilya@clicktrait.com
	';

	//html
	$message = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		  <head>
			<meta http-equiv="Content-Type" content="text/html;UTF-8" />
		  </head>
		  <body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F4F3F4">
			  <tbody>
				<tr>
				  <td style="padding: 15px;"><center>
					<table width="550" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
					  <tbody>
						<tr>
						  <td align="left">
							<div style="border: solid 1px #d9d9d9;">
							  <table id="header" style="font-size: 12px; font-family: Helvetica, Arial, sans-serif; border: solid 1px #FFFFFF; color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr>
									<td style="width: 100%; text-align: center;" valign="baseline"><a href="http://clicktrait.com"><img alt="Please enable display images" class="alignnone" src="http://clicktrait.com/ab/images/email-logo.png" /></a></td>
								  </tr>
								</tbody>
							  </table>
							  <table id="content" style="margin-top: 15px; margin-right: 30px; margin-left: 30px; color: #444; line-height: 1.6; font-size: 12px; font-family: Arial, sans-serif;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr>
									<td colspan="2">
									  <div style="padding: 15px 0;">
									  
										Hello '.$fullname.',<br><br>
																				
										Your new password is: <strong>'.$password.'</strong> <br>
										You can login here: <a href="http://clicktrait.com/ab/login.php">http://clicktrait.com/ab/login.php</a> .<br>
										Feel free to contact us for any support, assistance or questions.<br><br>

									  </div>
									</td>
								  </tr>
								</tbody>
							  </table>
							  <table id="footer" style="line-height: 1.5; font-size: 12px; font-family: Arial, sans-serif; margin-right: 30px; margin-left: 30px;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr style="font-size: 11px; color: #999999;">
									<td style="border-top: solid 1px #d9d9d9;" colspan="2"><br />
										Have a great day!<br />
										Ilya Nevolin<br />
										<a href="mailto:ilya@clicktrait.com">ilya@clicktrait.com</a><br><br>
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
						  </td>
						</tr>
					  </tbody>
					</table>
					</center></td>
				</tr>
			  </tbody>
			</table>
		  </body>
		</html>
		';

	send_mail($message,$text_message,$subject,$email,$name);
}

function send_mail_pro_purchase($fullname, $email)
{

	$subject = 'ClickTrait - PRO plan payment confirmation.';
	$due=date('d/m/Y', strtotime('+1 months'));

	//text
	$text_message = '
		Hello '.$fullname.',\r\n\r\n
		
		I hope you are doing well today!\r\n
		We have successfully received your Pro-plan payment.\r\n
		The next due date is '.$due.' \r\n\r\n

		Have a great day!\r\n
		Ilya Nevolin\r\n
		ilya@clicktrait.com
	';

	//html
	$message = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		  <head>
			<meta http-equiv="Content-Type" content="text/html;UTF-8" />
		  </head>
		  <body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F4F3F4">
			  <tbody>
				<tr>
				  <td style="padding: 15px;"><center>
					<table width="550" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
					  <tbody>
						<tr>
						  <td align="left">
							<div style="border: solid 1px #d9d9d9;">
							  <table id="header" style="font-size: 12px; font-family: Helvetica, Arial, sans-serif; border: solid 1px #FFFFFF; color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr>
									<td style="width: 100%; text-align: center;" valign="baseline"><a href="http://clicktrait.com"><img alt="Please enable display images" class="alignnone" src="https://clicktrait.com/ab/images/email-logo.png" /></a></td>
								  </tr>
								</tbody>
							  </table>
							  <table id="content" style="margin-top: 15px; margin-right: 30px; margin-left: 30px; color: #444; line-height: 1.6; font-size: 12px; font-family: Arial, sans-serif;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr>
									<td colspan="2">
									  <div style="padding: 15px 0;">
									  
											Hello '.$fullname.',<br><br>
		
											I hope you are doing well today! <br>
											We have successfully received your Pro-plan payment. <br>
											The next due date is '.$due.' <br><br>

									  </div>
									</td>
								  </tr>
								</tbody>
							  </table>
							  <table id="footer" style="line-height: 1.5; font-size: 12px; font-family: Arial, sans-serif; margin-right: 30px; margin-left: 30px;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr style="font-size: 11px; color: #999999;">
									<td style="border-top: solid 1px #d9d9d9;" colspan="2"><br />
										Have a great day!<br />
										Ilya Nevolin<br />
										<a href="mailto:ilya@clicktrait.com">ilya@clicktrait.com</a><br><br>
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
						  </td>
						</tr>
					  </tbody>
					</table>
					</center></td>
				</tr>
			  </tbody>
			</table>
		  </body>
		</html>
		';

	send_mail($message,$text_message,$subject,$email,$fullname);
}

function send_mail_welcome($fullname, $email, $link)
{

	$subject = 'ClickTrait - Activate your account';
	$due=date('d/m/Y', strtotime('+1 months'));

	//text
	$text_message = '
		Hello '.$fullname.',\r\n\r\n
		
		Thank you for working with us!\r\n
		You can start using Clicktrait right away.\r\n
		All you need to do is follow this link ( '.$link.' ) to activate your account. \r\n\r\n

		Have a great day!\r\n
		Ilya Nevolin\r\n
		ilya@clicktrait.com
	';

	//html
	$message = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		  <head>
			<meta http-equiv="Content-Type" content="text/html;UTF-8" />
		  </head>
		  <body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F4F3F4">
			  <tbody>
				<tr>
				  <td style="padding: 15px;"><center>
					<table width="550" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
					  <tbody>
						<tr>
						  <td align="left">
							<div style="border: solid 1px #d9d9d9;">
							  <table id="header" style="font-size: 12px; font-family: Helvetica, Arial, sans-serif; border: solid 1px #FFFFFF; color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr>
									<td style="width: 100%; text-align: center;" valign="baseline"><a href="http://clicktrait.com"><img alt="Please enable display images" class="alignnone" src="https://clicktrait.com/ab/images/email-logo.png" /></a></td>
								  </tr>
								</tbody>
							  </table>
							  <table id="content" style="margin-top: 15px; margin-right: 30px; margin-left: 30px; color: #444; line-height: 1.6; font-size: 12px; font-family: Arial, sans-serif;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr>
									<td colspan="2">
									  <div style="padding: 15px 0;">
									  
											Hello '.$fullname.',<br><br>
		
											Thank you for working with us!<br>
											You can start using Clicktrait right away.<br>
											All you need to do is follow <a href="'.$link.'">this link</a> to activate your account. <br><br>

									  </div>
									</td>
								  </tr>
								</tbody>
							  </table>
							  <table id="footer" style="line-height: 1.5; font-size: 12px; font-family: Arial, sans-serif; margin-right: 30px; margin-left: 30px;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
								<tbody>
								  <tr style="font-size: 11px; color: #999999;">
									<td style="border-top: solid 1px #d9d9d9;" colspan="2"><br />
										Have a great day!<br />
										Ilya Nevolin<br />
										<a href="mailto:ilya@clicktrait.com">ilya@clicktrait.com</a><br><br>
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
						  </td>
						</tr>
					  </tbody>
					</table>
					</center></td>
				</tr>
			  </tbody>
			</table>
		  </body>
		</html>
		';

	send_mail($message,$text_message,$subject,$email,$fullname);
}