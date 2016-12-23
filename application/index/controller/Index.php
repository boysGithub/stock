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

	public function login(){
		return $this->fetch("login/login");
	}

	public function register(){
		return $this->redirect("http://www.sjqcj.com/register",0);
	}

	//登录验证
	private function checkLogin(){
		if(isset($_SESSION['uid'])){
			return true;
		}else{
			return false;
		}
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
		curl_close($ch);
		$output = iconv('GBK', 'UTF-8', $output);
		echo $output;
	}
	/**
	 * [quiet 获取股票数据]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function quiet(Request $request){
		$stock = $request->param('stock');
		$ch = curl_init("http://hq.sinajs.cn?list=".$stock.",s_".$stock) ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch) ;
		curl_close($ch);
		$output = iconv('GBK', 'UTF-8', $output);
		echo $output;
	}

	/**
	 * [quiet 获取多支股票数据]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function getStocks(Request $request){
		$stock = $request->param('stock');
		$ch = curl_init("http://hq.sinajs.cn?list=".$stock) ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch) ;
		$output = iconv('GBK', 'UTF-8', $output);
		curl_close($ch);

		echo $output;
	}

	/**
	 * [打印用户的头像]
	 */
	public function avatar(Request $request){
		$uid = $request->param('uid');

		$img = 'https://moni.sjqcj.com/static/img/portrait.gif';
		if(!empty($uid)){
			$img = $this->getAvatar($uid);
		}

		$opts = array(
			'http'=>array(
				'timeout'=>3,
			)
		);
		$context = stream_context_create($opts);
		$resource = @file_get_contents($img, false, $context);

		if($resource) {
		} else {
			$img = 'https://moni.sjqcj.com/static/img/portrait.gif';
		}

		header('content-type: image/png'); 
		echo @file_get_contents($img);
	}


    /**
     * [获取用户头像]
     * @return [string] 
     */
    private function getAvatar($uid)
    {
        $avatar = 'http://www.sjqcj.com/data/upload/avatar/';

        $str = md5($uid);
        $avatar .= substr($str, 0, 2) . '/' . substr($str, 2, 2) . '/' . substr($str, 4, 2);
        $avatar .= '/original_200_200.jpg';

        return $avatar;
    }

    public function doLogin(){
    	if(isset($_COOKIE['login_email']) && isset($_COOKIE['login_password'])){
    		if(isset($_SESSION['uid'])){
    			$token = Db::connect('sjq1')->name('user')->where(['uid'=>$_SESSION['uid']])->Field('stock_token as token,uname as username,uid')->find();
    			return json(['status'=>'success','data'=>$token]);
    		}else{
    			$login = cookieDecrypt($_COOKIE['login_email']);
				$passowrd = cookieDecrypt($_COOKIE['login_password']);
				$base = new Base;
				$base->doLogin($login,$passowrd);
				if(isset($_SESSION['uid'])){
					$token = Db::connect('sjq1')->name('user')->where(['uid'=>$_SESSION['uid']])->Field('stock_token as token,uname as username,uid')->find();
    				return json(['status'=>'success','data'=>$token]);
				}else{
					return json(['status'=>'failed','data'=>'账号密码不匹配']);
				}
    		}
    	}else{
    		$base = new Base;
    		return $base->logout();
    	}
    }
}
?>