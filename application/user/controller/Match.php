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
            $field .= ",u.id muid,(SELECT count(id) FROM sjq_match_user WHERE (u.end_capital - u.initial_capital) / u.initial_capital < ( end_capital - initial_capital) / initial_capital AND match_id=m.id)+1 ranking";
            if(isset($data['joined']) && $data['joined'] == 1){
                $where['u.id'] = ['not null',''];
            }
            $matchs->join('sjq_match_user u',"u.match_id=m.id AND u.uid={$data['uid']}", 'LEFT');
        }

        $matchs = $matchs->where($where)->field('m.id,name,image,type,start_date,end_date'.$field)->select();
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
            if(isset($data['uid']) && $data['uid'] > 0){
                $match['ranking'] = $val['ranking'];
                if(empty($val['muid'])){
                    $match['ranking'] = 0;
                    $match['joined'] = 0;
                } else{
                    $match['joined'] = 1;
                }
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

        $match = MatchModel::where($where)->alias('m');
        $field = '';
        if(isset($data['uid']) && !empty($data['uid'])){//登录后获取总收益和排名
            $field .= ",u.id muid,FORMAT((u.end_capital - u.initial_capital) / u.initial_capital,2) total_rate,(SELECT count(*) FROM sjq_match_user WHERE total_rate < ( end_capital - initial_capital) / initial_capital AND match_id=m.id)+1 ranking";
            $match->join('sjq_match_user u',"u.match_id=m.id AND u.uid={$data['uid']}", 'LEFT');
        }

        $match = $match->field('m.id,name,type,start_date,end_date'.$field)->find();
        if (empty($match)) {
            return json(['status'=>'failed','data'=>'比赛不存在']);
        }
        
        //比赛排行
        $rankList = MatchUser::where(['match_id'=>$match['id']])->limit(($page-1)*$limit, $limit)->order('total_rate desc')->field('id,uid,user_name,ROUND((end_capital - initial_capital) / initial_capital,2) total_rate,
(SELECT count(id) FROM sjq_match_user WHERE total_rate < ( end_capital - initial_capital) / initial_capital AND match_id=1)+1 ranking')->select();

        $res = [
            'match'=>['id'=>$match['id'],'name'=>$match['name'],'joined'=>empty($match['muid']) ? 0 : 1,'total_rate'=>empty($match['muid']) ? 0 : $match['total_rate'],'ranking'=>empty($match['muid']) ? 0 : $match['ranking']],
            'rankList' => $rankList
        ];

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
        
        $user = User::where(['u.uid'=>$data['uid']])->alias('u')->join('sjq_users_funds uf', 'u.uid=uf.uid')->field('username,funds')->find();
        if(empty($user)){
            return json(['status'=>'failed','data'=>'无效的用户']);
        }

        $match_user = MatchUser::create([
            'match_id'=>$data['id'], 
            'uid'=> $data['uid'],
            'user_name'=> $user['username'],
            'initial_capital' => $user['funds'],
            'balance' => 0,
            'end_capital' => $user['funds']
            ]);
        
        if(empty($match_user->id)){
            return json(['status'=>'failed','data'=>'添加失败']);
        }

        return json(['status'=>'success','data'=>'添加成功']);
    }
}
?>