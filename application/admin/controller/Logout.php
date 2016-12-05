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
		if(Session::has('id', 'admin')){
			Session::delete('id', 'admin');
		}

		$this->success('退出成功','login/index');
	}
}
?>