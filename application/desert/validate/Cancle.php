<?php 
namespace app\desert\validate;

use think\Validate;

/**
* 所有的用户token验证
*/
class Cancle extends Validate
{
	protected $rule = [
		['id','require|number','订阅id不能为空|订阅id必须为数字'],
	];
}
?>