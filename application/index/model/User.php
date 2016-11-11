<?php 
namespace app\index\model;

use think\model\Merge;

class User extends Merge
{
    // 定义关联模型列表
    protected $relationModel = ['Profile'];
    // 定义关联外键
    protected $fk = 'user_id';
    protected $mapFields = [
        // 为混淆字段定义映射
        'id'        =>  'User.id',
        'profile_id' =>  'Profile.id',
    ];
}

?>