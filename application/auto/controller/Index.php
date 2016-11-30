<?php

namespace app\auto\controller;

use think\Controller;
use think\Request;
use think\cache\driver\Redis;
use think\Db;
use app\common\model\UserPosition;
use app\common\model\UserFunds;
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


    /**
     * [autoUpdateFrozen 自动更新冻结数量]
     * @return [boolean] [布尔值]
     */
    public function autoUpdateFrozen(){
        $sql = "SELECT id,(available_number + freeze_number) as available_number, (freeze_number=0) as freeze_number FROM `sjq_users_position` WHERE `is_position` = 1 AND ( `freeze_number` >0 )";
        $availableInfo = Db::query($sql);
        
        if($availableInfo){
            $userPosition = new UserPosition;
            if($result = $userPosition->saveAll($availableInfo)){
                return json(['status'=>'success','data'=> '更新成功']);
            }else{
                return json(['status'=>'failed','data'=> '更新失败']);
            }
        }else{
            return json(['status'=>'failed','data'=> '没有可以更新的数据']);
        }
        
        
    }

}
