<?php 
namespace app\desert\controller;

use app\index\controller\Base;
use think\Request;
use think\Db;
use app\common\model\Desert;
use app\common\model\UserPrice;
use app\common\model\Order;
use think\Config;
/**
* 
*/
class Index extends Base
{
	protected $_base;
    public function  __construct(){
        $this->_base = new Base();
    }

    /**
     * [getDesertTime  得到时间列表]
     * @return [type] [description]
     */
    public function getDesertTime(){
    	return json(['status'=>'success','data'=>[1,3,6,12,18,24]]);
    }

    /**
     * [userOrder 用户定价]
     * @return [type] [description]
     */
    public function userOrder(){
    	// $this->_base->checkToken();
    	// $data = input('post.');
    	// unset($data['token']);
    	// $res = $this->validate($data,'UserOrder');
     //    if (true !== $res) {
     //        return json(['status'=>'failed','data'=>$res]);
     //    }
     //    $data['time'] = date("Y-m-d H:i:s");
     //    if($id = UserPrice::where(['uid'=>$data['uid'],'exp_time'=>$data['exp_time']])->value('id')){
     //    	if(UserPrice::update($data,['id'=>$id])){
     //    		return json(['status'=>'success','data'=>'修改定价成功']);
     //    	}else{
     //    		return json(['status'=>'failed','data'=>'修改定价失败']);
     //    	}
     //    }else{
     //    	if(UserPrice::create($data)){
     //    		return json(['status'=>'success','data'=>'定价成功']);
     //    	}else{
     //    		return json(['status'=>'failed','data'=>'定价失败']);
     //    	}
     //    }
    }

    /**
     * [isDesert 是否订阅]
     * @return boolean [description]
     */
   	public function isDesert(){
   		$data = input('get.');
   		$res = $this->validate($data,'IsDesert');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $da = Desert::where($data)->Field('id,exp_time')->find();
        if($da){
        	if(floor((strtotime($da['exp_time']) - time())/24/3600) <= 30){
        		$da['is_extend'] = 1;
        		return json(['status'=>'success','data'=>$da]);
        	}else{
        		$da['is_extend'] = -1;
        		return json(['status'=>'success','data'=>$da]);
        	}
        }else{
        	return json(['status'=>'failed','data'=>[]]);
        }
   	}

