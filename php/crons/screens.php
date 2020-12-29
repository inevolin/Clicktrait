<?php
set_time_limit(0);

function genFileName() {
	return uniqid();
}

function capture($url, $divs) {
	$divs = urlencode(json_encode($divs));
	$dir = dirname(__FILE__) . "/screens/uploads/";
	$cmd = 'timeout 6 phantomjs ' . dirname(__FILE__) . '/screens/screenshot.js "' . $url . '" "' . $divs . '"' . ' "' . $dir . '"';
	//print " $cmd \n";
	$ret = shell_exec($cmd);		
	return $ret;
}


function genData() {	
	require_once(dirname(__FILE__).'/../security.php');
	my_mysql_connect();
	$query = "SELECT id, json, page_url FROM campaigns where status > 0 AND (screenshots IS NULL OR screenshots = '[]') ORDER BY ID DESC;";  
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	
	$ret = [];
	if ($count > 0) {             
		while($row = mysql_fetch_assoc($result)) {		

			$ids = [];
			$url = $row['page_url'];
			$json = json_decode($row["json"]);
			foreach($json->vars->VAR as $index => $obj) {
				$ids[] = $obj->group;
			}
			$ret[$row["id"]] = [ 'groups' => $ids, 'url' => $url ] ;	
		}
	}

	return $ret;
}

function processData($sc, $sg, $url) {	
	$divs['body'] = genFileName() . '.jpg'; //full page screenshot
	$url .= strpos($url, '?') > 0 ? '&' : '?';
	$url .= 'ab-sc=' . $sc . '&ab-sg=' . $sg . '&ab-srv=yes';
	print "$url\n";

	$output = capture($url, $divs);
	$attempts = 1;
	while ($output == NULL && $attempts++ <= 4) {
		$output = capture($url, $divs);
		usleep(1 * 50 * 1000); // 50ms sleep
	}

	if (strpos($output, 'CLICKTRAIT_OK') !== FALSE) {
		//compression phase	
		$prefix = dirname(__FILE__) . '/screens/uploads/';
		$file = $prefix . $divs['body'];
		$img = imagecreatefromjpeg($file);
		imagejpeg($img, $file, 50);
		return $divs['body']; //return filename;
	}
	return null;
}

function saveImages($sc, $val) {
	require_once(dirname(__FILE__).'/../security.php');
	my_mysql_connect();
	$val = mysql_real_escape_string(json_encode($val));
	$query = "UPDATE campaigns SET screenshots='$val' WHERE id='$sc' ;";
	$result = mysql_query($query);
}


header('Content-type: application/x-javascript');

$data = genData();
foreach ($data as $sc => $arr) {

	sort($arr['groups']);
	$url = $arr['url'];
	$store = [];
	foreach ($arr['groups'] as $sg) {
		$fn = processData($sc, $sg, $url);
		if ($fn != null) { $store[] = ['group' => $sg, 'file' => $fn]; }
	}
	print json_encode($store, JSON_PRETTY_PRINT);
	saveImages($sc, $store);
}

/*
$divs['body'] = 'test.jpg'; //full page screenshot
$output = capture("https://healzer.com/pinbot/?ab-srv=1&ab-sc=19&ab-sg=2", $divs);
print var_dump($output);
*/

?>