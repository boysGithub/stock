<?php

namespace app\user\controller;

use think\Controller;
use think\Request;
use app\index\controller\Base;
use app\common\model\UserPosition;
use app\common\model\Transaction as Trans;
use app\common\model\UserFunds;
use app\common\model\User;
use app\common\model\DaysRatio;
use app\common\model\OptionalStock;
use think\Db;
use think\cache\driver\Redis;
/**
 * 用户控制器
 */
class Index extends Base
{
    protected $_base;
    public function __construct(){
        $this->_base = new Base();
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $redis = new Redis();
        $data = input('get.');
        $res = $this->validate($data,'UserPosition');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        if($redis->get('create_'.$data['uid']) !== true){
            if(User::where(['uid'=>$data['uid']])->value('uid')){
                if(!UserFunds::where(['uid'=>$data['uid']])->value('id')){
                    $this->_base->createStock($data['uid']);
                }else{
                    $redis->set('create_'.$data['uid'],true);
                }
            }else{
               return json(['status'=>'failed','data'=>'用户不存在']); 
            }
        }
        $position = $this->getUserPosition($data); //获取持仓信息
        $noOrder = $this->getUserNoOrder($data); //获取待成交信息
        return json(['status'=>'success','data'=>$position['data'],'nData'=>$noOrder['data']]);
    }

