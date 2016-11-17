<?php 
namespace app\user\validate;

use think\Validate;
/**
* 用户持仓验证
*/
class UserPosition extends Validate
{
	protected $rule = [
		['uid','require|number','用户id不能为空|必须为数字'],
	];
}
?>