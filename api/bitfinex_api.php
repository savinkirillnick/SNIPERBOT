<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));

//---------------------------- get Rules

if ($method == 'getRules') {

	$link = "https://api.bitfinex.com/v1/symbols";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);
	$d = time();

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".$d.",";

	$count = count($fcontents);
	$coins = [];
	$i=0;
	for ($i = 0; $i < $count; $i++) {
		$coins[$i++]=substr($fcontents[$i], -3);
		$coins[$i++]=substr($fcontents[$i], 0, -3);
	}
	$coins = array_unique($coins);
	sort($coins);

	$rules .= "\"coins\":[";

	foreach ($coins as $value) {
		$rules .= "\"$value\",";
	}
	$rules = substr($rules, 0, -1) . "],";
	$rules .= "\"pairs\":{";

	$link = "https://api.bitfinex.com/v1/symbols_details";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);
	$count = count($fcontents);

		$data_pair=array();
		foreach($fcontents as $key=>$arr){
		    $data_pair[$key]=$arr['pair'];
		}
	    array_multisort($data_pair, SORT_NUMERIC, $fcontents);

	for ($i = 0; $i < $count; $i++) {
		$rules .= "\"".substr($fcontents[$i]['pair'], 0, -3)."_".substr($fcontents[$i]['pair'], -3)."\":{";
		$symbol = strtoupper($fcontents[$i]['pair']);
		$rules .= "\"symbol\":\"$symbol\",";
		$rules .= "\"minPrice\":0,";
		$rules .= "\"maxPrice\":0,";
		$rules .= "\"minQty\":".$fcontents[$i]['minimum_order_size'].",";
		$rules .= "\"maxQty\":".$fcontents[$i]['maximum_order_size'].",";
		$rules .= "\"aroundPrice\":".$fcontents[$i]['price_precision'].",";
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

function btce_query($method, array $req = array()) {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }

	$key = htmlspecialchars(strip_tags(trim($key)));
	$secret = htmlspecialchars(strip_tags(trim($secret)));

	$arr = array();
	$arr["request"] = "$method";
	$arr["nonce"] = time()."";

	$payload = json_encode($arr);
	$payload = base64_encode($payload);

	$signature = hash_hmac("sha384", $payload, $secret);
	$headers = array(
	    'X-BFX-APIKEY: '.$key,
    	'X-BFX-PAYLOAD: '.$payload,
    	'X-BFX-SIGNATURE: '.$sign
	);

	$ch = null;
	if (is_null($ch)) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BITFINEX PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}
	curl_setopt($ch, CURLOPT_URL, 'https://api.bitfinex.com'.$method);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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

	$result = btce_query('/v1/balances');

	if ($result['success'] == 1) {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			if ($result[$i]['type'] == "trading") {
				$balances .= "\"".$result[$i]['currency']."\":".$result[$i]['amount'].",";
			}
		}
		$balances = substr($balances, 0, -1) . "}}";
	} else {
		$balances = "{\"success\":0}";
	}

	echo $balances;
	exit;
}

//---------------------------- get History

if ($method == 'getTrades'){ //////////////////////////////////////////////////

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['since'])) { $since = $_POST['since']; } elseif(isset($_GET['since'])) { $since = $_GET['since']; } else { $since = 0; }
	$since = htmlspecialchars(strip_tags(trim($since)));
	$symbol = strtoupper(str_replace("_","",$pair));
	$result = btce_query('/v1/mytrades_funding', array("symbol" => "$symbol", "until" => $since));

	if ($result['success'] == 1) {
		$trades = "{";
		$trades .= "\"success\":1,";
		$trades .= "\"trades\":[";
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$trades .= "{\"pair\":\"".$pair."\",";
			$trades .= "\"type\":\"".strtolower($result[$i]['type'])."\",";
			$trades .= "\"qty\":".$result[$i]['amount'].",";
			$trades .= "\"price\":".$result[$i]['rate'].",";
			$trades .= "\"time\":".round($result[$i]['timestamp'],0)."},";
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

	$symbol = str_replace("_","",$pair);
	$result = btce_query('/v1/orders');
	$n = 0;
	if ($result) {
		$orders = "{";
		$orders .= "\"success\":1,";
		$orders .= "\"orders\":[";
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			if ($result[$i]['symbol'] == $symbol) {
				$orders .= "{\"id\":".$result[$i]['id'].",";
				$orders .= "\"pair\":\"".$pair."\",";
				$orders .= "\"type\":\"".$result[$i]['side']."\",";
				$orders .= "\"qty\":".$result[$i]['original_amount'].",";
				$orders .= "\"fill\":".($result[$i]['original_amount']-$result[$i]['remaining_amount']).",";
				$orders .= "\"price\":".$result[$i]['price'].",";
				$orders .= "\"time\":".round($result[$i]['timestamp'],0)."},";
				$n++;
			}
		}
		$orders = substr($orders, 0, -1) . "]}";
	} else {
		$orders = "{\"success\":0}";
	}
	if ($n > 0) {
		echo $orders;
	} else {
		print "{\"success\":0}";
	}
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

	$link = "https://api.bitfinex.com/v1/book/$symbol?limit_bids=$depth&limit_asks=$depth";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$asks = $fcontents['asks'];
	$bids = $fcontents['bids'];

	$depth = "{";
	$depth .= "\"success\":1,";
	$depth .= "\"asks\":[";

	$count = count($asks);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$asks[$i]['price'].",".$asks[$i]['amount']."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "],\"bids\":[";
	$count = count($bids);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$bids[$i]['price'].",".$bids[$i]['amount']."],";
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

	$result = btce_query('/v1/order/cancel', array("id" => "$id"));

	if ($result['id']) {
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

	$symbol = strtoupper(str_replace("_","",$pair));
	$result = btce_query('/v1/order/new', array("symbol" => "$symbol", "amount" => $qty, "price" => $price, "exchange" => "bitfinex", "side" => "$type", "type" => "limit"));

	if ($result['success'] == 1) {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['id'].",";
		$order .= "\"pair\":\"$pair\",";
		$order .= "\"type\":\"$type\",";
		$order .= "\"qty\":$qty,";
		$order .= "\"price\":$price,";
		$order .= "\"time\":".round($result['timestamp'],0);
		$order .= "}}";
	} else {
		$order = "{\"success\":0}";
	}

	echo $order;
	exit;
}

//---------------------------- get Prices

if ($method == 'getStrategyPrices') {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	$key = htmlspecialchars(strip_tags(trim($key)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	if(isset($_POST['strategy'])) { $strategy = $_POST['strategy']; } elseif(isset($_GET['strategy'])) { $strategy = $_GET['strategy']; } else { $strategy = 0; }
	$strategy = htmlspecialchars(strip_tags(trim($strategy)));

	$link = "http://www.funnymay.com/api/bitfinex_sapi.php?key=$key&pair=$pair&strategy=$strategy";
	$fcontents = implode ('', file ($link));

	echo $fcontents;
	exit;
}

print "{\"success\":0}";
exit;

?>
