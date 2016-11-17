<?php
namespace app\common\model;

use think\model\Merge;

/**
* 持仓模型
*/
class UserPosition extends Merge
{

	// 设置主表名
    protected $name = 'users_position';
	// 定义关联模型列表
    // protected $relationModel = [
    //     // 给关联模型设置数据表
    //     'TransLog'   =>  'sjq_transaction_logs',
    // ];
    // 定义关联外键
    // protected $fk = 'pid';

    // protected $mapFields = [
    //     // 为混淆字段定义映射
    //     'id'        	=>  'User.id',
    //     'transLog_id' 	=>  'TransLog.id',
    //     'transLog_time' => 'TransLog.time',
    //     'transLog_fee' 	=> 'TransLog.fee',
    // ];

    protected function setStockAttr($value){
        $value = str_replace('s_','',$value);
        return str_replace('_i','',$value);
    }

}
?>