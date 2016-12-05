<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;


/**
* 管理员
*/
class Admin extends Base
{
	public function index(){
		$admins = Db::name('admin')->select();

		$this->assign('admins', $admins);

		return $this->fetch();
	}

	public function add(){
		return $this->fetch();
	}

	public function edit(){
		$id = input('param.id');
		$match = Db::name('admin')->where(['id'=>intval($id)])->find();
		if(empty($match)){
			$this->error('比赛不存在');
		}

		$this->assign('match', $match);
		return $this->fetch();
	}

	public function update(){
		$id = input('post.id',0);
		$username = trim(input('post.user-name',''));
		$group_id = input('post.group_id',1);

		if(Db::name('admin')->where(['id'=>['<>',$id],'username'=>$username])->count()){
			$this->error('用户名已添加');		
		}


		if($id){
			$data = [
				'username' => $username,
				'group_id' => $group_id,
			];
			!empty(trim(input('post.password'))) &&  $data['password'] = md5(trim(input('post.password')));

			if(Db::name('admin')->where(['id'=>$id])->update($data) === false){
				$this->error('修改失败');
			} else {
				$this->success('修改成功', 'admin/index');
			}

		} else {
			$password = md5(trim(input('post.password')));
			$data = [
				'username' => $username,
				'group_id' => $group_id,
				'password' => $password
			];
			
			if(Db::name('admin')->insert($data) === false){
				$this->error('添加失败');
			} else {
				$this->success('添加成功', 'admin/index');
			}
		} 	
	}
}
?>