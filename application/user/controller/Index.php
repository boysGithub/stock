<?php

namespace app\user\controller;

use think\Controller;
use think\Request;
use app\index\controller\Base;
use app\common\model\UserPosition;
use app\common\model\Transaction as Trans;
use app\common\model\UserFunds;
use app\common\model\DaysRatio;
use app\user\controller\Rank;
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
        
        // $data = input('get.');
        // $res = $this->validate($data,'UserPosition');
        // if (true !== $res) {
        //     return json(['status'=>'failed','data'=>$res]);
        // }
        
        // $position = $this->getUserPosition($data); //获取持仓信息
        // $noOrder = $this->getUserNoOrder($data); //获取待成交信息
        // return json(['status'=>'success','data'=>$position['data'],'totalPage'=>$position['totalPage'],'nData'=>$noOrder['data'],'nTotalPage'=>$noOrder['totalPage']]);
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
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {   
        $stockFunds = $this->_base->_stockFunds;

        $fund = UserFunds::where(['uid'=>$id])->find(); //获取用户资产信息
        if($fund){
            $fund->append(['username']);
            $fund['totalProfitRatio'] = round(($fund['funds']-$stockFunds)/$stockFunds*100,2); //总盈利率
            $userInit = DaysRatio::where(['uid'=>$id])->whereTime('time','today')->value('initialCapital');
            $fund['shares'] = $userInit ? $fund['funds'] - $userInit : 0 ;  //今日盈亏
            //暂时用数据库处理才做   ---redis 还未完成
            $rank = new Rank();
            $tmp = $rank->totalProfitRank(false);
            foreach ($tmp as $key => $value) {
                if($value['uid'] == $id){
                    $fund['rank'] = $key+1;
                }
            }
            $result = json(['status'=>'success','data'=>$fund]);
        }else{
            $result = json(['status'=>'failed','data'=>'没有这个用户']);
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
        //
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
     * [getUserPosition 获取用户持仓]
     * @param  [array] $data [传入的数据]
     * @return [json]       [description]
     */
    protected function getUserPosition($data){
        $limit = $this->_base->_limit;

        $data['p'] = isset($data['p']) ? (int)$data['p'] > 0 ? $data['p'] : 1 : 1;
        $result['totalPage'] = ceil(UserPosition::where(['uid'=>$data['uid'],'is_position'=>1])->count()/$limit);
        $result['data'] = UserPosition::where(['uid'=>$data['uid'],'is_position'=>1])->limit(($data['p']-1)*$limit,$limit)->select();
        return $result;
    }

    protected function getUserNoOrder($data){
        $limit = $this->_base->_limit;
        $data['np'] = isset($data['np']) ? (int)$data['np'] > 0 ? $data['np'] : 1 : 1;
        $result['totalPage'] = ceil(Trans::where(['uid'=>$data['uid'],'status'=>0])->whereTime('time','today')->count()/$limit);
        $result['data'] = Trans::where(['uid'=>$data['uid'],'status'=>0])->whereTime('time','today')->limit(($data['np']-1)*$limit,$limit)->order('time desc')->select();
        return $result;
    }
}
