<?php

class MtGox_Private_Api extends MtGox_Api_Base 
{
    const uri_account_info = '1/generic/private/info';
    const uri_new_address = '1/generic/bitcoin/address';
    const uri_add_private_key = '1/generic/private/bitcoin/addpriv';
    const uri_add_wallet_dat = '1/generic/bitcoin/wallet_add';
    const uri_withdraw_coins = '1/generic/bitcoin/send_simple';
    const uri_id_key = '1/generic/private/idkey';
    const uri_trade_history = '1/BTC%s/private/trades';
    const uri_wallet_history = '1/generic/private/wallet/history';
    const uri_wallet_history_alt = '1/generic/wallet/history';
    const uri_submit_order = '1/BTC%s/private/order/add';
    const uri_get_orders = '1/generic/private/orders';
    const uri_order_result = '1/generic/private/order/result';
    const uri_cancel_order = '0/cancelOrder.php';

    // returns information about your account, funds, fees, API privileges, withdraw limits . . . 
    public function get_account_info()
    {
        return $this->send_request(self::uri_account_info);
    }

    // get a bitcoin address linked to your account. the returned address can be used to deposit bitcoins in your mtgox account 
    public function get_new_address($description=null, $ipn_url=null)
    {
        return $this->send_request(self::uri_new_address);
    }

    // allows you to add a private key to your account
    public function add_private_key($key, $key_type, $description=null)
    {
        return $this->send_request(self::uri_add_private_key);
    }

    // allows you to add a wallet.dat file to your account
    public function add_wallet_dat($file_contents, $description=null)
    {
        return $this->send_request(self::uri_add_wallet_dat);
    }

    // Send bitcoins from your account to a bitcoin address. 
    public function withdraw_coins($address, $amount_int, $fee_int=null, $no_instant=null, $green=null)
    {
        return $this->send_request(self::uri_withdraw_coins);
    }

    // Returns the idKey used to subscribe to a user's private updates channel in the websocket API. The key is valid for 24 hours. 
    public function get_id_key()
    {
        return $this->send_request(self::uri_id_key);
    }

    // Returns all of your trades in this currency (BTCUSD, BTCEUR . . . ) . Does not include fees. 
    public function get_trade_history()
    {
        return $this->send_request(self::uri_trade_history);
    }

    public function get_wallet_history($currency=null, $type=null, $date_start=null, $date_end=null, $trade_id=null, $page=null, $use_alternate=false)
    {
        if ($currency)
            $currency = $this->sanitise_currency($currency);
        else
            $currency = $this->active_currency;

        $params = array(
            'currency' => $currency,
            'type' => $type,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'trade_id' => $trade_id,
            'page' => $page,
            'use_alternate' => $use_alternate
        );

        //alternate is self::uri_wallet_history_alt
        return $this->send_request(self::uri_wallet_history, $params);
    }
    
    // Buy bitcoins
    public function submit_buy_order($amount, $price)
    {
        return $this->submit_order(self::order_buy, $amount, $price);
    }
    
    public function submit_buy_order_at_market($amount)
    {
        return $this->submit_order(self::order_buy, $amount);
    }

    // Sell bitcoins
    public function submit_sell_order($amount, $price)
    {
        return $this->submit_order(self::order_sell, $amount, $price);
    }

    public function submit_sell_order_at_market($amount)
    {
        return $this->submit_order(self::order_sell, $amount);
    }

    // submits an order and returns the OID and info about success or error 
    public function submit_order($type, $amount, $price=null)
    {
        $type = strtolower($type);
        
        if ($type!=self::order_buy && $type!=self::order_sell)
            throw new Exception('Order type can be bid or ask only. Check first parameter of submit_order($type)');

        $params = array(
            'type' => $type,
            'amount_int' => self::convert_btc_to_int($amount)
        );

        if ($price !== null)
            $params['price_int'] = self::convert_currency_to_int($price, $this->active_currency);

        return $this->send_request(self::uri_submit_order, $params);
    }

    // Get my open orders
    public function get_open_orders()
    {
        return $this->send_request(self::uri_get_orders);
    }
    
    public function get_sell_order($order_id)
    {
        return $this->get_order(self::order_sell, $order_id);
    }

    public function get_buy_order($order_id)
    {
        return $this->get_order(self::order_buy, $order_id);
    }
    
    public function get_order($type, $order_id)
    {
        if ($type!=self::order_buy && $type!=self::order_sell)
            throw new Exception('Order type can be bid or ask only. Check first parameter of get_order($type)');

        $params = array(
            'type' => $type,
            'order' => $order_id
        );
        return $this->send_request(self::uri_order_result);
    }

    // Cancel orders

    public function cancel_all_sell_orders()
    {
        return $this->cancel_all_orders(self::order_sell);
    }

    public function cancel_all_buy_orders()
    {
        return $this->cancel_all_orders(self::order_buy);
    }

    public function cancel_all_orders($type)
    {
        if ($type!=self::order_buy && $type!=self::order_sell)
            throw new Exception('Order type can be bid or ask only. Check first parameter of cancel_order($type)');

        $all_orders = $this->get_open_orders();
        foreach ($all_orders as $order)
        {
            if (isset($order['oid']) && isset($order['type']))
            {
                if ($order['type'] != $type)
                    continue;
                
                $this->cancel_order($order['type'], $order['oid']);
            }
        }
        return true;
    }

    public function cancel_buy_order($order_id)
    {
        return  $this->cancel_order(self::order_buy, $order_id);
        
    }
    public function cancel_sell_order($order_id)
    {
        return $this->cancel_order(self::order_sell, $order_id);
    }
    
    public function cancel_order($type, $order_id)
    {
        if ($type!=self::order_buy && $type!=self::order_sell)
            throw new Exception('Order type can be bid or ask only. Check first parameter of cancel_order($type)');

        // Fall back to v0 because this doesn't exist in v1
        $v0_type = (self::order_sell) ? '1' : '2';
        $params = array(
            'oid'=>$order_id,
            'type'=>$v0_type
        );

        return $this->send_request(self::uri_cancel_order, $params);
    }


}
 