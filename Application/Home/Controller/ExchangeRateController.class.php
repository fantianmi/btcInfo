<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/21
 * Time: 0:32
 */

namespace Home\Controller;


/**
 * 汇率查询
 * Class CnyExchangeRateController
 * @package Home\Controller
 */
class ExchangeRateController extends CommonController
{
//    /**
//     * 默认汇率
//     * @var array
//     */
//    private $exchangeRateDefault = array(
//        "TWDCNY" => 0.2304,
//        "HKDCNY" => 0.8992,
//        "SGDCNY" => 5.1656,
//        "USDCNY" => 7.0381,
//        "NZDCNY" => 4.5121,
//        "EURCNY" => 7.7961,
//        "AUDCNY" => 4.7838,
//        "GBPCNY" => 9.0989,
//        "CHFCNY" => 7.102,
//        "CADCNY" => 5.2886,
//        "JPYCNY" => 6.4808,
//        "KRWCNY" => 0.006,
//        "RUBCNY" => 0.1103,
//        "THBCNY" => 0.2331
//    );

    private $api_key = "y4lmqQRykolDeO3VkzjYp2XZfgCdo8Tv";

    /**
     * 查询汇率
     * @return array|mixed
     */
    private function quotes($symbols)
    {
        $cacheKey = "exchangeRateArr_" . $symbols;
        $exchangeRateArr = null;
        if (!$exchangeRateArr) {
            //demo of symbol array: FOREXUSDCNH,FOREXHKDCNY,FOREXSGDCNY,FOREXUSDCNY,FOREXNZDCNY,FOREXEURCNY,FOREXAUDCNY,FOREXGBPCNY,FOREXCHFCNY,FOREXCADCNY,FOREXJPYCNY,FOREXKRWCNY,FOREXRUBCNY,FOREXTHBCNY

            $requestUrl = "http://webforex.hermes.hexun.com/gb/forex/quotelist?code=" . $symbols . "&column=Code,Name,Price,updown,updownRate,DateTime,High,Low,priceWeight";
            $res = curlGetContent($requestUrl);
            $res = substr($res, 1);
            $res = substr($res, 0, strlen($res) - 2);
            $res = iconv("gb2312//IGNORE", "utf-8", $res);
            //$res = json_decode($res, true);
            $res = json_decode($res, true);
            $res = $res['Data'];
            $res = $res[0];

            //var_dump($res);
            //var_dump($res);
            $exchangeRateArr = array();
            foreach ($res as $val) {
                $item = array(
                    'symbol' => $val[0],
                    'price' => $val[2] / 10000,
                    'name' => $val[1],
                );
                $exchangeRateArr[] = $item;
            }

//            if ($exchangeRateArr == null) {
//                $exchangeRateArr = $this->exchangeRateDefault;
//            }
            S($cacheKey, $exchangeRateArr, 600);
        }

        return $exchangeRateArr != null ? $exchangeRateArr : array();
    }

    public function query($api_key = "", $pairs = "")
    {
        if ($api_key == null) {
            $this->jerror("api_key can not be empty");
        }
        if ($api_key != $this->api_key) {
            $this->jerror("error api_key");
        }
        if ($pairs == null) {
            $this->jerror("pairs error");
        }

        $pairs = explode(",", $pairs);
        if ($pairs == null) {
            $this->jsuccess(array());
        }
        $symbols = "";
        foreach ($pairs as $val) {
            $symbols .= "FOREX" . $val . ",";
        }
        $symbols = rtrim($symbols, ",");

        $return = $this->quotes($symbols);
        $this->jsuccess($return);
    }

}