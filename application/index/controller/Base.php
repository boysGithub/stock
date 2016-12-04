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
        if($redis->has('rand_token')){
        	$randToken = $redis->get('rand_token');
        }else{
        	$randToken = getRandChar(10);
        	Db::startTrans();
        	try {
        		$sql = "UPDATE `ts_user` a,(select uid from `ts_user`) obj set a.stock_token=sha1(concat('{$token}',obj.uid,'{$randToken}')) where a.uid=obj.uid";
        		Db::connect('sjq1')->query($sql);
        		Db::commit();
        		$redis->set('rand_token',$randToken,30);
        	} catch (\Exception $e) {
        		Db::rollback();
        		exit(JN(['status'=>'failed','data'=>'token生成失败']));
        	}
        }
    	if(sha1($token.$data['uid'].$randToken) != $data['token']){
    		exit(JN(['status'=>'failed','data'=>'token过期，请重新登录']));
    	}
    }
}
