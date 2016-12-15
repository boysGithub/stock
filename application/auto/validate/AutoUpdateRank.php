<?php 
namespace app\auto\validate;

use think\Validate;

/**
* 
*/
class AutoUpdateRank extends Validate
{
	protected $rule = [
		['condition','require|in:total_rate,success_rate,week_avg_profit_rate,fans','排序字段不能为空|排序的值不在指定范围内'],
		['sorts','require|number','账户类型不能为空|必须是数字'],
		['rankFiled','require|in:total_profit_rank,success_rank,week_avg_rank,fans_rank','更新的字段不能为空|更新的值不在指定范围内'],
	];
}