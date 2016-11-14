<?php
namespace app\order\validate;

use think\Validate;

class GetUserInfo extends Validate{
	// 验证规则
    protected $rule = [
    	['type','require','获取数据类型不能为空'],
    	['stime','requireIf:type,historical|date|dateFormat:Y-m-d','获取历史委托数据必须传入时间|请输入有效的日期时间|日期的格式不对,采用Y-m-d'],
    	['stime','requireIf:type,trans|date|dateFormat:Y-m-d','获取历史成交数据必须传入时间|请输入有效的日期时间|日期的格式不对,采用Y-m-d'],
    	['etime','requireIf:type,historical|date|dateFormat:Y-m-d','获取历史委托数据必须传入时间|请输入有效的日期时间|日期的格式不对,采用Y-m-d'],
    	['etime','requireIf:type,trans|date|dateFormat:Y-m-d','获取历史成交数据必须传入时间|请输入有效的日期时间|日期的格式不对,采用Y-m-d'],
    ];
}