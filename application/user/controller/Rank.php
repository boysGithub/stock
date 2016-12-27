<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\UserFunds;
use app\common\model\UserPosition;
use app\common\model\User;
use think\Db;
use app\common\model\Rank as RankModel;
use app\common\model\DaysRatio;
use app\common\model\WeeklyRatio;
use app\common\model\MonthRatio;
use app\common\model\NoTrande;
use think\cache\driver\Redis;
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
			'week_avg_profit_rate' => 'week_avg_rank',
			'fans' => 'fans_rank'
		];
		$redis = new Redis;
		$uid = $redis->get($data['condition']);
		$days_sql = DaysRatio::where('uid=uf.uid')->field("(endFunds - initialCapital) / initialCapital * 100")->order('time DESC')->limit(1)->buildSql();
        $weekly_sql = WeeklyRatio::where('uid=uf.uid')->field("(endFunds - initialCapital) / initialCapital * 100")->order('time DESC')->limit(1)->buildSql();
        $month_sql = MonthRatio::where("uid=uf.uid")->field("(endFunds - initialCapital) / initialCapital * 100")->order('time DESC')->limit(1)->buildSql();
		if($uid){
			$rankList = Userfunds::where(['uf.uid'=>['in',$uid],'is_trans'=>1])->alias('uf')->order("{$tmp[$data['condition']]} asc")->limit(($data['p']-1)*$limit,$limit)->Field("uf.uid,total_rate,{$days_sql} days_rate, {$weekly_sql} week_rate, {$month_sql} month_rate,success_rate,avg_position_day,week_avg_profit_rate,round((funds-available_funds)/funds*100,2) as position,{$tmp[$data['condition']]} as rownum,fans,account")->select();
		}else{
			$rankList = Userfunds::where(['is_trans'=>1])->order("{$tmp[$data['condition']]} asc")->alias('uf')->limit(($data['p']-1)*$limit,$limit)->Field("uf.uid,total_rate,{$days_sql} days_rate, {$weekly_sql} week_rate, {$month_sql} month_rate,success_rate,avg_position_day,week_avg_profit_rate,round((funds-available_funds)/funds*100,2) as position,{$tmp[$data['condition']]} as rownum,fans,account")->select();
		}
		foreach ($rankList as $key => $value) {
			$value->append(['username']);
            $value->days_rate = round($value->days_rate, 2);
            $value->week_rate = round($value->week_rate, 2);
            $value->month_rate = round($value->month_rate, 2);
            $value->total_rate = round($value->total_rate, 2);
            $value->success_rate = round($value->success_rate, 2);
            $value->week_avg_profit_rate = round($value->week_avg_profit_rate, 2);
			$value->avatar = $this->getAvatar($value->uid);
		}
		if($rankList){
			$result = json(['status'=>'success','data'=>$rankList]);
		}else{
			$result = json(['status'=>'failed','data'=>'数据数据不存在']);
		}
		return $result;
	}

	/**
	 * [rateRank 盈利率排名]
	 * @return json
	 */
	public function rateRank()
	{
		$data = input('get.');
		$page = isset($data['p']) && $data['p'] > 0 ? intval($data['p']) : 1;
		$limit = isset($data['limit']) && $data['limit'] > 0 && $data['limit'] < 1000 ? intval($data['limit']) : $this->_base->_limit;

		$date = $this->getRecentTradeDay();
		$ranking_sql = DaysRatio::where("DATE_FORMAT(time,'%Y-%m-%d')='{$date}' AND (endFunds - initialCapital) / initialCapital * 100 > days_rate")->field('count(id)')->buildSql();

		$join = [
            ["sjq_users u", "dr.uid=u.uid", 'LEFT'],
            ["sjq_users_funds uf", "dr.uid=uf.uid", 'LEFT'],
            ["sjq_weekly_ratio wr", "dr.uid=wr.uid AND DATE_FORMAT(wr.time,'%Y-%u')='" . date('Y-W', strtotime($date)) . "'", 'LEFT'],
            ["sjq_month_ratio mr", "dr.uid=mr.uid AND DATE_FORMAT(mr.time,'%Y-%m')='" . date('Y-m', strtotime($date)) . "'", 'LEFT'],
		];

		$count = DaysRatio::where("DATE_FORMAT(dr.time,'%Y-%m-%d')='{$date}'")->alias('dr')->join($join)->count();
        $page_total = ceil($count / $limit);

		$rankList = DaysRatio::where("DATE_FORMAT(dr.time,'%Y-%m-%d')='{$date}'")->alias('dr')
			->field("dr.id,u.uid,u.username,uf.success_rate,uf.week_avg_profit_rate,uf.avg_position_day,uf.total_rate,(dr.endFunds - dr.initialCapital) / dr.initialCapital * 100 days_rate,(wr.endFunds - wr.initialCapital) / wr.initialCapital * 100 week_rate,(mr.endFunds - mr.initialCapital) / mr.initialCapital * 100 month_rate,{$ranking_sql}+1 ranking")
			->join($join)
			->where(['is_trans'=>1])
            ->limit(($page-1)*$limit, $limit)
			->order("days_rate DESC,dr.id DESC")
			->select();
			
		foreach ($rankList as $key => $value) {
			$value->avatar = $this->getAvatar($value->uid);
			$value->days_rate = round($value->days_rate, 2);
            $value->week_rate = round($value->week_rate, 2);
            $value->month_rate = round($value->month_rate, 2);
            $value->total_rate = round($value->total_rate, 2);
            $value->success_rate = round($value->success_rate, 2);
            $value->week_avg_profit_rate = round($value->week_avg_profit_rate, 2);
		}	

		return json(['status'=>'success', 'data'=>$rankList, 'pageTotal' => $page_total]);
	}

	/**
	 * [dayRateRank 日盈利率排名]
	 * @return [type] [description]
	 */
	public function dayRateRank(){
		$limit = $this->_base->_limit;
		$data = input('get.');
		$data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
		$w = date('w');
		if($w == 6 || $w == 0){
			$dtime = date('Y-m-d 00:00:00',strtotime("last Friday"));
		}else{
			$dtime = date('Y-m-d 00:00:00');
		}
		if($w == 0){
			$wtime = date('Y-m-d 00:00:00',strtotime('+'. 1-7-$w .' days' ));
		}else{
			$wtime = date('Y-m-d 00:00:00',strtotime('+'. 1-$w .' days' ));
		}
		$mtime = date('Y-m-1 00:00:00');
		switch ($data['type']) {
			case 'days':
				$order = "d.proportion";
				$uid = "d.uid";
				
				break;
			case 'week':
				$order = "w.proportion";
				$uid = "w.uid";
				
				$totalPage = ceil(WeeklyRatio::whereTime('time','week')->count()/$limit);
				break;
			case 'month':
				$order = "m.proportion";
				$uid = "m.uid";
				$totalPage = ceil(MonthRatio::whereTime('time','month')->count()/$limit);
				break;
		}
		
		
		// $tmp = Db::table('sjq_days_ratio d')->join('sjq_weekly_ratio w','d.uid=w.uid')->join('sjq_month_ratio m','d.uid=m.uid')->join('sjq_users u','u.uid='.$uid)->join('sjq_users_funds f',$uid.'=f.uid')->Field("d.uid,d.proportion as day,w.proportion as week,m.proportion as month,u.username,f.total_rate,f.success_rate")->order("{$order} desc")->whereTime('m.time','>',$time)->group("{$uid}")->select();
		dump($t);
		echo Db::getLastSql();exit;

		
		// $weekInfo = WeeklyRatio::whereTime('time','week')->order('proportion desc')->Field("uid,round(endFunds-{$fund}/$fund*100,2) as endFunds")->limit(($data['p']-1)*$limit,$limit)->select();
		// $monthInfo = MonthRatio::whereTime('time','month')->order('proportion desc')->Field("uid,round(endFunds-{$fund}/$fund*100,2) as endFunds")->limit(($data['p']-1)*$limit,$limit)->select();
		return json(['status'=>'success','data'=>$tmp,'totalPage'=>$totalPage]);
	}

	/**
	 * [weekRateRank 周盈利率排名]
	 * @return [type] [description]
	 */
	public function weekRateRank(){
		$limit = $this->_base->_limit;
		$data = input('get.');
		$data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
		$totalPage = ceil(WeeklyRatio::whereTime('time','week')->count()/$limit);
		
		if($weekInfo){
			return json(['status'=>'success','data'=>$weekInfo,'totalPage'=>$totalPage]);
		}else{
			return json(['status'=>'failed','data'=>'获取数据失败']);
		}
	}

	/**
	 * [monthRateRank 月盈利率排名]
	 * @return [type] [description]
	 */
	public function monthRateRank(){
		$limit = $this->_base->_limit;
		$data = input('get.');
		$data['p'] = isset($data['p']) ? $data['p'] > 0 ? $data['p'] : 1 : 1 ;
		$totalPage = ceil(MonthRatio::whereTime('time','month')->count()/$limit);
		$monthInfo = MonthRatio::whereTime('time','month')->order('proportion desc')->limit(($data['p']-1)*$limit,$limit)->select();
		if($monthInfo){
			return json(['status'=>'success','data'=>$monthInfo,'totalPage'=>$totalPage]);
		}else{
			return json(['status'=>'failed','data'=>'获取数据失败']);
		}
	}	

	/**
	 * 获取最近一个交易日
	 * @param string $date 日期
	 * @return string
	 */
	public function getRecentTradeDay($date = '')
	{
		if(empty($date)){
			$date = date('Y-m-d');
		}

		if(date("w",strtotime($date)) == '0' || date("w",strtotime($date)) == '6'){
			$date = date('Y-m-d', strtotime($date . 'last Friday'));
		}

		$no_trade_day = NoTrande::where([])->column("DATE_FORMAT(day,'%Y-%m-%d')");
		if(in_array($date, $no_trade_day)){//节假日
			$date = date('Y-m-d', strtotime($date . '-1 day'));
			$this->getRecentTradeDay($date);
		}

		return $date;
	}
}
?>