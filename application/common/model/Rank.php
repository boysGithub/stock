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
	public function getTotalProfitRank($condition='total_rate',$sorts=1){
		$sql = "SELECT obj.uid, obj.total_rate,u.username,obj.success_rate,obj.avg_position_day,obj.week_avg_profit_rate,round((obj.funds-obj.available_funds)/obj.funds*100,2) AS position,
				@rownum := @rownum + 1 AS num_tmp,
				        @incrnum := CASE
				        WHEN @rowtotal = obj.{$condition} THEN
				            @incrnum
				        WHEN @rowtotal := obj.{$condition} THEN
				            @rownum
				        END AS rownum
				FROM (SELECT uid, total_rate ,success_rate,funds,available_funds,avg_position_day,week_avg_profit_rate
					FROM `sjq_users_funds`
					WHERE sorts = {$sorts}
					ORDER BY total_rate DESC
					) obj,(select @rownum := 0) r,`sjq_users` as u
					where obj.uid=u.uid
					order by obj.{$condition} desc";
		return Db::query($sql);			
	}

	
}
?>