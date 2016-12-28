<?php 
namespace app\desert\validate;

use think\Validate;

/**
* 所有的用户token验证
*/
class UserOrder extends Validate
{
	protected $rule = [
		['price','require|number|egt:0','定价不能为空|定价必须为数字|价格必须大于0'],
		['exp_time','require|number|egt:0','到期时间不能为空|到期时间必须为数字|时间不能为负数'],
	];
}
?>