    /**
     * 添加用户自选股
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $this->_base->checkToken();
        $data = $request->param();
        $res = $this->validate($data,'OptionalStock');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $optionalStock = new OptionalStock;
        $data['time'] = date("Y-m-d H:i:s");
        if(UserFunds::where(['uid'=>$data['uid']])->find()){
            if($optionalStock->where(['uid'=>$data['uid'],'stock'=>$data['stock']])->find()){
                $result = json(['status'=>'failed','data'=>'股票已经存在']);
            }else{
                $stockData = getStock($data['stock'],'s_');
                $data['stock_name'] = $stockData[$data['stock']][0];
                //这里后期更改 先暂时这样处理
                Db::startTrans();
                try {
                    $optionalStock->allowField(true)->save($data);
                    $id = $optionalStock->id;
                    $count = OptionalStock::where(['stock'=>$data['stock']])->count();
                    OptionalStock::where(['stock'=>$data['stock']])->update(['follow'=>$count]);
                    Db::commit();
                    $result = json(['status'=>'success','data'=>$id]);
                } catch (\Exception $e) {
                    Db::rollback();
                    $result = json(['status'=>'failed','data'=>'添加自选股失败']);
                }
            }
        }else{
            $result = json(['status'=>'failed','data'=>'用户不存在']);
        }
        return $result;
    }

    /**
     * 当前股票是否已经是自选股
     *
     * @param  \think\Request  $request
     * @return [json]
     */
    public function isOptional(Request $request)
    {
        $data = $request->param();
        $res = $this->validate($data,'OptionalStock');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        if(UserFunds::where(['uid'=>$data['uid']])->find()){
            $optional = OptionalStock::where(['uid'=>$data['uid'],'stock'=>$data['stock']])->find();
            $available_number = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1])->value('available_number');
            if(!empty($optional['id'])){
                $result = json(['status'=>'success','data'=>$optional['id'],'available'=>$available_number]);
            }else{
                $result = json(['status'=>'failed','data'=>'不是自选股','available'=>$available_number]);
            }
        }else{
            $result = json(['status'=>'failed','data'=>'用户不存在']);
        }

        return $result;
    }

    /**
     * [getUserOptional 获取用户的自选股信息]
     * @return [json] [用户自选股信息]
     */
    public function getUserOptional(){
        $uid = input('get.uid');
        if($uid && is_numeric($uid)){
            if($userOptionalStock = OptionalStock::where(['uid'=>$uid])->select()){
                $result = json(['status'=>'success','data'=>$userOptionalStock]);
            }else{
                $result = json(['status'=>'failed','data'=>'还没有添加自选股']);
            }
        }else{
            $result = json(['status'=>'failed','data'=>'用户不能为空,也不能有特殊字符']);
        }
        return $result;
    }

    /**
     * [delete 用户删除自选股信息]
     * @return [type] [description]
     */
    public function delete(Request $request,$id){
        $this->_base->checkToken();
        $uid = $request->param();
        $res = $this->validate($uid,'UserPosition');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        if(strpos($id,',')){
            $data = explode(',',$id);
        }else{
            $data = $id;
        }
        //这里后期更改 先暂时这样处理
        Db::startTrans();
        try {
            $info = OptionalStock::where(['uid'=>$uid['uid'],'id'=>['in',$data]])->select();
            OptionalStock::where(['uid'=>$uid['uid'],'id'=>['in',$data]])->delete();
            foreach ($info as $key => $value) {
                $count = 0;
                $count = OptionalStock::where(['stock'=>$value['stock']])->count();
                OptionalStock::where(['stock'=>$value['stock']])->update(['follow'=>$count]);
            }
            Db::commit();
            $result = json(['status'=>'success','data'=>'删除成功']);
        } catch (\Exception $e) {
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'删除失败']);
        }
        return $result;
    }

    /**
     * [read 获取用户账户信息]
     * @param  [number] $id [用户uid]
     * @return [json]     [返回json]
     */
    public function read($id)
    {   
        $redis = new Redis;
        if($redis->get('create_'.$id) !== true){
            if(!UserFunds::where(['uid'=>$id])->value('id')){
                $this->createStock($id);
            }else{
                $redis->set('create_'.$id,true);
            }
        }
        $stockFunds = $this->_base->_stockFunds;
        $fund = UserFunds::where(['uid'=>$id])->Field('id,uid,funds,time,operationTime,available_funds,sorts,total_rate,avg_position_day,total_profit_rank,week_avg_profit_rate,win_rate,success_rate')->find();
        //获取用户资产信息
        if($fund){
            $fund->append(['username']);
            $userInit = DaysRatio::where(['uid'=>$id])->whereTime('time','today')->value('initialCapital');
            $fund['shares'] = $userInit ? $fund['funds'] - $userInit : 0 ;  //今日盈亏
            $fund['position'] = round(($fund['funds'] - $fund['available_funds'])/$fund['funds']*100,2);
            $result = json(['status'=>'success','data'=>$fund]);
        }else{
            $result = json(['status'=>'failed','data'=>'没有这个用户']);
        }
        
        return $result;
    }

    /**
     * [获取牛人推荐]
     * @return [json]
     */
    public function getRecommend()
    {
        $users = User::where(['u.recommend'=> 1])->alias('u')->field('u.*, (w.endFunds - w.initialCapital) / w.initialCapital week_rate,
(SELECT count(id) FROM `sjq_weekly_ratio` WHERE week_rate> (endFunds - initialCapital) / initialCapital)+1 ranking')
                    ->join('sjq_weekly_ratio w', 'u.uid=w.uid', 'LEFT')
                    ->select();
        
        foreach ($users as $key => $val) {
            $users[$key]['week_rate'] = empty($val['week_rate']) ? 0 : round($val['week_rate'] * 100, 2);
            $users[$key]['avatar'] = $this->getAvatar($val['uid']);
        }
        
        $result = json(['status'=>'success', 'data'=> $users]);

        return $result;
    }

    /**
     * [getUserPosition 获取用户持仓]
     * @param  [array] $data [传入的数据]
     * @return [json]       [description]
     */
    protected function getUserPosition($data){
        $result['data'] = UserPosition::where(['uid'=>$data['uid'],'is_position'=>1])->select();
        return $result;
    }

    /**
     * [getUserNoOrder 获取用户待成交的订单]
     * @param  [array] $data [用户的uid]
     * @return [json]       [返回订单详情]
     */
    protected function getUserNoOrder($data){
        $result['data'] = Trans::where(['uid'=>$data['uid'],'status'=>0])->whereTime('time','today')->order('time desc')->select();
        return $result;
    }
}
