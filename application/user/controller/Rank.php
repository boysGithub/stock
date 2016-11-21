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
		$data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
		$rank = new RankModel();
		$rankList = $rank->getTotalProfitRank($data['p'],$data['condition']);
		if($rankList){
			$result = json(['status'=>'success','data'=>$rankList]);
		}else{
			$result = json(['status'=>'failed','data'=>'获取数据失败，请确认你的获取条件是否正确']);
		}
		return $result;
	}

	/**
	 * [totalProfitRank 总盈利排名处理方法]
	 * @return [array] [返回一个二维数组]
	 */
	public function totalProfitRank($tell=true,$data=null){
		
		
		
		$userFunds = new UserFunds();
		$tmp = $userFunds->Field('id,uid,funds,available_funds,sorts')->order('funds desc')->select();
		return $tmp;
        foreach ($tmp as $key => $value) {
            $f[$key] = $value['funds'];
            $tmp[$key]->append(['username']);
        }
        array_multisort($f,SORT_NUMERIC,SORT_DESC,$tmp);
		if($tell){
			
			
			
				
				// if($tmpArr){
	   //      		$result = $tmpArr; 
		  //       }else{
		  //       	$result = false;
		  //       }
			
		}else{
			if($tmp){
        		$result = $tmp;
	        }else{
	        	$result = false;
	        }
		}
        return $result;
	}

	
}
?>