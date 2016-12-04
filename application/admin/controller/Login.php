<?php 
namespace app\admin\controller;

use think\Db;
use think\Session;
use app\admin\controller\Base;



/**
* 登陆
*/
class Login extends Base
{
	public function _initialize()
    {
        parent::_initialize();

        if($this->isLogined()){
        	$this->error('已登录', 'index/index');
        }
    }

	public function index(){
		return $this->fetch();
	}

	public function logined()
	{
		$username = trim(input('post.user-name'));
		$password = md5(trim(input('post.password')));

		$user = Db::name('admin')->where(['username'=>$username, 'password'=>$password])->find();
		if(empty($user)){
			$this->error('登录失败');
		}

		Session::set('id', $user['id'], 'admin');

		$this->success('登录成功', 'index/index');
	}
}
?>