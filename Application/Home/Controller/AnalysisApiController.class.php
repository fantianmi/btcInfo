<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 9:13
 */

namespace Home\Controller;

define('BTC', '1');
define('USDT', '2');
define('ETH', '3');
define('EOS', '5');

class AnalysisApiController extends CommonController
{
    public $btcPriceOtc;
    public $ethPriceOtc;
    public $eosPriceOtc;
    public $usdtPriceOtc;

    public $btcPrice;
    public $ethPrice;
    public $eosPrice;
    public $usdtPrice;

    public $coinIds = array(BTC, USDT, ETH, EOS);

    const RATE_URL = "https://query1.finance.yahoo.com/v8/finance/chart/CNY=X?region=US&lang=en-US&includePrePost=false&interval=1h&range=1d&corsDomain=finance.yahoo.com&.tsrc=finance";

    public function _initialize()
    {
        $res = curlGetContent(self::RATE_URL);
        $result = json_decode($res, true);
        $usdPrice = $result['chart']['result'][0]['meta']['previousClose'];
        $this->usdtPrice = $usdPrice;

        foreach ($this->coinIds as $coinId) {
            $this->requestData($coinId);
        }
    }

    public function getOtcData()
    {
        $return = array(
            'priceOtc' => array(
                'btc' => $this->btcPriceOtc,
                'eth' => $this->ethPriceOtc,
                'eos' => $this->eosPriceOtc,
                'usdt' => $this->usdtPriceOtc,
            ),
            'price' => array(
                'btc' => $this->btcPrice,
                'eth' => $this->ethPrice,
                'eos' => $this->eosPrice,
                'usdt' => $this->usdtPrice
            ),
            'premiumRate' => array(
                'btc' => $this->premiumRate(BTC) . '%',
                'eth' => $this->premiumRate(ETH) . '%',
                'eos' => $this->premiumRate(EOS) . '%',
                'usdt' => $this->premiumRate(USDT) . '%'
            ),
            'logo' => array(
                'btc' => $this->getLogo(BTC),
                'eth' => $this->getLogo(ETH),
                'eos' => $this->getLogo(EOS),
                'usdt' => $this->getLogo(USDT),
            )
        );
        $this->jsuccess($return);
        exit;
    }

    /**
     * 溢价率
     * @param $coinId
     * @return string
     */
    function premiumRate($coinId)
    {
        if ($coinId == BTC) {
            $price = $this->btcPrice;
            $priceOtc = $this->btcPriceOtc;
        } else if ($coinId == ETH) {
            $price = $this->ethPrice;
            $priceOtc = $this->ethPriceOtc;
        } else if ($coinId == EOS) {
            $price = $this->eosPrice;
            $priceOtc = $this->eosPriceOtc;
        } else if ($coinId == USDT) {
            $price = $this->usdtPrice;
            $priceOtc = $this->usdtPriceOtc;
        } else {//default
            $price = $this->usdtPrice;
            $priceOtc = $this->usdtPriceOtc;
        }

        if (!$price) return '';
        $rate = sprintf("%.2f", floatval(($priceOtc - $price) / $price * 100));
        return $rate;
    }

    public function getLogo($coinId)
    {
        if ($coinId === BTC) {
            return 'https://s1.bqiapp.com/coin/20181030_72_png/bitcoin_200_200.png?v=1561015933';
        } else if ($coinId === ETH) {
            return 'https://s1.bqiapp.com/coin/20181030_72_png/ethereum_200_200.png?v=1561100400';
        } else if ($coinId === EOS) {
            return 'https://s1.bqiapp.com/coin/20181030_72_png/eos_200_200.png?v=1561110030';
        } else if ($coinId === USDT) {
            return 'https://s1.bqiapp.com/coin/20181030_72_png/tether_200_200.png?v=1562634583';
        }
        return '';
    }

    public function setPrice($coinId, $priceOtc, $price)
    {
        if ($coinId === BTC) {
            $this->btcPrice = $price;
            $this->btcPriceOtc = $priceOtc;
        } else if ($coinId === ETH) {
            $this->ethPrice = $price;
            $this->ethPriceOtc = $priceOtc;
        } else if ($coinId === EOS) {
            $this->eosPrice = $price;
            $this->eosPriceOtc = $priceOtc;
        } else if ($coinId === USDT) {
            $this->usdtPrice = $price;
            $this->usdtPriceOtc = $priceOtc;
        }
    }

    public function getCoin($coinId)
    {
        if ($coinId === BTC) {
            return 'BTC';
        } else if ($coinId === ETH) {
            return 'ETH';
        } else if ($coinId === EOS) {
            return 'EOS';
        } else if ($coinId === USDT) {
            return 'USDT';
        }
        return '';
    }

    public function getCoinHuobi($coinId)
    {
        if ($coinId === BTC) {
            return 'btc';
        } else if ($coinId === ETH) {
            return 'eth';
        } else if ($coinId === EOS) {
            return 'eos';
        } else if ($coinId === USDT) {
            return 'usdt';
        }
        return '';
    }

    public function requestData($coinId)
    {
        //场外交易价格
        $urlHuobiOtc = 'https://otc-api.eiijo.cn/v1/data/trade-market?coinId=' . $coinId . '&currency=1&tradeType=buy&currPage=1&country=37&blockType=general&online=1';//场外交易行情api（火币）
        //场内交易价格
        $urlHuobi = 'https://api.huobi.pro/market/detail/merged?symbol='.$this->getCoinHuobi($coinId).'usdt';
        //$urlBiance = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $this->getCoin($coinId) . 'USDT';//场内交易行情api（币安）

        $res = curlGetContent($urlHuobiOtc);
        $result = json_decode($res, true);
        $priceOtc = $result['data'][0]['price'];

        if ($coinId == USDT) {
            $this->setPrice($coinId, $priceOtc, $this->usdtPrice);
        } else {
            unset($res);
            $res = curlGetContent($urlHuobi);
            $result = json_decode($res, true);
            $price = $result['tick']['bid'][0];
            //$price = $result['price'];
            $price = $price * $this->usdtPrice;
            $price = round($price, 2);
            $this->setPrice($coinId, $priceOtc, $price);
        }
    }


}