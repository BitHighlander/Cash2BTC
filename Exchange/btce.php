<?php
/**
* Example Usage of the BTCe API PHP Class
*
* @author marinu666
* @license MIT License - https://github.com/marinu666/PHP-btce-api
*/

require_once('btce-api.php');
$BTCeAPI = new BTCeAPI(
                    /*API KEY: */ 'L13NPP1N-7F2E8KQ5-UYLI1EK8-D13ZRCEU-MJE10D3F',
                    /*API SECRET: */ 'fbfe62aa9255bac73e5f0344bf06fd33b7172a7d9b38476c63878407a7c3ccb1'
                      );

// Example getInfo
try {
    // Perform the API Call
    $getInfo = $BTCeAPI->apiQuery('getInfo');
    // Print so we can see the output
    print_r($getInfo);
} catch(BTCeAPIException $e) {
    echo $e->getMessage();
}

// Example Custom query
try {
    // Input Parameters as an array (see: https://btc-e.com/api/documentation for list of parameters per call)
    $params = array('pair' => 'btc_usd'); // Show info for the btc_usd pair
    // Perform the API Query
    print_r($BTCeAPI->apiQuery('ActiveOrders', $params));
} catch(BTCeAPIException $e) {
    echo $e->getMessage();
}

// Making an order
try {
    /*
* CAUTION: THIS IS COMMENTED OUT SO YOU CAN READ HOW TO DO IT!
*/
    // $BTCeAPI->makeOrder(---AMOUNT---, ---PAIR---, BTCeAPI::DIRECTION_BUY/BTCeAPI::DIRECTION_SELL, ---PRICE---);
    // Example: to buy a bitcoin for $100
    // $BTCeAPI->makeOrder(1, 'btc_usd', BTCeAPI::DIRECTION_BUY, 100);
} catch(BTCeAPIInvalidParameterException $e) {
    echo $e->getMessage();
} catch(BTCeAPIException $e) {
    echo $e->getMessage();
}

// Example Public API JSON Request (Such as Fee / BTC_USD Tickers etc) - The result you get back is JSON RESTed to PHP
// Fee Call
$btc_usd = array();
$btc_usd['fee'] = $BTCeAPI->getPairFee('btc_usd');
// Ticker Call
$btc_usd['ticker'] = $BTCeAPI->getPairTicker('btc_usd');
// Trades Call
$btc_usd['trades'] = $BTCeAPI->getPairTrades('btc_usd');
// Depth Call
$btc_usd['depth'] = $BTCeAPI->getPairDepth('btc_usd');
// Show all information
print_r($btc_usd);

?>