<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use app\common\model\Advertisement as AdModel;

/**
* 广告管理
*/
class Advertisement extends Base
{
	//轮播图
	public function index(){
		$advertisement = AdModel::where(['type'=>1])->order('id DESC')->select();

		$this->assign('ads', $advertisement);

		return $this->fetch();
	}

	//公告
	public function announcement()
	{
		$advertisement = AdModel::where(['type'=>2])->order('id DESC')->select();

		$this->assign('ads', $advertisement);

		return $this->fetch();
	}

	//广告
	public function banner()
	{
		$advertisement = AdModel::where(['type'=>['in', [3,4]]])->order('id DESC')->select();

		$this->assign('ads', $advertisement);

		return $this->fetch();
	}

	//赛况
	public function report()
	{
		$advertisement = AdModel::where(['type'=>5])->order('id DESC')->select();

		$this->assign('ads', $advertisement);

		return $this->fetch();
	}

	public function add()
	{
		$title = ['1'=>'添加首页轮播图','2'=>'添加公告','3'=>'添加banner','4'=>'添加banner','5'=>'添加赛况'];

		$this->assign([
			'type'=> input('param.type', 2),
			'title' => $title[input('param.type', 2)]
			]);

		return $this->fetch();
	}

	public function save_add()
	{
		$data = [
			'url' => trim(input('post.url')),
			'enabled' => input('post.enabled', 0),
			'type' => input('post.type', 1),
			'title' => input('post.title', '')
		];

		$file = request()->file('image');
		if(!empty($file)){
	    	$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'ad');
	    	if($info){
	    		$data['image'] = '/uploads/ad/'.$info->getSaveName();
	    	}
		}
		
		if(AdModel::create($data) === false){
			$this->error('添加失败');
		} else {
			switch ($data['type']) {
				case '1':
					$this->success('添加成功', 'index');
					break;
				case '2':
					$this->success('添加成功', 'announcement');
					break;
				case '3':
				case '4':
					$this->success('添加成功', 'banner');
					break;
				case '5':
					$this->success('添加成功', 'report');
					break;
			}
		}
	}

	public function save_edit()
	{
		$data = input('post.');
		if(empty($data['id'])){
			$this->error('参数错误');
		}
		
		$s_data['url'] = trim($data['url']);
		$s_data['enabled'] = $data['enabled'];
		isset($data['title']) && $s_data['title'] = trim($s_data['title']);

		$file = request()->file('image');
		if(!empty($file)){
	    	$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'ad');
	    	if($info){
	    		$s_data['image'] = '/uploads/ad/'.$info->getSaveName();
	    	}
		}
		
		if(AdModel::update($s_data,['id'=>intval($data['id'])]) === false){
			$this->error('修改失败');
		} else {
			$this->success('修改成功');
		}
	}

	public function delete()
	{
		$id = intval(input('param.id'));
		if(empty($id)){
			$this->error('参数错误');
		}

		if(AdModel::where(['id'=>$id])->delete()){
			switch (input('param.type', 2)) {
				case '1':
					$this->success('删除成功', 'index');
					break;
				case '2':
					$this->success('删除成功', 'announcement');
					break;
				case '3':
				case '4':
					$this->success('删除成功', 'banner');
					break;
				case '5':
					$this->success('删除成功', 'report');
					break;
			}
		} else {
			$this->error('删除失败');
		}
	}
}
?>