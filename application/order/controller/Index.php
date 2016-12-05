<?php
namespace app\order\controller;

use think\Db;
use think\Request;
use think\Config;
use think\Validate;
use app\index\controller\Base;
use app\common\model\UserPosition;
use app\common\model\UserFunds;
use app\common\model\Transaction as Trans;
use think\cache\driver\Redis;

/**
 * 订单控制器
 */
class Index extends Base
{   
    protected $_base;
    public function  __construct(){
        $this->_base = new Base();
    }

    /**
     * [index 牛人动态（用户订单列表）]
     * @return [json] [用户的数据]
     */
    public function index(){
        $expert = Db::table('sjq_transaction t')->join('sjq_users u','t.uid=u.uid')->join('sjq_users_funds uf','u.uid=uf.uid')->join('sjq_users_position up','u.uid=up.uid AND t.stock=up.stock')->Field('t.id,t.uid,t.stock,t.stock_name,u.username,t.price,t.time,t.type,uf.total_rate,up.ratio')->order('t.id desc')->limit(30)->select();
        
        if($expert){
            $result = json(['status'=>'success','data'=>$expert]);
        }else{
            $result = json(['status'=>'failed','data'=>'还没有数据']);
        }
        return $result;
    }

    /**
     * 新建股票的订单
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $tell = Config::has('stocktell.transactiontime') ? Config::get('stocktell.transactiontime'): true;
        $data = $request->param();
        //验证传递的参数
        $result = $this->validate($data,'SaveOrder');
        if (true !== $result) {
            return json(['status'=>'failed','data'=>$result]);
        }
        if($this->isCanTrade($tell)){
            if($data['type'] == 1){
                //获取股票信息
                if($data['number']%100 == 0){
                    $stockData = getStock($data['stock'],'s_');
                    if($funds = $this->isToBuy($data,$stockData)){
                        $res = $this->trans($data,$stockData,$funds);
                    }else{
                        $res = json(['status'=>'failed','data'=>'资金不足']);
                    }
                }else{
                    $res = json(['status'=>'failed','data'=>'购买数量必须为100的整数倍']);
                }
            }else if($data['type'] == 2){
                if($bool = $this->isToSell($data)){
                    if(!is_object($bool)){
                        if($bool == 1){
                            $res = json(['status'=>'failed','data'=>'没有对应的持仓信息']);
                        }
                    }else{
                        $funds = UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->find();
                        $stockData = getStock($data['stock'],'s_');
                        $res = $this->trans($data,$stockData,$funds);
                    }
                }else{
                    $res = json(['status'=>'failed','data'=>'可卖数量不够']);
                }
            }   
        }else{
            $res = json(['status'=>'failed','data'=>'现在不是交易时间']);
        }
        return $res;
    }

    /**
     * 获取一个用户交易详情
     *
     * @param  int  $id | \think\Request  $request
     * @return \think\Response
     */
    public function read($id)
    {
        $data   = input('get.');

        //验证传递的参数
        $result = $this->validate($data,'GetUserOrderInfo');
        if (true !== $result) {
            return json(['status'=>'failed','data'=>$result]);
        }

        $result = $this->getAccessType($data,$id);
        if($result['data'] && $result['totalPage']){
            //添加成交状态的名字
            for ($i=0; $i < count($result['data']); $i++) {
                $result['data'][$i]->append(['status_name','username']);
            }
            $result = json(['status'=>'success','data'=>$result['data'],'totalPage'=>$result['totalPage']]);
        }else{
            $result = json(['status'=>'failed','data'=>'获取的数据不存在']);
        }
        return $result;
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data   = $request->param();

        $res = $this->validate($data,'UpdateOrder');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }

        $userOrder = Trans::where(['id'=>$id,'uid'=>$data['uid']])->find();

