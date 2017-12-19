<?php
$home_dir = '/home/ethos/';
$autominer_dir = $home_dir . '.autominer/';
$config = json_decode(file_get_contents($autominer_dir . 'config.json'));

function scrape_api( $url ) {

		$curl = curl_init($url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($curl);
                curl_close($curl);
                return json_decode($output);
}

function get_usd( $symbol ) {
	$api = "https://min-api.cryptocompare.com/data/price?fsym=".$symbol."&tsyms=USD";
	$scrape_url = scrape_api($api);
	return $scrape_url;
}

function get_yiimp( $url ) {
	$scrape_url = scrape_api($url);
	return $scrape_url;
}
function get_stats() {
	global $config;
	$config_coins = $config->configs;
	$output = array();
	foreach($config_coins as $label => $coin) {
		if(!empty($coin->yiimp)) {
			$yiimp_url = $coin->yiimp . "/api/wallet?address=" . $coin->wallet;
			$yiimp_scrape = get_yiimp( $yiimp_url );
			$yiimp_unpaid = $yiimp_scrape->unpaid;
			$yiimp_paid24 = $yiimp_scrape->paid24;
			$usd_scrape = get_usd( $coin->coin );
			$usd_value = $usd_scrape->USD;
			$output[$coin->coin]['coin'] = $coin->coin;
			$output[$coin->coin]['USD'] = $usd_value;
			$output[$coin->coin]['paid24'] = $yiimp_paid24;
			$output[$coin->coin]['unpaid'] = $yiimp_unpaid;
		}
	}
	return $output;
}
$grab = get_stats();
foreach($grab as $coin) {
	$unpaid = $coin['unpaid'] * $coin['USD'];
	$paid24 = $coin['paid24'] * $coin['USD'];
	$total = $unpaid + $paid24;
	echo $coin['coin'].":" . $total . "\r\n";
}
?>
