<?php 
namespace app\order\validate;

use think\Validate;

/**
* 自选股验证
*/
class GetTimeChart extends Validate
{
	protected $rule = [
		['uid','require|number','用户id不能为空|必须为数字'],
	];
}
?>