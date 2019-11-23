<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));

//---------------------------- get Rules !

if ($method == 'getRules') {

	$link = "https://api.dovewallet.com/v1.1/public/getmarkets";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".time().",";

	$coins = [];
	$j=0;

	$count = count($fcontents['result']);
	
	for ($i = 0; $i < $count; $i++) {
		$coins[$j++]=strtolower($fcontents['result'][$i]['MarketCurrency']);
		$coins[$j++]=strtolower($fcontents['result'][$i]['BaseCurrency']);
	}

	$coins = array_unique($coins);
	sort($coins);

	$rules .= "\"coins\":[";

	foreach ($coins as $value) {
		$rules .= "\"$value\",";
	}
	$rules = substr($rules, 0, -1) . "],";
	$rules .= "\"pairs\":{";

	for ($i = 0; $i < $count; $i++) {
		$rules .= "\"".strtolower($fcontents['result'][$i]['MarketCurrency']."_".$fcontents['result'][$i]['BaseCurrency'])."\":{";
		$symbol = strtoupper($fcontents['result'][$i]['MarketCurrency'].$fcontents['result'][$i]['BaseCurrency']);
		$rules .= "\"symbol\":\"$symbol\",";
		$rules .= "\"minPrice\":0,";
		$rules .= "\"maxPrice\":0,";
		$rules .= "\"minQty\":".$fcontents['result'][$i]['MinTradeSize'].",";
		$rules .= "\"maxQty\":0,";
		$rules .= "\"aroundPrice\":9,";
		$rules .= "\"aroundQty\":9,";
		$rules .= "\"minSum\":0,";
		$rules .= "\"maxSum\":0";
		$rules .= "},";
	}

	$rules = substr($rules, 0, -1) . "}}";

	echo $rules;
	exit;
}

//---------------------------- QUERY !

function dovewallet_query($path, array $req = array())
{
    $mt = explode(' ', microtime());
    $NONCE = $mt[1] . substr($mt[0], 2, 6);

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }

	$key = htmlspecialchars(strip_tags(trim($key)));
	$secret = htmlspecialchars(strip_tags(trim($secret)));

	$req['apikey'] = $key;
    $req['nonce'] = $NONCE;

	ksort($req);

    // generate the POST data string
    $post_data = http_build_query($req, '', '&');
    
    $url = 'https://api.dovewallet.com/v1.1/'.$path."?".$post_data;
    $sign = hash_hmac('sha512', $url, $secret);
    // generate the extra headers
	$headers = array(
		'apisign: '.$sign,
	);

    // our curl handle (initialize if required)
    static $ch = null;
    if (is_null($ch)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
    }

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);

    // run the query
    $res = curl_exec($ch);

	if ($res === false) {
		print "{\"success\":0}";
		exit;
	}

	$dec = json_decode($res, true);
	if (!$dec) {
		print "{\"success\":0}";
		exit;
	}
	return $dec;
}


//---------------------------- get Info !

if ($method == 'getBalances'){

	$result = dovewallet_query('account/getbalances');

	if ($result['success'] == true) {
		$count = count($result['result']);
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		for ($i = 0; $i < $count; $i++) {
			$balances .= "\"".strtolower($result['result'][$i]['Currency'])."\":".$result['result'][$i]['Available'].",";
		}
		$balances = substr($balances, 0, -1) . "}}";
	} else {
		$balances = "{\"success\":0}";
	}

	echo $balances;
	exit;

}

//---------------------------- get History !

if ($method == 'getTrades'){

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['since'])) { $since = $_POST['since']; } elseif(isset($_GET['since'])) { $since = $_GET['since']; } else { $since = 0; }
	$since = htmlspecialchars(strip_tags(trim($since)));
	$v = explode('_',$pair);
	$market = $v[1] . "-" . $v[0];

	$result = dovewallet_query('account/getorderhistory', array("market" => $market, "limit" => 10));
	$k = 0;
	
	if ($result['success'] == true) {
		$count = count($result['result']);
		$trades = "{";
		$trades .= "\"success\":1,";
		$trades .= "\"trades\":[";
		if ($count > 20) {$count = 20;}
		for ($i = $count; $i > 0; --$i) {
			if ($result['result'][$i]['PricePerUnit'] != null) {
				$trades .= "{\"pair\":\"".$pair."\",";
				if ($result['result'][$i]['OrderType'] == "LIMIT_SELL") {
					$trades .= "\"type\":\"sell\",";
				} else if ($result['result'][$i]['OrderType'] == "LIMIT_BUY") {
					$trades .= "\"type\":\"buy\",";
				}
				$trades .= "\"qty\":".($result['result'][$i]['Quantity'] - $result['result'][$i]['QuantityRemaining']).",";
				$trades .= "\"price\":".$result['result'][$i]['Limit'].",";
				$trades .= "\"time\":".strtotime(str_replace("T", " ", $result['result'][$i]['Closed']))."},";
				$k++;
			}
		}
		$trades = substr($trades, 0, -1) . "]}";
	} else {
		$trades = "{\"success\":0}";
	}
	
	if ($k) { echo $trades;} else { echo "{\"success\":0}";}
	exit;
}

