<?php 
namespace app\order\controller;

use app\index\controller\Base;
use app\common\model\Transaction;
use app\common\model\DaysRatio;
use app\common\model\UserFunds;
use app\common\model\UserPosition;
/**
* 股票信息控制
*/
class Share extends Base
{   
    protected $_base;
    public function  __construct(){
        $this->_base = new Base();
        
    }
	/**
	 * [getStockInfo 得到一直股票明细]
	 * @return [type] [description]
	 */
	public function getStockInfo(){
		$data = input('get.');
		//验证传递的参数
        $res = $this->validate($data,'Share');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $data['etime'] = date("Y-m-d H:i:s",strtotime($data['etime'])+86399);
        if($shareInfo = Transaction::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'status'=>1])->whereTime('time','between',[$data['stime'],$data['etime']])->order('id desc')->select()){
        	$result = json(['status'=>'success','data'=>$shareInfo]);
        }else{
        	$result = json(['status'=>'failed','data'=>'获取数据失败']);
        }
        return $result;
	}

    /**
     * [GetTimeChart 得到用户分时图]
     */
    public function getTimeChart(){
        $data = input("get.");
        $res = $this->validate($data,"GetTimeChart");
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $chart = DaysRatio::where(['uid'=>$data['uid']])->Field("uid,round((endFunds-{$this->_base->_stockFunds})/{$this->_base->_stockFunds}*100,2) as endFunds,time")->select();
        if($chart){
            $time = UserFunds::where(['uid'=>$data['uid']])->value('time');
            array_unshift($chart,['uid'=>$data['uid'],'endFunds'=>0,'time'=>date('Y-m-d',strtotime($time))]);
            $result = json(['status'=>'success','data'=>$chart]);
        }else{
            $result = json(['status'=>'failed','data'=>'还没有数据']);
        }
        return $result;
    }

    /**
     * [historicalPosition 历史持仓]
     * @return [type] [description]
     */
    public function historicalPosition(){
        $limit = $this->_base->_limit;
        $data = input("get.");
        $res = $this->validate($data,"GetTimeChart");
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1;
        if(isset($data['stock']) && $data['stock']){
            $count = ceil(UserPosition::where(['uid'=>$data['uid'],'is_position'=>2,'stock'=>$data['stock']])->count()/$limit);
            $historical = UserPosition::where(['uid'=>$data['uid'],'is_position'=>2,'stock'=>$data['stock']])->limit(($data['p']-1)*$limit,$limit)->select();
        }else{
            $count = ceil(UserPosition::where(['uid'=>$data['uid'],'is_position'=>2])->count()/$limit);
            $historical = UserPosition::where(['uid'=>$data['uid'],'is_position'=>2])->limit(($data['p']-1)*$limit,$limit)->select();
        }
        
        if($historical){
            return json(['status'=>'success','data'=>$historical,'pageTotal'=>$count]);
        }else{
            return json(['status'=>'failed','data'=>'还没有历史持仓','pageTotal'=>0]);
        }
    }
}	
?>