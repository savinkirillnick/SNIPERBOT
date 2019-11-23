<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));

if ($method == 'getRules') {

	$link = "https://poloniex.com/public?command=returnCurrencies";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$d = time();

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".$d.",";

	$rules .= "\"coins\":[";

	foreach ($fcontents as $key => $value) {
		$rules .= "\"$key\",";
	}
	$rules = substr($rules, 0, -1) . "],";

	$link = "https://poloniex.com/public?command=returnTicker";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$rules .= "\"pairs\":{";

		$data_pair=array();
		foreach($fcontents as $key => $value){
		    array_push($data_pair, $key);
		}
	    sort($data_pair);

	foreach ($data_pair as $value) {

		$v = explode('_',$value);
		$pair = strtolower($v[1]."_".$v[0]);
		$rules .= "\"$pair\":{";
		$symbol = strtoupper($v[1].$v[0]);
		$rules .= "\"symbol\":\"$symbol\",";
		$rules .= "\"minPrice\":0,";
		$rules .= "\"maxPrice\":0,";
		$rules .= "\"minQty\":0,";
		$rules .= "\"maxQty\":0,";
		$rules .= "\"aroundPrice\":0,";
		$rules .= "\"aroundQty\":0,";
		$rules .= "\"minSum\":0,";
		$rules .= "\"maxSum\":0";
		$rules .= "},";
	}
	$rules = substr($rules, 0, -1) . "}}";

	echo $rules;
	exit;
}

//---------------------------- QUERY

function poloniex_query($method, array $req = array()) {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }

	$key = htmlspecialchars(strip_tags(trim($key)));
	$secret = htmlspecialchars(strip_tags(trim($secret)));

	$req['command'] = $method;
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1];

	$post_data = http_build_query($req, '', '&');
    $sign = hash_hmac("sha512", $post_data, $secret);
	$headers = array(
		'Sign: '.$sign,
		'Key: '.$key,
	);

	$ch = null;
	if (is_null($ch)) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; POLONIEX PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}
	curl_setopt($ch, CURLOPT_URL, 'https://poloniex.com/tradingApi');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$res = curl_exec($ch);

	if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
	return $dec;
}

//---------------------------- get Info

if ($method == 'getBalances'){

	$result = poloniex_query("returnBalances");

	if ($result) {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		foreach ($result as $key => $value) {
			$balances .= "\"".strtolower($key)."\":$value,";
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

	$start = time()-86400;
	$pair = strtoupper($pair);
	$pair = explode('_',$pair);
	$pair = $pair[1]."_".$pair[0];
	$result = poloniex_query("returnTradeHistory", array("currencyPair" => "$pair", "start" => "$start"));

	if ($result) {
		$trades = "{";
		$trades .= "\"success\":1,";
		$trades .= "\"trades\":[";
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$trades .= "{\"pair\":\"".$pair[0]."_".$pair[1]."\",";
			$trades .= "\"type\":\"".$result[$i]['type']."\",";
			$trades .= "\"qty\":".$result[$i]['amount'].",";
			$trades .= "\"price\":".$result[$i]['rate'].",";
			$trades .= "\"time\":".strtotime($result[$i]['date'])."},";
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

	$pair = strtoupper($pair);
	$pair = explode('_',$pair);
	$pair = $pair[1]."_".$pair[0];
	$result = poloniex_query("returnOpenOrders", array("currencyPair" => "$pair"));

	if ($result) {
		$orders = "{";
		$orders .= "\"success\":1,";
		$orders .= "\"orders\":[";
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$orders .= "{\"id\":".$result[$i]['orderNumber'].",";
			$orders .= "\"pair\":\"".$pair[0]."_".$pair[1]."\",";
			$orders .= "\"type\":\"".$result[$i]['type']."\",";
			$orders .= "\"qty\":".$result[$i]['startingAmount'].",";
			$orders .= "\"fill\":".$result[$i]['amount'].",";
			$orders .= "\"price\":".$result[$i]['rate'].",";
			$orders .= "\"time\":".strtotime($result[$i]['date'])."},";
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
	$symbol = strtoupper($v[1]."_".$v[0]);

	$link = "https://poloniex.com/public?command=returnOrderBook&currencyPair=$symbol&depth=$depth";
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

	$result = poloniex_query("cancelOrder", array("orderNumber" => "$id"));

	if ($result['success'] == 1) {
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

	$pair = strtoupper($pair);
	$pair = explode('_',$pair);
	$pair = $pair[1]."_".$pair[0];

	$result = poloniex_query("$type", array("currencyPair" => "$pair", "type" => "$type", "amount" => $qty, "rate" => $price));

	if ($result['orderNumber']) {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['orderNumber'].",";
		$order .= "\"pair\":\"".$pair[0]."_".$pair[1]."\",";
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

?>
