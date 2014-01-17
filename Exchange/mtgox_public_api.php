<?php

class MtGox_Public_Api extends MtGox_Api_Base 
{

    const uri_ticker          = '2/BTC%s/money/ticker';
    const uri_depth           = '2/BTC%s/money/depth';
    const uri_trades          = '2/BTC%s/money/trades';
    const uri_cancelledtrades = '2/BTC%s/money/cancelledtrades';
    const uri_fulldepth       = '2/BTC%s/money/fulldepth';
    const uri_tx_details      = '2/money/bitcoin/tx_details';
    const uri_block_list      = '2/money/bitcoin/block_list_tx';
    const uri_addr_details    = '2/money/bitcoin/addr_details';
    const uri_order_lag       = '2/money/order/lag';
    const uri_currency_info   = '2/BTC%s/money/currency';

    // returns the current ticker for the selected currency
    public function get_ticker()
    {
        return $this->send_request(self::uri_ticker);
    }

    // Depth returns outstanding asks (selling) and bids (buying) orders
    //   Returns array('asks'=>array(...), 'bids'=>array(...));
    public function get_depth()
    {
        return $this->send_request(self::uri_depth);
    }

    // To get only the trades since a given trade id
    public function get_trades($since_txn=null, $show_duplicates=false)
    {
        $params = array();
        if ($since_txn)
            $params['since'] = $since_txn;

        $params['primary'] = ($show_duplicates) ? 'N' : 'Y';

        return $this->send_request(self::uri_trades, $params);
    }

    // returns a list of all the cancelled trades this last month, list of trade ids in json format
    public function get_cancelled_trades()
    {
        return $this->send_request(self::uri_cancelledtrades);
    }

    // WARNING : since this is a big download, there is a rate limit on how often you can get it, 
    // limit your requests to 5 / hour or you could be dropped / banned. 
    public function get_full_depth()
    {
        return $this->send_request(self::uri_fulldepth);
    }

    public function get_transaction_by_hash($hash)
    {
        return $this->send_request(self::uri_tx_details, array('hash'=>$hash));
    }

    public function get_block_by_depth($depth)
    {
        return $this->send_request(self::uri_block_list, array('depth'=>$depth));
    }

    public function get_block_by_hash($hash)
    {
        return $this->send_request(self::uri_block_list, array('hash'=>$hash));
    }

    public function get_address_info($hash)
    {
        return $this->send_request(self::uri_addr_details, array('hash'=>$hash));
    }

    // The "lag" value is the age in microseconds of the oldest order pending execution If it's too 
    // large it means the engine is busy, and the depth is probably not reliable 
    public function get_order_lag()
    {
        return $this->send_request(self::uri_order_lag);
    }

    // returns information about a currency ( number of decimals . . . ) 
    public function get_currency_info($currency=null)
    {
        if ($currency)
            $currency = $this->sanitise_currency($currency);
        else
            $currency = $this->active_currency;
        
        return $this->send_request(self::uri_currency_info, array('currency'=>$currency));
    }
}
