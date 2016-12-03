<?php

namespace app\auto\controller;

use think\Controller;
use think\Request;
use think\cache\driver\Redis;
use think\Db;
use think\Config;
use app\common\model\UserPosition;
use app\common\model\UserFunds;
use app\common\model\Rank;
use app\common\model\AutoUpdate;

class Index extends Controller
{
    public $_stockFunds = 1000000; //股票账户初始金额
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
        $updateTime = strtotime(date("Y-m-d 00:00:00",time()));
        if(time() - $updateTime < 3600 && time() - $updateTime > 0 ){
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
        }else{
            return json(['status'=>'failed','data'=> '现在的时间不能更新']);
        }
          
    }

    /**
     * [autoUpdateRank 自动更新各种排行版排名]
     * @return [type] [description]
     */
    public function autoUpdateRank(Request $request){
        $updateTime1 = strtotime(date("Y-m-d 11:35:00",time()));
        $updateTime2 = strtotime(date("Y-m-d 12:55:00",time()));
        $updateTime3 = strtotime(date("Y-m-d 15:05:00",time()));
        if(($updateTime1 < time() && time() < $updateTime2) || time() > $updateTime3){
            $data = $request->param();
            //验证传递的参数
            $result = $this->validate($data,'AutoUpdateRank');
            if (true !== $result) {
                return json(['status'=>'failed','data'=>$result]);
            }
            $rank = new Rank;
            if($rank->updateRank($data['condition'],$data['sorts'],$data['rankFiled']) === TRUE){
                return json(['status'=>'success','data'=> '更新成功']);
            }else{
                return json(['status'=>'failed','data'=> '更新失败']);
            }
        }else{
            return json(['status'=>'failed','data'=> '现在的时间不能更新']);
        }
    }

    /**
     * [autoCalcGrossProfitRate 自动计算总盈利率,最新资产]
     * @return [type] [description]
     */
    public function autoCalcGrossProfitRate(){
        // 启动事务
        Db::startTrans();
        try {
            UserPosition::where(['is_position'=>1])->group('uid')->Field('id,uid')->chunk(500,function($list){
                $userPosition = new UserPosition;
                $userGather = '';
                foreach ($list as $key => $value) {
                    $userGather .= $value['uid'].',';
                }
                $userGather = substr($userGather,0,-1);
                //获取股票集合
                $stock = $userPosition->where(['is_position'=>1,'uid'=>['in',$userGather]])->Field('stock')->group('stock')->select();
                
                foreach ($stock as $key => $value) {
                    $stockGather[] = $value['stock'];
                }
                $stockTmp = getStock($stockGather,'s_');
                //获取持仓的集合
                $userInfo = $userPosition->where(['is_position'=>1,'uid'=>['in',$userGather]])->Field('id,uid,stock,(available_number + freeze_number) as number,cost_price')->select();
                //计算市值
                foreach ($userInfo as $key => $value) {
                    $userInfo[$key]['assets'] = $value['number'] * $stockTmp[$value['stock']][1];
                    $userInfo[$key]['ratio'] = round(($stockTmp[$value['stock']][1] - $value['cost_price'])/$value['cost_price'] * 100,8);
                    $userInfo[$key] = $value->toArray();
                    //把某一个用户的市值统计出来
                    $userTotal[$value['uid']][] = $value['number'] * $stockTmp[$value['stock']][1];

                }
                //更新现在的持仓比例,最新资产
                $userPosition->saveAll($userInfo);
                //计算总资产,总盈利率
                $userFunds = new userFunds;
                $funds = $userFunds->where(['uid'=>['in',$userGather]])->Field('id,uid,available_funds,funds')->select();
                foreach ($funds as $key => $value) {
                    $tmp = 0;
                    $value['funds'] = 0;
                    for ($i=0; $i < count($userTotal[$value['uid']]); $i++) {
                        $tmp += $userTotal[$value['uid']][$i];
                    }
                    $value['funds'] = $value['available_funds'] +$tmp;
                    $funds[$key]['total_rate'] = round(($funds[$key]['funds'] - $this->_stockFunds)/$this->_stockFunds * 100,8);
                    $funds[$key] = $value->toArray();
                }       
                //更新总资产，总盈利率
                $userFunds->saveAll($funds);
            });
            Db::commit();
            return json(['status'=>'success','data'=> '更新成功']);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['status'=>'failed','data'=> $e]);
        }
    }


    // /**
    //  * [updateTime 更新时间]
    //  * @return [type] [description]
    //  */
    // protected function updateTime(){
    //     //是否开启手动更新
    //     $tell = Config::has('autoData.manualupdate') ? Config::get('autoData.manualupdate') : false;
        
    // }

    // /**
    //  * [autoUpdateFunc 自动更新的方法]
    //  * @return [type] [description]
    //  */
    // protected function autoUpdateFunc($func){
    //     switch ($func) {
    //         case 'autoUpdateFrozen':
                
    //             break;
    //         default:
    //             # code...
    //             break;
    //     }
    // }
}
