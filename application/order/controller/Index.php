<?php

namespace app\order\controller;

use think\Db;
use think\Request;
use app\index\controller\Base;
use app\order\model\UserPosition;
use app\order\model\UserFunds as UserModel;
use app\order\model\Transaction as Trans;
class Index extends Base
{
    protected $_scale = 0.0003; //股票手续
    protected $_stockFunds = 1000000; //股票账户初始金额
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $redis = new \Redis();
        $list = UserModel::all();
        return json($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 新建股票的订单
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        if($this->isCanTrade()){
            $data = $request->param();

            //验证传递的参数
            $result = $this->validate($data,'Users');
            if (true !== $result) {
                return json(['status'=>'failed','data'=>$result]);
            }

            //获取股票信息
            $stockData = getStock($data['stock'],'s_');
            if($funds = $this->isToBuy($stockData[$data['stock']][1],$data)){
                $res = $this->trans($data,$stockData,$funds);
            }else{
                $res = json(['status'=>'failed','data'=>'资金不足']);
            }
        }else{
            $res = json(['status'=>'failed','data'=>'现在不是交易时间']);
        }
        return $res;
    }

    /**
     * 获取一个用户订单
     *
     * @param  int  $id | \think\Request  $request
     * @return \think\Response
     */
    public function read($id)
    {
        $data   = input('get.');
        $result = $this->getAccessType($data,$id);

        //添加成交状态的名字
        for ($i=0; $i < count($result); $i++) {
            $result[$i]->append(['status_name','username']);
        }
        if($result){
            $result = json(['status'=>'success','data'=>$result]);
        }else{
            $result = json(['status'=>'failed','data'=>'获取的数据不存在']);
        }
        return $result;
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
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
        $arr = [0,1,2];
        if(!in_array($data['status'],$arr)){
            exit(JN(['status'=>'failed','data'=>'非法参数']));
        }
        $status = Trans::where(['id'=>$id,'status'=>['=',0]])->value('status');
        if($status === 0){
            $result = Trans::update($data,['id'=>$id]);
            if($result){
                $result = json(['status'=>'success','data'=>'撤单成功']);
            }else{
                $result = json(['status'=>'failed','data'=>'撤单失败']);
            }
        }else{
            $result = json(['status'=>'failed','data'=>'订单已经不能修改']);
        }
        return $result;
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    /**
     * [getAccessType 得到获取数据的方式]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    protected function getAccessType($data,$uid){
        switch ($data['type']) {
            case 'all':
                # 获取历史成交所有数据
                return Trans::where(['uid'=>$uid,'status'=>1])->select();
                break;
            case 'deal':
                # 获取当日成交的数据
                return Trans::whereTime('time','today')->where(['uid'=>$uid,'status'=>1])->select();
                break;
            case 'entrust':
                # 获取当日委托数据
                return Trans::whereTime('time','week')->where(['uid'=>$uid])->select();
                break;
        }
    }

    /**
     * [trans 成交的方法]
     * @return [boolean] [成功为true,失败为false]
     */
    protected function trans($data,$stockData,$funds){
        $redis = redis();
        //判断是否停牌
        if((float)$stockData[$data['stock']][1] != 0){
            //买入
            if($data['type'] == 1){
                //判断是否涨跌停
                if($this->isLimitMove($stockData[$data['stock']],$data['type'])){
                    if($data['price'] >= $stockData[$data['stock']][1]){
                        $result = $this->buyProcess($data,$stockData,$funds);
                    }else{
                        //买入没有成交的处理
                        if(Trans::create($data)){
                            //添加进入redis   ----未完成
                            $result = json(['status'=>'success','data'=>'下单成功']);
                        }else{
                            $result = json(['status'=>'failed','data'=>'下单失败']);
                        }
                    }
                }else{
                    $result = json(['status'=>'failed','data'=>'涨停不能买入']);
                }
            }else if($data['type'] == 2){
                //卖出
                if($this->isLimitMove($stockData[$data['stock']],$data['type'])){
                    if($data['price'] <= $stockData[$data['stock']][1]){
                        //卖出成交的处理
                    }else{
                        //卖出没有成交的处理
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
                $bool = true;
            }else{
                $bool = false;
            }
        }else if($type == 2){
            //卖出的情况
            if((float)$stockData[1] <= $limitUp && (float)$stockData[1] > $limitDown){
                $bool = true;
            }else{
                $bool = false;
            }
        }
        return $bool;
    }

    /**
     * [isCanTrade 是否能够交易]
     * @return boolean [布尔值]
     */
    protected function isCanTrade(){
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
        return $bool;
    }

    /**
     * [isToBuy 资金是否能够订单的金额]
     * @param  [type]  $price [当前股票价格]
     * @param  [type]  $data  [订单详情的数组]
     * @return boolean        [布尔值]
     */
    protected function isToBuy($price,$data){
        $scale = $this->_scale;
        //查询对应账户的可用资金
        $funds = UserModel::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->find();
        $fee = $price*$data['number']*$scale >= 5 ? $price*$data['number']*$scale : 5;
        $available = $price*$data['number']+$fee;
        if($funds['available_funds'] >= $available){
            $bool = $funds;
        }else{
            $bool = false;
        }
        return $bool;
    }

    /**
     * [buyProcess description]
     * @param  [type] $data      [订单详情的数组]
     * @param  [type] $stockData [股票的实时信息]
     * @param  [type] $funds     [用户的资金信息]
     * @return [json]            [json]
     */
    protected function buyProcess($data,$stockData,$funds){
        //手续费比例
        $scale = $this->_scale;
        //买入成交的处理
        Db::startTrans();
        //开启事务
        try {
            //订单参数
            $data['status'] = 1;
            $data['price'] = $stockData[$data['stock']][1];
            $data['stock_name'] = $stockData[$data['stock']][0];
            // //手续费最低为5元
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            $data['available_funds'] = $funds['available_funds'] - $data['price']*$data['number'] - $data['fee'];
            //更新用户资金
            UserModel::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update(['available_funds'=>$data['available_funds']]);
            //添加订单到数据库
            Trans::create($data);

            //查看是否持有这只股票
            $userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();

            if($userInfo){
                //持有股票更改持仓表信息
                $da['cost'] = $userInfo['cost'] + $data['price']*$data['number'] + $data['fee'];
                $da['id'] = $userInfo['id'];
                $da['fee'] = $data['fee'] + $userInfo['fee'];
                $da['freeze_number'] = $userInfo['freeze_number'] + $data['number'];
                $da['cost_price'] = round($da['cost'] / ($userInfo['freeze_number']+$userInfo['available_number']+$data['number']),3);
                $da['assets'] = $data['price'] * ($userInfo['freeze_number']+$userInfo['available_number']+$data['number']) + $da['fee'];
                $da['ratio'] = round(($data['price'] - $userInfo['cost_price']) / $userInfo['cost_price'],3);
                UserPosition::update($da);
                Db::commit();
                $result = json(['status'=>'success','data'=>'购买成功']);;
            }else{
                //添加成交的订单到持仓表
                $da['fee'] = $data['fee'];
                $da['cost'] = $stockData[$data['stock']][1]*$data['number']+$da['fee'];
                $da['stock'] = $data['stock'];
                $da['stock_name'] = $data['stock_name'];
                $da['assets'] = $da['cost'];
                $da['freeze_number'] = $data['number'];
                $da['cost_price'] = round($da['cost'] / $data['number'],3);
                $da['uid'] = $data['uid'];
                $da['time'] = date("Y-m-d H:i:s",time());
                $da['sorts'] = $data['sorts'];
                UserPosition::create($da);
                Db::commit();
                $result = json(['status'=>'success','data'=>'购买成功']);;
            }
        } catch (\Exception $e){
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'下单失败，多次失败请联系管理员']);
        }
        return $result;
    }
}
