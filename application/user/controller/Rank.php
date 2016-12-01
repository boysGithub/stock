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
	protected $_base;
    public function __construct(){
        $this->_base = new Base();
    }
	/**
	 * [getRankList 牛人排行榜]
	 * @return [json] [返回获取数据的json]
	 */
	public function getRankList(){
		$data = input('get.');
		$res = $this->validate($data,'Rank');
		if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        $limit = isset($data['limit']) ? ($data['limit'] <= 100) ? $data['limit'] : 100 : 100; 
		$data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
		//兼容以前的接口地址
		$tmp = [
			'total_rate' => 'total_profit_rank',
			'success_rate' => 'success_rank',
			'week_avg_profit_rate' => 'week_avg_rank'
		];
		$rankList = Userfunds::order("{$tmp[$data['condition']]} asc")->limit(($data['p']-1)*$limit,$limit)->Field("uid,total_rate,success_rate,avg_position_day,week_avg_profit_rate,round((funds-available_funds)/funds*100,2) as position,{$tmp[$data['condition']]} as rownum")->select();
		if($rankList){
			$result = json(['status'=>'success','data'=>$rankList]);
		}else{
			$result = json(['status'=>'failed','data'=>'获取数据失败，请确认你的获取条件是否正确']);
		}
		return $result;
	}	
}
?>