<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\Match as MatchModel;
use app\common\model\MatchUser;
use app\common\model\DaysRatio;
use app\common\model\WeeklyRatio;
use app\common\model\UserFunds;
use app\common\model\User;
/**
* 比赛控制器
*/
class Match extends Base
{
    protected $_base;
    public function __construct(){
        $this->_base = new Base();
    }
	/**
     * 比赛列表
     *
     * @return [json]
     */
    public function index()
    {
        $data = input('get.');
        $page = isset($data['np']) && (int)$data['np'] > 0 ? $data['np'] : 1;
        $limit = isset($data['limit']) && (int)$data['limit'] > 0 ? $data['limit'] : $this->_limit;

        $where = [];
        if(isset($data['type']) && in_array($data['type'], ['1','2'])){
            $where['type'] = intval($data['type']);
        }

        $matchs = MatchModel::where($where)->alias('m')->limit(($page-1)*$limit, $limit)->order('start_date desc');
        $field = '';
        if(isset($data['uid']) && $data['uid'] > 0){//登录后获取参加状态和排名
            $field .= ",u.id muid";
            if(isset($data['joined']) && $data['joined'] == 1){
                $where['u.id'] = ['not null',''];
            }
            $matchs->join('sjq_match_user u',"u.match_id=m.id AND u.uid={$data['uid']}", 'LEFT');
        }

        $matchs = MatchModel::where($where)->field('m.id,name,image,type,start_date,end_date'.$field)->select();
        $res = [];
        foreach ($matchs as $key => $val) {

        	if(time() >= strtotime($val['start_date']) && time() < strtotime($val['end_date']) + 24 * 3600){
        		$status = 1;
        		$status_name = '进行中';
        	} else if(time() >= strtotime($val['end_date']) + 24 * 3600){
        		$status = 3;
        		$status_name = '已结束';
        	}

            $match = [
                'id' => $val['id'],
                'name' => $val['name'],
                'image' => empty($val['image']) ? '' : Config('use_url.img_url').$val['image'],
                'type' => $val['type'],
                'start_date' => date('Y-m-d', strtotime($val['start_date'])),
                'end_date' => date('Y-m-d', strtotime($val['end_date'])),
                'status' => $status,
                'status_name' => $status_name,
            ];
            if(isset($val['muid']) && empty($val['muid'])){
                $match['ranking'] = 0;
                $match['joined'] = 0;
            } elseif (isset($val['muid'])) {    
                $ranking = 0;
                if($val['type'] == 1){//周赛
                    $user = MatchUser::where(['u.uid'=>$data['uid'], 'u.match_id'=>$val['id']])->alias('u')
                    ->field("(r.endFunds - r.initialCapital) / r.initialCapital total_rate,(SELECT count(uc.id) FROM sjq_match_user uc LEFT JOIN sjq_weekly_ratio rc ON uc.uid=rc.uid AND YEAR(rc.time)=YEAR(uc.join_time) AND MONTH(rc.time)=MONTH(uc.join_time) WHERE uc.match_id={$val['id']} AND (rc.endFunds - rc.initialCapital) / rc.initialCapital > total_rate)+1 ranking")
                    ->join("sjq_weekly_ratio r", "u.uid=r.uid AND YEAR(r.time)=YEAR(u.join_time) AND WEEK(r.time)=WEEK(u.join_time)", "left")
                    ->find();
                    $ranking = $user['ranking'];
                } elseif ($val['type'] == 2){//月赛
                    $user = MatchUser::where(['u.uid'=>$data['uid'], 'u.match_id'=>$val['id']])->alias('u')
                    ->field("(r.endFunds - r.initialCapital) / r.initialCapital total_rate,(SELECT count(uc.id) FROM sjq_match_user uc LEFT JOIN sjq_month_ratio rc ON uc.uid=rc.uid AND YEAR(rc.time)=YEAR(uc.join_time) AND MONTH(rc.time)=MONTH(uc.join_time) WHERE uc.match_id={$val['id']} AND (rc.endFunds - rc.initialCapital) / rc.initialCapital > total_rate)+1 ranking")
                    ->join("sjq_month_ratio r", "u.uid=r.uid AND YEAR(r.time)=YEAR(u.join_time) AND MONTH(r.time)=MONTH(u.join_time)", "left")
                    ->find();
                    $ranking = $user['ranking'];
                }

                $match['joined'] = 1;
                $match['ranking'] = $ranking;
            }

            $res[] = $match;
        }

        return json(['status'=>'success','data'=>$res]);
    }

