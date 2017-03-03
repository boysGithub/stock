<?php 
namespace app\admin\controller;

use app\admin\controller\Base;
use app\common\model\User as UserModel;
use app\common\model\UserPosition;
use app\common\model\Transaction;
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

	//持仓
	public function positions()
	{
		$data = input('get.');
		$query = [];
		$where = ['is_position'=>1];
		if(!empty($data['stock'])){
			$query['stock'] = $data['stock'];
			$where['stock'] = $data['stock'];
		}

		$positions = UserPosition::where($where)->alias('p')->join('sjq_users u','p.uid=u.uid', 'LEFT')->order('last_time DESC')->paginate(20,false,['query' => $query]);

		$this->assign([
			'positions' => $positions,
			'pages' => $positions->render(),
			'page' => isset($data['page']) ? $data['page'] : 1
		]);

		return $this->fetch();
	}

	//除权
	public function exRight()
	{
		$id = input('get.id', 0);
		$stock = input('get.stock', '');
		$type = 1;
		$value = input('get.value', 0);
		if(empty($stock) || empty($value)){
			$this->error('参数错误', 'positions');
		}

		if(empty($id)){
			$positions = UserPosition::where(['stock'=>$stock,'is_position'=>1])->select();
			$data = [];
			$p_data = [];
			foreach ($positions as $key => $val) {
				$number = $val->position_number * $value / 10;
				$data[] = [
					'uid' => $val->uid,
					'stock' => $val->stock,
					'price' => 0,
					'number' => $number,
					'type' => 3,
					'status' => 1,
					'fee' => 0,
					'stock_name' => $val->stock_name,
					'time' => date('Y-m-d H:i:s'),
					'available_funds' => 0,
					'deal_time' => date('Y-m-d H:i:s'),
					'pid' => $val->id,
					'sorts' => 1,
					'entrustment' => 0
				];
				$p_data[] = [
					'id' => $val->id,
					'freeze_number' => $val->freeze_number + $number,
	                'cost_price' => round($val->cost / ($val->freeze_number + $number + $val->available_number),3),
	                'last_time' => date("Y-m-d H:i:s"),
	                'position_number' => $val->freeze_number + $number + $val->available_number
	            ];
			}
			$trans = new Transaction;
			$trans->saveAll($data);

			$positionModal = new UserPosition;
			$positionModal->saveAll($p_data);
		} else {
			$position = UserPosition::get($id);
			if($position->is_position == 1 && $position->position_number > 0){
				$number = $position->position_number * $value / 10;
				$data = [
					'uid' => $position->uid,
					'stock' => $position->stock,
					'price' => 0,
					'number' => $number,
					'type' => 3,
					'status' => 1,
					'fee' => 0,
					'stock_name' => $position->stock_name,
					'time' => $position->stock,
					'available_funds' => 0,
					'deal_time' => $position->stock,
					'pid' => $position->id,
					'sorts' => 1,
					'entrustment' => 0
				];

				$trans = Transaction::create($data);
				if(empty($trans->id)){
					$this->error('除权失败', 'positions');
				}

				$p_data = [
					'freeze_number' => $position->freeze_number + $number,
	                'cost_price' => round($position->cost / ($position->freeze_number + $number + $position->available_number),3),
	                'last_time' => date("Y-m-d H:i:s"),
	                'position_number' => $position->freeze_number + $number + $position->available_number
	            ];
				$position->save($p_data);
			}
		}

		$this->error('除权成功', 'positions');
	}
}
?>