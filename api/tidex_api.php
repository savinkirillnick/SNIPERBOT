<?php
error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);
if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));
//---------------------------- get Rules
if ($method == 'getRules') {
	$link = "https://api.tidex.com/api/3/info";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);
	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".$fcontents['server_time'].",";
	$count = count($fcontents['pairs']);
	$coins = [];
	$i=0;
	foreach ($fcontents['pairs'] as $key => $value) {
		$v = explode('_',$key);
		$coins[$i++]=$v[0];
		$coins[$i++]=$v[1];
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
		foreach($fcontents['pairs'] as $key => $value){
		    array_push($data_pair, $key);
		}
	    sort($data_pair);
	foreach ($data_pair as $value) {
		$rules .= "\"$value\":{";
		$v = explode('_',$value);
		$symbol = strtoupper($v[0].$v[1]);
		$rules .= "\"symbol\":\"$symbol\",";
		$rules .= "\"minPrice\":".$fcontents['pairs'][$value]['min_price'].",";
		$rules .= "\"maxPrice\":".$fcontents['pairs'][$value]['max_price'].",";
		$rules .= "\"minQty\":".$fcontents['pairs'][$value]['min_amount'].",";
		$rules .= "\"maxQty\":".$fcontents['pairs'][$value]['max_amount'].",";
		$rules .= "\"aroundPrice\":".$fcontents['pairs'][$value]['decimal_places'].",";
		$rules .= "\"aroundQty\":".$fcontents['pairs'][$value]['decimal_places'].",";
		$rules .= "\"minSum\":".$fcontents['pairs'][$value]['min_total'].",";
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
	$req['method'] = $method;
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
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}
	curl_setopt($ch, CURLOPT_URL, 'https://api.tidex.com/tapi');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$res = curl_exec($ch);
	//print $res;
	if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
	return $dec;
}
//---------------------------- get Info
if ($method == 'getBalances'){
	$result = btce_query('getInfo');
	if ($result['success'] == 1) {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		foreach ($result['return']['funds'] as $key => $value) {
			$balances .= "\"$key\":$value,";
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
	$result = btce_query('TradeHistory', array("pair" => "$pair", "count" => 10));
	$count = count($result['return']);
	if ($result['success'] == 1 && $count > 0) {
		$trades = "{";
		$trades .= "\"success\":1,";
		$trades .= "\"trades\":[";
		foreach ($result['return'] as $key => $value) {
			$trades .= "{\"pair\":\"".$result['return'][$key]['pair']."\",";
			$trades .= "\"type\":\"".$result['return'][$key]['type']."\",";
			$trades .= "\"qty\":".$result['return'][$key]['amount'].",";
			$trades .= "\"price\":".$result['return'][$key]['rate'].",";
			$trades .= "\"time\":".$result['return'][$key]['timestamp']."},";
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
	$result = btce_query('ActiveOrders', array("pair" => "$pair"));
	$count = count($result['return']);
	if ($result['success'] == 1 && $count > 0) {
		$orders = "{";
		$orders .= "\"success\":1,";
		$orders .= "\"orders\":[";
		foreach ($result['return'] as $key => $value) {
			$orders .= "{\"id\":".$key.",";
			$orders .= "\"pair\":\"".$result['return'][$key]['pair']."\",";
			$orders .= "\"type\":\"".$result['return'][$key]['type']."\",";
			$orders .= "\"qty\":".$result['return'][$key]['timestamp_created'].",";
			$orders .= "\"fill\":".$result['return'][$key]['amount'].",";
			$orders .= "\"price\":".$result['return'][$key]['rate'].",";
			$orders .= "\"time\":".$result['return'][$key]['timestamp_created']."},";
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
	$link = "https://api.tidex.com/api/3/depth/$pair?limit=$depth";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);
	$asks = $fcontents[$pair]['asks'];
	$bids = $fcontents[$pair]['bids'];
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
	$result = btce_query('CancelOrder', array("order_id" => "$id"));
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
	$result = btce_query('Trade', array("pair" => "$pair", "type" => "$type", "amount" => $qty, "rate" => $price));
	if ($result['success'] == 1) {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['return']['order_id'].",";
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
