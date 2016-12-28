<?php 
namespace app\desert\validate;

use think\Validate;

/**
* 所有的用户token验证
*/
class Order extends Validate
{
	protected $rule = [
		['desert_time','require|number|egt:0','到期时间不能为空|到期时间必须为数字|时间不能为负数'],
		['uid','require|number','用户id不能为空|用户id必须为数字'],
		['price_uid','require|number','被订阅的用户id不能为空|被订阅的用户id必须为数字'],
	];
}
?>