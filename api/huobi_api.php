<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));

if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
$key = htmlspecialchars(strip_tags(trim($key)));

if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }
$secret = htmlspecialchars(strip_tags(trim($secret)));

$api = "api.huobi.pro";
$req_method = "";
$api_method = "";

//---------------------------- Check Account ID

if ($key && $secret) {
	if ($_COOKIE["huobiAccountID"]) {
		$accountID = $_COOKIE["huobiAccountID"];
	} else {
		$accountID = getAccount();
		setcookie("huobiAccountID",$accountID,time()+3600);
	}
}

//---------------------------- QUERY

function create_sign_url($append_param = []) {
	global $api;
	global $api_method;
	global $key;

	$param = [
		'AccessKeyId' => $key,
		'SignatureMethod' => 'HmacSHA256',
		'SignatureVersion' => 2,
		'Timestamp' => date('Y-m-d\TH:i:s', time())
	];
	if ($append_param) {
		foreach($append_param as $k=>$ap) {
			$param[$k] = $ap;
		}
	}
	$bind_param = bind_param($param);
	return 'https://'.$api.$api_method.'?'.$bind_param;
}

function bind_param($param) {
	$u = [];
	$sort_rank = [];
	foreach($param as $k=>$v) {
		$u[] = $k."=".urlencode($v);
		$sort_rank[] = ord($k);
	}
	asort($u);
	$signature = create_sig($u);
	$u[] = "Signature=".urlencode($signature);
	return implode('&', $u);
}

function create_sig($param) {

	global $api;
	global $api_method;
	global $req_method;
	global $secret;

	$sign_param_1 = $req_method."\n".$api."\n".$api_method."\n".implode('&', $param);
	$signature = hash_hmac('sha256', $sign_param_1, $secret, true);
	return base64_encode($signature);
}

function huobi_query($url,$postdata=[]) {

	global $req_method;

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	if ($req_method == 'POST') {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
	}
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_HEADER,0);
	curl_setopt($ch, CURLOPT_TIMEOUT,60);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, [
		"Content-Type: application/json",
		]);
	$output = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);

	if ($output === false) {
		print "{\"success\":0}";
		exit;
	}

	$dec = json_decode($output, true);
	if (!$dec) {
		print "{\"success\":0}";
		exit;
	}
	return $dec;

}

//---------------------------- get Rules

if ($method == 'getRules') {

	$link = "http://api.huobi.pro/v1/common/timestamp";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".round($fcontents['data']/1000,0).",";

	$link = "http://api.huobi.pro/v1/common/symbols";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$count = count($fcontents['data']);
	$coins = [];
	$j=0;

	for ($i = 0; $i < $count; $i++) {
		$coins[$j++]=strtolower($fcontents['data'][$i]['base-currency']);
		$coins[$j++]=strtolower($fcontents['data'][$i]['quote-currency']);
	}
	$coins = array_unique($coins);
	sort($coins);

	$rules .= "\"coins\":[";

	foreach ($coins as $value) {
		$rules .= "\"$value\",";
	}
	$rules = substr($rules, 0, -1) . "],";
	$rules .= "\"pairs\":{";

	$i=0;
	$data_pair=array();
	foreach($fcontents['data'] as $key => $arr){
	    $data_pair[$key]=$arr[$i]['base-currency'];
	    $i++;
	}
    array_multisort($data_pair, SORT_NUMERIC, $fcontents['data']);

	for ($i = 0; $i < $count; $i++) {
		$rules .= "\"".strtolower($fcontents['data'][$i]['base-currency'])."_".strtolower($fcontents['data'][$i]['quote-currency'])."\":{";
		$rules .= "\"symbol\":\"".strtoupper($fcontents['data'][$i]['base-currency']).strtoupper($fcontents['data'][$i]['quote-currency'])."\",";
		$rules .= "\"minPrice\":0,";
		$rules .= "\"maxPrice\":0,";
		$rules .= "\"minQty\":0,";
		$rules .= "\"maxQty\":0,";
		$rules .= "\"aroundPrice\":".$fcontents['data'][$i]['price-precision'].",";
		$rules .= "\"aroundQty\":".$fcontents['data'][$i]['amount-precision'].",";
		$rules .= "\"minSum\":0,";
		$rules .= "\"maxSum\":0";
		$rules .= "},";
	}

	$rules = substr($rules, 0, -1) . "}}";

	echo $rules;
	exit;
}

//---------------------------- get Account ID

function getAccount (){

	global $api_method;
	global $req_method;

	$api_method = '/v1/account/accounts';
	$req_method = 'GET';
	$url = create_sign_url([]);

	$result = huobi_query($url);

	return $result['data'][0]['id'];

}

//---------------------------- get Info

