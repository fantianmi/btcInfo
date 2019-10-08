<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/6
 * Time: 15:09
 */

namespace Home\Controller;


use Think\Controller;

class CommonController extends Controller
{
    public function jsuccess($data = '', $msg = '')
    {
        $this->json(1, $msg, $data);
        exit;
    }

    public function jerror($msg = '', $data = '', $ret = 10000)
    {
        $this->json($ret, $msg, $data);
        exit;
    }

    public function json($ret = 0, $msg = '', $data = '')
    {
        //解决跨域问题
        header("Access-Control-Allow-Origin:*");//注意修改这里填写你的前端的域名

        header('Content-Type:application/json; charset=utf-8');
        $return = array(
            'status' => $ret,
            'info' => $msg,
            'data' => $data
        );
        echo json_encode($return);
        exit;
    }
}