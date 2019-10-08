<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/6
 * Time: 15:09
 */

namespace Home\Controller;


class IndexController extends CommonController
{
    //自己申请的key
    private $appKey = "8e2dc9ee-9e62-40a8-8c7c-ea4aa96a337a";
    const BTC = '1';
    const USDT = '2';
    const ETH = '3';
    const EOS = '5';

    public function index()
    {

    }

    public function marketPage()
    {
        $data = $this->queryMarket();
        $this->jsuccess($data);
    }

    public function otcPage()
    {

    }

    private function queryCoinIds()
    {
        $coins = S("COINMARKETAPI_COINS");
        if (!$coins) {
            //获取币种id
            $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/map';
            $parameters = [
                'symbol' => 'BTC,ETH,EOS,BCH'
            ];
            $rs = $this->requestApi($url, $parameters);
            $rs = json_decode($rs, true);

            $coins = array();
            foreach ($rs['data'] as $val) {
                $coins[] = $val['id'];
            }

            $coins = implode(",", $coins);
            S("COINMARKETAPI_COINS", $coins);
        }
        return $coins;
    }

    public function queryMarket()
    {
        $coins = $this->queryCoinIds();
        $market = S("COINMARKETAPI_MARKET");
        if (!$market) {
            $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
            $parameters = [
                'id' => $coins
            ];

            $rs = $this->requestApi($url, $parameters);
            $rs = json_decode($rs, true);
            $market = array();
            //加入vcs 行情
            foreach ($rs['data'] as $val) {
                $item = array(
                    'name' => $val['name'],
                    'symbol' => $val['symbol'],
                    'volume_24h' => number_format($val['quote']['USD']['volume_24h'], 2),
                    'price' => number_format($val['quote']['USD']['price'], 2),
                    'percent_change_24h' => ($val['quote']['USD']['percent_change_24h'] > 0 ? "+" : "") . number_format($val['quote']['USD']['percent_change_24h'], 2),
                    'chg' => $val['quote']['USD']['percent_change_24h'] > 0 ? "up" : "down",
                );
                $market[] = $item;
            }
            S("COINMARKETAPI_MARKET", $market, 600);
        }
        return $market;
    }
    //从火币 获取 https://c2c.huobi.br.com/zh-cn/trade/sell-btc/ 第一条价格
    public function queryOtcLast()
    {
        $url = "https://c2c.huobi.br.com/zh-cn/trade/sell-btc/";
        $res = file_get_contents($url);
        myLog($res);
        exit();
    }

    private function requestApi($url, $parameters)
    {
        $appKey = $this->appKey;
        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $appKey
        ];
        $qs = http_build_query($parameters); // query string encode the parameters
        $request = "{$url}?{$qs}"; // create the request URL
        $curl = curl_init(); // Get cURL resource
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));
        $response = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request
        return $response;
    }
}