   	/**
   	 * [order 订阅用户]
   	 * @return [type] [description]
   	 */
   	public function order(){
   		$this->_base->checkToken();
   		$data = input('post.');
    	unset($data['token']);
    	$res = $this->validate($data,'Order');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $desert = new Desert;
        //获取当前的订阅时间
        $user = $desert->where(['uid'=>$data['uid'],'price_uid'=>$data['price_uid']])->find();
        if($user){
        	if(floor((strtotime($user['exp_time']) - time())/24/3600) > 30){
	        	return json(['status'=>'failed','data'=>'时间小于31天才可以延迟订阅时间']);
	        }else{
	        	$tmp = UserPrice::where(['uid'=>$data['price_uid'],'exp_time'=>$data['desert_time']])->Field('id,price')->find();
		        if($tmp){
		        	Db::startTrans();
		        	try {
		        		$data['time'] = date("Y-m-d H:i:s");
		        		$data['price_id'] = $tmp['id'];
		        		if($user['exp_time'] == "0000-00-00 00:00:00"){
		        			$data['exp_time'] = date("Y-m-d 23:59:59",strtotime("+{$data['desert_time']} month")-24*3600);
		        		}else{
		        			$extend = strtotime($user['exp_time']) -time();
		        			$data['exp_time'] = date("Y-m-d 23:59:59",$extend + strtotime("+{$data['desert_time']} month"));
		        		}
		        		unset($data['desert_time']);
		        		$data['price'] = $tmp['price'];
		        		$data['status'] = 1;
		        		$order = new Order;
		        		$orderNumber = $order->whereTime('time','today')->value('order_number');
		        		if($orderNumber){
		        			$data['order_number'] = date("Ymd").substr($orderNumber,8)+1;
		        		}else{
		        			$data['order_number'] = date("Ymd")."000001";
		        		}
		        		$order->where(['uid'=>$data['uid']])->order('id desc')->value('exp_time');
		        		$order->allowField(true)->save($data);
		        		//这里还差一个付费成功返回    -------------------------------
		        		$user['exp_time'] = $data['exp_time'];
		        		$user['time'] = date("Y-m-d H:i:s");
		        		$user['status'] = 1;
		        		$user = $user->toArray();
		        		$desert->update($user);
		        		Db::commit();
		        		return json(['status'=>'success','data'=>'订阅成功']);
		        	} catch (\Exception $e) {
		        		echo $e;
		        		Db::rollback();
		        		return json(['status'=>'failed','data'=>'订阅失败']);
		        	}
		        }else{
		        	return json(['status'=>'failed','data'=>'订阅模式错误']);
		        }
	        }
        }else{
        	$tmp = UserPrice::where(['uid'=>$data['price_uid'],'exp_time'=>$data['desert_time']])->Field('id,price')->find();
	        if($tmp){
	        	Db::startTrans();
	        	try {
	        		$data['time'] = date("Y-m-d H:i:s");
	        		$data['price_id'] = $tmp['id'];
	        		$data['exp_time'] = date("Y-m-d 23:59:59",strtotime("+{$data['desert_time']} month")-24*3600);
	        		unset($data['desert_time']);
	        		$data['price'] = $tmp['price'];
	        		$data['status'] = 1;
	        		$order = new Order;
	        		$orderNumber = $order->whereTime('time','today')->value('order_number');
	        		if($orderNumber){
	        			$data['order_number'] = date("Ymd").substr($orderNumber,8)+1;
	        		}else{
	        			$data['order_number'] = date("Ymd")."000001";
	        		}
	        		$order->where(['uid'=>$data['uid']])->order('id desc')->value('exp_time');
	        		$order->allowField(true)->save($data);
	        		//这里还差一个付费成功返回    -------------------------------
	        		
	        		$desert->allowField(true)->save($data);
	        		Db::commit();
	        		return json(['status'=>'suceess','data'=>'订阅成功']);
	        	} catch (\Exception $e) {
	        		Db::rollback();
	        		return json(['status'=>'failed','data'=>'订阅失败']);
	        	}
	        }else{
	        	return json(['status'=>'failed','data'=>'订阅模式错误']);
	        }
        }
   	}

   	/**
   	 * [cancel 取消订阅]
   	 * @return [type] [description]
   	 */
   	public function cancel(){
   		$this->_base->checkToken();
   		$data = input('post.');
   		$res = $this->validate($data,'Cancle');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $user = Desert::field('uid,price_uid')->where(['id'=>$data['id']])->find();
        if($user['uid'] == $data['uid']){
        	Db::startTrans();
        	try {
        		Desert::destroy($data['id']);
        		$user = $user->toArray();
        		Order::where($user)->update(['status'=>2]);
        		Db::commit();
        		return json(['status'=>'success','data'=>'取消订阅成功']);
        	} catch (\Exception $e) {
        		Db::rollback();
        		return json(['status'=>'failed','data'=>'取消订阅失败']);
        	}
        }else{
        	return json(['status'=>'failed','data'=>'非法请求']);
        }
   	}

   	/**
   	 * [getDesertList 获取用户订阅的列表]
   	 * @return [type] [description]
   	 */
   	public function getDesertList(){
   		$this->_base->checkToken();
   		$data = input('get.');
   		$limit = $this->_base->_limit;
   		$data['p'] = isset($data['p']) ? (int)$data['p'] > 0 ? $data['p'] : 1 : 1 ;
   		$list = Db::table('sjq_desert d')->join("sjq_users us","us.uid=d.price_uid")->Field('d.id,d.price_uid,us.username as price_username,d.exp_time,d.status')->where(['d.uid'=>$data['uid']])->order('time desc')->limit(($data['p']-1)*$limit,$limit)->select();
   		if($list){
   			return json(['status'=>'success','data'=>$list]);
   		}else{
   			return json(['status'=>'failed','data'=>[]]);
   		}
   	}

