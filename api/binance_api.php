<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));

//---------------------------- get Rules

if ($method == 'getRules') {

	$link = "https://api.binance.com/api/v1/exchangeInfo";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".round($fcontents['serverTime']/1000,0).",";

	$count = count($fcontents['symbols']);
	$coins = [];
	$j=0;

	for ($i = 0; $i < $count; $i++) {
		if ($fcontents['symbols'][$i]['baseAsset'] != 123) {
			$coins[$j++]=strtolower($fcontents['symbols'][$i]['baseAsset']);
			$coins[$j++]=strtolower($fcontents['symbols'][$i]['quoteAsset']);
		}
	}
	$coins = array_unique($coins);
	sort($coins);

	$rules .= "\"coins\":[";

	foreach ($coins as $value) {
		$rules .= "\"$value\",";
	}
	$rules = substr($rules, 0, -1) . "],";
	$rules .= "\"pairs\":{";

		$data_pair=array();
		foreach($fcontents['symbols'] as $key=>$arr){
		    $data_pair[$key]=$arr;
		}
	    array_multisort($data_pair, SORT_NUMERIC, $fcontents['symbols']);

	for ($i = 0; $i < $count; $i++) {
		if ($fcontents['symbols'][$i]['baseAsset'] != 123 && $fcontents['symbols'][$i]['status'] != "BREAK") {
			$rules .= "\"".strtolower($fcontents['symbols'][$i]['baseAsset'])."_".strtolower($fcontents['symbols'][$i]['quoteAsset'])."\":{";
			$symbol = $fcontents['symbols'][$i]['symbol'];
			$rules .= "\"symbol\":\"$symbol\",";
			$rules .= "\"minPrice\":".$fcontents['symbols'][$i]['filters'][0]['minPrice'].",";
			$rules .= "\"maxPrice\":".$fcontents['symbols'][$i]['filters'][0]['maxPrice'].",";
			$rules .= "\"minQty\":".$fcontents['symbols'][$i]['filters'][2]['minQty'].",";
			$rules .= "\"maxQty\":".$fcontents['symbols'][$i]['filters'][2]['maxQty'].",";
			$rules .= "\"aroundPrice\":".abs(log10($fcontents['symbols'][$i]['filters'][0]['tickSize'])).",";
			$rules .= "\"aroundQty\":".abs(log10($fcontents['symbols'][$i]['filters'][2]['stepSize'])).",";
			$rules .= "\"minSum\":0,";
			$rules .= "\"maxSum\":0";
			$rules .= "},";
		}
	}

	$rules = substr($rules, 0, -1) . "}}";

	echo $rules;
	exit;
}

//---------------------------- QUERY

function binance_query($path, $method, array $req = array()) {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }

	$key = htmlspecialchars(strip_tags(trim($key)));
	$secret = htmlspecialchars(strip_tags(trim($secret)));

	$req['recvWindow'] = 5000;

	if ($_COOKIE["binanceDeltaTime"]) {
		$deltaTime = $_COOKIE["binanceDeltaTime"];
		$correctTime = time()*1000 - $deltaTime;
	} else {
		$link = "https://api.binance.com/api/v1/time";
		$fcontents = implode ('', file ($link));
		$fcontents = json_decode($fcontents, true);
		$serverTime = $fcontents["serverTime"];
		$deltaTime = time()*1000 - $serverTime;
		setcookie("binanceDeltaTime",$deltaTime,time()+3600);
		$correctTime = $serverTime;
	}

	$req['timestamp'] = $correctTime;

	$post_data = http_build_query($req, '', '&');
    $sign = hash_hmac("sha256", $post_data, $secret);
   	$req['signature'] = $sign;

	$post_data = http_build_query($req, '', '&');

	$ch = null;
	if (is_null($ch)) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; binance PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}

	if ($method == 'GET') {
		$headers = array(
			'X-MBX-APIKEY: '.$key,
		);
		$url = 'https://api.binance.com'.$path."?".$post_data;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}
	if ($method == 'POST') {
		$headers = array(
			'X-MBX-APIKEY: '.$key,
			'Content-Type: application/x-www-form-urlencoded',
		);
		$url = 'https://api.binance.com'.$path;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	if ($method == 'DELETE') {
		$headers = array(
			'X-MBX-APIKEY: '.$key,
			'Content-Type: application/x-www-form-urlencoded',
		);
		$url = 'https://api.binance.com'.$path;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

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

//---------------------------- get Info

if ($method == 'getBalances'){

	$result = binance_query("/api/v3/account","GET");

	if ($result['updateTime'] != null) {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		$count = count($result['balances']);

		for ($i = 0; $i < $count; $i++) {
			$balances .= "\"".strtolower($result['balances'][$i]['asset'])."\":".$result['balances'][$i]['free'].",";
		}
		$balances = substr($balances, 0, -1) . "}}";
	} else {
		$balances = "{\"success\":0}";
	}

	echo $balances;
	exit;

}

//---------------------------- get History

if ($method == 'getTrades'){

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	//if(isset($_POST['since'])) { $since = $_POST['since']; } elseif(isset($_GET['since'])) { $since = $_GET['since']; } else { $since = 0; }
	//$since = htmlspecialchars(strip_tags(trim($since)));
	$v = explode('_',$pair);
	$symbol = strtoupper($v[0].$v[1]);

	$result = binance_query("/api/v3/myTrades","GET", array("symbol" => "$symbol","limit" => "10",));
	$count = count($result);

		if ($count) {
			$trades = "{";
			$trades .= "\"success\":1,";
			$trades .= "\"trades\":[";
				for ($i = 0; $i < $count; $i++) {
					$trades .= "{\"pair\":\"".$pair."\",";
					if ($result[$i]['isBuyer'] === true) {
						$trades .= "\"type\":\"buy\",";
					} else {
						$trades .= "\"type\":\"sell\",";
					}
					$trades .= "\"qty\":".$result[$i]['qty'].",";
					$trades .= "\"price\":".$result[$i]['price'].",";
					$trades .= "\"time\":".round($result[$i]['time']/1000,0)."},";
				}
			$trades = substr($trades, 0, -1) . "]}";
		} else {
			$trades = "{\"success\":0}";
		}

	echo $trades;
	exit;
}

//---------------------------- get Active Orders

if ($method == 'getOrders') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	$v = explode('_',$pair);
	$symbol = strtoupper($v[0].$v[1]);

	$result = binance_query("/api/v3/openOrders","GET", array("symbol" => "$symbol",));
	$count = count($result);

		if ($count) {
			$orders = "{";
			$orders .= "\"success\":1,";
			$orders .= "\"orders\":[";
				for ($i = 0; $i < $count; $i++) {
					$orders .= "{\"id\":".$result[$i]['orderId'].",";
					$orders .= "\"pair\":\"".$pair."\",";
					$orders .= "\"type\":\"".strtolower($result[$i]['side'])."\",";
					$orders .= "\"qty\":".$result[$i]['origQty'].",";
					$orders .= "\"price\":".$result[$i]['price'].",";
					$orders .= "\"time\":".round($result[$i]['time']/1000,0)."},";
				}
			$orders = substr($orders, 0, -1) . "]}";
		} else {
			$orders = "{\"success\":0}";
		}
	echo $orders;
	exit;


}

//---------------------------- get Order Book

if ($method == 'getDepth') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['depth'])) { $depth = $_POST['depth']; } elseif(isset($_GET['depth'])) { $depth = $_GET['depth']; } else { $depth = 0; }
	$depth = htmlspecialchars(strip_tags(trim($depth)));

	$v = explode('_',$pair);
	$symbol = strtoupper($v[0].$v[1]);

	$link = "https://api.binance.com/api/v1/depth?symbol=$symbol&limit=$depth";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$asks = $fcontents['asks'];
	$bids = $fcontents['bids'];

	$depth = "{";
	$depth .= "\"success\":1,";
	$depth .= "\"asks\":[";

	$count = count($asks);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$asks[$i][0].",".$asks[$i][1]."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "],\"bids\":[";
	$count = count($bids);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$bids[$i][0].",".$bids[$i][1]."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "]}";

	echo $depth;
	exit;
}

