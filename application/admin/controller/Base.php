<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;

/**
* 
*/
class Base extends Controller
{
    public function _initialize()
    {
    	$controller = Request::instance()->controller();
    	$c_arr = ['Login'];

    	if(!in_array($controller, $c_arr) && !$this->isLogined()){
    		$this->error('请登录！', 'login/index');
    	}

    	if($this->isLogined()){
    		$user = $this->get_user();
    		$this->assign('user', $user);
    	}
    }

	public function get_user()
	{
		$id = Session::get('id','admin');
		$user = [];
		if(!empty($id)){
			$user = Db::name('admin')->where(['id'=>$id])->find();
		}

		return $user;
	}


	public function isLogined()
	{
		return Session::has('id','admin');
	}
}
