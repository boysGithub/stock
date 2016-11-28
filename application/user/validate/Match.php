<?php 
namespace app\user\validate;

use think\Validate;
/**
* 比赛验证
*/
class Match extends Validate
{
	protected $rule = [
		['id','require|number','比赛id不能为空|必须为数字'],
		['uid','require|number','用户id不能为空|必须为数字'],
		['name','require','比赛名称不能为空'],
		['start_date','require|date','开始日期不能为空|必须为日期'],
		['end_date','require|date','结束日期不能为空|必须为日期'],
		['initial_capital','require|number','初始资金不能为空|必须为数字'],
		['period','in:days,month,weekly','周期必须在days,month,weekly中'],
	];

	protected $scene = [
        'match'  =>  ['id'],
        'detail'  =>  ['match_id','id'],
        'add' => ['name','start_date','end_date','initial_capital'],
        'ranking'  =>  ['id','period'],
    ];
}
?>