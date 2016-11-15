<?php
namespace app\order\validate;

use think\Validate;

class SaveOrder extends Validate
{
    // 验证规则
    protected $rule = [
        ['uid','require|number','用户id不能为空|必须为数字'],
        ['stock','require|number|length:6','股票代码不能为空|请输入正确的股票代码|股票代码必须是6位数字'],
        ['price','require|float|number','价格不能为空|请输入正确的数字|请输入正确的数字'],
        ['number','require|number|egt:99','数量不能为空|请输入正确的数字|购买数量至少为100股'],
        ['type','require|number|in:1,2','购买类型不能为空|请输入正确的数字|只能输入1和2'],
        ['sorts','require|number|egt:0','账户类型不能为空|请输入正确的数字|必须大于0'],
        ['isMarket','require|number|in:1,2','下单方式不能为空|请输入正确的数字|只能在1和2之间'],
    ];
}