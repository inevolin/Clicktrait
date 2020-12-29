<?php


	if ($_SERVER['HTTP_HOST'] == "127.0.1.1") {
		$file_contents = file_get_contents('wpb-track.js');
	} else if (empty($_SERVER['HTTP_REFERER'])) {
		die('oops?');
	} else {
		$file_contents = file_get_contents('wpb-ab-min-1464894586.js'); //wpb-track.js
	}
    
    if ($file_contents) {
        header('Content-type: text/javascript');
        print $file_contents;
    }
