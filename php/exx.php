<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class exx extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'exx',
            'name' => 'EXX',
            'countries' => array ( 'CN' ),
            'rateLimit' => 1000 / 10,
            'userAgent' => $this->userAgents['chrome'],
            'has' => array (
                'fetchOrder' => true,
                'fetchTickers' => true,
                'fetchOpenOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/37770292-fbf613d0-2de4-11e8-9f79-f2dc451b8ccb.jpg',
                'api' => array (
                    'public' => 'https://api.exx.com/data/v1',
                    'private' => 'https://trade.exx.com/api',
                ),
                'www' => 'https://www.exx.com/',
                'doc' => 'https://www.exx.com/help/restApi',
                'fees' => 'https://www.exx.com/help/rate',
                'referral' => 'https://www.exx.com/r/fde4260159e53ab8a58cc9186d35501f',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'markets',
                        'tickers',
                        'ticker',
                        'depth',
                        'trades',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'order',
                        'cancel',
                        'getOrder',
                        'getOpenOrders',
                        'getBalance',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.1 / 100,
                    'taker' => 0.1 / 100,
                ),
                'funding' => array (
                    'withdraw' => array (
                        'BCC' => 0.0003,
                        'BCD' => 0.0,
                        'BOT' => 10.0,
                        'BTC' => 0.001,
                        'BTG' => 0.0,
                        'BTM' => 25.0,
                        'BTS' => 3.0,
                        'EOS' => 1.0,
                        'ETC' => 0.01,
                        'ETH' => 0.01,
                        'ETP' => 0.012,
                        'HPY' => 0.0,
                        'HSR' => 0.001,
                        'INK' => 20.0,
                        'LTC' => 0.005,
                        'MCO' => 0.6,
                        'MONA' => 0.01,
                        'QASH' => 5.0,
                        'QCASH' => 5.0,
                        'QTUM' => 0.01,
                        'USDT' => 5.0,
                    ),
                ),
            ),
            'commonCurrencies' => array (
                'TV' => 'TIV', // Ti-Value
            ),
            'exceptions' => array (
                '103' => '\\ccxt\\AuthenticationError',
            ),
        ));
    }

    public function fetch_markets () {
        $markets = $this->publicGetMarkets ();
        $ids = is_array ($markets) ? array_keys ($markets) : array ();
        $result = array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $markets[$id];
            list ($baseId, $quoteId) = explode ('_', $id);
            $upper = strtoupper ($id);
            list ($base, $quote) = explode ('_', $upper);
            $base = $this->common_currency_code($base);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $active = $market['isOpen'] === true;
            $precision = array (
                'amount' => intval ($market['amountScale']),
                'price' => intval ($market['priceScale']),
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => $active,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => pow (10, -$precision['amount']),
                        'max' => pow (10, $precision['amount']),
                    ),
                    'price' => array (
                        'min' => pow (10, -$precision['price']),
                        'max' => pow (10, $precision['price']),
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = intval ($ticker['date']);
        $ticker = $ticker['ticker'];
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'sell'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $this->safe_float($ticker, 'riseRate'),
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetTicker (array_merge (array (
            'currency' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetTickers ($params);
        $result = array ();
        $timestamp = $this->milliseconds ();
        $ids = is_array ($tickers) ? array_keys ($tickers) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            if (!(is_array ($this->marketsById) && array_key_exists ($id, $this->marketsById)))
                continue;
            $market = $this->marketsById[$id];
            $symbol = $market['symbol'];
            $ticker = array (
                'date' => $timestamp,
                'ticker' => $tickers[$id],
            );
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetDepth (array_merge (array (
            'currency' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook, $orderbook['timestamp']);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $trade['date'] * 1000;
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $symbol = $market['symbol'];
        $cost = $this->cost_to_precision($symbol, $price * $amount);
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'tid'),
            'order' => null,
            'type' => 'limit',
            'side' => $trade['type'],
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $trades = $this->publicGetTrades (array_merge (array (
            'currency' => $market['id'],
        ), $params));
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $balances = $this->privateGetGetBalance ($params);
        $result = array ( 'info' => $balances );
        $balances = $balances['funds'];
        $currencies = is_array ($balances) ? array_keys ($balances) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $id = $currencies[$i];
            $balance = $balances[$id];
            $currency = $this->common_currency_code($id);
            $account = array (
                'free' => floatval ($balance['balance']),
                'used' => floatval ($balance['freeze']),
                'total' => floatval ($balance['total']),
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_order ($order, $market = null) {
        $symbol = $market['symbol'];
        $timestamp = intval ($order['trade_date']);
        $price = $this->safe_float($order, 'price');
        $cost = $this->safe_float($order, 'trade_money');
        $amount = $this->safe_float($order, 'total_amount');
        $filled = $this->safe_float($order, 'trade_amount', 0.0);
        $remaining = floatval ($this->amount_to_precision($symbol, $amount - $filled));
        $status = $this->safe_integer($order, 'status');
        if ($status === 1) {
            $status = 'canceled';
        } else if ($status === 2) {
            $status = 'closed';
        } else {
            $status = 'open';
        }
        $fee = null;
        if (is_array ($order) && array_key_exists ('fees', $order)) {
            $fee = array (
                'cost' => $this->safe_float($order, 'fees'),
                'currency' => $market['quote'],
            );
        }
        return array (
            'id' => $this->safe_string($order, 'id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $order['type'],
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => $fee,
            'info' => $order,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateGetGetOrder (array_merge (array (
            'currency' => $market['id'],
            'type' => $side,
            'price' => $price,
            'amount' => $amount,
        ), $params));
        $id = $response['id'];
        $order = $this->parse_order(array (
            'id' => $id,
            'trade_date' => $this->milliseconds (),
            'total_amount' => $amount,
            'price' => $price,
            'type' => $side,
            'info' => $response,
        ), $market);
        $this->orders[$id] = $order;
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $result = $this->privateGetCancel (array_merge (array (
            'id' => $id,
            'currency' => $market['id'],
        ), $params));
        return $result;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $order = $this->privateGetGetOrder (array_merge (array (
            'id' => $id,
            'currency' => $market['id'],
        ), $params));
        return $this->parse_order($order, $market);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $orders = $this->privateGetGetOpenOrders (array_merge (array (
            'currency' => $market['id'],
        ), $params));
        if (!gettype ($orders) === 'array' && count (array_filter (array_keys ($orders), 'is_string')) == 0) {
            return array ();
        }
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/' . $path;
        if ($api === 'public') {
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        } else {
            $this->check_required_credentials();
            $query = $this->urlencode ($this->keysort (array_merge (array (
                'accesskey' => $this->apiKey,
                'nonce' => $this->nonce (),
            ), $params)));
            $signature = $this->hmac ($this->encode ($query), $this->encode ($this->secret), 'sha512');
            $url .= '?' . $query . '&$signature=' . $signature;
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            //
            //  array ("$result":false,"$message":"服务端忙碌")
            //  ... and other formats
            //
            $code = $this->safe_string($response, 'code');
            $message = $this->safe_string($response, 'message');
            $feedback = $this->id . ' ' . $this->json ($response);
            if ($code === '100')
                return;
            if ($code !== null) {
                $exceptions = $this->exceptions;
                if (is_array ($exceptions) && array_key_exists ($code, $exceptions)) {
                    throw new $exceptions[$code] ($feedback);
                } else if ($code === '308') {
                    // this is returned by the exchange when there are no open orders
                    // array ("$code":308,"$message":"Not Found Transaction Record")
                    return;
                } else {
                    throw new ExchangeError ($feedback);
                }
            }
            $result = $this->safe_value($response, 'result');
            if ($result !== null) {
                if (!$result) {
                    if ($message === '服务端忙碌') {
                        throw new ExchangeNotAvailable ($feedback);
                    } else {
                        throw new ExchangeError ($feedback);
                    }
                }
            }
        }
    }
}
