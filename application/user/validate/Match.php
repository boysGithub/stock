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
	];

	protected $scene = [
        'match'  =>  ['id'],
        'detail'  =>  ['id', 'uid'],
    ];
}
?>