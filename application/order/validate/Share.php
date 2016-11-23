<?php 
namespace app\order\validate;

use think\Validate;

/**
* 自选股验证
*/
class Share extends Validate
{
	protected $rule = [
		['uid','require|number','用户id不能为空|必须为数字'],
		['stock','require|number|length:6','股票代码不能为空|请输入正确的股票代码|股票代码必须是6位数字'],
		['stime','require|date|dateFormat:Y-m-d','开始时间不能为空|请输入有效的日期时间|日期的格式不对,采用Y-m-d'],
		['etime','require|date|dateFormat:Y-m-d','结束时间不能为空|请输入有效的日期时间|日期的格式不对,采用Y-m-d'],
	];
}
?>