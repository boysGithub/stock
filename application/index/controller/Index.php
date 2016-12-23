<?php 
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\common\model\User;
/**
* 首页的控制器
*/
class Index extends Controller
{	
	public function index(){
		return $this->fetch('index/index');
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
		session_start();
		if(isset($_SESSION['uid'])){
			$this->success("您已经登录过了",'Index/index',1);
		}else{
			return $this->fetch("login/login");
		}
	}

	public function register(){
		return $this->redirect("http://www.sjqcj.com/register",0);
	}

	//登录验证
	private function checkLogin(){
		session_start();
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

		header('Content-type: image/png'); 
		echo @file_get_contents($img);
	}


    /**
     * [获取用户头像]
     * @return [string] 
     */
    public function getAvatar($uid)
    {
        $avatar = 'http://www.sjqcj.com/data/upload/avatar/';

        $str = md5($uid);
        $avatar .= substr($str, 0, 2) . '/' . substr($str, 2, 2) . '/' . substr($str, 4, 2);
        $avatar .= '/original_200_200.jpg';

        return $avatar;
    }

    public function autoLogin($login='',$password='',$auto=false){
    	if(!$auto){
    		session_start();
    	}
    	if($login == ''){
    		$login = input('post.login_email');
    	}
        if($password==''){
        	$password = input('post.login_password');
        }
        if($login == ''){
           $this->error("用户名不能为空",'Index/login','',1);
           exit();
        }
        if($password == ''){
           $this->error("密码不能为空",'Index/login','',1);
           exit();
        }
        if(strpos($login,"@")){
            $salt = User::where(['login'=>$login])->value('login_salt');
            if($salt){
            	
            		$pass = md5(md5($password).$salt);
            	
                if($info = User::where(['login'=>$login,'password'=>$pass])->find()){
                    $_SESSION['username'] = $info['username'];
                    $_SESSION['uid'] = $info['uid'];
                    if(!$auto){
                    	$this->success('登录成功，正在跳转....','Index/index','',1);
                    }
                }else{
                    $this->error("用户名和密码不匹配",'Index/login','',1);
                    exit();
                }
            }else{
                $this->error("用户不存在",'Index/login','',1);
                exit();
            }
        }else if(is_numeric($login) && strlen($login) == 11){
            $salt = User::where(['phone'=>$login])->value('login_salt');
            if($salt){
                
            		$pass = md5(md5($password).$salt);
            	
                if($info = User::where(['phone'=>$login,'password'=>$pass])->find()){
                    $_SESSION['username'] = $info['username'];
                    $_SESSION['uid'] = $info['uid'];
                    if(!$auto){
                    	$this->success('登录成功，正在跳转....','Index/index','',1);
                    }
                }else{
                    $this->error("用户名和密码不匹配",'Index/login','',1);
                    exit();
                }
            }else{
                $this->error("用户不存在",'Index/login','',1);
                exit();
            }
        }else{
            $salt = User::where(['username'=>$login])->value('login_salt');
            if($salt){
                
            		$pass = md5(md5($password).$salt);
            	
                if($info = User::where(['username'=>$login,'password'=>$pass])->find()){
                    $_SESSION['username'] = $info['username'];
                    $_SESSION['uid'] = $info['uid'];
                    if(!$auto){
                    	$this->success('登录成功，正在跳转....','Index/index','',1);
                    }
                }else{
                    $this->error("用户名和密码不匹配",'Index/login','',1);
                    exit();
                }
            }else{
                $this->error("用户不存在",'Index/login','',1);
                exit();
            }
        }
    }

    public function doLogin(){
    	session_start();
    	if(isset($_COOKIE['login_email']) || isset($_COOKIE['login_password'])){
    		if(isset($_SESSION['uid'])){
    			$token = Db::connect('sjq1')->name('user')->where(['uid'=>$_SESSION['uid']])->Field('stock_token as token,uname as username,uid')->find();
    			return json(['status'=>'success','data'=>$token]);
    		}else{
    			$login = cookieDecrypt($_COOKIE['login_email']);
				$password = cookieDecrypt($_COOKIE['login_password']);
				$this->autoLogin($login,$password,true);
				if(isset($_SESSION['uid'])){
					$token = Db::connect('sjq1')->name('user')->where(['uid'=>$_SESSION['uid']])->Field('stock_token as token,uname as username,uid')->find();
    				return json(['status'=>'success','data'=>$token]);
				}else{
					return json(['status'=>'failed','data'=>'账号密码不匹配']);
				}
    		}
    	}else{
    		return $this->logout();
    	}
    }

   	
    /**
     * [divisionLogin 区分登录方式]
     * @return [type] [description]
     */
    public function logout(){
        setcookie('login_email','',0,'/','.sjqcj.com');
        setcookie('login_password','',0,'/','.sjqcj.com');
        $_SESSION = [];
        return json(['status'=>'failed','data'=>'退出成功']);
    }
}
?>