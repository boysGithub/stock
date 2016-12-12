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
		$id = input('param.id', 0);
		if(empty($id)){
			$this->error('错误', 'index/matchList');
		}
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
		if($this->checkLogin()){
			return $this->fetch('trade/tradeCenter');
		}else{
			return $this->redirect('index/login');
		}
	}

	//卖出
	public function sale(){
		if($this->checkLogin()){
			return $this->fetch('trade/sale');
		}else{
			return $this->redirect('index/login');
		}
	}

	//交易记录
	public function entrust(){
		if($this->checkLogin()){
			return $this->fetch('trade/entrust');
		}else{
			return $this->redirect('index/login');
		}
	}

	/**
	 * [personal 个人中心页面]
	 * @return [type] [description]
	 */
	public function personal(){
		$uid = input('param.uid', 0);
		if(empty($uid)){
			if(!$this->checkLogin()){
				return $this->redirect('index/login');
			}
		}

		$this->assign('uid', $uid);
		return $this->fetch('member/personal');
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

	/**
	 * [search 搜索方法]
	 * @return [type] [description]
	 */
	public function search(Request $request){
		$stock = $request->param('stock');
		$ch = curl_init("http://suggest3.sinajs.cn/suggest/?type=111&key={$stock}&name=suggestdata") ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch) ;
		$output = iconv('GBK', 'UTF-8', $output);
		echo $output;
	}

	public function quiet(Request $request){
		$stock = $request->param('stock');
		$ch = curl_init("http://hq.sinajs.cn?list=".$stock.",s_".$stock) ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch) ;
		$output = iconv('GBK', 'UTF-8', $output);
		echo $output;
	}	
	
}
?>