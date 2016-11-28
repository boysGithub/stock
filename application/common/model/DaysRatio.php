<?php 
namespace app\common\model;

use think\Model;

/**
* 每天统计模型
*/
class DaysRatio extends Model
{
	protected $name = "days_ratio";

	protected function getTimeAttr($time){
		return date("Y-m-d",strtotime($time));
	}
}
?>