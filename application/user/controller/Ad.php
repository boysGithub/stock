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
        $types = [1,2];
        if (!in_array($type, $types)) {
            return json(['status'=>'failed','data'=>'参数错误']);
        }

        $ads = AdModel::where(['type'=>$type, 'enabled'=>1])->field('image,title,url')->select();

        $res = [];
        if(is_https()){
            $imgAttr = Config('use_url.img_url');
        }else{
            $imgAttr = Config('use_url.img_url_http');
        }
        foreach ($ads as $key => $val) {
            $res[] = [
                'image'=>empty($val['image']) ? '' :  . $val['image'],
                'title' => $val['title'],
                'url' => $val['url']
            ];
        }

        return json(['status'=>'success','data'=>$res]);
    }
}
?>