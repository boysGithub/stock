<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\UserFunds;
use app\common\model\UserPosition;
use app\common\model\User;
use think\Db;
use app\common\model\Rank as RankModel;
/**
* 排行榜控制器
*/
class Rank extends Base
{
	/**
	 * [getTotalProfit 总盈利率牛人排行榜]
	 * @return [json] [返回获取数据的json]
	 */
	public function getRankList(){
		//这里的分页 limit 在model 里面
		$data = input('get.');
		$res = $this->validate($data,'Rank');
		if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
		// $data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
		$rank = new RankModel();
		$rankList = $rank->getTotalProfitRank($data['condition']);
		if($rankList){
			$result = json(['status'=>'success','data'=>$rankList]);
		}else{
			$result = json(['status'=>'failed','data'=>'获取数据失败，请确认你的获取条件是否正确']);
		}
		return $result;
	}	
}
?>