<?php


class CodeDecode {
	function encode($data) {
		if (is_array($data)) {
			return array_map(array($this, 'encode'), $data);
		}
		if (is_object($data)) {
			$tmp = clone $data; // avoid modifing original object
			foreach ($data as $k => $var) {
				$tmp->{$k} = $this->encode($var);
			}

			return $tmp;
		}
		return htmlentities($data);
	}

	function decode($data) {
		if (is_array($data)) {
			return array_map(array($this, 'decode'), $data);
		}
		if (is_object($data)) {
			$tmp = clone $data; // avoid modifing original object
			foreach ($data as $k => $var) {
				$tmp->{$k} = $this->decode($var);
			}

			return $tmp;
		}
		return html_entity_decode($data);
	}
}

// URL from referer ; SpecificGroup ; Live-site-indicator
// if SC given and $SRV null, SC must be correct otherwise random SC returned.
// if SC and SG given    and $SRV  => SC-SG returned
// if SC and SG are null and $SRV  => [] returned
function loadVarsPublic($url, $SG, $SC, $SRV) {
	$cid = null;
	$vars=null;	

	require_once(dirname(__FILE__).'/../php/security.php');    
	my_mysql_connect();
	$surl = mysql_real_escape_string($url);

	if ( $SC == null &&  $SRV == null) {
		// random campaign		
		$query = "SELECT json, id FROM campaigns WHERE (page_url='$surl' OR page_url='$surl/') AND status=1 ;";
	} else if ($SC != null && $SRV == null) {
		// specific campaign (stored in cookies) °°could be empty if ended campaign...°°
		$esc = mysql_real_escape_string($SC);
		$query = "SELECT json, id FROM campaigns WHERE (page_url='$surl' OR page_url='$surl/') AND status=1 AND id='$esc';";
	} else if ($SC != null && $SG != null && $SRV != null) {
		// probably the screenshot script doing it's thing.
		$esc = mysql_real_escape_string($SC);
		$query = "SELECT json, id FROM campaigns WHERE (page_url='$surl' OR page_url='$surl/') and id='$esc';";
	} else {
		return null;	
	}


	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	$rows = array(); 
    if ($count > 0) {            	
        while ($row = mysql_fetch_assoc($result)) {
            $rows[] = $row;
        }        
    } else if ($count == 0 && $SRV == null) {
		// just in case campaign ended, and user has old cookie values, let's give him random.		
		$query = "SELECT json, id FROM campaigns WHERE page_url='$surl' AND status=1 ;";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count > 0) {      
			$SG = null; // important line: SC was invalid, so SG will definitely be invalid !
	        while ($row = mysql_fetch_assoc($result)) {
	            $rows[] = $row;
	        }        
	    } else {
	    	return null; //nothing is active right now.
	    }		
	} else {
		return null; //SRV request and invalid SC
	}

	$row = $rows[array_rand($rows)]; //pick random active campaign (if multiple/random)
    $cid = $row["id"];
    $found = json_decode($row["json"]);
    $vars = isset($found->vars) ? $found->vars : null;
    if (sizeof($vars) == 0) {
    	// invalid JSON in DB ; someone should get notified.
    	return null;
	} 


	// Get specific group
	$new_vars = [];
	if ($SG != null) {
		foreach ($vars->VAR as $struct) {
			if ($SG == $struct->group) {
				$new_vars[] = $struct;
				break;
			}
		}
	}

	// 		if 	no specific group given 		=> pick random
	// Else if  specific group not found LIVE 	=> pick random
	// Else if 	specific group not found 		=> return null;
	if ($SG == null) {		
		$new_vars[] = $vars->VAR[array_rand($vars->VAR)];
	} else if (sizeof($new_vars) == 0 && $SRV == null) {		
		$new_vars[] = $vars->VAR[array_rand($vars->VAR)];
	} else if (sizeof($new_vars) == 0 && $SRV != null) {		
		return null;
	}	

	// append CTAs
	foreach ($vars->CTA as $cta) {$new_vars[] = $cta;}

	$new_vars[0]->campaign_id = $cid;
	return $new_vars;
}


function getCollection($col) {
	global $connection;	
	$db = "demo4";
	$connection = new MongoClient();
	return $collection = $connection->selectCollection($db,$col);
}

function closeConnection() {
	global $connection;	
	$connection->close();
}

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}




function AB_HeatmapMode_Data($campaign, $group) {

	/*$json = [	["selector" => "img", "selector_clicks"=>6, "pos" => ["offsetX" => 30, "offsetY" => 10, "el_width" => 500, "el_height" => 600]],
				["selector" => "img", "selector_clicks"=>6, "pos" => ["offsetX" => 40, "offsetY" => 10, "el_width" => 500, "el_height" => 600]],			
				["selector" => "img", "selector_clicks"=>6, "pos" => ["offsetX" => 50, "offsetY" => 10, "el_width" => 500, "el_height" => 600]],
				["selector" => "img", "selector_clicks"=>6, "pos" => ["offsetX" => 60, "offsetY" => 10, "el_width" => 500, "el_height" => 600]],
				["selector" => "span", "selector_clicks"=>8, "pos" => ["offsetX" => 100, "offsetY" => 40, "el_width" => 100, "el_height" => 100]],
				["selector" => "span", "selector_clicks"=>7, "pos" => ["offsetX" => 0, "offsetY" => 40, "el_width" => 100, "el_height" => 100]],
		 	];
	return $json;*/

	$connection = null;
	$collection = getCollection("callbacks");

	$add =	[
				'selector' => ['$ne' => ''],
				'event' => ['$eq' => 'click'],				
				'campaign' => ['$eq' => "$campaign"],
				'group' => ['$eq' => "$group"],
				'pos' => ['$exists' => 1],
				'pos.el_width' => ['$exists' => 1],
			
				
			];
	$cursor=$collection->find($add);
	$selectors = [];
	$data = [];
	foreach($cursor as $jokes) {
		if (!isset($selectors[$jokes["selector"]])) {$selectors[$jokes["selector"]]=0;}
		$selectors[$jokes["selector"]]++;
		$data[] = $jokes;
	}

	$all = [];
	foreach ($data as $fields) {
		$selector = $fields['selector'];
		$selector_clicks = $selectors[$selector];
		$pos = $fields['pos'];

		$all[] = ["selector" => $selector, "selector_clicks" => $selector_clicks, "pos" => $pos ];		
	}

	closeConnection();

	return $all;
}

?>