        if($userOrder['status'] === 0){
            if($data['status'] == 2){
                Db::startTrans();
                try {
                    Trans::update($data,['id'=>$id,'uid'=>$data['uid']]);
                    if($userOrder['type'] == 1){
                        $availableFunds = UserFunds::where(['uid'=>$userOrder['uid']])->value('available_funds');
                        $da['available_funds'] = $userOrder['fee'] + $userOrder['price'] * $userOrder['number'] + $availableFunds;
                        UserFunds::update($da,['uid'=>$userOrder['uid']]);
                    }else if($userOrder['type'] == 2){
                        $position = UserPosition::where(['uid'=>$data['uid'],'stock'=>$userOrder['stock'],'is_position'=>1,'sorts'=>$userOrder['sorts']])->Field('id,available_number')->find();
                        $da['available_number'] = $position['available_number'] + $userOrder['number'];
                        UserPosition::where(['id'=>$position['id']])->update($da);
                    }
                    Db::commit();
                    $result = json(['status'=>'success','data'=>'撤单成功']);
                } catch (\Exception $e){
                    Db::rollback();
                    $result = json(['status'=>'failed','data'=>'撤单失败']);
                }
            }
        }else if($userOrder['status'] === 1){
            $result = json(['status'=>'failed','data'=>'订单已经成交']);
        }else if($userOrder['status'] === 2){
            $result = json(['status'=>'failed','data'=>'订单已经撤单']);
        }else{
            $result = json(['status'=>'failed','data'=>'订单不能存在']);
        }
        return $result;
    }

    /**
     * [getAccessType 得到获取数据的方式]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    protected function getAccessType($data,$uid){
        //设置显示的数量
        $limit = $this->_base->_limit;
        $data['p'] = isset($data['p']) ? (int)$data['p'] > 0 ? $data['p'] : 1 : 1 ;
        
        switch ($data['type']) {
            case 'trans':
                # 获取历史成交所有数据
                $result['totalPage'] = ceil(Trans::where(['uid'=>$uid,'status'=>1])->whereTime('time','between',[$data['stime'],$data['etime']])->count()/$limit);
                $result['data'] = Trans::where(['uid'=>$uid,'status'=>1])->whereTime('time','between',[$data['stime'],$data['etime']])->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
            case 'deal':
                # 获取当日成交的数据
                $result['totalPage'] = ceil(Trans::whereTime('time','today')->where(['uid'=>$uid,'status'=>1])->count()/$limit);
                $result['data'] = Trans::where(['uid'=>$uid,'status'=>1])->whereTime('time','today')->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
            case 'entrust':
                # 获取当日委托数据
                $result['totalPage'] = ceil(Trans::whereTime('time','today')->where(['uid'=>$uid])->count()/$limit);
                $result['data'] = Trans::whereTime('time','today')->where(['uid'=>$uid])->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
            case 'historical':
                # 获取历史委托数据
                $result['totalPage'] = ceil(Trans::where(['uid'=>$uid])->whereTime('time','between',[$data['stime'],$data['etime']])->count()/$limit);  //获取总页数
                $result['data'] = Trans::where(['uid'=>$uid])->whereTime('time','between',[$data['stime'],$data['etime']])->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
        }
    }

    /**
     * [trans 成交的方法]
     * @return [boolean] [成功为true,失败为false]
     */
    protected function trans($data,$stockData,$funds){
        $data['number'] = $data['number']%100 ? $data['number'] - $data['number']%100 : $data['number'];
        //判断是否停牌
        if((float)$stockData[$data['stock']][1] != 0){
            //买入
            if($data['type'] == 1){
                //判断是否涨跌停
                if($highLimit = $this->isLimitMove($stockData[$data['stock']],$data['type'])){
                    //是否市价买入
                    if($data['isMarket'] == 1){
                        $result = $this->buyProcess($data,$stockData,$funds);
                    }else{
                        //购买的价格不能比今天的涨停价高
                        if($highLimit[0] > $data['price']){
                            if($highLimit[1] <= $data['price']){
                                //先处理为大于等于才成交
                                if($data['price'] >= $stockData[$data['stock']][1]){
                                    $result = $this->buyProcess($data,$stockData,$funds);
                                }else{
                                    //买入没有成交的处理
                                    $result = $this->noBuyOrder($data,$stockData,$funds);
                                }
                            }else{
                                $result = json(['status'=>'failed','data'=>'购买的价格不能低于今天的跌停的价格'.$highLimit[1].'元']);
                            }
                        }else{
                            $result = json(['status'=>'failed','data'=>'购买的价格不能高于今天的涨停的价格'.$highLimit[0].'元']);
                        }
                    }
                }else{
                    $result = json(['status'=>'failed','data'=>'涨停不能买入']);
                }
            }else if($data['type'] == 2){
                //卖出
                if($lowLimit = $this->isLimitMove($stockData[$data['stock']],$data['type'])){
                    //市价卖出
                    if($data['isMarket'] == 1){
                        $result = $this->sellProcess($data,$stockData,$funds);
                    }else{
                        //卖出的价格不能比今天的跌停价低
                        if($lowLimit[1] < $data['price']){
                            if($lowLimit[0] >= $data['price']){
                                //小于等于才成交
                                if($data['price'] <= $stockData[$data['stock']][1]){
                                    $result = $this->sellProcess($data,$stockData,$funds);
                                }else{
                                    //卖出没有成交的处理
                                    $result = $this->noSellOrder($data,$stockData,$funds);
                                } 
                            }else{
                                $result = json(['status'=>'failed','data'=>'卖出的价格不能高于今天的涨停的价格'.$lowLimit[0].'元']);
                            }
                        }else{
                             $result = json(['status'=>'failed','data'=>'卖出的价格不能低于今天的跌停的价格'.$lowLimit[1].'元']);
                        }
                    }
                }else{
                    $result = json(['status'=>'failed','data'=>'跌停不能买卖']);
                }
            }
        }else{
            //停牌了不能交易
            $result = json(['status'=>'failed','data'=>'股票停牌']);
        }    
        return $result;
    }

    /**
     * [isLimitMove 是否涨跌停能否买入]
     * @return boolean [可以买入为true  不能买入为false]
     */
    protected function isLimitMove($stockData,$type){
        $limitUp = round(($stockData[1]-$stockData[2])*1.1,2);//涨停的价格
        $limitDown = round(($stockData[1]-$stockData[2])*0.9,2);//跌停的价格
        if($type == 1){
            //买入的情况
            if((float)$stockData[1] < $limitUp && (float)$stockData[1] >= $limitDown){
                $bool = [$limitUp,$limitDown];
            }else{
                $bool = false;
            }
        }else if($type == 2){
            //卖出的情况
            if((float)$stockData[1] <= $limitUp && (float)$stockData[1] > $limitDown){
                $bool = [$limitUp,$limitDown];
            }else{
                $bool = false;
            }
        }
        return $bool;
    }

    /**
     * [isCanTrade 是否能够交易的时间]
     * @return boolean [布尔值]
     */
    protected function isCanTrade($tell){
        //是否启用时间验证
        if($tell){
            $w = date("w",time());
            if($w === 6 || $w === 0 ){
                $bool = false;
            }else{
                $nowTime = time();
                $startAm = strtotime(date("Y-m-d 09:30:00"));
                $endAm = strtotime(date("Y-m-d 11:30:00"));
                $startPm = strtotime(date("Y-m-d 13:00:00"));
                $endPm = strtotime(date("Y-m-d 15:00:00"));
                if(($startAm < $nowTime && $nowTime < $endAm) || ($startPm < $nowTime && $nowTime < $endPm)){
                    //获取不能交易的日子
                    $year = date("Y-01-01");
                    $noTradeDays = Db::name('no_trade_days')->whereTime('day','>',$year)->select();
                    if($noTradeDays){
                        foreach ($noTradeDays as $key => $value) {
                            $today = date("Y-m-d",time());
                            if($value['day'] == $today){
                                $bool = false;
                            }else{
                                $bool = true;
                            }
                        }
                    }else{
                        $bool = true;
                    }
                }else{
                    $bool = false;
                }
            }
        }else{
            $bool = true;
        }
        return $bool;
    }

    /**
     * [isToBuy 资金是否能够订单的金额]
     * @param  [array]  $data  [买入的订单信息]
     * @return boolean        [布尔值]
     */
    protected function isToBuy($data,$stockData){
        $scale = $this->_base->_scale;
        //查询对应账户的可用资金
        $funds = UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->find();
        $fee = $data['price']*$data['number']*$scale >= 5 ? $data['price']*$data['number']*$scale : 5;
        //沪市过户费
        if($data['stock']{0} == "6"){
                $fee = ceil($data['number']/1000) + $fee;
        }
        if($data['isMarket'] == 1){
            $available = $stockData[$data['stock']][1]*$data['number']+$fee;
        }else{
            $available = $data['price']*$data['number']+$fee;
        }
        if($funds['available_funds'] >= $available){
            $bool = $funds;
        }else{
            $bool = false;
        }
        return $bool;
    }

    /**
     * [isToSell 股票是否有可以卖出数量]
     * @param  [array]  $data [卖出的订单信息]
     * @return boolean       [description]
     */
    protected function isToSell($data){
        $position = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'sorts'=>$data['sorts'],'is_position'=>1])->find();
        if($position){
            if($position['available_number'] - $data['number'] >= 0 ){
                $bool = $position;
            }else{
                $bool = false;
            }
        }else{
            $bool = 1;
        }
        return $bool;
    }
    /**
     * [buyProcess 买入交易]
     * @param  [type] $data      [订单详情的数组]
     * @param  [type] $stockData [股票的实时信息]
     * @param  [type] $funds     [用户的资金信息]
     * @return [json]            [json]
     */
    protected function buyProcess($data,$stockData,$funds){
        //手续费比例
        $scale = $this->_base->_scale;
        //买入成交的处理
        Db::startTrans();
        //开启事务
        try {
            //订单参数
            $data['status'] = 1;
            $data['price'] = $stockData[$data['stock']][1];
            $data['stock_name'] = $stockData[$data['stock']][0];
            //手续费最低为5元
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            //沪市过户费
            if($data['stock']{0} == "6"){
                $data['fee'] = ceil($data['number']/1000) + $data['fee'];
            }
            $data['available_funds'] = $funds['available_funds'] - $data['price']*$data['number'] - $data['fee'];
            //更新用户资金
            UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update(['available_funds'=>$data['available_funds']]);
            //查看是否持有这只股票
            $userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();

            if($userInfo){
                //持有股票更改持仓表信息
                $da['fee'] = $data['fee'] + $userInfo['fee'];
                $da['cost'] = $userInfo['cost'] + $data['price']*$data['number'] + $data['fee'];
                $da['freeze_number'] = $userInfo['freeze_number'] + $data['number'];
                $da['cost_price'] = round($da['cost'] / ($da['freeze_number']+$userInfo['available_number']),8);
                $da['assets'] = $data['price'] * ($da['freeze_number']+$userInfo['available_number']);
                $da['ratio'] = round(($data['price'] - $da['cost_price']) / $da['cost_price']*100,8);
                $UserPosition = new UserPosition();
                $UserPosition->allowField(true)->where(['id'=>$userInfo['id']])->update($da);
                $data['pid'] = $userInfo['id'];
                //添加订单到数据库
                $Trans = new Trans();
                $Trans->allowField(true)->save($data);
                Db::commit();
                $result = json(['status'=>'success','data'=>'购买成功']);
            }else{
                //添加成交的订单到持仓表
                $da['fee'] = $data['fee'];
                $da['cost'] = $data['price'] * $data['number'] + $data['fee'];
                $da['assets'] = $data['price'] * $data['number'];
                $da['stock'] = $data['stock'];
                $da['stock_name'] = $data['stock_name'];
                $da['freeze_number'] = $data['number'];
                $da['cost_price'] = round($da['cost'] / $data['number'],8);
                $da['uid'] = $data['uid'];
                $da['time'] = date("Y-m-d H:i:s",time());
                $da['sorts'] = $data['sorts'];
                $da['ratio'] = round(($data['price'] - $da['cost_price'])/$da['cost_price']*100,8);
                $UserPosition = new UserPosition();
                $UserPosition->allowField(true)->save($da);
                $data['pid'] = $UserPosition->id;
                //添加订单到数据库
                $Trans = new Trans();
                $Trans->allowField(true)->save($data);
                Db::commit();
                $result = json(['status'=>'success','data'=>'购买成功']);;
            }
        } catch (\Exception $e){
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'下单失败，多次失败请联系管理员']);
        }
        return $result;
    }

    /**
     * [sellProcess 卖出交易]
     * @param  [array] $data      [卖出订单详情]
     * @param  [array] $stockData [股票信息]
     * @param  [array] $funds     [账户资金]
     * @return [json]            [返回信息]
     */
    protected function sellProcess($data,$stockData,$funds){
        //手续费比例
        $scale = $this->_base->_scale;
        //买入成交的处理
        Db::startTrans();
        //开启事务
        try {
            //订单参数
            $data['status'] = 1;
            $data['price'] = $stockData[$data['stock']][1];
            $data['stock_name'] = $stockData[$data['stock']][0];
            //手续费最低为5元
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            //沪市过户费
            if($data['stock']{0} == "6"){
                $data['fee'] = ceil($data['number']/1000) + $data['fee'];
            }
            //收取印花税
            $data['fee'] = $data['price'] * $data['number'] * 0.001 + $data['fee'];
            //为用户增加卖出金额
            $data['available_funds'] = $funds['available_funds'] + $data['price']*$data['number'] - $data['fee'];
            UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update(['available_funds'=>$data['available_funds']]);
            //获取用户持有股票的信息
            $userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            
            //判断是否清仓完全卖出
            if($userInfo['available_number'] == $data['number'] && $userInfo['freeze_number'] == 0){
                //更新用户信息
                $da['available_number'] = 0;
                $da['is_position'] = 2;
                $buyInfo = Trans::where(['pid'=>$userInfo['id'],'type'=>1])->select();
                $sellInfo = Trans::where(['pid'=>$userInfo['id'],'type'=>2])->select();
                if(count($buyInfo) == 1){
                    $costTotal = $buyInfo[0]['price'] * $buyInfo[0]['number'] + $buyInfo[0]['fee'];
                    $totalNum = $buyInfo[0]['number'];
                }else if(count($buyInfo) >= 2){
                    foreach ($buyInfo as $key => $value) {
                        $tmpTotal[] = $value['price'] * $value['number'] + $value['fee'];
                        $tmpNum[] = $value['number'];
                    }
                    $costTotal = array_sum($tmpTotal);
                    $totalNum = array_sum($tmpNum);
                }
                if(count($sellInfo) == 1){
                    $profits = $data['price'] * $sellInfo[0]['number'] - $sellInfo[0]['fee'];
                }else if(count($sellInfo) >= 2){
                    foreach ($sellInfo as $key => $value) {
                        $tmp[] = $data['price'] * $value['number'] - $value['fee'];
                    }
                    $profits = array_sum($tmp);
                }
                $profits = isset($profits) ? $profits : 0 ;
                $da['assets'] = $profits + $data['price'] * $data['number'] - $data['fee'];
                $da['fee'] = $userInfo['fee'] + $data['fee'];
                $da['cost'] = $costTotal;
                $da['cost_price'] = round($da['cost'] / $totalNum,8);
                $da['ratio'] = round(($da['assets'] - $da['cost'])/$da['cost']*100,8);
                $da['delete_time'] = time();

                //添加订单到数据库
                UserPosition::where(['id'=>$userInfo['id']])->update($da);
                $data['pid'] = $userInfo['id'];
                $Trans = new Trans();
                $Trans->allowField(true)->save($data);
                Db::commit();
                $result = json(['status'=>'success','data'=>'卖出成功']);
            }else{
                //更新用户信息
                $da['available_number'] = $userInfo['available_number'] - $data['number'];
                $da['cost'] = $userInfo['cost'] - $data['price'] * $data['number'] + $data['fee'];
                $da['cost_price'] = round($da['cost'] / ($userInfo['freeze_number']+$da['available_number']),8);
                $da['fee'] = $userInfo['fee'] + $data['fee'];
                $da['assets'] = $data['price'] * ($userInfo['freeze_number']+$da['available_number']);
                $tmp = 0;
                if($da['cost_price'] == 0){
                    $tmp = 1; 
                }
                $da['ratio'] = round(($data['price'] - $da['cost_price'])/abs(($da['cost_price'] + $tmp))*100,8);
                UserPosition::where(['id'=>$userInfo['id']])->update($da);
                //添加订单到数据库
                $data['pid'] = $userInfo['id'];
                $Trans = new Trans();
                $Trans->allowField(true)->save($data);
                Db::commit();
                $result = json(['status'=>'success','data'=>'卖出成功']);
            }
        } catch (\Exception $e){
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'下单失败，多次失败请联系管理员']);
        }
        return $result;
    }

    /**
     * [noOrder 买入没有成交的订单]
     * @param  [array] $data      [订单信息]
     * @param  [array] $stockData [获取的股票现价信息]
     * @param  [array] $funds     [用户资金信息]
     * @return [json]             [返回对应信息]
     */
    protected function noBuyOrder($data,$stockData,$funds){
        $scale = $this->_base->_scale;
        Db::startTrans();
        try {
            //买入没有成交的处理
            $data['stock_name'] = $stockData[$data['stock']][0];
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            //沪市过户费
            if($data['stock']{0} == "6"){
                $data['fee'] = ceil($data['number']/1000) + $data['fee'];
            }
            //扣除用户资金
            $data['available_funds'] = $funds['available_funds'] - $data['fee'] - $data['price'] * $data['number'];
            $Trans = new Trans();
            $Trans->allowField(true)->save($data);
            
            UserFunds::where(['uid'=>$funds['uid']])->update(['available_funds'=>$data['available_funds']]);
            //添加进入redis   ----未完成
            $redis = new Redis();
            $redis->set("noBuyOrder_".$Trans->id."_".$data['uid'],$data);
            Db::commit();
            $result = json(['status'=>'success','data'=>'下单成功']);
        } catch (\Exception $e) {
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'下单失败']);
        }
        return $result;
    }

    /**
     * [noSellOrder 卖出没有成交的订单]
     * @param  [array] $data      [订单的详情]
     * @param  [array] $stockData [当前股票的信息]
     * @param  [array] $funds     [用户的账户信息]
     * @return [json]            [提示信息]
     */
    protected function noSellOrder($data,$stockData,$funds){
        $scale = $this->_base->_scale;
        Db::startTrans();
        try {
            //买入没有成交的处理
            $data['stock_name'] = $stockData[$data['stock']][0];
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            //沪市过户费
            if($data['stock']{0} == "6"){
                $data['fee'] = ceil($data['number']/1000) + $data['fee'];
            }
            //收取印花税
            $data['fee'] = $data['price'] * $data['number'] * 0.001 + $data['fee'];
            //获取用户持有股票的信息
            $info = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            $data['available_funds'] = $funds['available_funds'] + $data['price'] * $data['number'] - $data['fee'];
            //减少可卖数量
            UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->update(['available_number'=>$info['available_number']-$data['number']]);
            $data['pid'] = $info['id'];
            $Trans = new Trans();
            $Trans->allowField(true)->save($data);
            $redis = new Redis();
            $redis->set("noSellOrder_".$Trans->id."_".$data['uid'],$data);
            Db::commit();
            $result = json(['status'=>'success','data'=>'下单成功']);
        } catch (\Exception $e) {
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'下单失败']);
        }
        return $result;
    }
}
