<?php 
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\index\controller\Base;
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
		return $this->fetch('match/matchpage');
	}

	public function macthUser(){
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
		return $this->fetch('rank/RankingList');
	}

	/**
	 * [tradingCenter 交易中心页面]
	 * @return [type] [description]
	 */
	public function tradeCenter(){
		if(@$_COOKIE['PHPSESSID']){
			$uid = Db::connect('sjq1')->name('moni_user')->where(['sessionid'=>$_COOKIE['PHPSESSID']])->find();
			if($uid){
				$_SESSION['uid'] = $uid['uid'];
				return $this->fetch('trade/tradeCenter');
			}else{
				return $this->fetch('login/login');
			}
		}else{
			return $this->fetch('login/login');
		}
	}

	/**
	 * [personal 个人中心页面]
	 * @return [type] [description]
	 */
	public function personal(){
		if(@$_COOKIE['PHPSESSID']){
			$uid = Db::connect('sjq1')->name('moni_user')->where(['sessionid'=>$_COOKIE['PHPSESSID']])->find();
			if($uid){
				$_SESSION['uid'] = $uid['uid'];
				return $this->fetch('member/personal');
			}else{
				return $this->fetch('login/login');
			}
		}else{
			return $this->fetch('login/login');
		}
	}

	public function doLogin(){
        if($_COOKIE['PHPSESSID']){
            $uid = Db::connect('sjq1')->name('moni_user')->where(['sessionid'=>$_COOKIE['PHPSESSID']])->value('uid');
            if($uid){
            	$token = Db::connect('sjq1')->name('user')->where(['uid'=>$uid])->value('stock_token');
            	$userInfo = Db::name('users')->where(['uid'=>$uid])->find();
            	$userInfo['token'] = $token;
                return json(['status'=>'success','data'=>$userInfo]);
            }else{
            	return json(['status'=>'failed','data'=>'已经退出,请重新登录']);
            }
        }else{
        	return json(['status'=>'failed','data'=>'获取不到cookie']);
        }
	}

	public function login(){
		return $this->redirect("http://www.sjqcj.com",0);
	}
}
?>