<?php 

//ini_set('display_errors', '1');
header('Content-type: application/x-javascript');

$connection = null;
require(dirname(__FILE__).'/../php/functions.php');

if ( isset($_GET['callback']) && isset($_GET['data']) ) {
    $json = json_encode($_GET);
    $collection = getCollection('callbacks');

    foreach($_GET["data"] as $d)
    {
        $d["SESSION"] = $_GET["SESSION"];
        $d["campaign"] = $_GET["campaign"];
        $d["group"] = $_GET["group"];

        //capture this server sided to prevent malicious acts
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        $d["URL_original"] = $url;
        $url = preg_replace("/(index)\.(php|html|htm|asp|aspx)$/",'', $url); //if index.php or similar at the end, remove it
        $d["URL"] = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

        $d["REFERRER"] = $_GET["REFERRER"]; //from where the visitor came to target website
        
        $d["timestamp"] =  new MongoDate(time());        
        // we will use SESSION and EVENTid to idenitify consecutive events from a session and not timestamp
        // db.callbacks.findOne()._id.getTimestamp()
        $d["IP"] = get_client_ip();
        $collection->insert($d);
    }

    closeConnection();    
    echo $_GET['callback'] . "([".$json."])"; //return jsonp  
}


?>