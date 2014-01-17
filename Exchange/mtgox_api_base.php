<?php

class MtGox_Api_Base
{

    protected $key;
    protected $secret;
    protected $active_currency;

    const api_url = 'https://data.mtgox.com/api/';

    const order_buy = 'bid';
    const order_sell = 'ask';

    protected $currency_codes = array(
        'USD' => 'USD',
        'AUD' => 'AUD',
        'CAD' => 'CAD',
        'CHF' => 'CHF',
        'CNY' => 'CNY',
        'DKK' => 'DKK',
        'EUR' => 'EUR',
        'GBP' => 'GBP',
        'HKD' => 'HKD',
        'JPY' => 'JPY',
        'NZD' => 'NZD',
        'PLN' => 'PLN',
        'RUB' => 'RUB',
        'SEK' => 'SEK',
        'SGD' => 'SGD',
        'THB' => 'THB'
    );

    public function __construct($currency='USD', $key=null, $secret=null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->set_currency($currency);
    }

    public function set_authentication($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        return $this;
    }

    public function set_currency($currency)
    {
        $this->active_currency = $this->sanitise_currency($currency);
        return $this;
    }

    public function sanitise_currency($currency)
    {
        $currency = strtoupper($currency);

        if (!isset($this->currency_codes[$currency]))
            throw new Exception('Currency code '.$currency.' not available');

        return $currency;
    }

    protected function send_request($uri, $params=array())
    {
        if (strstr($uri, '%s'))
            $uri = sprintf($uri, $this->active_currency);

        $result = $this->post_api_request($uri, $params);
        return $this->process_api_response($result);
    }

    protected function process_api_response($result)
    {
        if (!isset($result['result']))
            return $result;

        if (isset($result['result']) && $result['result'] == 'error')
            throw new Exception($result['error']);

        return $result['return'];
    }

    protected function post_api_request($path, $params = array())
    {
        $params['nonce'] = self::create_nonce();
        $post_data = http_build_query($params, '', '&');
        $url = self::api_url . $path;

        $headers = array(
            'Rest-Key: ' . $this->key,
            'Rest-Sign: ' . base64_encode(
                hash_hmac('sha512', $post_data, base64_decode($this->secret), true)
             )
        );
        $user_agent = 'Mozilla/4.0 (compatible; MtGox PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')';
        static $ch = null;
        if (is_null($ch)) 
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $net = curl_exec($ch);
        
        if ($net === false) 
            throw new Exception('Could not get reply: ' . curl_error($ch));
        
        $result = json_decode($net, true);

        if (!$result) 
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');

        return $result;
    }

    private static function create_nonce()
    {
        $mt = explode(' ', microtime());
        return $mt[1] . substr($mt[0], 2, 6);
    }

    public static function convert_btc_to_int($amount)
    {
        $int = (float)$amount * 100000000;
        return (int)$int;
    }

    public static function convert_int_to_btc($amount)
    {
        $int = (int)$amount / 100000000;
        return (float)$int;
    }

    public static function convert_currency_to_int($amount, $currency)
    {
        // japanese yen
        if ($currency == 'JPY')
            $int = (float)$amount * 1000;
        else
            $int = (float)$amount * 100000;

        return (int)$int;
    }

    public static function convert_int_to_currency($amount, $currency=null)
    {
        // japanese yen
        if ($currency == 'JPY')
            $int = (int)$amount / 1000;
        else
            $int = (int)$amount / 100000;

        return (float)$int;
    }


}
