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
use app\common\model\DaysRatio;
use app\common\model\MonthRatio;

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
        $redis = new Redis();
        $buyKeys = $redis->keys("*noBuyOrder*");
        $sellKeys = $redis->keys("*noSellOrder*");
        //卖出操作
        if($buyKeys){
            for ($i=0; $i < count($buyKeys); $i++) { 
                $tmpBuy = $redis->get($buyKeys[$i]);
                $buy[$buyKeys[$i]] = $tmpBuy;
                $stockBuy[] = $tmpBuy['stock'];
            }
            $stockInfo = getStock($stockBuy,"s_");

            $orderIndex = new OrderIndex;
            foreach ($buy as $key => $value) {
                if($orderIndex->isLimitMove($stockInfo[$value['stock']],1)){
                    if($stockInfo[$value['stock']][1] <= $value['price']){
                        $funds = UserFunds::where(['uid'=>$value['uid']])->find();
                        $orderIndex->buyProcess($value,$stockInfo,$funds,true);
                        $redis->rm($key);
                        $this->handle($value['stock_name']."买入成功;成交价:".$stockInfo[$value['stock']][1],1);
                    }
                }else{
                    if($stockInfo[$value['stock']][1] < $value['price']){
                        $funds = UserFunds::where(['uid'=>$value['uid']])->find();
                        $orderIndex->buyProcess($value,$stockInfo,$funds,true);
                        $redis->rm($key);
                        $this->handle($value['stock_name']."买入成功;成交价:".$stockInfo[$value['stock']][1],1);
                    }
                }
                
            }
        }
        //卖出操作
        if($sellKeys){
            for ($i=0; $i < count($sellKeys); $i++) { 
                $tmpSell = $redis->get($sellKeys[$i]);
                $sell[$sellKeys[$i]] = $tmpSell;
                $stockSell[] = $tmpSell['stock'];
            }
            $stockInfo = getStock($stockSell,"s_");
            $orderIndex = new OrderIndex;
            foreach ($sell as $key => $value) {
                if($orderIndex->isLimitMove($stockInfo[$value['stock']],2)){
                    if($stockInfo[$value['stock']][1] >= $value['price']){
                        $funds = UserFunds::where(['uid'=>$value['uid']])->find();
                        $orderIndex->sellProcess($value,$stockInfo,$funds,true);
                        $redis->rm($key);
                        $this->handle($value['stock_name']."卖出成功;成交价:".$stockInfo[$value['stock']][1],1);
                    }
                }else{
                    if($stockInfo[$value['stock']][1] > $value['price']){
                        $funds = UserFunds::where(['uid'=>$value['uid']])->find();
                        $orderIndex->sellProcess($value,$stockInfo,$funds,true);
                        $redis->rm($key);
                        $this->handle($value['stock_name']."卖出成功;成交价:".$stockInfo[$value['stock']][1],1);
                    }
                }
            }
        }
    }

    /**
     * [autoClearOrder 自动清空所有的未成交的订单]
     * @return [type] [description]
     */
    public function autoClearOrder(){
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
                $this->handle("自动清空未成交订单成功".$value['id'],1);
            } catch (\Exception $e){
                Db::rollback();
                $this->handle("自动清空未成交订单失败".$value['id'],0);
            }
        }
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
                    $this->handle("解冻冻结股票数量成功",1);
                }else{
                    $this->handle("解冻冻结股票数量成功",0);
                }
            }else{
                return json(['status'=>'failed','data'=> '没有可以更新的数据']);
            }
        }else{
            exit("非法请求");
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
        $rank = new Rank;
        if($rank->updateRank($data['condition'],$data['sorts'],$data['rankFiled']) === TRUE){
            $this->handle("自动更新".$data['condition']."成功",1);
        }else{
            $this->handle("自动更新".$data['condition']."失败",0);
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
                $sellFreezeNumber = Transaction::where(['type'=>2,'status'=>0,'uid'=>['in',$userGather]])->Field('uid,stock,number')->select();
                $tmp = '';
                $tmp1 = '';
                $tmp2 = '';
                foreach ($sellFreezeNumber as $key => $value) {
                    $tmp[$value['uid']][$value['stock']] = $value['number'];
                    $tmp1[] = $value['uid'];
                    $tmp2[] = $value['stock'];
                }
                //计算市值
                foreach ($userInfo as $key => $value) {
                    //把某一个用户的市值统计出来
                    if(is_array($tmp1) && is_array($tmp2)){
                        if(in_array($value['uid'],$tmp1) && in_array($value['stock'],$tmp2)){
                            $userTotal[$value['uid']][] = ($tmp[$value['uid']][$value['stock']] + $value['number']) * $stockTmp[$value['stock']][1];
                        }else{
                            $userTotal[$value['uid']][] = $value['number'] * $stockTmp[$value['stock']][1];
                        }
                    }else{
                        $userTotal[$value['uid']][] = $value['number'] * $stockTmp[$value['stock']][1];
                    }
                    $userInfo[$key]['assets'] = $value['number'] * $stockTmp[$value['stock']][1];
                    $userInfo[$key]['ratio'] = round(($stockTmp[$value['stock']][1] - $value['cost_price'])/$value['cost_price'] * 100,8);
                    $userInfo[$key] = $value->toArray();  
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
                Db::commit();
                $this->handle("自动更新总资产和盈利率成功",1);
            });
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("自动更新总资产和盈利率失败",0);
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
                Db::commit();
                $this->handle("自动更新胜率成功",1);
            });
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("自动更新胜率失败",0);
        }
    }

    /**
     * [autoDayRatio 自动更新日盈利率]
     * @return [type] [description]
     */
    public function autoDayRatio(){
        //获取一周的时间
        $week = date('w');
        if($week == 6 || $week == 0) return json(['status'=>'failed','data'=> '周末不能操作']);
        // 启动事务
        Db::startTrans();
        try {
            DaysRatio::whereTime('time','today')->Field('id,uid,initialCapital')->chunk(500,function($list){
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
                $weeklyRatio = new DaysRatio;
                $weeklyRatio->allowField(true)->saveAll($list);
                Db::commit();
                $this->handle("自动更新日盈利率成功",1);
            });
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("自动更新日盈利率失败",0);
        }    
    }

    /**
     * [autoWeekRatio 自动更新周盈利率]
     * @return [type] [description]
     */
    public function autoWeekRatio(){
        //获取一周的时间
        $week = date('w');
        if($week == 6 || $week == 0) return json(['status'=>'failed','data'=> '周末不能操作']);
        // 启动事务
        Db::startTrans();
        try {
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
                Db::commit();
                $this->handle("自动更新周盈利率成功",1);
            });
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("自动更新周盈利率失败",0);
        }    
    }

    /**
     * [autoMonthRatio 自动更新月盈利率]
     * @return [type] [description]
     */
    public function autoMonthRatio(){
        //获取一周的时间
        $week = date('w');
        if($week == 6 || $week == 0) return json(['status'=>'failed','data'=> '周末不能操作']);
        // 启动事务
        Db::startTrans();
        try {
            MonthRatio::whereTime('time','month')->Field('id,uid,initialCapital')->chunk(500,function($list){
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
                $weeklyRatio = new MonthRatio;
                $weeklyRatio->allowField(true)->saveAll($list);
                Db::commit();
                $this->handle("自动更新月盈利率成功",1);
            });
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("自动更新月盈利率失败",0);
        }    
    }

    /**
     * [autoAddWeek 自动添加日盈利率]
     * @return [type] [description]
     */
    public function autoAddDay(){
        $week = Date('w');
        if($week == 0 || $week == 6){
            exit("非法请求");
        }
        if(DaysRatio::whereTime('time','today')->value('id')){
            $this->handle("添加日盈利率报警，请检查",2);
        }else{
            $user = new UserFunds;
            $user->Field('id,uid,funds')->chunk(500,function($list){
                // 启动事务
                Db::startTrans();
                try {
                    $day = new DaysRatio;
                    foreach ($list as $key => $value) {
                        $data[$key]['uid'] = $value['uid'];
                        $data[$key]['initialCapital'] = $value['funds'];
                        $data[$key]['endFunds'] = $value['funds'];
                        $data[$key]['proportion'] = 0;
                        $data[$key]['time'] = date("Y-m-d H:i:s");
                    }
                    $day->saveAll($data);
                    Db::commit();
                    $this->handle("自动添加日盈利率成功",1);
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->handle("自动添加日盈利率失败",0);
                }
            });
        } 
    }

    /**
     * [autoAddWeek 自动添加周赛]
     * @return [type] [description]
     */
    public function autoAddWeek(){
        $week = Date('w');
        if($week != 1){
            exit("非法请求");
        }
        if(WeeklyRatio::whereTime('time','week')->value('id')){
            $this->handle("添加周盈利率报警，请检查",2);
        }else{
            $user = new UserFunds;
            $user->Field('id,uid,funds')->chunk(500,function($list){
                // 启动事务
                Db::startTrans();
                try {
                    $day = new WeeklyRatio;
                    foreach ($list as $key => $value) {
                        $data[$key]['uid'] = $value['uid'];
                        $data[$key]['initialCapital'] = $value['funds'];
                        $data[$key]['endFunds'] = $value['funds'];
                        $data[$key]['proportion'] = 0;
                        $data[$key]['time'] = date("Y-m-d H:i:s");
                    }
                    $day->saveAll($data);
                    Db::commit();
                    $this->handle("自动添加周盈利率成功",1);
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->handle("自动添加周盈利率失败",0);
                }
            }); 
        }
    }

    /**
     * [autoAddonth 自动添加月盈利率]
     * @return [type] [description]
     */
    public function autoAddonth(){
        $monthOne = date("d");
        if($monthOne != 1){
            exit("非法请求");
        }
        if(MonthRatio::whereTime('time','month')->value('id')){
            $this->handle("添加月盈利率报警，请检查",2);
        }else{ 
            $user = new UserFunds;
            $user->Field('id,uid,funds')->chunk(500,function($list){
                // 启动事务
                Db::startTrans();
                try {
                    $day = new MonthRatio;
                    foreach ($list as $key => $value) {
                        $data[$key]['uid'] = $value['uid'];
                        $data[$key]['initialCapital'] = $value['funds'];
                        $data[$key]['endFunds'] = $value['funds'];
                        $data[$key]['proportion'] = 0;
                        $data[$key]['time'] = date("Y-m-d H:i:s");
                    }
                    $day->saveAll($data);
                    Db::commit();
                    $this->handle("自动添加月盈利率成功",1);
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->handle("自动添加月盈利率失败",0);
                }
            });
        } 
    }

    /**
     * [autoBuildToken 自动生成token]
     * @return [type] [description]
     */
    public function autoBuildToken(){
        // 启动事务
        Db::startTrans();
        try {
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
            Db::commit();
            $this->handle("自动更新token成功",1);
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("自动更新token失败",0);
        }
        
    }

    /**
     * [handle 自动操作添加]
     * @return [type] [description]
     */
    private function handle($da,$normal){
        $data['column'] = $da;
        $data['sorts'] = 1;
        $data['is_update'] = 1;
        $data['normal'] = $normal;
        AutoUpdate::create($data);
    } 

    /**
     * [redone 清算某一个用户所有的数据]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function redone(Request $request){
        $uid = $request->param('uid');
        //获取用户资产
        // $funds = UserFunds::where(['uid'=>$uid])->Field('uid')->find();
        //获取用户所有的持仓
        // 启动事务
        Db::startTrans();
        try {
            $position = UserPosition::where(['uid'=>$uid])->select();
            foreach ($position as $key => $value) {
                //获取持仓对应的操作
                $buyInfo = Transaction::where(['pid'=>$value['id'],'type'=>1,'status'=>1])->select();
                $sellInfo = Transaction::where(['pid'=>$value['id'],'type'=>2,'status'=>1])->select();
                $tmpBuy = '';
                $numBuy = '';
                $tmpSell = '';
                $numSell = '';
                $sell = 0;
                $snum = 0; 
                $stockData = getStock($value['stock'],'s_');
                foreach ($buyInfo as $k => $v) {
                    $tmpBuy[] = $v['price'] * $v['number'] + $v['fee'];
                    $numBuy[] = $v['number'];
                }
                if($sellInfo){
                    foreach ($sellInfo as $k => $v) {
                        $tmpSell[] = $v['price'] * $v['number'] - $v['fee'];
                        $numSell[] = $v['number'];
                    }
                    $sell = array_sum($tmpSell);
                    $snum = array_sum($numSell);
                }
                $buy = array_sum($tmpBuy);
                $num = array_sum($numBuy);
                $market = $stockData[$value['stock']][1] * ($num - $snum);
                //获取用户资产
                $availableFunds = UserFunds::where(['uid'=>$uid])->value('available_funds');
                $data['funds'] = $sell - $buy + $market + $availableFunds;
                UserFunds::where(['uid'=>$uid])->update($data);
                $d['available_number'] = $num - $snum;
                $d['assets'] = $market;
                $d['cost'] = $buy;
                $d['cost_price'] = round(($buy - $sell)/($num - $snum),8);
                $d['ratio'] = round(($stockData[$value['stock']][1] - $d['cost_price']) / $value['cost_price'] * 100,8);
                UserPosition::where(['id'=>$value['id']])->update($d);
            }
            $funds = UserFunds::where(['uid'=>$uid])->value('funds');
            $da['funds'] = 1000000 + $funds;
            UserFunds::where(['uid'=>$uid])->update($da);
            Db::commit();
            return json("成功");
        } catch (\Exception $e) {
            Db::rollback();
            return json("失败");
        }
    }
}
