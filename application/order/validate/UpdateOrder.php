<?php 
namespace app\order\validate;

use think\Validate;

/**
* 用户订单更新验证规则
*/
class UpdateOrder extends Validate
{
	//验证规则
	protected $rule = [
		['id','require|number','订单的号不能空|订单号只能为数字'],
		['status','require|number|in:2','更新状态不能为空|更新状态值只能是数字|更新状态的值只能为2'],

	];
}
?>