<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;


/**
* 广告管理
*/
class Advertisement extends Base
{
	public function index(){
		$advertisement = Db::name('advertisement')->select();

		$this->assign('ads', $advertisement);

		return $this->fetch();
	}

	public function save_add()
	{
		$file = request()->file('image');
	    // 移动到框架应用根目录/public/uploads/ 目录下
	    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
		if($info === false){
			$this->error('上传图片失败');
	        // 成功上传后 获取上传信息
	        // 输出 jpg
	        echo $info->getExtension();
	        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	        echo $info->getSaveName();
	        // 输出 42a79759f284b767dfcb2a0197904287.jpg
	        echo $info->getFilename(); 
	    }

	    $image = '/uploads/'.$info->getSaveName();
		$data = [
			'image' => $image,
			'url' => trim(input('post.url')),
		];
		
		if(Db::name('advertisement')->insert($data) === false){
			$this->error('添加失败');
		} else {
			$this->success('添加成功');
		}
	}

	public function save_edit()
	{

	}

	public function delete(){
		
	}
}
?>