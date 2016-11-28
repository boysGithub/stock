<?php 
namespace app\common\model;

use think\Model;
use think\Db;

/**
* 参赛用户模型
*/
class MatchUser extends Model
{
	protected $name = "match_user";

	/**
	 * 取得排名	
	 * @param $period 周期[days|month|weekly]
	 * @param $id 比赛id
	 * @param $sort 排序方式
	 * @return [array] [返回排名数据]
	 */
	public static function getRinking($id,$period='days',$sort='DESC',$limit='100'){
		$sql = "SELECT u.uid,u.user_name,
			((d.endFunds - d.initialCapital) / d.initialCapital) as days_rate,
			((w.endFunds - w.initialCapital) / w.initialCapital) as weekly_rate,
			((m.endFunds - m.initialCapital) / m.initialCapital) as month_rate,
			(SELECT count(id) FROM sjq_{$period}_ratio WHERE ((endFunds - initialCapital) / initialCapital) > {$period}_rate) + 1 as ranking 
			FROM sjq_match_user as u 
			INNER JOIN sjq_days_ratio as  d ON u.match_id=d.periods AND u.uid=d.uid 
			INNER JOIN sjq_weekly_ratio as w ON u.match_id=w.periods AND u.uid=w.uid 
			INNER JOIN sjq_month_ratio as m ON u.match_id=m.periods AND u.uid=m.uid 
			WHERE u.match_id={$id} 
			ORDER BY {$period}_rate {$sort}
			LIMIT {$limit}";
		return Db::query($sql);			
	}

	/**
	 * 更新总资产	
	 * @param $id 比赛id
	 * @return [bool] [更新结果]
	 */
	public static function settlement($id)
	{
		$sql = "UPDATE sjq_days_ratio d, sjq_match_user u 
				SET d.endFunds=(SELECT balance FROM sjq_match_user WHERE uid=d.uid) + (SELECT IFNULL(SUM(available_number * cost_price), 0) FROM sjq_users_position WHERE uid=d.uid) 
				WHERE u.uid=d.uid AND periods={$id}";
		return Db::query($sql);
	}
}
?>