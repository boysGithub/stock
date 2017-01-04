<?php 
namespace app\admin\controller;

use think\Db;
use think\Session;
use app\admin\controller\Base;

/**
* 退出
*/
class Logout extends Base
{
	public function index(){
		if($this->isLogined()){
			unset($_SESSION['admin_id']);
		}

		$this->success('退出成功','login/index');
	}
}
?>