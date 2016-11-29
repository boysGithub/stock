<?php

namespace app\auto\controller;

use think\Controller;
use think\Request;
use think\cache\driver\Redis;
class Index extends Controller
{
    public function __construct(){
        $addr = $_SERVER['REMOTE_ADDR'];
        if(!($addr=='127.0.0.1')) exit("非法请求");
    }

    public function autoTrans(){
        $redis = new Redis();
        $buyKyes = $redis->keys("*noBuyOrder*");
        $tmp = "";
        for ($i=0; $i < count($buyKyes); $i++) { 
            $tmp[] = $redis->get($buyKyes[$i]);
        }
        dump($buyKyes);
    }


}
