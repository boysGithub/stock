<?php 
namespace app\user\validate;

use think\Validate;

/**
* 排名验证
*/
class Rank extends Validate
{
	protected $rule = [
		['condition','require|in:total_rate,success_rate,week_avg_profit_rate','排序字段不能为空|排序的值不在指定范围内'],
	];
}
?>