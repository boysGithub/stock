<?php 
namespace app\desert\validate;

use think\Validate;

/**
* 所有的用户token验证
*/
class isDesert extends Validate
{
	protected $rule = [
		['uid','require|number','用户id不能为空|用户id必须为数字'],
		['price_uid','require|number','被订阅的用户id不能为空|被订阅的用户id必须为数字'],
	];
}
?>