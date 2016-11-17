<?php 
namespace app\common\model;

use think\Model;

/**
* 用户模型
*/
class UserFunds extends Model
{
	
	protected $name = 'users_funds';
	
	protected function getUsernameAttr($username){
        return $this->user->username;
    }
    
    // 定义关联方法
    public function user()
    {
    	return $this->hasOne('User','uid','uid');
    }
}
?>