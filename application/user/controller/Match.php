<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\Match as MatchModel;
use app\common\model\MatchUser;
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
                'start_date' => $val['start_date'],
                'end_date' => $val['end_date'],
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

        //比赛排行
        $rankList = MatchUser::where(['match_id'=>$match['id']])->alias('u')
            ->limit(($page-1)*$limit, $limit)->order('total_rate desc');
        if($match['type'] == 1){//周赛
            $rankList->field("u.id,u.uid,u.user_name,(r.endFunds - r.initialCapital) / r.initialCapital total_rate,(SELECT count(uc.id) FROM sjq_match_user uc LEFT JOIN sjq_weekly_ratio rc ON uc.uid=rc.uid AND YEAR(rc.time)=YEAR(uc.join_time) AND MONTH(rc.time)=MONTH(uc.join_time) WHERE uc.match_id={$match['id']} AND (rc.endFunds - rc.initialCapital) / rc.initialCapital > total_rate)+1 ranking")
            ->join("sjq_weekly_ratio r", "u.uid=r.uid AND YEAR(r.time)=YEAR(u.join_time) AND WEEK(r.time)=WEEK(u.join_time)", "left");
        } elseif ($match['type'] == 2){//月赛
            $rankList->field("u.id,u.uid,u.user_name,(r.endFunds - r.initialCapital) / r.initialCapital total_rate,(SELECT count(uc.id) FROM sjq_match_user uc LEFT JOIN sjq_month_ratio rc ON uc.uid=rc.uid AND YEAR(rc.time)=YEAR(uc.join_time) AND MONTH(rc.time)=MONTH(uc.join_time) WHERE uc.match_id={$match['id']} AND (rc.endFunds - rc.initialCapital) / rc.initialCapital > total_rate)+1 ranking")
            ->join("sjq_month_ratio r", "u.uid=r.uid AND YEAR(r.time)=YEAR(u.join_time) AND MONTH(r.time)=MONTH(u.join_time)", "left");
        }
        $rankList = $rankList->select();
        
        foreach ($rankList as $key => $val) {
            $rankList[$key]->total_rate = round($val->total_rate * 100, 2);
        }

        $res = [
            'match'=>['id'=>$match['id'],'name'=>$match['name'],'joined'=>0,'total_rate'=>0,'ranking'=> 0],
            'rankList' => $rankList
        ];

        if(isset($data['uid']) && $data['uid'] > 0){//登录后获取参加状态和排名
            $user = [];
            if($match['type'] == 1){//周赛
                $user = MatchUser::where(['u.uid'=>$data['uid'], 'u.match_id'=>$match['id']])->alias('u')
                ->field("(r.endFunds - r.initialCapital) / r.initialCapital total_rate,(SELECT count(uc.id) FROM sjq_match_user uc LEFT JOIN sjq_weekly_ratio rc ON uc.uid=rc.uid AND YEAR(rc.time)=YEAR(uc.join_time) AND MONTH(rc.time)=MONTH(uc.join_time) WHERE uc.match_id={$match['id']} AND (rc.endFunds - rc.initialCapital) / rc.initialCapital > total_rate)+1 ranking")
                ->join("sjq_weekly_ratio r", "u.uid=r.uid AND YEAR(r.time)=YEAR(u.join_time) AND WEEK(r.time)=WEEK(u.join_time)", "left")
                ->find();
            } elseif ($match['type'] == 2){//月赛
                $user = MatchUser::where(['u.uid'=>$data['uid'], 'u.match_id'=>$match['id']])->alias('u')
                ->field("(r.endFunds - r.initialCapital) / r.initialCapital total_rate,(SELECT count(uc.id) FROM sjq_match_user uc LEFT JOIN sjq_month_ratio rc ON uc.uid=rc.uid AND YEAR(rc.time)=YEAR(uc.join_time) AND MONTH(rc.time)=MONTH(uc.join_time) WHERE uc.match_id={$match['id']} AND (rc.endFunds - rc.initialCapital) / rc.initialCapital > total_rate)+1 ranking")
                ->join("sjq_month_ratio r", "u.uid=r.uid AND YEAR(r.time)=YEAR(u.join_time) AND MONTH(r.time)=MONTH(u.join_time)", "left")
                ->find();
            }

            if(!empty($user)){
                $res['match']['joined'] = 1;
                $res['match']['total_rate'] = round($user['total_rate'] * 100, 2);
                $res['match']['ranking'] = $user['ranking'];
            }
        }

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