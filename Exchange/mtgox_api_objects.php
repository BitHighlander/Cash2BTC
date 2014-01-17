<?php

class MtGox_Order 
{
    public $type; // Buy BTC or Sell BTC
    public $type_raw; 
    public $is_buy = false;
    public $is_sell = false;

    public $order_id;
    public $amount;
    public $price;
    public $currency;

    public function __construct($raw_obj)
    {
        if (isset($raw_obj['type']))
        {
            $this->type_raw = $raw_obj['type'];
            $this->is_buy = (strtolower($this->type_raw) == MtGox_Api_Base::order_buy);
            $this->is_sell = (strtolower($this->type_raw) == MtGox_Api_Base::order_sell);
            $this->type = $this->is_buy  ? 'buy' : 'sell';
        }

        $this->order_id = (isset($raw_obj['oid'])) ? $raw_obj['oid'] : null;
        $this->currency = (isset($raw_obj['currency'])) ? $raw_obj['currency'] : null;
    }
} 

class MtGox_Value
{
    public $amount;
    public $price;
    public $datetime;

    public function __construct($raw_obj)
    {
        $obj = (object)$raw_obj;
        $this->amount = MtGox_Api_Base::convert_int_to_btc($obj->amount_int);
        $this->price = MtGox_Api_Base::convert_int_to_currency($obj->price_int);
        $this->datetime = strtotime($obj->stamp);
    }

    public static function create($raw_obj)
    {
        return new self($raw_obj);
    }

}
