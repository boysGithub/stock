<?php 
namespace app\index\controller;

use think\Controller;
/**
* 首页的控制器
*/
class Index extends Controller
{
	public function index(){
		return $this->fetch();
	}

	/**
	 * [matchpage 比赛的列表页面]
	 * @return [type] [description]
	 */
	public function matchList(){
		$uid = 49125;//$_SESSION['uid'];

		$this->assign('uid', $uid);
		return $this->fetch('match/matchpage');
	}

	public function macthUser(){
		$id = input('param.id', 0);
		if(empty($id)){
			$this->error('错误', 'index/matchList');
		}

		$uid = 49125;//$_SESSION['uid'];

		$this->assign('uid', $uid);
		$this->assign('id', $id);

		return $this->fetch('match/matchDetails');
	}

	/**
	 * [tradingRules 交易规则页面]
	 * @return [type] [description]
	 */
	public function tradingRules(){
		return $this->fetch('page/index');
	}

	/**
	 * [rankingList 牛人列表页面]
	 * @return [type] [description]
	 */
	public function rankingList(){

		$this->assign('order', input('param.order', 'total_rate'));

		return $this->fetch('rank/RankingList');
	}

	/**
	 * [tradingCenter 交易中心页面]
	 * @return [type] [description]
	 */
	public function tradeCenter(){
		return $this->fetch('trade/tradeCenter');
	}

	/**
	 * [personal 个人中心页面]
	 * @return [type] [description]
	 */
	public function personal(){
		return $this->fetch('member/personal');
	}

	public function login(){
		return $this->fetch('login/login');
	}
}
?>