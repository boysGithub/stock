<?php 
namespace app\common\model;

use think\Model;
use app\common\model\Desert;

/**
* 消息模型
*/
class Message extends Model
{
	protected $name = "message";

	//给订阅的人发送交易动态消息
	public function desertMsgSend($param)
	{
		$deserts = Desert::where(['price_uid'=>$param['uid'], 'status'=> 1])->field('uid,exp_time')->select();
		if(empty(count($deserts))){
			return false;
		}
		
		$data = [];
		foreach ($deserts as $key => $value) {
			if(time() < strtotime($value->exp_time)){
				$data[] = [
					'send'=>$param['uid'], 
					'addressee'=>$value->uid, 
					'type'=>1,
					'title'=>$param['title'], 
					'content'=>$param['content'],
					'read'=>0,
				];
			}
		}
		
		$this->saveAll($data);

		return true;
	}
}
?>