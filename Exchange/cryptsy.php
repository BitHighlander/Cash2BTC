<?php

function api_query($method, array $req = array()) {
        // API settings
        $key = ''; // your API-key
        $secret = ''; // your Secret-key
 
        $req['method'] = $method;
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1];
       
        // generate the POST data string
        $post_data = http_build_query($req, '', '&');

        $sign = hash_hmac("sha512", $post_data, $secret);
 
        // generate the extra headers
        $headers = array(
                'Sign: '.$sign,
                'Key: '.$key,
        );
 
        // our curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Cryptsy API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }
        curl_setopt($ch, CURLOPT_URL, 'https://www.cryptsy.com/api');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 
        // run the query
        $res = curl_exec($ch);

        if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
        return $dec;
}
 
$result = api_query("getinfo");

//$result = api_query("getmarkets");

//$result = api_query("mytransactions");

//$result = api_query("markettrades", array("marketid" => 26));

//$result = api_query("marketorders", array("marketid" => 26));

//$result = api_query("mytrades", array("marketid" => 26, "limit" => 1000));

//$result = api_query("allmytrades");

//$result = api_query("myorders", array("marketid" => 26));

//$result = api_query("allmyorders");

//$result = api_query("createorder", array("marketid" => 26, "ordertype" => "Sell", "quantity" => 1000, "price" => 0.00031000));

//$result = api_query("cancelorder", array("orderid" => 139567));
 
$result = api_query("calculatefees", array("ordertype" => 'Buy', 'quantity' => 1000, 'price' => '0.005'));

echo "<pre>".print_r($result, true)."</pre>";


?>