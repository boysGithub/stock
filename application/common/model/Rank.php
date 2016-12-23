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
	public function updateRank($condition,$sorts,$rankFiled){
		// 启动事务
		Db::startTrans();
		try {
			$sql = "UPDATE `sjq_users_funds` as a,(SELECT obj.uid, obj.{$condition},
				@rownum := @rownum + 1 AS num_tmp,
				        @incrnum := CASE
				        WHEN @rowtotal = obj.{$condition} THEN
				            @incrnum
				        WHEN @rowtotal := obj.{$condition} THEN
				            @rownum
				        WHEN @rowtotal = 0 THEN
				        	@rownum
				        END AS rownum
				FROM (SELECT uid, {$condition}
					FROM `sjq_users_funds`
					WHERE sorts = {$sorts} AND is_trans = 1
					ORDER BY {$condition} DESC
					) obj,(select @rownum := 0) r
					order by obj.{$condition} desc) new_obj
				SET a.{$rankFiled} = new_obj.rownum WHERE a.uid = new_obj.uid";
				Db::query($sql);
				// 提交事务
    			Db::commit();
    			return TRUE;
		} catch (\Exception $e) {
			// 回滚事务
    		Db::rollback();
    		return $e;
		}		
	}
}
?>