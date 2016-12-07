<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use app\common\model\User as UserModel;
/**
* 会员
*/
class User extends Base
{
	public function index(){
		$where = [];

		$hot = input('get.recommend', '');
		if($hot != ''){//推荐
			$where['recommend'] = $hot;
		}

		$key = input('get.key', '');
		if(!empty($key)){
			$where['username'] = ['like', "%{$key}%"];
		}

		$users = UserModel::where($where)->order('uid desc')->paginate(10);


		$this->assign([
			'users' => $users,
			'page' => $users->render()
		]);

		return $this->fetch();
	}
}
?>