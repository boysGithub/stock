<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\UserFunds;
/**
* 排行榜控制器
*/
class Rank extends Base
{
	protected $_base;
	public function __construct(){
		$this->_base =new Base();
	}
	/**
	 * [getTotalProfit 总盈利率排行榜]
	 * @return [type] [description]
	 */
	public function getTotalProfit(){
		$data = input('get.');
		$res = $this->totalProfitRank(true,$data);
		if($res){
			$result = json(['status'=>'success','data'=>$res]);
		}else{
			$result = json(['status'=>'failed','data'=>'数据不存在']);
		}
		return $result;
	}

	/**
	 * [totalProfitRank 总盈利排名处理方法]
	 * @return [array] [返回一个二维数组]
	 */
	public function totalProfitRank($tell=true,$data=null){
		$limit = $this->_base->_limit;
		$tmp = UserFunds::all();
        foreach ($tmp as $key => $value) {
            $f[$key] = $value['funds'];
            $tmp[$key]->append(['username']);
        }
        array_multisort($f,SORT_NUMERIC,SORT_DESC,$tmp);
		if($tell){
			$data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
			$count = count($tmp) < $data['p'] * $limit ? count($tmp) - ($data['p'] - 1) * $limit : $data['p'] * $limit;
			for ($i=($data['p']-1)*$limit; $i < $count; $i++) { 
				$tmpArr[$i] = $tmp[$i];
			}
			if($tmpArr){
        	$result = $tmpArr;
	        }else{
	        	$result = false;
	        }
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