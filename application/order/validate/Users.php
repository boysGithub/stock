<?php
namespace app\order\validate;

use think\Validate;

class Users extends Validate
{
	//protected $name = "users_position";
    // 验证规则
    protected $rule = [
        ['uid','require|number','用户id不能为空|必须为数字'],
        ['stock','require|number|length:6','股票代码不能为空|请输入正确的股票代码|股票代码必须是6位数字'],
        ['price','require|float|number','价格不能为空|请输入正确的数字'],
        ['number','require|number|egt:99','数量不能为空|请输入正确的数字|购买数量至少为100股'],
        ['type','require|number','购买类型不能为空|请输入正确的数字'],
        ['sorts','require|number','账户类型不能为空|请输入正确的数字'],
    ];
}