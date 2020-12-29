<?php
set_time_limit(0);

function getSessionsByGroup($campaign) {
	$connection = null;
	require_once(dirname(__FILE__).'/../functions.php');
	$collection = getCollection("callbacks");

	$add =	[
				['$match' =>
					[
						'campaign' => "$campaign",
						'group' => ['$exists' => 1]
					]
				],
				['$group' => [
					'_id' => [
						'campaign' => '$campaign',
						'group' => '$group', 
						'SESSION' => '$SESSION'
					],
					'counts' => ['$sum' => 1],
				]],
			];
	$records=$collection->aggregate($add);
	$records = $records['result'];

	$SESSIONS = [];
	foreach ($records as $fields) {
		$sg = $fields['_id']['group'];
		$sid = $fields['_id']['SESSION'];
		$SESSIONS[$sg][$sid] = '';
	}
	return $SESSIONS;
}

function AB_BestPerformingVariations($campaign) {
	/*
		This will take the sum of #events for clicks
		and group it by each a/b-variation. It will calculate the performance
		foreach a/b-variation (sum of a/b-variation events DIVIDED BY(/) sum of total events).
		The final result will return a SORTED array.		
	*/	

	$connection = null;
	require_once(dirname(__FILE__).'/../functions.php');
	$collection = getCollection("callbacks");

	$add =	[
				['$match' =>
					[
						'campaign' => "$campaign",
						'event' => 'click', //['$in' => ['click', 'mouseenter', 'scroll_stop'] ],
						'group' => ['$exists' => 1],
						'element' => ['$ne' => 'undefined'] //only CTA clicks, no heatmap clicks.
					]
				],
				['$group' => [
					'_id' => [
						'event'=>'$event',
						'campaign' => '$campaign',
						'group' => '$group'
					],
					'counts' => ['$sum' => 1],
				]],
			];
	$records=$collection->aggregate($add);
	$records = $records['result'];


	//calculate total_events (sum of all 'counts') AND make an array of all unique sessions per group.
	$TOTAL_EVENTS = 0;
	
	foreach ($records as $fields) {
		$sum = $fields['counts'];
		$sg = $fields['_id']['group'];
		$TOTAL_EVENTS += $sum;
	}
	//print json_encode($SESSIONS, JSON_PRETTY_PRINT);;

	//calculate total unique sessions.
	$SESSIONS = getSessionsByGroup($campaign);
	$TOTAL_SESSIONS = 0 ;
	foreach ($SESSIONS as $key => $value) {
		$TOTAL_SESSIONS += sizeof($SESSIONS[$key]);
	}	
	//calculate performance for each group	
	$all = [];
	foreach ($records as $fields) {
		$sum = $fields['counts'];
		$event = $fields['_id']['event'];
		$sg = $fields['_id']['group'];

		$_perf = ($sum / $TOTAL_EVENTS * 100);		
		if (!isset($all[ $campaign ][$sg]["perf"])) {				
			$all[ $campaign ][$sg]["perf"] = 0;
			$all[ $campaign ][$sg]["events"] = 0;
			$all[ $campaign ][$sg]["sessions"] = 0;
		}
		$all[ $campaign ][$sg]["perf"] += $_perf;
		$all[ $campaign ][$sg]["events"] += $sum;
		$all[ $campaign ][$sg]["sessions"] = sizeof($SESSIONS[$sg]);
		
	}
	//print json_encode($all, JSON_PRETTY_PRINT); ;



	asort($all);
	closeConnection();

	return array('json' => $all, 'total_events' => $TOTAL_EVENTS, 'total_sessions' => $TOTAL_SESSIONS);
}



function calcAndStoreResult($campaign, $uid) {
	require_once(dirname(__FILE__).'/../security.php');
	my_mysql_connect();

	$campaign = mysql_real_escape_string($campaign);
	$rs = (AB_BestPerformingVariations($campaign));
	checkMAXDisable($rs, $campaign, $uid);
	//print json_encode($rs, JSON_PRETTY_PRINT);
	$rs = mysql_real_escape_string(json_encode($rs));

	//we make sure that newest calculated value is different from previously added one.
	//just in case the user didn't had any visitors; it would result in duplicate/identical entries.
	$query = "SELECT md5(value) t1, md5('$rs') t2 from results where campaign_id='$campaign' ORDER BY ID DESC LIMIT 1;";
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	if ($count == 1) {     
		$row = mysql_fetch_row($result);
		$t1 = $row[0];
		$t2 = $row[1];
		if (strcmp($t1, $t2) != 0) {
			$query = "INSERT INTO results (campaign_id, value) VALUES ('$campaign','$rs') ;";
			$result = mysql_query($query);
			print "$campaign: record added\n";
		} else {
			print "$campaign: duplicate record prevented\n";
		}
	} else if ($count == 0) {
		$query = "INSERT INTO results (campaign_id, value) VALUES ('$campaign','$rs') ;";
		$result = mysql_query($query);
		print "$campaign: record added\n";
	}

	
}

function checkMAXDisable($rs, $campaign, $uid) {
	//check if each group has reached 1000 visitors, if so, stop campaign.	
	$MAX = is_pro_user($uid) || is_pro_trial_user($uid)>0 ? 1000 : 100;
	$json = (array)current($rs['json']);
	#print json_encode($json, JSON_PRETTY_PRINT);
	if ( isset($json[0]) ) {return;} //filter out empty
	foreach ($json as $key => $obj) {
		if ($obj["sessions"] < $MAX) {
			return; //nope
		}
	}
	//all have at least 1000 or 100; so let's stop the campaign.
	//total sessions should be 1000 or 100 x #Groups
	require_once(dirname(__FILE__).'/../security.php');
	my_mysql_connect();
	$query = "UPDATE campaigns SET status=3, ended=now() WHERE id='$campaign';";
	$result = mysql_query($query);
	print "$campaign: MAX reached\n";
}

function calcAll() {	
	require_once(dirname(__FILE__).'/../security.php');
	my_mysql_connect();
	$query = "SELECT distinct c.id id, c.user_id uid FROM campaigns c LEFT JOIN results r ON r.campaign_id=c.id WHERE c.status >0 AND c.ended IS NULL;";
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	$ids = [];
	if ($count > 0) {             
		while($row = mysql_fetch_assoc($result)) {
			$ids[ $row["id"] ] = ['cid'=>$row["id"],'uid'=>$row["uid"]];
			print "-- " . $row["id"] . " --\n";
		}
	}

	foreach ($ids as $id => $value) {
		calcAndStoreResult($value['cid'], $value['uid']);
	}
}

//print json_encode(AB_BestPerformingVariations(8), JSON_PRETTY_PRINT);die;
header('Content-type: application/x-javascript');
calcAll();