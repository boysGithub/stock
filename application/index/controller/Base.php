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
        if($redis->get('create_'.$data['uid']) !== true){
            if(!UserFunds::where(['uid'=>$data['uid']])->value('id')){
                $this->createStock($data['uid']);
            }else{
                $redis->set('create_'.$data['uid'],true);
            }
        }
        $tokenInfo = $redis->get('token');
        if($tokenInfo){
            if($tokenInfo[$data['uid']]['stock_token'] != $data['token'] && $tokenInfo[$data['uid']]['expired_token'] != $data['token']){
                exit(JN(['status'=>'failed','data'=>'token过期，请重新登录']));
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
}
