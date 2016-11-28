<?php 
namespace app\order\controller;

use app\index\controller\Base;
use app\common\model\Transaction;
/**
* 股票信息控制
*/
class Share extends Base
{
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
        if($shareInfo = Transaction::where(['uid'=>$data['uid'],'stock'=>$data['stock']])->whereTime('time','between',[$data['stime'],$data['etime']])->order('id desc')->select()){
        	$result = json(['status'=>'success','data'=>$shareInfo]);
        }else{
        	$result = json(['status'=>'failed','data'=>'获取数据失败']);
        }
        return $result;
	}
}	
?>