<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Config;
use think\Db;
use think\cache\driver\Redis;
use app\common\model\UserFunds;
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
        $redis = new Redis;
        $data['uid'] = Request::instance()->param('uid') ? Request::instance()->param('uid') : Request::instance()->param('id');
        $data['token'] = Request::instance()->param('token');
    	$res = $this->validate($data,'TellToken');
        if (true !== $res) {
            exit(JN(['status'=>'failed','data'=>$res]));
        }
        if($redis->get('create_'.$data['uid']) !== true){
            if(!UserFunds::where(['uid'=>$data['uid']])->value('id')){
                $this->createStock($data['uid']);
            }else{
                $redis->set('create_'.$data['uid'],true);
            }
        }
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

    /**
     * [createStock 股票账户的创建]
     * @return [type] [description]
     */
    protected function createStock($uid){
        $data['uid'] = $uid;
        $data['sorts'] = $this->_sorts;
        $data['funds'] = $this->_stockFunds;
        $data['time'] = date("Y-m-d H:i:s",time());
        $data['available_funds'] = $data['funds'];
        if(UserFunds::create($data)){
            $redis = new Redis;
            $redis->set('create_'.$uid,true);
        }else{
            return json(['status'=>'failed','data'=>'创建账户失败']);
        } 
    }
}