//---------------------------- cancel Order

if ($method == 'cancelOrder') {

	if(isset($_POST['id'])) { $id = $_POST['id']; } elseif(isset($_GET['id'])) { $id = $_GET['id']; } else { $id = 0; }
	$id = htmlspecialchars(strip_tags(trim($id)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	$v = explode('_',$pair);
	$symbol = strtoupper($v[0].$v[1]);

	$result = binance_query("/api/v3/order","DELETE", array("symbol" => "$symbol", "orderId" => "$id", ));

	if ($result['orderId'] != null) {
		$return = "{\"success\":1}";
	} else {
		$return = "{\"success\":0}";
	}

	echo $return;
	exit;

}

//---------------------------- TRADE

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
	$symbol = strtoupper($v[0].$v[1]);

	$result = binance_query("/api/v3/order","POST", array("symbol" => "$symbol", "type" => "LIMIT", "side" => "$type", "timeInForce" => "GTC", "quantity" => $qty, "price" => $price));

	if ($result['orderId'] != null) {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['orderId'].",";
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

//---------------------------- get Prices

if ($method == 'getPrices') {

	$link = "https://api.binance.com/api/v1/exchangeInfo";
	$fcontents = implode ('', file ($link));
	$rules = json_decode($fcontents, true);

	$link = "https://api.binance.com/api/v3/ticker/price";
	$fcontents = implode ('', file ($link));
	$prices = json_decode($fcontents, true);

	$count = count($prices);
	if ($count > 0) {
		$price = "{";
		$price .= "\"status\":1,";
		$price .= "\"data\":{";
		for ($i = 0; $i < $count; $i++) {
			$price .= "\"".strtolower($rules['symbols'][$i]['baseAsset'])."_".strtolower($rules['symbols'][$i]['quoteAsset'])."\":".$prices[$i]['price'].",";
		}
		$price = substr($price, 0, -1) . "}}";
	} else {
		$price = "{\"status\":0}";
	}

	echo $price;
	exit;
}

//---------------------------- get Strategy Prices

if ($method == 'getStrategyPrices') {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	$key = htmlspecialchars(strip_tags(trim($key)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	if(isset($_POST['strategy'])) { $strategy = $_POST['strategy']; } elseif(isset($_GET['strategy'])) { $strategy = $_GET['strategy']; } else { $strategy = 0; }
	$strategy = htmlspecialchars(strip_tags(trim($strategy)));

	$link = "http://www.funnymay.com/api/binance_sapi.php?key=$key&pair=$pair&strategy=$strategy";
	$fcontents = implode ('', file ($link));

	echo $fcontents;
	exit;
}

print "{\"success\":0}";
exit;

?>
