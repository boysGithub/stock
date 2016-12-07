<?php

namespace app\auto\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use think\cache\driver\Redis;
use app\common\model\UserPosition;
use app\common\model\UserFunds;
use app\common\model\Rank;
use app\common\model\AutoUpdate;
use app\common\model\WeeklyRatio;

class Index extends Controller
{
    public $_stockFunds = 1000000; //股票账户初始金额
    public function __construct(){
        $addr = getIP();
        if(!($addr=='115.29.199.94') exit("非法请求");
        
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

    /**
     * [autoSuccessRate 自动更新胜率]
     * @return [type] [description]
     */
    public function autoSuccessRate(){
        // 启动事务
        Db::startTrans();
        try {
            UserPosition::group('uid')->Field('id,uid')->chunk(500,function($list){
                $userPosition = new UserPosition;
                $userGather = '';
                foreach ($list as $key => $value) {
                    $userGather .= $value['uid'].',';
                }
                $userGather = substr($userGather,0,-1);
                //获取持仓的集合
                $userInfo = $userPosition->where(['uid'=>['in',$userGather]])->Field('id,uid,ratio')->select();
                //计算选股成功率
                foreach ($userInfo as $key => $value) {
                    $winRate[$value['uid']][] = $value['ratio'];
                }
                $userFunds = new userFunds;
                $funds = $userFunds->where(['uid'=>['in',$userGather]])->Field('id,uid,success_rate')->select();
                foreach ($funds as $key => $value) {
                    $tmp = 0;
                    for ($i=0; $i < count($winRate[$value['uid']]); $i++) { 
                        if($winRate[$value['uid']][$i] > 0 ){
                            $tmp += 1;
                        }
                    }
                    $funds[$key]['success_rate'] = round($tmp/count($winRate[$value['uid']])*100,3);
                    $funds[$key] = $value->toArray();
                }
                //更新选股成功率
                $userFunds->saveAll($funds);
            });
            Db::commit();
            return json(['status'=>'success','data'=> '更新成功']);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['status'=>'failed','data'=> $e]);
        }
    }

    /**
     * [autoWeekRatio 自动添加和更新周赛数据]
     * @return [type] [description]
     */
    public function autoWeekRatio(){
        //获取一周的时间
        $week = date('w');
        if($week == 6 || $week == 0) return json(['status'=>'failed','data'=> '周末不能操作']);
        // 启动事务
        Db::startTrans();
        try {
            $weekInfo = WeeklyRatio::whereTime('time','week')->find();
            if($weekInfo){
                WeeklyRatio::whereTime('time','week')->Field('id,uid,initialCapital')->chunk(500,function($list){
                    $userGather = '';
                    foreach ($list as $key => $value) {
                        $userGather .= $value['uid'].',';
                    }
                    $userGather = substr($userGather,0,-1);
                    $funds = userFunds::where(['uid'=>['in',$userGather]])->Field('id,uid,funds')->select();
                    
                    foreach ($list as $key => $value) {
                        $value['endFunds'] = $funds[$key]['funds'];
                        $value['proportion'] = round(($value['endFunds'] - $value['initialCapital'])/$value['initialCapital'] * 100 , 8);

                        $list[$key] = $value->toArray();
                    }
                    $weeklyRatio = new WeeklyRatio;
                    $weeklyRatio->allowField(true)->saveAll($list);
                });
                Db::commit();
                return json(['status'=>'success','data'=> '更新成功']);
            }else{
                if($week != 1){
                    return json(['status'=>'failed','data'=> '周一才能创建比赛']);
                }
                UserPosition::group('uid')->Field('id,uid')->chunk(500,function($list){
                    $userGather = '';
                    foreach ($list as $key => $value) {
                        $userGather .= $value['uid'].',';
                    }
                    $userGather = substr($userGather,0,-1);
                    $funds = userFunds::where(['uid'=>['in',$userGather]])->Field('id,uid,funds')->select();
                    $weekInfo = WeeklyRatio::whereTime('time','last week')->find();
                    foreach ($funds as $key => $value) {
                        $value['initialCapital'] = $value['funds'];
                        $value['endFunds'] = $value['initialCapital'];
                        $value['periods'] = $weekInfo['periods'] + 1;
                        $value['proportion'] = round(($value['endFunds'] - $value['initialCapital'])/$value['initialCapital'] * 100 , 8);
                        $value['time'] = date('Y-m-d H:i:s',time());
                        unset($value['id']);
                        unset($value['funds']);
                        $funds[$key] = $value->toArray();
                    }
                    $weeklyRatio = new WeeklyRatio;
                    $weeklyRatio->allowField(true)->saveAll($funds); 
                });
                Db::commit();
                return json(['status'=>'success','data'=> '新增成功']);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return json(['status'=>'failed','data'=> $e]);
        }    
    }

    /**
     * [autoBuildToken 自动生成token]
     * @return [type] [description]
     */
    public function autoBuildToken(){
        $redis = new Redis;
        //固定的token
        $token = Config::has("stocktell.token") ? Config::get('stocktell.token') : '';
        $randToken = getRandChar(10);
        $sql = "UPDATE `ts_user` a,(select `uid`,`stock_token` from `ts_user`) obj set a.expired_token = obj.stock_token,a.stock_token=sha1(CONCAT('{$token}',obj.uid,'{$randToken}')) where a.uid=obj.uid";
        Db::connect('sjq1')->query($sql);
        $sql1 = "SELECT `uid`,`stock_token`,`expired_token` from `ts_user`";
        $tokenInfo = Db::connect('sjq1')->query($sql1);
        foreach ($tokenInfo as $key => $value) {
            $tmp[$value['uid']]['stock_token'] = $value['stock_token'];
            $tmp[$value['uid']]['expired_token'] = $value['expired_token'];
        }
        $redis->set('token',$tmp,3600);
    }
}
