<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use app\common\model\AutoUpdate;
/**
* 定时结算
*/
class Index extends Base
{
	public function index()
	{
		$autos = AutoUpdate::where([])->order('id DESC')->paginate(50);

		$this->assign('autos', $autos);
		$this->assign('page', $autos->render());
		return $this->fetch();
	}
}
?>