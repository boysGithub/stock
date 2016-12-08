<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use app\common\model\Match as MatchModel;


/**
* 比赛
*/
class Match extends Base
{
	public function index()
	{
		$matchs = MatchModel::where([])->order('id DESC')->paginate(10);

		$this->assign('matchs', $matchs);
		$this->assign('page', $matchs->render());
		return $this->fetch();
	}

	public function add()
	{
		return $this->fetch();
	}

	public function edit(){
		$id = input('param.id');
		$match = MatchModel::where(['id'=>intval($id)])->find();
		if(empty($match)){
			$this->error('比赛不存在');
		}

		$this->assign('match', $match);
		return $this->fetch();
	}

	public function update(){
		$data = input('post.');

		$m_data = [
			'name' => trim($data['name']),
			'type' => $data['type'],
			'periods' => intval($data['periods']),
			'start_date' => $data['start_date'],
			'end_date' => $data['end_date']
		];

		if($file = request()->file('image')){
	    	$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'match');
	    	if($info){
	    		$m_data['image'] = '/uploads/match/'.$info->getSaveName();
	    	}
		}
		

		if(!empty($data['id'])){
			if(MatchModel::update($m_data,['id'=>intval($data['id'])]) === false){
				$this->error('修改失败');
			} else {
				$this->success('修改成功', 'match/index');
			}

		} else {
			if(MatchModel::create($m_data) === false){
				$this->error('添加失败');
			} else {
				$this->success('添加成功', 'match/index');
			}
		} 	
	}
}
?>