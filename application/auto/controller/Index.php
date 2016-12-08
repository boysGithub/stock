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
use app\order\controller\Index as OrderIndex;
use app\common\model\Transaction;

class Index extends Controller
{
    public $_stockFunds = 1000000; //股票账户初始金额
    public function __construct(){
        $addr = getIP();
        #if(!($addr=='115.29.199.94')) exit("非法请求");
        
    }

    /**
     * [autoTrans 自动成交的方法]
     * @return [type] [description]
     */
    public function autoTrans(){
        $data['column'] = "自动交易的方法";
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
        $redis = new Redis();
        $buyKeys = $redis->keys("*noBuyOrder*");
        $sellKeys = $redis->keys("*noSellOrder*");
        if($buyKeys){
            for ($i=0; $i < count($buyKeys); $i++) { 
                $tmpBuy = $redis->get($buyKeys[$i]);
                $buy[$buyKeys[$i]] = $tmpBuy;
                $stockBuy[] = $tmpBuy['stock'];
            }
            $stockInfo = getStock($stockBuy,"s_");
            $orderIndex = new OrderIndex;
            foreach ($buy as $key => $value) {
                if($stockInfo[$value['stock']][1] <= $value['price']){
                    $funds = UserFunds::where(['uid'=>$value['uid']])->find();
                    $orderIndex->buyProcess($value,$stockInfo,$funds);
                    $redis->rm($key);
                }
            }
        }

        if($sellKeys){
            for ($i=0; $i < count($sellKeys); $i++) { 
                $tmpSell = $redis->get($sellKeys[$i]);
                $sell[$sellKeys[$i]] = $tmpSell;
                $stockSell[] = $tmpSell['stock'];
            }
            $stockInfo = getStock($stockSell,"s_");
            $orderIndex = new OrderIndex;
            foreach ($sell as $key => $value) {
                if($stockInfo[$value['stock']][1] >= $value['price']){
                    $funds = UserFunds::where(['uid'=>$value['uid']])->find();
                    $orderIndex->sellProcess($value,$stockInfo,$funds);
                    $redis->rm($key);
                }
            }
        }
    }

    /**
     * [autoClearOrder 自动清空所有的未成交的订单]
     * @return [type] [description]
     */
    public function autoClearOrder(){
        $data['column'] = "自动清空未成交订单";
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
        $redis = new Redis();
        $orderInfo = Transaction::whereTime('time','today')->where('status',0)->select();
        foreach ($orderInfo as $key => $value) {
            Db::startTrans();
            try {
                Transaction::update(['status'=>2],['id'=>$value['id']]);
                if($value['type'] == 1){
                    $redis->rm("noBuyOrder_".$value['id']."_".$value['uid']);
                    $availableFunds = UserFunds::where(['uid'=>$value['uid']])->value('available_funds');
                    $da['available_funds'] = $value['fee'] + $value['price'] * $value['number'] + $availableFunds;
                    UserFunds::update($da,['uid'=>$value['uid']]);
                }else if($value['type'] == 2){
                    $redis->rm("noSellOrder".$value['id']."_".$value['uid']);
                    $position = UserPosition::where(['uid'=>$value['uid'],'stock'=>$value['stock'],'is_position'=>1,'sorts'=>$value['sorts']])->Field('id,available_number')->find();
                    $da['available_number'] = $position['available_number'] + $value['number'];
                    UserPosition::where(['id'=>$position['id']])->update($da);
                }
                Db::commit();

                $result = json(['status'=>'success','data'=>'撤单成功']);
            } catch (\Exception $e){
                Db::rollback();
                $result = json(['status'=>'failed','data'=>'撤单失败']);
            }
        }
    }



    /**
     * [autoUpdateFrozen 自动更新冻结数量]
     * @return [boolean] [布尔值]
     */
    public function autoUpdateFrozen(){
        $data['column'] = "自动更新冻结数量";
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
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
        $data = $request->param();
        //验证传递的参数
        $result = $this->validate($data,'AutoUpdateRank');
        if (true !== $result) {
            return json(['status'=>'failed','data'=>$result]);
        }
        $data['column'] = "自动更新".$data['condition'];
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
        $rank = new Rank;
        if($rank->updateRank($data['condition'],$data['sorts'],$data['rankFiled']) === TRUE){
            return json(['status'=>'success','data'=> '更新成功']);
        }else{
            return json(['status'=>'failed','data'=> '更新失败']);
        }
    }

    /**
     * [autoCalcGrossProfitRate 自动计算总盈利率,最新资产]
     * @return [type] [description]
     */
    public function autoCalcGrossProfitRate(){
        $data['column'] = "自动更新总资产和盈利率";
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
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
        $data['column'] = "自动更新胜率";
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
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
     * [autoWeekRatio 自动添加和更新周盈利率]
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
        $data['column'] = "自动更新token";
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        AutoUpdate::create($data);
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
