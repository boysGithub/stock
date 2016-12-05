<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Config;
use think\Db;
use think\cache\driver\Redis;
/**
* 
*/
class Base extends Controller
{
	public $_limit = 20; //显示的条数
	public $_stockFunds = 1000000; //股票账户初始金额
	public $_scale = 0.0003; //股票手续费
    public $_sorts = 1;
    public function _initialize()
    {
    	$data = input('get.');

    	$res = $this->validate($data,'TellToken');
        if (true !== $res) {
            exit(JN(['status'=>'failed','data'=>$res]));
        }
        $redis = new Redis;
        //固定的token
    	$token = Config::has("stocktell.token") ? Config::get('stocktell.token') : '';
    	//随机token字符串
        if($redis->has('rand_token_'.$data['uid'])){
        	$randToken = $redis->get('rand_token_'.$data['uid']);
        }else{
        	exit(JN(['status'=>'failed','data'=>'token过期，请重新登录']));
        }
        if($redis->get('expired_token_'.$data['uid']) != ''){
            if(sha1($token.$data['uid'].$randToken) != $data['token'] && $redis->get('expired_token_'.$data['uid']) != $data['token']){
                exit(JN(['status'=>'failed','data'=>'token过期，请重新登录']));
            }
        }else{
            if(sha1($token.$data['uid'].$randToken) != $data['token']) exit(JN(['status'=>'failed','data'=>'token过期，请重新登录']));
        }
    }
}
