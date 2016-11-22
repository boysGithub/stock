<?php 
namespace app\user\validate;

use think\Validate;

/**
* 自选股验证
*/
class OptionalStock extends Validate
{
	protected $rule = [
		['uid','require|number','用户id不能为空|必须为数字'],
		['stock','require|number|length:6','股票代码不能为空|请输入正确的股票代码|股票代码必须是6位数字'],
	];
}
?>