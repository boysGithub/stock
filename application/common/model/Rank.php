<?php 
namespace app\common\model;

use think\Model;
use think\Db;
/**
* 排名模型
*/
class Rank extends Model
{
	/**
	 * [getTotalProfitRank 得到总盈利率排名]
	 * @return [array] [返回排名数据]
	 */
	public function getTotalProfitRank($p,$condition='total_rate',$sorts=1){
		$limit = 50;
		$p = ($p-1)*$limit;
		$sql = "SELECT obj.uid, obj.total_rate,u.username,obj.success_rate,obj.avg_position_day,obj.week_avg_profit_rate,
				@rownum := @rownum + 1 AS num_tmp,
				        @incrnum := CASE
				        WHEN @rowtotal = obj.{$condition} THEN
				            @incrnum
				        WHEN @rowtotal := obj.{$condition} THEN
				            @rownum
				        END AS rownum
				FROM (SELECT uid, total_rate ,success_rate,avg_position_day,week_avg_profit_rate
					FROM `sjq_users_funds`
					WHERE sorts = {$sorts}
					ORDER BY total_rate DESC
					) obj,(select @rownum := 0) r,sjq_users as u
					where obj.uid=u.uid
					order by obj.{$condition} desc limit {$p},{$limit};";
		return Db::query($sql);			
	}
}
?>