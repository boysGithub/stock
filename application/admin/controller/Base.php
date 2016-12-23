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
    {	if (!session_id()) session_start();
    	$controller = Request::instance()->controller();
    	$c_arr = ['Login'];

    	if(!in_array($controller, $c_arr) && !$this->isLogined()){
    		$this->error('请登录！', 'login/index');
    	}

    	if($this->isLogined()){
    		$user = $this->get_user();
    		$this->assign('user', $user);
    	}

    	$this->assign('img_url', Config('use_url.img_url'));
    }

	public function get_user()
	{
		$id = $_SESSION['admin_id'];
		$user = [];
		if(!empty($id)){
			$user = Db::name('admin')->where(['id'=>$id])->find();
		}

		return $user;
	}


	public function isLogined()
	{
		return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
	}
}
