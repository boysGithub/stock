<?php 
namespace app\auto\validate;

use think\Validate;

/**
* 
*/
class AutoUpdateRank extends Validate
{
	protected $rule = [
		['condition','require|in:total_rate,success_rate,week_avg_profit_rate','排序字段不能为空|排序的值不在指定范围内'],
		['sorts','require|number','账户类型不能为空|必须是数字'],
		['rankFiled','require','更新的字段不能为空'],
	];
}