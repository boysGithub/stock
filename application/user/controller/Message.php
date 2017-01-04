<?php 
namespace app\user\controller;

use think\Request;
use app\index\controller\Base;
use app\common\model\Message as MsgModel;
/**
* 消息控制器
*/
class Message extends Base
{
	public function unread()
    {
        $this->_base->checkToken();
        $type = input('get.type', 1);
        $uid = input('get.uid', 0);
        if (empty($uid)) {
            return json(['status'=>'failed','data'=>'参数错误']);
        }

        $count = MsgModel::where(['type'=>$type, 'read'=>0, 'addressee'=>$uid])->count();

        return json(['status'=>'success','data'=>intval($count)]);
    }
}
?>