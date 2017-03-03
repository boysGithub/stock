<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use app\common\model\StockUnusual;
/**
* 股票
*/
class Stock extends Base
{	
	//异常列表
	public function unusual()
	{
		$stocks = StockUnusual::where([])->order('id DESC')->paginate(50);
		foreach ($stocks as $key => $val) {
			$val->rate = round(($val->old_price / $val->price - 1) * 100, 2) . '%';
		}

		$this->assign('stocks', $stocks);
		$this->assign('pages', $stocks->render());
		return $this->fetch();
	}

	//处理
	public function dispose()
	{
		$id = input('get.id', 0);
		if(empty($id)){
			$this->error('参数错误','unusual');
		}

		$type = input('get.type', 2);
		StockUnusual::get($id)->save(['status'=>2]);//标识为已处理
		if(intval($type) !== 1){
			$this->success('成功','unusual');
		}

		//去除权
		$stock = input('get.stock', '');
		if(empty($stock)){
			$this->error('参数错误','unusual');
		}

		$this->redirect(url('user/positions')."?stock={$stock}");
	}
}
?>