if ($method == 'getBalances'){

	$api_method = "/v1/account/accounts/$accountID/balance";
	$req_method = 'GET';
	$url = create_sign_url([]);
	$result = huobi_query($url);

	if ($result['status'] == 'ok') {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		$count = count($result['data']['list']);

		for ($i = 0; $i < $count; $i++) {
			if ($result['data']['list'][$i]['type'] == 'trade') {
				$balances .= "\"".strtolower($result['data']['list'][$i]['currency'])."\":".$result['data']['list'][$i]['balance'].",";
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

if ($method == 'getTrades'){

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['since'])) { $since = $_POST['since']; } elseif(isset($_GET['since'])) { $since = $_GET['since']; } else { $since = 0; }
	$since = htmlspecialchars(strip_tags(trim($since)));

	$v = explode('_',$pair);
	$symbol = strtolower($v[0].$v[1]);

	$api_method = '/v1/order/matchresults';
	$req_method = 'GET';

	$postdata = [
		'symbol' => $symbol
	];
	$url = create_sign_url($postdata);
	$result = huobi_query($url,$postdata);

	if ($result['status'] != 'ok') {
		print "{\"success\":0}";
		exit;
	}
	$count = count($result['data']);

		if ($count) {
			$trades = "{";
			$trades .= "\"success\":1,";
			$trades .= "\"trades\":[";

				for ($i = 0; $i < $count; $i++) {
				$k=0;
					if ($result['data'][$i]['type'] != 'submit-cancel') {
						$trades .= "{\"pair\":\"".$pair."\",";
							if (substr($result['data'][$i]['type'],0,3) == "buy") {
								$trades .= "\"type\":\"buy\",";
							}
							if (substr($result['data'][$i]['type'],0,4) == "sell") {
								$trades .= "\"type\":\"sell\",";
							}

						$trades .= "\"qty\":".$result['data'][$i]['filled-amount'].",";
						$trades .= "\"price\":".$result['data'][$i]['price'].",";
						$trades .= "\"time\":".round($result['data'][$i]['created-at']/1000,0)."},";
						$k++;
					}
				}
			$trades = substr($trades, 0, -1) . "]}";
		} else {
			$trades = "{\"success\":0}";
		}

	if ($k == 0) {
		print "{\"success\":0}";
		exit;
	}

	echo $trades;
	exit;
}

//---------------------------- get Active Orders

if ($method == 'getOrders') {


	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	$v = explode('_',$pair);
	$symbol = strtolower($v[0].$v[1]);

	$api_method = '/v1/order/orders';
	$req_method = 'GET';

	$postdata = [
		'symbol' => $symbol,
		'states' => 'submitted'
	];

	$url = create_sign_url($postdata);
	$result = huobi_query($url,$postdata);

	if ($result['status'] != 'ok') {
		print "{\"success\":0}";
		exit;
	}
	$count = count($result['data']);

	if ($count) {
			$orders = "{";
			$orders .= "\"success\":1,";
			$orders .= "\"orders\":[";

				for ($i = 0; $i < $count; $i++) {
				$k=0;
					if ($result['data'][$i]['type'] != 'submit-cancel') {
						$orders .= "{\"id\":".$result['data'][$i]['id'].",";
						$orders .= "\"pair\":\"".$pair."\",";
							if (substr($result['data'][$i]['type'],0,3) == "buy") {
								$orders .= "\"type\":\"buy\",";
							}
							if (substr($result['data'][$i]['type'],0,4) == "sell") {
								$orders .= "\"type\":\"sell\",";
							}
						$orders .= "\"qty\":".$result['data'][$i]['amount'].",";
						$orders .= "\"fill\":".$result['data'][$i]['field-amount'].",";
						$orders .= "\"price\":".$result['data'][$i]['price'].",";
						$orders .= "\"time\":".round($result['data'][$i]['created-at']/1000,0)."},";
						$k++;
					}
				}
			$orders = substr($orders, 0, -1) . "]}";
		} else {
			$orders = "{\"success\":0}";
		}

	if ($k == 0) {
		print "{\"success\":0}";
		exit;
	}

	echo $orders;
	exit;

}

//---------------------------- get Order Book

if ($method == 'getDepth') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['depth'])) { $depth = $_POST['depth']; } elseif(isset($_GET['depth'])) { $depth = $_GET['depth']; } else { $depth = 0; }
	$depth_limit = htmlspecialchars(strip_tags(trim($depth)));

	$v = explode('_',$pair);
	$symbol = strtolower($v[0].$v[1]);

	$api_method = "/market/depth";
	$req_method = 'GET';
	$param = [
		'symbol' => $symbol,
		'type' => 'step0'
	];
	$url = create_sign_url($param);
	$result = huobi_query($url);

	$asks = $result['tick']['asks'];
	$bids = $result['tick']['bids'];

	$depth = "{";
	$depth .= "\"success\":1,";
	$depth .= "\"asks\":[";

	$count = count($asks);
	if ($count >= $depth_limit) {
		$count = $depth_limit;
	}
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$asks[$i][0].",".$asks[$i][1]."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "],\"bids\":[";
	$count = count($bids);
	if ($count >= $depth_limit) {
		$count = $depth_limit;
	}
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

	$api_method = '/v1/order/orders/'.$id.'/submitcancel';
	$req_method = 'POST';
	$postdata = [];
	$url = create_sign_url();
	$result = huobi_query($url,$postdata);

	if ($result['status'] == 'ok') {
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
	$symbol = strtolower($v[0].$v[1]);

	$api_method = "/v1/order/orders/place";
	$req_method = 'POST';
	$type_limit = "$type-limit";

	$postdata = [
		'account-id' => $accountID,
		'amount' => $qty,
		'source' => 'api',
		'symbol' => $symbol,
		'type' => $type_limit,
		'price' => $price
	];

	$url = create_sign_url();
	$result = huobi_query($url,$postdata);

	if ($result['status'] == 'ok') {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['data'].",";
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

	$link = "http://www.funnymay.com/api/huobi_sapi.php?key=$key&pair=$pair&strategy=$strategy";
	$fcontents = implode ('', file ($link));

	echo $fcontents;
	exit;
}

//---------------------------- get Chart

if ($method == 'getChart') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	$v = explode('_',$pair);
	$symbol = strtolower($v[0].$v[1]);

	$interval = "30min";
	$limit = 48;

	$link = "http://api.huobi.pro/market/history/kline?symbol=$symbol&period=$interval&size=$limit";
	$fcontents = implode ('', file ($link));

	$fcontents = json_decode($fcontents, true);

	$i=0;
	$j=0;
	$volumechart="";

	$chart = "<html>
<head>
<title>Chart</title>
<style>
body, html {
    height:100%;
    margin:0;
    padding:0;
}
</style>
</head>
<bodycellspacing='0' cellpadding='0'>
<script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
    <script type=\"text/javascript\">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
	  google.charts.setOnLoadCallback(drawChartVolume);

  function drawChart() {
    var data = google.visualization.arrayToDataTable([";

	$count = count($fcontents['data']);

	for ($i = 0; $i < $count; $i++) {
		$lasttime = getdate($fcontents['data'][($count-$i-1)]['id']);
		$tcandle[$j] =  date("H:i", mktime($lasttime["hours"], $lasttime["minutes"], 0, $lasttime["mon"], $lasttime["mday"], $lasttime["year"]));
		$lowcandle[$j] = $fcontents['data'][($count-$i-1)]['low'];
		$opencandle[$j] = $fcontents['data'][($count-$i-1)]['open'];
		$closecandle[$j] = $fcontents['data'][($count-$i-1)]['close'];
		$highcandle[$j] = $fcontents['data'][($count-$i-1)]['high'];
		$volumecandle[$j] = $fcontents['data'][($count-$i-1)]['vol'];
		$chart .= "[\"".$tcandle[$j]."\",".$lowcandle[$j].",".$opencandle[$j].",".$closecandle[$j].",".$highcandle[$j]."],";
		$volumechart .= "[\"".$tcandle[$j]."\",".$volumecandle[$j]."],";
		$j++;
	}

	$chart = substr ($chart, 0, -1);
	$volumechart = substr ($volumechart, 0, -1);

	$chart .= "    ], true);

    var options = {
      chartArea:{
		    left: 50,
		    top: 10,
		    width: 500,
		    height: 200
		  },
      legend:'none',
      colors: ['#515151', '#515151'],
      backgroundColor: {fill: '#131722', stroke: '#333333' },
      candlestick: {
            fallingColor: { strokeWidth: 0, fill: '#eb4d5c' },
            risingColor: { strokeWidth: 0, fill: '#53b987' }
          },
      hAxis: {
	        textStyle: {color: '#666666', fontSize: 12},
	        slantedTextAngle: 90
	      },
	  vAxis: {
	        gridlines: {color: '#333333'},
	        textStyle: {color: '#666666', fontSize: 12}
	      },
	  series: {0: {type: 'candlesticks'}, 1: {type: 'bars', targetAxisIndex:1, color:'#ebebeb'}}

    };

    var chart = new google.visualization.CandlestickChart(document.getElementById('chart_div'));

    chart.draw(data, options);
  }

  function drawChartVolume() {
    var data = google.visualization.arrayToDataTable([
    $volumechart
        ], true);

    var options = {
      chartArea:{
		    left: 50,
		    top: 10,
		    width: 500,
		    height: 100
		  },
	  hAxis: {
	        textStyle: {color: '#666666', fontSize: 12},
	        slantedTextAngle: 90
	      },
	  vAxis: {
	        gridlines: {color: '#333333'},
	        textStyle: {color: '#666666', fontSize: 12}

	      },
      legend:'none',
      colors: ['#515151', '#515151'],
      backgroundColor: {fill: '#131722', stroke: '#333333' }

    };
      var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_volume'));

      chart.draw(data, options);

  }
</script>
<div id=\"chart_div\" style=\"width: 600px; height: 250px;\"></div>
<div id=\"chart_div_volume\" style=\"width: 600px; height: 150px;\"></div>
</body></html>";

	echo $chart;
	exit;

}

print "{\"success\":0}";
exit;

?>
