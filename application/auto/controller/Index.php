<?php

namespace app\auto\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use think\cache\driver\Redis;
use app\common\model\User;
use app\common\model\UserPosition;
use app\common\model\UserFunds;
use app\common\model\Rank;
use app\common\model\AutoUpdate;
use app\common\model\WeeklyRatio;
use app\order\controller\Trans;
use app\common\model\Transaction;
use app\common\model\DaysRatio;
use app\common\model\MonthRatio;
use app\common\model\MatchUser;
use app\common\model\Match;
use app\common\model\AllStock;

class Index extends Controller
{
    public $_stockFunds = 1000000; //股票账户初始金额
    public function __construct(){
        $addr = getIP();
        if(!($addr=='115.29.199.94')) exit("非法请求");
    }
    
    /**
     * [autoTrans 自动成交的方法]
     * @return [type] [description]
     */
    public function autoTrans(){
        $this->handle("进入交易方法",1);
        $t1 = strtotime(date("Y-m-d 9:30:00"));
        $t2 = strtotime(date("Y-m-d 11:30:00"));
        $t3 = strtotime(date("Y-m-d 13:00:00"));
        $t4 = strtotime(date("Y-m-d 15:00:00"));
        if(($t1 <= time() && $t2 >= time()) || ($t3 <= time() && $t4 >= time())){
            $buyKeys = Transaction::where(['status'=>0,'type'=>1])->select();
            $sellKeys = Transaction::where(['status'=>0,'type'=>2])->select();
            $orderIndex = new Trans;
            //卖出操作
            if($buyKeys){
                foreach ($buyKeys as $key => $value) {
                    $buy[] = $value;
                    $stockBuy[] = $value['stock'];
                }
                $stockInfo = getStock($stockBuy,"s_");
                foreach ($buy as $key => $value) {
                    if($stockInfo[$value['stock']][1] <= $value['price']){
                        $value = $value->toArray();
                        $orderIndex->buyProcess($value,$stockInfo[$value['stock']],true);
                        $this->handle($value['stock_name']."买入成功;成交价:".$stockInfo[$value['stock']][1]."_".$value['uid'],1);
                    }
                }
            }
            //卖出操作
            if($sellKeys){
                foreach ($sellKeys as $key => $value) {
                    $sell[] = $value;
                    $stockSell[] = $value['stock'];
                }
                $stockInfo = getStock($stockSell,"s_");
                foreach ($sell as $key => $value) {
                    if($stockInfo[$value['stock']][1] >= $value['price']){
                        $value = $value->toArray();
                        $orderIndex->sellProcess($value,$stockInfo[$value['stock']],true);
                        $this->handle($value['stock_name']."卖出成功;成交价:".$stockInfo[$value['stock']][1]."_".$value['uid'],1);
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
        $orderInfo = Transaction::where('status',0)->select();
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
                    $available_number = $position['available_number'] + $value['number'];
                    UserPosition::where(['id'=>$position['id']])->update(['available_number'=>$available_number]);
                }
                Db::commit();
                $this->handle("自动清空未成交订单成功".$value['id'],1);
            } catch (\Exception $e){
                echo $e;
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
        if($this->isUpdate()){
            $fund = $this->_stockFunds;
            // 启动事务
            Db::startTrans();
            try {
                //计算买入待成交的资产
                $pendBuy = Transaction::where(['type'=>1,'status'=>'0'])->group('uid')->Field('uid,sum(price * number + fee) as total')->select();
                //计算卖出待成交的资产
                $pendSell = Transaction::where(['type'=>2,'status'=>'0'])->Field('pid,uid,number,stock')->select();
                //获取所有的持仓
                $position = userPosition::where(['is_position'=>1])->Field('id,uid,(available_number + freeze_number) as number,stock')->select();
                $market = array_merge($pendSell,$position);
                foreach ($market as $key => $value) {
                    $stock[] = $value['stock'];
                    $tmp[$value['uid']][$value['stock']][] = $value['number'];
                }
                $stock = array_values(array_unique($stock));
                $stockData = getStock($stock,'s_');
                foreach ($tmp as $key => $value) {
                   foreach ($value as $k => $val) {
                        $num = array_sum($val);
                        $user[$key][] = $stockData[$k][1] * $num;
                   }
                }
                if($pendBuy){
                    foreach ($pendBuy as $key => $value) {
                        $user[$value['uid']][] = $value['total'];
                    }
                }
                
                $userInfo = UserFunds::field('id,uid,funds,available_funds')->select();
                foreach ($userInfo as $key => $value) {
                    if(isset($user[$value['uid']])){
                        $value['funds'] = array_sum($user[$value['uid']])+$value['available_funds'];
                    }
                    $value['total_rate'] = round(($value['funds'] - $fund)/$fund*100,3);
                    unset($value['available_funds']);
                    $userInfo[$key] = $value->toArray();
                }
                $userFunds = new UserFunds;
                $userFunds->saveAll($userInfo);
                $this->handle("更新总盈利率和总资产成功",1);
                $userInfo = UserFunds::field('uid')->where(['is_trans'=>1])->select();
                foreach ($userInfo as $key => $value) {
                    $tmp1[] = $value['uid'];
                }
                $t = join(',',$tmp1);
                $redis = new Redis;
                $redis->set("total_rate",$t);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->handle("更新总盈利率和总资产失败",0);
            }
        }
    }

    /**
     * [autoSuccessRate 自动更新胜率]
     * @return [type] [description]
     */
    public function autoSuccessRate(){
        if($this->isUpdate()){
            // 启动事务
            Db::startTrans();
            try {
                    $list = UserPosition::group('uid')->Field('id,uid,stock')->select();
                    $userPosition = new UserPosition;
                    $userGather = '';
                    foreach ($list as $key => $value) {
                        $tp[] = $value['uid'];
                    }
                    $userGather = join(',',$tp);
                    //获取股票集合
                    $stock = $userPosition->where(['is_position'=>1,'uid'=>['in',$userGather]])->Field('stock')->group('stock')->select();
                    foreach ($stock as $key => $value) {
                        $stockGather[] = $value['stock'];
                    }
                    $stockTmp = getStock($stockGather);
                    //获取持仓的集合
                    $userInfo = $userPosition->where(['uid'=>['in',$userGather],'is_position'=>1])->Field('id,uid,ratio,stock,(available_number + freeze_number) as number,cost_price')->select();
                    //计算选股成功率
                    foreach ($userInfo as $key => $value) {
                        //把某一个用户的市值统计出来
                        if($stockTmp[$value['stock']][3] != 0){
                            $userInfo[$key]['assets'] = $value['number'] * $stockTmp[$value['stock']][3];
                            $userInfo[$key]['ratio'] = round(($stockTmp[$value['stock']][3] - $value['cost_price'])/$value['cost_price'] * 100,3);

                        }else{
                            $userInfo[$key]['assets'] = $value['number'] * $stockTmp[$value['stock']][2];
                            $userInfo[$key]['ratio'] = round(($stockTmp[$value['stock']][2] - $value['cost_price'])/$value['cost_price'] * 100,3);
                        }
                        $userInfo[$key] = $value->toArray();    
                    }
                    $userPosition->saveAll($userInfo);
                    $userInfo = $userPosition->where(['uid'=>['in',$userGather]])->Field('id,uid,ratio,stock,(available_number + freeze_number) as number,cost_price')->select();
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
                    $this->handle("自动更新胜率成功",1);
                    $redis = new Redis;
                    $redis->set("success_rate",$userGather);
                    Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->handle("自动更新胜率失败",0);
            }
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
                $sql = "UPDATE `sjq_days_ratio` r,(SELECT `d`.`id`,f.funds as endFunds,round((f.funds-d.initialCapital)/d.initialCapital*100,3) as proportion FROM `sjq_days_ratio` `d` INNER JOIN `sjq_users_funds` `f` ON `d`.`uid`=`f`.`uid` WHERE  `d`.`time` >  '".date("Y-m-d 00:00:00")."') obj set r.endFunds = obj.endFunds,r.proportion = obj.proportion where r.id=obj.id";
                Db::query($sql);
                $this->handle("自动更新日盈利率成功",1);
                Db::commit();
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
        $sdefaultDate = date("Y-m-d");
        $first=1;
        if($week == 6 || $week == 0) return json(['status'=>'failed','data'=> '周末不能操作']);
        $week_start=date('Y-m-d 00:00:00',strtotime("$sdefaultDate -".($week ? $week - $first : 6).' days'));
        // 启动事务
        Db::startTrans();
        try {
            $sql = "UPDATE `sjq_weekly_ratio` r,(SELECT `d`.`id`,f.funds as endFunds,round((f.funds-d.initialCapital)/d.initialCapital*100,3) as proportion FROM `sjq_weekly_ratio` `d` INNER JOIN `sjq_users_funds` `f` ON `d`.`uid`=`f`.`uid` WHERE  `d`.`time` >  '".$week_start."') obj set r.endFunds = obj.endFunds,r.proportion = obj.proportion where r.id=obj.id";
                Db::query($sql);
                $this->handle("自动更新周盈利率成功",1);
                Db::commit();
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
        $mtime = date('Y-m-1 00:00:00');
        // 启动事务
        Db::startTrans();
        try {
            $sql = "UPDATE `sjq_month_ratio` r,(SELECT `d`.`id`,f.funds as endFunds,round((f.funds-d.initialCapital)/d.initialCapital*100,3) as proportion FROM `sjq_month_ratio` `d` INNER JOIN `sjq_users_funds` `f` ON `d`.`uid`=`f`.`uid` WHERE  `d`.`time` >  '".$mtime."') obj set r.endFunds = obj.endFunds,r.proportion = obj.proportion where r.id=obj.id";
                Db::query($sql);
                $this->handle("自动更新月盈利率成功",1);
                Db::commit();
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
        // $sql = "INSERT into `sjq_days_ratio` (`uid`,`initialCapital`,`time`) VALUES";
        // $t = DaysRatio::whereTime('time','between',['2016-12-26','2016-12-27'])->Field('uid,endFunds')->select();
        // $a = DaysRatio::whereTime('time','>','2016-12-27')->Field('uid,endFunds')->select();
        
        // foreach ($t as $key => $value) {

        //         $sql .= "({$value['uid']},{$value['endFunds']},'".date("Y-m-d 00:00:02",time())."'),";
        //     } 
        //     // dump($a);
              
        // echo $sql;exit;
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
        //获取用户所有的持仓
        // 启动事务
        Db::startTrans();
        try {
            $position = UserPosition::where(['uid'=>$uid])->select();
            $data['funds'] = 0;
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
                    $buyInfo[$k] = $v->toArray();
                }
                if($sellInfo){
                    foreach ($sellInfo as $k => $v) {
                        $tmpSell[] = $v['price'] * $v['number'] - $v['fee'];
                        $numSell[] = $v['number'];
                        $sellInfo[$k] = $v->toArray();
                    }
                    $sell = array_sum($tmpSell);
                    $snum = array_sum($numSell);
                }
                $buy = array_sum($tmpBuy);
                $num = array_sum($numBuy);
                $market = $stockData[$value['stock']][1] * ($num - $snum);
                dump($value['stock']);
                $data['funds'] = $market + $data['funds'];
                $d['available_number'] = $num - $snum;
                $d['freeze_number'] = 0;
                $d['assets'] = $market;
                $d['cost'] = $buy;
                $d['cost_price'] = round(($buy - $sell)/($num - $snum),8);
                $d['ratio'] = round(($stockData[$value['stock']][1] - $d['cost_price']) / $value['cost_price'] * 100,8);
                dump($market);
                UserPosition::where(['id'=>$value['id']])->update($d);
            }
            $funds = UserFunds::where(['uid'=>$uid])->value('available_funds');
            $da['funds'] = $data['funds']+$funds;
            dump($da);exit;
            UserFunds::where(['uid'=>$uid])->update($da);
            Db::commit();
            return json("成功");
        } catch (\Exception $e) {
            Db::rollback();
            return json("失败");
        }
    }

    /**
     * [autoUpdateUser 自动更新用户]
     * @return [type] [description]
     */
    public function autoUpdateUser(){
        $uid = User::order('uid desc')->value('uid');
        if(!$uid) $uid = 0;
        $userInfo = Db::connect('sjq1')->name('user')->where('uid','>',$uid)->Field('uid,uname as username,password,login_salt,login,phone')->select();
            foreach ($userInfo as $key => $value) {
                User::create($value);
                $this->handle('添加用户'.$value['uid'],1);
            }
        
    }

    /**
     * [autoAverage 自动更新周平均率]
     * @return [type] [description]
     */
    public function autoAverage(){
        // 启动事务
        Db::startTrans();
        try {
            $sql = "UPDATE `sjq_users_funds` f,(select uid,obj.tmp_a/obj.tmp_week_avg as week_avg from (select uid,COUNT(proportion) as tmp_week_avg,sum(proportion) as tmp_a from `sjq_weekly_ratio` GROUP BY uid) obj group by obj.uid) new_obj set f.week_avg_profit_rate = new_obj.week_avg where f.uid=new_obj.uid";
            Db::query($sql);
            Db::commit();
            $this->handle("更新周平均率成功",1);
            $userInfo = UserFunds::field('uid')->where(['is_trans'=>1])->select();
                foreach ($userInfo as $key => $value) {
                    $tmp1[] = $value['uid'];
                }
            $t = join(',',$tmp1);
            $redis = new Redis;
            $redis->set("week_avg_profit_rate",$t);
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("更新周平均率失败",0);
        }
        
    }

    /**
     * [autoAddStock 自动添加股票]
     * @return [type] [description]
     */
    public function autoAddStock(){
        $flag = 1;
        $tag1 = 'sh';
        $tag2 = "sz";
            
            $host = "http://ali-stock.showapi.com";
            $path = "/stocklist";
            $method = "GET";
            $appcode = "258d13c99a494969a8f12e9a123ae016";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            
            $querys = "market={$tag2}&page=14";
            $bodys = "";
            $url = $host . $path . "?" . $querys;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            if (1 == strpos("$".$host, "https://"))
            {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            $res = curl_exec($curl);
            $stock = json_decode($res,true);
            foreach ($stock['showapi_res_body']['contentlist'] as $key => $value) {
                $tmp[$key]['stock'] = $value['code'];
                $tmp[$key]['stock_name'] = $value['name'];
                $tmp[$key]['time'] = date('Y-m-d H:i:s');
            }
            $stock = new AllStock;
            $stock->saveAll($tmp);
             
    }

    /**
     * [autoAvgHoldDay 平均持股天数]
     * @return [type] [description]
     */
    public function autoAvgHoldDay(){
        // 启动事务
        Db::startTrans();
        try {
            $userInfo = UserFunds::field('uid')->select();
            foreach ($userInfo as $key => $value) {
                $position = UserPosition::where(['uid'=>$value['uid'],'is_position'=>1])->select();
                if($position){
                    foreach ($position as $k => $v) {
                        $tmp[] = ceil((time() - strtotime($v['time'])) / 86400);
                    }
                    $avg_position_day = array_sum($tmp)/count($tmp);
                }  
                $position2 = UserPosition::where(['uid'=>$value['uid'],'is_position'=>2])->select();
                if($position2){
                    foreach ($position2 as $k => $v) {
                        $tmp2[] = ceil((strtotime($v['last_time']) - strtotime($v['time'])) / 86400);
                    }
                    $avg_position_day2 = array_sum($tmp2)/count($tmp2);
                }

                if($position && $position2){
                    $avg_position_day = ($avg_position_day + $avg_position_day2)/2;
                    UserFunds::update(['avg_position_day'=>$avg_position_day],['uid'=>$value['uid']]);
                    $this->handle("更新平均持股天数成功_".$value['uid'],1);
                    Db::commit(); 
                }else if($position){
                    UserFunds::update(['avg_position_day'=>$avg_position_day],['uid'=>$value['uid']]);
                    $this->handle("更新平均持股天数成功_".$value['uid'],1);
                    Db::commit(); 
                }else if($position2){
                    UserFunds::update(['avg_position_day'=>$avg_position_day2],['uid'=>$value['uid']]);
                    $this->handle("更新平均持股天数成功_".$value['uid'],1);
                    Db::commit(); 
                }
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->handle("更新持股天数失败",0);
        }
        
    }
    
    /**
     * [autoUpdateFans 自动更新用户粉丝数]
     * @return [type] [description]
     */
    public function autoUpdateFans(){
        $userInfo = UserFunds::field('uid')->select();
        foreach ($userInfo as $key => $value) {
            $tmp[] = $value['uid'];
        }
        $userGather = join(',',$tmp);
        $sjq = Db::connect('sjq1');
        $sql = "SELECT fid as uid,count(uid) as fans from `ts_user_follow` where fid in ({$userGather}) group by fid";
        $info = $sjq->query($sql);
        foreach ($info as $key => $value) {
            UserFunds::update(['fans'=>$value['fans']],['uid'=>$value['uid']]);
            $this->handle("更新".$value['uid']."粉丝数成功",1);
        }
        $userInfo = UserFunds::field('uid')->where(['is_trans'=>1])->select();
            foreach ($userInfo as $key => $value) {
                $tmp1[] = $value['uid'];
            }
        $t = join(',',$tmp1);
        $redis = new Redis;
        $redis->set("fans",$t);
    }

    /**
     * [isUpdate 是否能更新]
     * @return boolean [description]
     */
    public function isUpdate(){
        $t1 = strtotime(date("Y-m-d 9:30:00"));
        $t2 = strtotime(date("Y-m-d 15:05:00"));
        if(time() > $t1 && time() < $t2){
            return true;
        }else{
            return false;
        }
    }
}
