<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;


/**
* 管理员
*/
class Match extends Base
{
	public function index(){
		return $this->fetch();
	}

	public function add(){
		return $this->fetch();
	}

	public function update(){
		$data = [
			'username' => 'zp',
			'group_id' => 1,
			'password' => md5('123456')
		];
		
		Db::name('admin')->insert($data);

		//return $this->fetch();
	}
}
?>