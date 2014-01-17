<?php

class MtGox_Merchant_Api extends MtGox_Api_Base 
{

    const uri_order_create = '2/money/merchant/order/create';
    const uri_api_info     = '2/money/info';

    public function create_order($amount, $currency, $description, $order_number, $success_url, $failure_url, $ipn_url)
    {
        $params = array(
            'amount'         => $amount, 
            'currency'       => $currency, 
            'description'    => $description, 
            'data'           => $order_number, 
            'return_success' => $success_url, 
            'return_failure' => $failure_url, 
            'ipn'            => $ipn_url
        );
        return $this->send_api_request(self::uri_order_create, $params);
    }    
}