    /**
     * 比赛详情
     *
     * @return [json]
     */
    public function detail()
    {
        $data = input('get.');
        $where = [];
        if(!isset($data['id'])){
            $where = [
                'type'=> !isset($data['type']) ? 1 : intval($data['type']),
                'start_date' => ['<=', date('Y-m-d')],
                'end_date' => ['>=', date('Y-m-d')]
            ];
        } else {
            $where = ['m.id'=> intval($data['id'])];
        }

        $page = isset($data['np']) && (int)$data['np'] > 0 ? $data['np'] : 1;
        $limit = isset($data['limit']) && (int)$data['limit'] > 0 ? $data['limit'] : 100;

        $match = MatchModel::where($where)->alias('m')->field('m.id,name,type,start_date,end_date')->find();
        if (empty($match)) {
            return json(['status'=>'failed','data'=>'比赛不存在']);
        }

        $where = "";
        $field = "mu.id,mu.uid,u.username,uf.success_rate,uf.week_avg_profit_rate,uf.avg_position_day,uf.total_rate,(dr.endFunds - dr.initialCapital) / dr.initialCapital * 100 days_rate,(wr.endFunds - wr.initialCapital) / wr.initialCapital * 100 week_rate,(mr.endFunds - mr.initialCapital) / mr.initialCapital * 100 month_rate";
        $order = "";
        //比赛排行
        if($match->type == 1){//周赛
            $where = " AND DATE_FORMAT(mrc.time,'%Y-%u')='" . date('Y-W', strtotime($match->start_date)) . "'";
            $field .= ",(SELECT count(muc.id) FROM sjq_match_user muc LEFT JOIN sjq_weekly_ratio wr ON muc.uid=wr.uid{$where} WHERE muc.match_id={$match['id']} AND (wr.endFunds - wr.initialCapital) / wr.initialCapital * 100 > week_rate)+1 ranking";
            $order = "week_rate DESC";
        } elseif ($match->type == 2){//月赛
            $where = " AND DATE_FORMAT(mr.time,'%Y-%m')='" . date('Y-m', strtotime($match->start_date)) . "'";
            $field .= ",(SELECT count(muc.id) FROM sjq_match_user muc LEFT JOIN sjq_month_ratio mr ON muc.uid=mr.uid{$where} WHERE muc.match_id={$match['id']} AND (mr.endFunds - mr.initialCapital) / mr.initialCapital * 100 > month_rate)+1 ranking";
            $order = "month_rate DESC";
        }
        $days_sql = DaysRatio::where("DATE_FORMAT(time,'%Y-%m')='" . date('Y-m', strtotime($match->start_date)) . "'")->field("uid,endFunds,initialCapital")->order('time DESC')->limit(1)->buildSql();
        $weekly_sql = WeeklyRatio::where("DATE_FORMAT(time,'%Y-%m')='" . date('Y-m', strtotime($match->start_date)) . "'")->field("uid,endFunds,initialCapital")->order('time DESC')->limit(1)->buildSql();
        $join = [
            ["sjq_users u", "mu.uid=u.uid", 'LEFT'],
            ["sjq_users_funds uf", "mu.uid=u.uid", 'LEFT'],
            [[$days_sql => "dr"], "mu.uid=dr.uid", 'LEFT'],
            [[$weekly_sql => "wr"], "mu.uid=wr.uid", 'LEFT'],
            ["sjq_month_ratio mr", "mu.uid=mr.uid AND DATE_FORMAT(mr.time,'%Y-%m')='" . date('Y-m', strtotime($match->start_date)) . "'", 'LEFT']
        ];
        $rankList = MatchUser::where(['match_id'=>$match->id])->alias('mu')
            ->field($field)
            ->join($join)
            ->limit(($page-1)*$limit, $limit)
            ->order($order)
            ->fetchSql(true)
            ->select();
        echo $rankList;die;
        $res = [
            'match'=>['id'=>$match->id,'name'=>$match->name,'joined'=>0,'total_rate'=>0,'ranking'=> 0],
        ];

        foreach ($rankList as $key => $val) {

            if(isset($data['uid']) && $data['uid'] > 0 && $val->uid == $data['uid']){//登录后获取参加状态和排名
                $res['match']['joined'] = 1;
                $res['match']['total_rate'] = $match->type == 1 ? $val->week_rate : $val->month_rate;
                $res['match']['ranking'] = $val->ranking;
            }

            $val->days_rate = round($val->days_rate * 100, 2);
            $val->week_rate = round($val->week_rate * 100, 2);
            $val->month_rate = round($val->month_rate * 100, 2);
            $val->total_rate = round($val->total_rate * 100, 2);
            $val->success_rate = round($val->success_rate * 100, 2);
            $val->week_avg_profit_rate = round($val->week_avg_profit_rate * 100, 2);
        }

        $res['rankList'] = $rankList;

        return json(['status'=>'success','data'=>$res]);
    }

    /**
     * 参加比赛
     *
     * @return [json]
     */
    public function join()
    {
        $this->_base->checkToken();
        $data = input('post.');
        $res = $this->validate($data,'Match.detail');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }

        $count = MatchUser::where(['uid'=>$data['uid'],'match_id'=>$data['id']])->count();
        if($count > 0){
            return json(['status'=>'failed','data'=>'已参加']);
        }

        $match = MatchModel::where(['id'=>$data['id']])->find();
        if(empty($match) || time() < strtotime($match['start_date']) || time() > strtotime($match['end_date'])+24*3600){
            return json(['status'=>'failed','data'=>'不可参加']);
        }
        
        $user = User::where(['uid'=>$data['uid']])->alias('u')->field('uid,username')->find();
        if(empty($user)){
            return json(['status'=>'failed','data'=>'无效的用户']);
        }

        $match_user = MatchUser::create([
            'match_id'=>$match['id'], 
            'uid'=> $user['uid'],
            'user_name'=> $user['username'],
            ]);
        
        if(empty($match_user->id)){
            return json(['status'=>'failed','data'=>'添加失败']);
        }

        return json(['status'=>'success','data'=>'添加成功']);
    }
}
?>