<?php 
namespace app\user\controller;

use think\Request;
use app\index\controller\Base;
use app\common\model\Advertisement as AdModel;
/**
* 广告控制器
*/
class Ad extends Base
{
	public function index($type = 1)
    {
        $types = [1,2,3,4,5];
        if (!in_array($type, $types)) {
            return json(['status'=>'failed','data'=>'参数错误']);
        }

        $ads = AdModel::where(['type'=>$type, 'enabled'=>1])->field('image,title,url,time')->order('sort ASC,time DESC')->select();

        $res = [];
        foreach ($ads as $key => $val) {
            $res[] = [
                'image'=>empty($val->image) ? '' :  Config('use_url.img_url'). $val->image,
                'title' => $val->title,
                'time' => $val->time,
                'url' => $val->url
            ];
        }

        return json(['status'=>'success','data'=>$res]);
    }
}
?>