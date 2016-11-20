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
		$this->_base =new Base();
	}

	// /**
	//  * [testData description]
	//  * @return [type] [description]
	//  */
	// public function testData(){
	// 	Db::startTrans();
	// 	for ($i=900001; $i <= 1000000; $i++) {
	// 		try{
	// 	    	$data['uid'] = $i;
	// 			$data['username'] = 'test'.$i;
	// 			User::create($data);
	// 			$da['uid'] = $i;
	// 			$da['funds'] = 1000000 - rand(0,10000);
	// 			$da['time'] = date('Y-m-d H:i:s',time());
	// 			$da['total_rate'] = rand(0,1000000)/100;
	// 			$da['success_rate'] = rand(0,10000)/100;
	// 			$da['week_avg_profit_rate'] = rand(0,10000)/100;
	// 			UserFunds::create($da);
	// 		    // 提交事务
	// 		    Db::commit();    
	// 		} catch (\Exception $e) {
	// 		    // 回滚事
	// 		    Db::rollback();
	// 		}	
	// 	}
	// }

	/**
	 * [getTotalProfit 总盈利率牛人排行榜]
	 * @return [type] [description]
	 */
	public function getRankList(){
		$limit = $this->_base->_limit;
		$stockFunds = $this->_base->_stockFunds;
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