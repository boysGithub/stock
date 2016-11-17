<?php 
namespace app\common\model;

use think\Model;

/**
* 订单模型
*/
class Transaction extends Model
{
	
	protected function setStockAttr($value){
        $value = str_replace('s_','',$value);
        return str_replace('_i','',$value);
    }
    
    protected function getUsernameAttr($username){
    	return $this->user->username;
    }


    protected function getStatusNameAttr($value,$data){
    	$status = [0=>'待交易',1=>'交易成功',2=>'撤单成功'];
        return $status[$data['status']];
    }

    // 定义关联方法
    public function user()
    {
    	return $this->hasOne('User','uid','uid');
    }
}
?>