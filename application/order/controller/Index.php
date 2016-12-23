<?php
namespace app\order\controller;

use think\Db;
use think\Request;
use think\Config;
use think\Validate;
use app\index\controller\Base;
use app\common\model\UserPosition;
use app\common\model\UserFunds;
use app\common\model\Transaction;
use think\cache\driver\Redis;
use app\order\controller\Trans;
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
        $expert = Db::table('sjq_transaction t')->join('sjq_users u','t.uid=u.uid')->join('sjq_users_funds uf','u.uid=uf.uid')->join('sjq_users_position up','u.uid=up.uid AND t.stock=up.stock')->where('status',1)->Field('t.id,t.uid,t.stock,t.stock_name,u.username,t.price,t.time,t.type,uf.total_rate,up.ratio')->order('t.id desc')->limit(30)->select();
        foreach ($expert as $key => $value) {
            $expert[$key]['avatar'] = $this->getAvatar($value['uid']);
        }

        if($expert){
            $result = json(['status'=>'success','data'=>$expert]);
        }else{
            $result = json(['status'=>'failed','data'=>'还没有数据']);
        }
        return $result;
    }

    /**
     * [save 保存订单的方法]
     * @return [type] [description]
     */
    public function save(Request $request){
        $this->_base->checkToken();
        $trans = new Trans;
        $data = $request->param();
        //验证传递的参数
        $result = $this->validate($data,'SaveOrder');
        if (true !== $result) {
            return json(['status'=>'failed','data'=>$result]);
        }
        return $trans->save($data);
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
        if(!$this->isTrans()){
            return json(['status'=>'failed','data'=>'交易时间才能撤单']);
        }
        $this->_base->checkToken();
        $data   = $request->param();
        $res = $this->validate($data,'UpdateOrder');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $userOrder = Transaction::where(['id'=>$id,'uid'=>$data['uid']])->find();
        if($userOrder['status'] === 0){
            Db::startTrans();
            try {
                $redis = new Redis;
                Transaction::update(['status'=>$data['status']],['id'=>$id,'uid'=>$data['uid']]);
                if($userOrder['type'] == 1){
                    $availableFunds = UserFunds::where(['uid'=>$userOrder['uid']])->value('available_funds');
                    $da['available_funds'] = $userOrder['fee'] + $userOrder['price'] * $userOrder['number'] + $availableFunds;
                    UserFunds::update($da,['uid'=>$userOrder['uid']]);
                    $redis->rm('noBuyOrder_'.$id.'_'.$data['uid']);
                }else if($userOrder['type'] == 2){
                    $position = UserPosition::where(['uid'=>$data['uid'],'stock'=>$userOrder['stock'],'is_position'=>1,'sorts'=>$userOrder['sorts']])->Field('id,available_number')->find();
                    $da['available_number'] = $position['available_number'] + $userOrder['number'];
                    $stockData = getStock($userOrder['stock'],'s_');
                    $da['assets'] = $stockData[$userOrder['stock']][1] * $da['available_number'];
                    UserPosition::where(['id'=>$position['id']])->update($da);
                    $redis->rm('noSellOrder_'.$id.'_'.$data['uid']);
                }
                Db::commit();
                $result = json(['status'=>'success','data'=>'撤单成功']);
            } catch (\Exception $e){
                Db::rollback();
                $result = json(['status'=>'failed','data'=>'撤单失败']);
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
                $data['etime'] = date("Y-m-d H:i:s",strtotime($data['etime'])+86399);
                # 获取历史成交所有数据
                $result['totalPage'] = ceil(Transaction::where(['uid'=>$uid,'status'=>1])->whereTime('time','between',[$data['stime'],$data['etime']])->count()/$limit);
                $result['data'] = Transaction::where(['uid'=>$uid,'status'=>1])->whereTime('time','between',[$data['stime'],$data['etime']])->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
            case 'deal':
                # 获取当日成交的数据
                $result['totalPage'] = ceil(Transaction::whereTime('time','today')->where(['uid'=>$uid,'status'=>1])->count()/$limit);
                $result['data'] = Transaction::where(['uid'=>$uid,'status'=>1])->whereTime('time','today')->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
            case 'entrust':
                # 获取当日委托数据
                $result['totalPage'] = ceil(Transaction::whereTime('time','today')->where(['uid'=>$uid])->count()/$limit);
                $result['data'] = Transaction::whereTime('time','today')->where(['uid'=>$uid])->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
            case 'historical':
                $data['etime'] = date("Y-m-d H:i:s",strtotime($data['etime'])+86399);
                # 获取历史委托数据
                $result['totalPage'] = ceil(Transaction::where(['uid'=>$uid])->whereTime('time','between',[$data['stime'],$data['etime']])->count()/$limit);  //获取总页数
                $result['data'] = Transaction::where(['uid'=>$uid])->whereTime('time','between',[$data['stime'],$data['etime']])->limit(($data['p']-1)*$limit,$limit)->order('id desc')->select();
                return $result;
                break;
        }
    }

    /**
     * [isTrans 是否能交易]
     * @return boolean [description]
     */
    public function isTrans(){
        $t1 = strtotime(date("Y-m-d 9:30:00"));
        $t2 = strtotime(date("Y-m-d 11:30:00"));
        $t3 = strtotime(date("Y-m-d 13:00:00"));
        $t4 = strtotime(date("Y-m-d 15:00:00"));
        if($t1 <= time() &&  $t4 >= time()){
            return true;
        }else{
            return false;
        }
    }

}
