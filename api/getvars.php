<?php

ini_set('display_errors', '1');
header('Content-type: application/x-javascript');

//$_GET['callback']='remove me';
//$_SERVER['HTTP_REFERER'] = 'http://127.0.1.1/demo/';

$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
if ($url === null) {die('error');}
$url = preg_replace('~&ab-sg=.*?&~', '&', $url);
$url = preg_replace('~\?ab-sg=.*?&~', '?', $url);
$url = preg_replace('~ab-sg=.*?&~', '&', $url);
$url = preg_replace('~&ab-sg=.*?$~', '&', $url);
$url = preg_replace('~\?ab-sg=.*?$~', '?', $url);
$url = preg_replace('~ab-sg=.*?$~', '&', $url);

$url = preg_replace('~&ab-sc=.*?&~', '&', $url);
$url = preg_replace('~\?ab-sc=.*?&~', '?', $url);
$url = preg_replace('~ab-sc=.*?&~', '&', $url);
$url = preg_replace('~&ab-sc=.*?$~', '&', $url);
$url = preg_replace('~\?ab-sc=.*?$~', '?', $url);
$url = preg_replace('~ab-sc=.*?$~', '&', $url);

$url = preg_replace('~&ab-srv=.*?&~', '&', $url);
$url = preg_replace('~\?ab-srv=.*?&~', '?', $url);
$url = preg_replace('~ab-srv=.*?&~', '&', $url);
$url = preg_replace('~&ab-srv=.*?$~', '&', $url);
$url = preg_replace('~\?ab-srv=.*?$~', '?', $url);
$url = preg_replace('~ab-srv=.*?$~', '&', $url);

$url = preg_replace('~&ab-hmp=.*?&~', '&', $url);
$url = preg_replace('~\?ab-hmp=.*?&~', '?', $url);
$url = preg_replace('~ab-hmp=.*?&~', '&', $url);
$url = preg_replace('~&ab-hmp=.*?$~', '&', $url);
$url = preg_replace('~\?ab-hmp=.*?$~', '?', $url);
$url = preg_replace('~ab-hmp=.*?$~', '&', $url);

if (isset($_GET['sg']) && substr($url,-2,2)=='/?') { $url = rtrim($url, "/?"); }
if (isset($_GET['sg']) && substr($url,-2,2)=='/&') { $url = rtrim($url, "/&"); }
if (isset($_GET['sg']) && substr($url,-1,1)=='?') { $url = rtrim($url, "?"); }
if (isset($_GET['sg']) && substr($url,-1,1)=='&') { $url = rtrim($url, "&"); }
if (isset($_GET['sg']) && substr($url,-1,1)=='/') { $url = rtrim($url, "/"); } //!! functions will test with '/' and without '/' version

require_once(dirname(__FILE__).'/../php/functions.php');
$json = [];
if (isset($_GET['hmp'])) {
	$SG = isset($_GET['sg']) && strlen($_GET['sg']) > 0 ? $_GET['sg'] : null;
	$SC = isset($_GET['sc']) && strlen($_GET['sc']) > 0 ? $_GET['sc'] : null; 
	$json = AB_HeatmapMode_Data($SC, $SG);
	$json = json_encode($json); 
} else {
	$SG = isset($_GET['sg']) && strlen($_GET['sg']) > 0 ? $_GET['sg'] : null; 		// specific group (cookies || screenshot)
	// <<==this==>> there can be more than one campaign active
	$SC = isset($_GET['sc']) && strlen($_GET['sc']) > 0 ? $_GET['sc'] : null; 		// specific campaign (cookies || screenshot)
	$SRV = isset($_GET['srv']) && strlen($_GET['srv']) > 0 ? $_GET['srv'] : null; 		// from server indicator

	$new_vars = loadVarsPublic($url, $SG, $SC, $SRV);
	$coder = new CodeDecode();
	$vars = $coder->decode($new_vars);
	$json = json_encode($vars);
}

echo $_GET['callback'] . "([" . $json . "])"; //return jsonp
die;

?>