<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));

//---------------------------- get Rules

if ($method == 'getRules') {

	$link = "https://api.exmo.com/v1/pair_settings/";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".time().",";

	$coins = [];
	$j=0;

	foreach ($fcontents as $key => $value) {
		$v = explode('_',$key);
		$coins[$i++]=strtolower($v[0]);
		$coins[$i++]=strtolower($v[1]);
	}

	$coins = array_unique($coins);
	sort($coins);

	$rules .= "\"coins\":[";

	foreach ($coins as $value) {
		$rules .= "\"$value\",";
	}
	$rules = substr($rules, 0, -1) . "],";
	$rules .= "\"pairs\":{";

	foreach ($fcontents as $key => $value) {
		$rules .= "\"".strtolower($key)."\":{";
		$v = explode('_',$key);
		$symbol = strtoupper($v[0].$v[1]);
		$rules .= "\"symbol\":\"$symbol\",";
		$rules .= "\"minPrice\":".$fcontents[$key]['min_price'].",";
		$rules .= "\"maxPrice\":".$fcontents[$key]['max_price'].",";
		$rules .= "\"minQty\":".$fcontents[$key]['min_quantity'].",";
		$rules .= "\"maxQty\":".$fcontents[$key]['max_quantity'].",";
		$rules .= "\"aroundPrice\":8,";
		$rules .= "\"aroundQty\":8,";
		$rules .= "\"minSum\":".$fcontents[$key]['min_amount'].",";
		$rules .= "\"maxSum\":".$fcontents[$key]['max_amount'];
		$rules .= "},";
	}

	$rules = substr($rules, 0, -1) . "}}";

	echo $rules;
	exit;
}

//---------------------------- QUERY

function exmo_query($api_name, array $req = array())
{
    $mt = explode(' ', microtime());
    $NONCE = $mt[1] . substr($mt[0], 2, 6);

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }

	$key = htmlspecialchars(strip_tags(trim($key)));
	$secret = htmlspecialchars(strip_tags(trim($secret)));

    $url = "http://api.exmo.com/v1/$api_name";

    $req['nonce'] = $NONCE;

    // generate the POST data string
    $post_data = http_build_query($req, '', '&');

    $sign = hash_hmac('sha512', $post_data, $secret);

    // generate the extra headers
    $headers = array(
        'Sign: ' . $sign,
        'Key: ' . $key,
    );

    // our curl handle (initialize if required)
    static $ch = null;
    if (is_null($ch)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

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


//---------------------------- get Info

if ($method == 'getBalances'){

	$result = exmo_query('user_info');

	if ($result['uid'] !== null) {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		foreach ($result['balances'] as $key => $value) {
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
	if(isset($_POST['since'])) { $since = $_POST['since']; } elseif(isset($_GET['since'])) { $since = $_GET['since']; } else { $since = 0; }
	$since = htmlspecialchars(strip_tags(trim($since)));

	$symbol = strtoupper($pair);

	$result = exmo_query('user_trades', array("pair" => "$symbol", "lomit" => 10));
	$count = count($result);

		if ($count) {
			$trades = "{";
			$trades .= "\"success\":1,";
			$trades .= "\"trades\":[";
				for ($i = 0; $i < $count; $i++) {
					$trades .= "{\"pair\":\"".$pair."\",";
					$trades .= "\"type\":".strtolower($result[$key][$i]['type']).",";
					$trades .= "\"qty\":".$result[$key][$i]['quantity'].",";
					$trades .= "\"price\":".$result[$key][$i]['price'].",";
					$trades .= "\"time\":".$result[$key][$i]['date']."},";
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
	$symbol = strtoupper($pair);

	$result = exmo_query('user_open_orders');
	$count = count($result);
	$k  = 0;

		if ($count) {
			$orders = "{";
			$orders .= "\"success\":1,";
			$orders .= "\"orders\":[";
				foreach($result as $key => $value){
					if ($key == $symbol) {
						$count = count($result[$key]);
						for ($i = 0; $i < $count; $i++) {
							$orders .= "{\"id\":".$result[$key][$i]['order_id'].",";
							$orders .= "\"pair\":\"".$pair."\",";
							$orders .= "\"type\":\"".strtolower($result[$key][$i]['type'])."\",";
							$orders .= "\"qty\":".$result[$key][$i]['quantity'].",";
							$orders .= "\"price\":".$result[$key][$i]['price'].",";
							$orders .= "\"time\":".$result[$key][$i]['created']."},";
							$k++;
						}
					}
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

//---------------------------- get Order Book

if ($method == 'getDepth') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['depth'])) { $depth = $_POST['depth']; } elseif(isset($_GET['depth'])) { $depth = $_GET['depth']; } else { $depth = 0; }
	$depth = htmlspecialchars(strip_tags(trim($depth)));

	$pair = strtoupper($pair);

	$link = "https://api.exmo.com/v1/order_book/?pair=$pair&limit=$depth";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$asks = $fcontents[$pair]['ask'];
	$bids = $fcontents[$pair]['bid'];

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

	$result = exmo_query('order_cancel', array("order_id" => "$id"));

	if ($result['result'] == true) {
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

	$symbol = strtoupper($pair);

	$result = exmo_query('order_create', array("pair" => "$symbol", "type" => "$type", "quantity" => $qty, "price" => $price));

	if ($result['result'] == true) {
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

if ($method == 'getStrategyPrices') {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	$key = htmlspecialchars(strip_tags(trim($key)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	if(isset($_POST['strategy'])) { $strategy = $_POST['strategy']; } elseif(isset($_GET['strategy'])) { $strategy = $_GET['strategy']; } else { $strategy = 0; }
	$strategy = htmlspecialchars(strip_tags(trim($strategy)));

	$link = "http://www.funnymay.com/api/exmo_sapi.php?key=$key&pair=$pair&strategy=$strategy";
	$fcontents = implode ('', file ($link));

	echo $fcontents;
	exit;
}

print "{\"success\":0}";
exit;

?>
