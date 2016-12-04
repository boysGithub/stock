<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\UserFunds;
use think\Db;
/**
* 股票账户注册控制器
*/
class Stock extends Base
{	
	protected $base;
	public function __construct(){
		$this->_base = new Base;
	}
	/**
	 * [createStock 股票账户的创建]
	 * @return [type] [description]
	 */
	public function createStock(){
		$data['uid'] = input('get.uid');
		if(UserFunds::where(['uid'=>$data['uid']])->value('id')){
			return json(['status'=>'failed','data'=>'账户已经存在']);
		}else{
			$data['sorts'] = $this->_base->_sorts;
			$data['funds'] = $this->_base->_stockFunds;
			$data['time'] = date("Y-m-d H:i:s",time());
			$data['available_funds'] = $data['funds'];
			if(UserFunds::create($data)){
				return json(['status'=>'success','data'=>'准备开始交易吧']);
			}else{
				return json(['status'=>'failed','data'=>'创建账户失败']);
			}
		}
	}
}
?>