   	/**
   	 * [details 订单的列表]
   	 * @return [type] [description]
   	 */
   	public function orderList(){
   		$this->_base->checkToken();
   		$data = input('get.');
      if(!isset($data['price_uid'])){
          return json(['status'=>'failed','data'=>'必须传被订阅人的id']);
      }else{
        $limit = $this->_base->_limit;
        $data['p'] = isset($data['p']) ? (int)$data['p'] > 0 ? $data['p'] : 1 : 1 ;
        $list = @Db::table('sjq_order d')->join("sjq_users us","us.uid=d.price_uid")->join("sjq_desert o",'o.uid=d.uid and o.price_uid=d.price_uid')->Field('d.id,d.price_uid,us.username as price_username,d.exp_time,d.status,d.time,d.order_number,d.uid,o.id as t')->where(['d.price_uid'=>$data['price_uid']])->order('id desc')->limit(($data['p']-1)*$limit,$limit)->select();
        if($list){
          return json(['status'=>'success','data'=>$list]);
        }else{
          return json(['status'=>'failed','data'=>[]]);
        }
      }
   	}

   	/**
   	 * [details 订单的详情]
   	 * @return [type] [description]
   	 */
   	public function details(){
   		$this->_base->checkToken();
   		$data = input('get.');
   		if(!isset($data['id'])){
   			return json(['status'=>'failed','data'=>'id不能为空']);
   		}
   		$list = Db::table('sjq_order d')->join("sjq_users us","us.uid=d.price_uid")->Field('d.id,d.price_uid,us.username as price_username,d.exp_time,d.status,d.time,d.order_number')->where(['d.id'=>$data['id'],'d.uid'=>$data['uid']])->find();
   		if($list){
   			return json(['status'=>'success','data'=>$list]);
   		}else{
   			return json(['status'=>'failed','data'=>[]]);
   		}
   	}

   	/**
   	 * [getCattleTrack 获取牛人动态]
   	 * @return [type] [description]
   	 */
   	public function getCattleTrack(){
   		$this->_base->checkToken();
   		$data = input('get.');
      $limit = $this->_base->_limit;
      $data['p'] = isset($data['p']) ? (int)$data['p'] > 0 ? $data['p'] : 1 : 1 ;
      
   		$info = Desert::where(['uid'=>$data['uid'],'status'=>1])->Field('price_uid')->select();
   		if($info){
        if(!isset($data['price_uid'])){
          foreach ($info as $key => $value) {
            $tmp[] = $value['price_uid'];
          }
          $user = join(',',$tmp);
        }else{
          if(!Desert::where(['price_uid'=>$data['price_uid'],'uid'=>$data['uid'],'status'=>1])->find()){
            return json(['status'=>'failed','data'=>'还没有订阅']);
          }
          $user = $data['price_uid'];
        }
	   		
	   		$expert = Db::table('sjq_transaction t')->join('sjq_users u','t.uid=u.uid')->join('sjq_users_funds uf','u.uid=uf.uid')->join('sjq_users_position up','u.uid=up.uid AND t.stock=up.stock')->where(['t.status'=>1,'t.uid'=>['in',$user]])->Field('t.id,t.uid,t.stock,t.stock_name,u.username,t.price,t.time,t.type,uf.total_rate,up.ratio')->order('t.id desc')->limit(($data['p']-1)*$limit,$limit)->select();
	        foreach ($expert as $key => $value) {
	            $expert[$key]['avatar'] = $this->getAvatar($value['uid']);
	        }
	        if($expert){
	            return json(['status'=>'success','data'=>$expert]);
	        }else{
	            return json(['status'=>'failed','data'=>[]]);
	        } 
   		}else{
   			return json(['status'=>'failed','data'=>'还没有订阅']);
   		}
   	}
}
?>