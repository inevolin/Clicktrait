<?php

$map = [
      "AT" => "20",
      "BE" => "21",
      "BG" => "20",
      "HR" => "25",
      "CY" => "19",
      "CZ" => "21",
      "DK" => "25",
      "EE" => "20",
      "FI" => "24",
      "FR" => "20",
      "DE" => "19",
      "EL" => "23",
      "HU" => "27",
      "IE" => "23",
      "IT" => "22",
      "LV" => "21",
      "LT" => "21",
      "LU" => "17",
      "MT" => "18",
      "NL" => "21",
      "PL" => "23",
      "PT" => "23",
      "RO" => "24",
      "SK" => "20",
      "SI" => "22",
      "ES" => "21",
      "SE" => "25",
      "UK" => "20",
      "GB" => "20",
    ];

  function getCountry() { 
    $country = 'BE';
    if (isset($_COOKIE['country_geo_ip']) && strlen($_COOKIE['country_geo_ip']) == 2 && isset($map[$_COOKIE['country_geo_ip']]) ) {
      $country = $_COOKIE['country_geo_ip'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
      $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
      $country = $details->country;   
    }
    setcookie("country_geo_ip", $country, time()+3600*30, '/');
    return $country;
  }

  function proceed_paypal($uid, $subtotal, $vat, $vatp, $total) {
    $json = [];
    $json["email"] = get_user_email($uid);
    $json["uid"] = $uid;
    $json["subtotal"] = $subtotal;
    $json["vat"] = $vat;
    $json["total"] = $total;

    $query = array();
    $query['item_name'] = 'Clicktrait PRO membership';
    $query['notify_url'] = 'https://clicktrait.com/ab/php/ipn.php';
    $query['image_url'] = 'https://clicktrait.com/ab/images/pp_logo.png';
    $query['return'] = 'https://clicktrait.com/ab/pro.php';
    $query['business'] = 'yourpaypal@gmail.com';          
    $query['currency_code'] = 'USD';            
    $query['no_shipping'] = '1';      
    $query['custom'] = json_encode($json);

    if (isset($_POST['sub'])) {
      $query['cmd'] = '_xclick-subscriptions';
      $query['a3'] = $total; //price
      $query['p3'] = 1; //one month
      $query['t3'] = 'M'; //monthly
      $query['src'] = 1; //recurring
    } else if (isset($_POST['pay'])) {
      $query['cmd'] = '_xclick';
      $query['amount'] = $subtotal;
      $query['tax_rate'] = $vatp;
    }

    $query_string = http_build_query($query);           
    header('Location: https://www.paypal.com/cgi-bin/webscr?' . $query_string); //sandbox
    exit();
  }

  function discount() {
    if (isset($_GET['disc']) && $_GET['disc']=='50OFF') {
      return 0.5;
    }
    return 1;
  }
    
  $country = getCountry();
  $disc = (float)discount();
  $subtotal = round((float)49.99*$disc,2);
  $vatp = $map[$country];
  $vat = round($subtotal * $vatp / 100,2);
  $total = round($vat + $subtotal,2);


?>