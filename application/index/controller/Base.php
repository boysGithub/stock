<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Config;
use think\Db;
use think\cache\driver\Redis;
use app\common\model\UserFunds;
use app\common\model\DaysRatio;
use app\common\model\WeeklyRatio;
use app\common\model\MonthRatio;
use app\common\model\User;
/**
* 
*/
class Base extends Controller
{
	public $_limit = 20; //显示的条数
	public $_stockFunds = 1000000; //股票账户初始金额
	public $_scale = 0.001; //股票手续费
    public $_sorts = 1;
    /**
     * [createStock 股票账户的创建]
     * @return [type] [description]
     */
    protected function createStock($uid){
        $num = rand(0,99999);
        if($num < 10000){
            if(10000 - $num > 9000){
                $num = "00".(string)$num;
            }else{
                $num = "0".(string)$num;
            }
        }else{
            $num = (string)$num;
        }
        $data['uid'] = $uid;
        $data['sorts'] = $this->_sorts;
        $data['funds'] = $this->_stockFunds;
        $data['time'] = date("Y-m-d H:i:s",time());
        $data['available_funds'] = $data['funds'];
        $data['account'] = "m00".date("YmdHis",time()).$num;
        $this->checkAccount($uid,$data['account']);
        //开启事务
        Db::startTrans();
        try {
            UserFunds::create($data);
            $da['uid'] = $uid;
            $da['initialCapital'] = $data['funds'];
            $da['endFunds'] = $data['funds'];
            $da['proportion'] = 0;
            $da['time'] = date("Y-m-d H:i:s",time());
            DaysRatio::create($da);
            WeeklyRatio::create($da);
            MonthRatio::create($da);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * [checkToken description]
     * @return [type] [description]
     */
    protected function checkToken(){
        $redis = new Redis;
        $data['uid'] = Request::instance()->param('uid') ? Request::instance()->param('uid') : Request::instance()->param('id');
        $data['token'] = Request::instance()->param('token');
        $res = $this->validate($data,'TellToken');
        if (true !== $res) {
            exit(JN(['status'=>'failed','data'=>$res]));
        }
        if(!User::where(['uid'=>$data['uid']])->find()){
            exit(JN(['status'=>'failed','data'=>'没有此用户']));
        }
        $tokenInfo = $redis->get('token');
        if($tokenInfo){
            if($tokenInfo[$data['uid']]['stock_token'] != $data['token'] && $tokenInfo[$data['uid']]['expired_token'] != $data['token']){
                exit(JN(['status'=>'failed','data'=>'token过期，请重新登录']));
            }
            if($redis->get('create_'.$data['uid']) !== true){
                if(!UserFunds::where(['uid'=>$data['uid']])->value('id')){
                    $this->createStock($data['uid']);
                }else{
                    $redis->set('create_'.$data['uid'],true);
                }
            }
        }else{
            $this->autoBuildToken();
            $this->checkToken();
        }
    }

     /**
     * [autoBuildToken 自动生成token]
     * @return [type] [description]
     */
    private function autoBuildToken(){
        $redis = new Redis;
        //固定的token
        $token = Config::has("stocktell.token") ? Config::get('stocktell.token') : '';
        $randToken = getRandChar(10);
        $sql1 = "SELECT `uid`,`stock_token`,`expired_token` from `ts_user`";
        $tokenInfo = Db::connect('sjq1')->query($sql1);
        foreach ($tokenInfo as $key => $value) {
            $tmp[$value['uid']]['stock_token'] = $value['stock_token'];
            $tmp[$value['uid']]['expired_token'] = $value['expired_token'];
        }
        $redis->set('token',$tmp,3600);
    }

    /**
     * [checkAccout 检查账户是否重复]
     * @return [type] [description]
     */
    public function checkAccount($uid,$str){
        $account = UserFunds::where(['uid'=>$uid])->value('account');
        if($account == $str){
            $this->createStock($uid);
        }
    }

    public function doLogin($login='',$password=''){
        if($login == ''){
            $login = input('post.login_email');
        }
        if($password == ''){
            $password = input('post.login_password');
        }
        if($login == ''){
           $this->error("用户名不能为空");
        }
        if($password == ''){
           $this->error("密码不能为空",'Index/login',1);
        }
        if(strpos($login,"@")){
            $salt = User::where(['login'=>$login])->value('login_salt');
            if($salt){
                $pass = md5(md5($password).$salt);
                if($info = User::where(['login'=>$login,'password'=>$pass])->find()){
                    setcookie('login_email','',0,'/','.sjqcj.com');
                    setcookie('login_password','',0,'/','.sjqcj.com');
                    setcookie('login_email',cookieEncrypt($login),time()+86400,'/','.sjqcj.com');
                    setcookie('login_password',cookieEncrypt($password),time()+86400,'/','.sjqcj.com');
                    $_SESSION['username'] = $info['username'];
                    $_SESSION['uid'] = $info['uid'];
                    $this->success('登录成功，正在跳转....','Index/index',1);
                }else{
                    $this->error("用户名和密码不匹配");
                }
            }else{
                $this->error("用户不存在");
            }
        }else if(is_numeric($login) && strlen($login) == 11){
            $salt = User::where(['phone'=>$login])->value('login_salt');
            if($salt){
                $pass = md5(md5($password).$salt);
                if($info = User::where(['phone'=>$login,'password'=>$pass])->find()){
                    setcookie('login_email','',0,'/','.sjqcj.com');
                    setcookie('login_password','',0,'/','.sjqcj.com');
                    setcookie('login_email',cookieEncrypt($login),time()+86400,'/','.sjqcj.com');
                    setcookie('login_password',cookieEncrypt($password),time()+86400,'/','.sjqcj.com');
                    $_SESSION['username'] = $info['username'];
                    $_SESSION['uid'] = $info['uid'];
                    $this->success('登录成功，正在跳转....','Index/index',1);
                }else{
                    $this->error("用户名和密码不匹配");
                }
            }else{
                $this->error("用户不存在");
            }
        }else{
            $salt = User::where(['username'=>$login])->value('login_salt');
            if($salt){
                $pass = md5(md5($password).$salt);
                if($info = User::where(['username'=>$login,'password'=>$pass])->find()){
                    setcookie('login_email','',0,'/','.sjqcj.com');
                    setcookie('login_password','',0,'/','.sjqcj.com');
                    setcookie('login_email',cookieEncrypt($login),time()+86400,'/','.sjqcj.com');
                    setcookie('login_password',cookieEncrypt($password),time()+86400,'/','.sjqcj.com');
                    $_SESSION['username'] = $info['username'];
                    $_SESSION['uid'] = $info['uid'];
                    $this->success('登录成功，正在跳转....','Index/index',1);
                }else{
                    $this->error("用户名和密码不匹配");
                }
            }else{
                $this->error("用户不存在");
            }
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
