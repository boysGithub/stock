<?php 
namespace app\user\controller;

use app\index\controller\Base;
use app\common\model\Match as MatchModel;
use app\common\model\MatchUser;
/**
* 比赛控制器
*/
class Match extends Base
{
	/**
     * 比赛列表
     *
     * @return [json]
     */
    public function index()
    {
        $page = isset($data['np']) && (int)$data['np'] > 0 ? $data['np'] : 1;
        $limit = isset($data['limit']) && (int)$data['limit'] > 0 ? $data['limit'] : $this->_limit;

        $ranking = MatchModel::where(['start_date' => ['<', date('Y-m-d')]])->limit(($page-1)*$limit, $limit)->order('start_date desc')->field('id,name,type,start_date,end_date')->select();
        foreach ($ranking as $key => $val) {
        	if(time() >= strtotime($val['start_date']) && time() < strtotime($val['end_date']) + 24 * 3600){
        		$ranking[$key]['status'] = 1;
        		$ranking[$key]['status_name'] = '进行中';
        	} else if(time() >= strtotime($val['end_date']) + 24 * 3600){
        		$ranking[$key]['status'] = 3;
        		$ranking[$key]['status_name'] = '已结束';
        	}
        }


        return json(['status'=>'success','data'=>$ranking]);
    }

    /**
     * 比赛详情
     *
     * @return [json]
     */
    public function detail()
    {
        $data = input('get.');
        $res = $this->validate($data,'Match.match');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }

        $match = Match::where(['id'=>$data['id']])->find();
        if (empty($match)) {
            return json(['status'=>'failed','data'=>'比赛不存在']);
        }

        return json(['status'=>'success','data'=>$match]);
    }

    /**
     * 添加比赛
     *
     * @return [json]
     */
    public function create()
    {
        $data = input('post.');
        $res = $this->validate($data,'Match.add');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        
        $match = MatchModel::data([
            'name'=>$data['name'], 
            'periods'=> $data['periods'],
            'type'=> $data['type'],
            'start_date'=> $data['start_date'],
            'end_date'=> $data['end_date'],
            'initial_capital' => $data['initial_capital'],
            ])->save();
        
        if(empty($match->id)){
            return json(['status'=>'failed','data'=>'创建失败']);
        }

        return json(['status'=>'success','data'=>"创建成功"]);
    }

    /**
     * 参加比赛
     *
     * @return [json]
     */
    public function join()
    {
        $data = input('post.');
        $res = $this->validate($data,'Match.detail');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        
        $initial_capital = MatchModel::where(['id'=>$data['id']])->value('initial_capital');
        $match_user = MatchUser::data([
            'match_id'=>$data['id'], 
            'uid'=> $data['uid'],
            'initial_capital' => $initial_capital,
            'balance' => $initial_capital,
            'end_capital' => $initial_capital
            ])->save();
        
        if(empty($match_user->id)){
            return json(['status'=>'failed','data'=>'添加失败']);
        }

        return json(['status'=>'success','data'=>'添加成功']);
    }

    /**
     * 比赛排名
     *
     * @return [json]
     */
    public function ranking()
    {
        $data = input('get.');
        $res = $this->validate($data,'Match.match');
        if (true !== $res) {
            return json(['status'=>'failed','data'=>$res]);
        }
        
        $page = isset($data['np']) && (int)$data['np'] > 0 ? $data['np'] : 1;
        $limit = isset($data['limit']) && (int)$data['limit'] > 0 ? $data['limit'] : $this->_limit;

        $ranking = MatchUser::where(['match_id'=>$data['id']])->limit(($page-1)*$limit, $limit)->select();

        return json(['status'=>'success','data'=>$ranking]);
    }
}
?>