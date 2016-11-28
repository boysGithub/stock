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