//---------------------------- get Active Orders !

if ($method == 'getOrders') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	$v = explode('_',$pair);
	$market = $v[1] . "-" . $v[0];

	$result = dovewallet_query('market/getopenorders', array("market" => $market));
	
	$k  = 0;
	if ($result['success'] == true) {
		$count = count($result['result']);
		$orders = "{";
		$orders .= "\"success\":1,";
		$orders .= "\"orders\":[";
		for ($i = ($count-1); $i >= 0; $i--) {
			$orders .= "{\"id\":".$result['result'][$i]['OrderUuid'].",";
			$orders .= "\"pair\":\"".$pair."\",";
			
			if ($result['result'][$i]['OrderType'] == "LIMIT_SELL") {
				$orders .= "\"type\":\"sell\",";
			} else if ($result['result'][$i]['OrderType'] == "LIMIT_BUY") {
				$orders .= "\"type\":\"buy\",";
			}

			$orders .= "\"qty\":".$result['result'][$i]['Quantity'].",";
			$orders .= "\"fill\":".($result['result'][$i]['Quantity']-$result['result'][$i]['QuantityRemaining']).",";
			$orders .= "\"price\":".$result['result'][$i]['Limit'].",";
			$orders .= "\"time\":".strtotime(str_replace("T", " ", $result['result'][$i]['Opened']))."},";
			$k++;
		}
		$orders = substr($orders, 0, -1) . "]}";
	} else {
		$orders = "{\"success\":0}";
	}
	if ($k == 0) {
		$orders = "{\"success\":0}";
	}
	echo $orders;
	exit;


}

//---------------------------- get Order Book !

if ($method == 'getDepth') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['depth'])) { $depth = $_POST['depth']; } elseif(isset($_GET['depth'])) { $depth = $_GET['depth']; } else { $depth = 0; }
	$depth = htmlspecialchars(strip_tags(trim($depth)));

	$v = explode('_',$pair);
	$market = $v[1] . "-" . $v[0];

	$link = "https://api.dovewallet.com/v1.1/public/getorderbook?market=$market&type=both";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$asks = $fcontents['result']['sell'];
	$bids = $fcontents['result']['buy'];

	$depth = "{";
	$depth .= "\"success\":1,";
	$depth .= "\"asks\":[";

	$count = count($asks);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$asks[$i]['Rate'].",".$asks[$i]['Quantity']."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "],\"bids\":[";
	$count = count($bids);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$bids[$i]['Rate'].",".$bids[$i]['Quantity']."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "]}";

	echo $depth;
	exit;
}

//---------------------------- cancel Order !

if ($method == 'cancelOrder') {

	if(isset($_POST['id'])) { $id = $_POST['id']; } elseif(isset($_GET['id'])) { $id = $_GET['id']; } else { $id = 0; }
	$id = htmlspecialchars(strip_tags(trim($id)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	$result = dovewallet_query('market/cancel', array("uuid" => "$id"));

	if ($result['success'] == true) {
		$return = "{\"success\":1}";
	} else {
		$return = "{\"success\":0}";
	}

	echo $return;
	exit;

}

//---------------------------- TRADE !

if ($method == 'sendOrder') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	if(isset($_POST['type'])) { $type = $_POST['type']; } elseif(isset($_GET['type'])) { $type = $_GET['type']; } else { $type = 0; }
	$type = htmlspecialchars(strip_tags(trim($type)));

	if(isset($_POST['qty'])) { $qty = $_POST['qty']; } elseif(isset($_GET['qty'])) { $qty = $_GET['qty']; } else { $qty = 0; }
	$qty = htmlspecialchars(strip_tags(trim($qty)));

	if(isset($_POST['price'])) { $price = $_POST['price']; } elseif(isset($_GET['price'])) { $price = $_GET['price']; } else { $price = 0; }
	$price = htmlspecialchars(strip_tags(trim($price)));

	$v = explode('_',$pair);
	$market = $v[1] . "-" . $v[0];

	if ($type == "buy") {
		$result = dovewallet_query('market/buylimit', array("market" => $market, "quantity" => $qty, "rate" => $price));
	} else if ($type == "sell") {
		$result = dovewallet_query('market/selllimit', array("market" => $market, "quantity" => $qty, "rate" => $price));
	}
	
	if ($result['success'] == true) {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['result']['uuid'].",";
		$order .= "\"pair\":\"$pair\",";
		$order .= "\"type\":\"$type\",";
		$order .= "\"qty\":$qty,";
		$order .= "\"price\":$price,";
		$order .= "\"time\":$time";
		$order .= "}}";
	} else {
		$order = "{\"success\":0}";
	}

	echo $order;
	exit;

}

print "{\"success\":0}";
exit;

?>
