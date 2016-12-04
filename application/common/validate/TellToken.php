<?php 
namespace app\common\validate;

use think\Validate;

/**
* 所有的用户token验证
*/
class TellToken extends Validate
{
	protected $rule = [
		['token','require|alphaNum','token不能为空|不能有特殊字符'],
		['uid','require|number','用户id不能为空|用户id只能是数字'],
	];
}
?>