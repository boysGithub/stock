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
		$query = [];
		if($hot != ''){//推荐
			$where['recommend'] = $hot;
			$query['recommend'] = $hot;
		}

		$key = input('get.key', '');
		if(!empty($key)){
			$where['username'] = ['like', "%{$key}%"];
			$query['key'] = $key;
		}

		$users = UserModel::where($where)->order('uid desc')->paginate(20,false,['query' => $query]);


		$this->assign([
			'users' => $users,
			'pages' => $users->render(),
			'page' => input('get.page', 1)
		]);

		return $this->fetch();
	}

	//牛人推荐
	public function recommend()
	{
		$recommend = input('get.recommend');
		$uid = input('get.uid');
		if(!in_array($recommend, ['0', '1']) || empty($uid)){
			$this->error('参数错误', 'user/index');
		}

		if($recommend == 1){
			$data = [
				'recommend' => 1,
				'sort' => input('get.sort', 0),
				'reason' => trim(input('get.reason', ''))
			];
		} else {//撤销推荐
			$data = [
				'recommend' => 0,
				'reason' => ''
			];
		}

		if(UserModel::update($data, ['uid'=> $uid]) === false){
			$this->error('修改失败', 'user/index');
		} else {
			$this->success('编辑成功', 'user/index');
		}
	}
}
?>