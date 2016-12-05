<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
/**
* 首页的控制器
*/
class Index extends Base
{
	public function index(){
		return $this->fetch();
	}
}
?>