<?php 
namespace app\order\controller;

use app\index\controller\Base;
use app\common\model\Transaction;
use app\common\model\NoTrande;
use think\Config;
use think\Request;
use app\common\model\UserFunds;
use think\cache\driver\Redis;
use think\Db;
use app\common\model\UserPosition;
/**
* 
*/
class Trans extends Base
{	
	protected $_base;
    public function  __construct(){
        $this->_base = new Base();
    }

	/**
	 * [save 保存订单的方法]
	 * @return [type] [description]
	 */
	public function save($data){
		//验证是否是可以挂单的时间
		$tell = Config::has('stocktell.transactiontime') ? Config::get('stocktell.transactiontime'): true;
		if($this->isDeityTime($tell)){
	        return $this->trans($data);
		}else{
			return json(['status'=>'failed','data'=>'开市时间才能挂单']);
		}			
	}

	/**
	 * [trans 分配成交信息的方法]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function trans($data){
		$stock = getStock($data['stock'],'s_');//获取股票当前的信息
		//判断股票现在是否处于集合竞价还是停牌中
		$t = strtotime(date("Y-m-d 9:30:00"));
		if(time() > $t){
			if($stock[$data['stock']][1] == 0){
				return json(['status'=>'failed','data'=>'停牌']);
			}
		}else{
			if($stock[$data['stock']][1] == 0){
				return json(['status'=>'failed','data'=>'集合竞价中']);
			}
		}
		//判断是否是st
		if(strpos($stock[$data['stock']][0],'ST')){
			$limitUp = round(($stock[$data['stock']][1]-$stock[$data['stock']][2])*1.05,2);//涨停的价格
			$limitDown = round(($stock[$data['stock']][1]-$stock[$data['stock']][2])*0.95,2);//跌停的价格
		}else{
			$limitUp = round(($stock[$data['stock']][1]-$stock[$data['stock']][2])*1.1,2);//涨停的价格
			$limitDown = round(($stock[$data['stock']][1]-$stock[$data['stock']][2])*0.9,2);//跌停的价格
		}
        //区分购买方式
        if($data['type'] == 1){
        	//验证买入数量必须大于等于100
        	if($data['number']>=100){
        		if($data['isMarket'] == 1){
        			if($this->isTrans()){
        				//验证股票今天是否涨停
		        		if($stock[$data['stock']][1] != $limitUp){
		        			//买入验证用户是否足够的资金
		        			if($this->isCanBuy($data,$stock[$data['stock']])){
		        				//成交准备入库
		        				return $this->buyProcess($data,$stock);
		        			}else{
		        				return json(['status'=>'failed','data'=>'可用资金不足']);
		        			}
		        		}else{
		        			return json(['status'=>'failed','data'=>'股票涨停了不能市价买入']);
		        		}
        			}else{
        				return json(['status'=>'failed','data'=>'开市后才能市价交易']);
        			}
		        }else if($data['isMarket'] == 2){
		        	//验证价格是否超过今天的区间
		        	if( $data['price'] >= $limitDown && $data['price'] < $limitUp ){
		        		//买入验证用户是否足够的资金
		        		if(!$this->isCanBuy($data,$stock[$data['stock']])){
		        			return json(['status'=>'failed','data'=>'可用资金不足']);
		        		}
		        		//验证股票是否涨停
		        		if($this->isLimit($stock[$data['stock']],$data['type'])){
			        		//比对价格进行成交
			        		if($data['price'] >= $stock[$data['stock']][1]){
			        			//成交准备入库
			        			if($this->isTrans()){
			        				return $this->buyProcess($data,$stock);
			        			}else{
			        				//未成交进入挂单
			        				return $this->noBuyOrder($data,$stock);
			        			}	
			        		}else{
			        			//未成交进入挂单
			        			return $this->noBuyOrder($data,$stock);
			        		}
		        		}else{
		        			//涨停未成交进入挂单
			        		return $this->noBuyOrder($data,$stock);
		        		}
		        	}else{
	        			return json(['status'=>'failed','data'=>'价格不能超过'.$limitDown.'元和'.$limitUp.'元']);
	        		}
		        }
        	}else{
        		return json(['status'=>'failed','data'=>'买入数量不能为小于100']);
        	}
        }else if($data['type'] == 2){
        	if($data['isMarket'] == 1){
        		if(!$this->isTrans()){
        			return json(['status'=>'failed','data'=>'开市后才能市价交易']);
        		}
        		if($stock[$data['stock']][1] * $data['number'] < 5){
	        		return json(['status'=>'failed','data'=>'你卖出的总价不能小于手续费']);
	        	}
        		//验证股票是否跌停
        		if($stock[$data['stock']][1] != $limitDown){
        			//卖出验证用户持仓数
        			$availableNumber = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->value('available_number');
        			//判断用户是否持仓
        			if(is_null($availableNumber)) return json(['status'=>'failed','data'=>'你还没有持仓']);
        			if($availableNumber){
        				if($this->isCanSell($data['number'],$availableNumber)){
        					//成交入库
        					return $this->sellProcess($data,$stock);
        				}else{
        					return json(['status'=>'failed','data'=>'超过最大可卖数量']);
        				}
        			}else{
        				return json(['status'=>'failed','data'=>'你还没有可卖数量']);
        			}
        		}else{
        			return json(['status'=>'failed','data'=>'股票跌停了不能市价卖出']);
        		}
	        }else if($data['isMarket'] == 2){
	        	if($data['price'] * $data['number'] < 5){
	        		return json(['status'=>'failed','data'=>'你卖出的总价不能小于手续费']);
	        	}
	        	//验证价格是否超过今天的区间
	        	if($data['price'] > $limitDown && $data['price'] <= $limitUp){
	        		//验证股票是否跌停
		        	if($this->isLimit($stock[$data['stock']],$data['type'])){
		        		//卖出验证用户持仓数
		        		$availableNumber = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->value('available_number');
		        		//判断用户是否持仓
        				if(is_null($availableNumber)) return json(['status'=>'failed','data'=>'你还没有持仓']);
	        			if($availableNumber){
	        				if($this->isCanSell($data['number'],$availableNumber)){
	        					//比对价格进行成交 
	        					if($data['price'] <= $stock[$data['stock']][1]){
	        						//成交入库
	        						if($this->isTrans()){
	        							return $this->sellProcess($data,$stock);
	        						}else{
	        							return $this->noSellOrder($data,$stock);
	        						}
	        					}else{
	        						//未成交进入挂单
	        						return $this->noSellOrder($data,$stock);
	        					}
	        				}else{
	        					return json(['status'=>'failed','data'=>'超过最大可卖数量']);
	        				}
	        			}else{
	        				return json(['status'=>'failed','data'=>'你还没有可卖数量']);
	        			}
	        		}else{
	        			//跌停未成交进入挂单
	        			//卖出验证用户持仓数
		        		$availableNumber = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->value('available_number');
		        		if($availableNumber){
	        				if($this->isCanSell($data['number'],$availableNumber)){
	        					//未成交进入挂单
	        					return $this->noSellOrder($data,$stock);
	        				}else{
	        					return json(['status'=>'failed','data'=>'超过最大可卖数量']);
	        				}
	        			}else{
	        				return json(['status'=>'failed','data'=>'你还没有可卖数量']);
	        			}
	        		}
	        	}else{
        			return json(['status'=>'failed','data'=>'价格不能超过'.$limitDown.'元和'.$limitUp.'元']);
        		}
	        }
        }
	}

	/**
	 * [isDeityTime 是否是可以挂单的时间]
	 * @return boolean [description]
	 */
	public function isDeityTime($tell){
		if($tell === false){
			return true;
		}else{
			$w = date('w');
			if($w == 0 || $w == 6){
				return false;
			}else{
				// $t1 = strtotime(date("Y-m-d 9:15:00"));
				// $t2 = strtotime(date("Y-m-d 15:00:00"));
				// if(time() > $t1 && time() < $t2){
				// 	$tmp = NoTrande::where(['day' => date('Y-m-d 00:00:00',time())])->find();
				// 	if($tmp){
				// 		return false;
				// 	}else{
						return true;
				// 	}
				// }else{
				// 	return false;
				// }
			}
		}
	}

	/**
	 * [isLimit 是否涨跌停]
	 * @return boolean [description]
	 */
	public function isLimit($stock,$type){
		//判断是否是st
		if(strpos($stock[0],'ST')){
			$limitUp = round(($stock[1]-$stock[2])*1.05,2);//涨停的价格
        	$limitDown = round(($stock[1]-$stock[2])*0.95,2);//跌停的价格
		}else{
			$limitUp = round(($stock[1]-$stock[2])*1.1,2);//涨停的价格
        	$limitDown = round(($stock[1]-$stock[2])*0.9,2);//跌停的价格
		}
		if($type == 1){
			//买入的情况
            if((float)$stock[1] < $limitUp && (float)$stock[1] >= $limitDown){
                $bool = true;
            }else{
                $bool = false;
            }
		}else if($type == 2){
			//卖出的情况
            if((float)$stock[1] <= $limitUp && (float)$stock[1] > $limitDown){
                $bool = true;
            }else{
                $bool = false;
            }
		}
		return $bool;
	}

	/**
	 * [isCanBuy 资金是否能够买入]
	 * @return boolean [description]
	 */
	public function isCanBuy($data,$stock){
		$scale = $this->_base->_scale;
		$available = UserFunds::where(['uid'=>$data['uid']])->value('available_funds');
		if($stock[1] * $data['number'] * (1+$scale) <= $available){
			return true;
		}else{
			return false;
		}
	}

	public function isCanSell($number,$availableNumber){
		if($number > 0 ){
			if($availableNumber - $number >= 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
     * [noOrder 买入没有成交的订单]
     * @param  [array] $data      [订单信息]
     * @param  [array] $stockData [获取的股票现价信息]
     * @param  [array] $funds     [用户资金信息]
     * @return [json]             [返回对应信息]
     */
    protected function noBuyOrder($data,$stockData){
    	$funds = UserFunds::where(['uid'=>$data['uid']])->find();
        $scale = $this->_base->_scale;
        Db::startTrans();
        try {
            //买入没有成交的处理
            $data['stock_name'] = $stockData[$data['stock']][0];
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            $data['time'] = date("Y-m-d H:i:s");
            //扣除用户资金
            $data['available_funds'] = $funds['available_funds'] - $data['fee'] - $data['price'] * $data['number'];
            $Trans = new Transaction();
            $Trans->allowField(true)->save($data);
            
            UserFunds::where(['uid'=>$funds['uid']])->update(['available_funds'=>$data['available_funds']]);
            //添加进入redis
            $da = $Trans->where(['id'=>$Trans->id])->find();
            $redis = new Redis();
            $redis->set("noBuyOrder_".$Trans->id."_".$data['uid'],$da);
            Db::commit();
            $result = json(['status'=>'success','data'=>'委托成功']);
        } catch (\Exception $e) {
            Db::rollback();
            echo $e;exit;
            $result = json(['status'=>'failed','data'=>'下单失败']);
        }
        return $result;
    }

    /**
     * [buyProcess 买入交易]
     * @param  [type] $data      [订单详情的数组]
     * @param  [type] $stockData [股票的实时信息]
     * @param  [type] $funds     [用户的资金信息]
     * @return [json]            [json]
     */
    public function buyProcess($data,$stockData,$auto=false){
    	//处理买入为整数
    	$data['number'] = floor($data['number']/100)*100;
        $scale = $this->_base->_scale;
        if($auto){
        	//开启事务
	        Db::startTrans();
	        try {
	        	$funds = UserFunds::where(['uid'=>$data['uid']])->find();
	        	//订单参数
	            $data['status'] = 1;
	            $tmp = $data['price'] * $data['number'];
	            $data['price'] = $stockData[1];
	            $data['time'] = date("Y-m-d H:i:s");
	            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
	            //更改用户资金账户信息
	            $d['funds'] = $funds['funds'] - $data['fee'];
	            $d['total_rate'] = round(($d['funds'] - $this->_base->_stockFunds)/$this->_base->_stockFunds * 100,3);
	            $d['available_funds'] = $funds['available_funds'] + $tmp - $data['price'] * $data['number'];
	            UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update($d);
	            //查看是否持有这只股票
            	$userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            	if($userInfo){
            		//持有股票更改持仓表信息
            		$da['fee'] = $data['fee'] + $userInfo['fee'];
	                $da['cost'] = $userInfo['cost'] + $data['price']*$data['number'] + $data['fee'];
	                $da['freeze_number'] = $userInfo['freeze_number'] + $data['number'];
	                $da['cost_price'] = round($da['cost'] / ($da['freeze_number']+$userInfo['available_number']),3);
	                $da['assets'] = $data['price'] * ($da['freeze_number']+$userInfo['available_number']);
	                $da['ratio'] = round(($data['price'] - $da['cost_price']) / $da['cost_price']*100,3);
	                $da['last_time'] = date("Y-m-d H:i:s");
	                $da['position_number'] = $da['freeze_number'] + $userInfo['available_number'];
	                $UserPosition = new UserPosition;
	               	$UserPosition->allowField(true)->where(['id'=>$userInfo['id']])->update($da);
	               	$data['pid'] = $userInfo['id'];
	                //添加订单到数据库
	                $Trans = new Transaction;
	                $Trans->update($data);
	                Db::commit();
	                $result = json(['status'=>'success','data'=>'委托成功']);
            	}else{
            		//添加成交的订单到持仓表
            		$da['fee'] = $data['fee'];
            		$da['cost'] = $data['price'] * $data['number'] + $data['fee'];
	                $da['assets'] = $data['price'] * $data['number'];
	                $da['stock'] = $data['stock'];
	                $da['stock_name'] = $data['stock_name'];
	                $da['freeze_number'] = $data['number'];
	                $da['cost_price'] = round($da['cost'] / $data['number'],3);
	                $da['uid'] = $data['uid'];
	                $da['time'] = date("Y-m-d H:i:s",time());
	                $da['sorts'] = $data['sorts'];
	                $da['ratio'] = round(($data['price'] - $da['cost_price'])/$da['cost_price']*100,3);
	                $da['last_time'] = date("Y-m-d H:i:s");
	                $da['position_number'] = $da['freeze_number'];
	                $UserPosition = new UserPosition;
                	$UserPosition->allowField(true)->save($da);
                	$data['pid'] = $UserPosition->id;
                	//添加订单到交易表
                	$Trans = new Transaction;
                	$Trans->update($data);
                	Db::commit();
                	$result = json(['status'=>'success','data'=>'委托成功']);
            	}
	        } catch (\Exception $e) {
	        	Db::rollback();
	       		$result = json(['status'=>'failed','data'=>'委托多次失败请联系管理员']);
	        }
        }else{
        	//开启事务
	        Db::startTrans(); 
	       	try {
	       		//订单参数
	            $data['status'] = 1;
	            $data['price'] = $stockData[$data['stock']][1];
	            $data['stock_name'] = $stockData[$data['stock']][0];
	            $data['time'] = date("Y-m-d H:i:s");
	       		//手续费最低为5元
            	$data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
	       		$funds = UserFunds::where(['uid'=>$data['uid']])->find();
	       		$data['available_funds'] = $funds['available_funds'] - $data['price']*$data['number'] - $data['fee'];
	       		//更改用户资金账户信息
	       		$d['funds'] = $funds['funds'] - $data['fee'];
	       		$d['total_rate'] = round(($d['funds'] - $this->_base->_stockFunds)/$this->_base->_stockFunds * 100,3);
	       		$d['available_funds'] = $data['available_funds'];
	       		UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update($d);
	       		//更改用户持仓信息
	    		//查看是否持有这只股票
            	$userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            	if($userInfo){
            		//持有股票更改持仓表信息
            		$da['fee'] = $data['fee'] + $userInfo['fee'];
	                $da['cost'] = $userInfo['cost'] + $data['price']*$data['number'] + $data['fee'];
	                $da['freeze_number'] = $userInfo['freeze_number'] + $data['number'];
	                $da['cost_price'] = round($da['cost'] / ($da['freeze_number']+$userInfo['available_number']),3);
	                $da['assets'] = $data['price'] * ($da['freeze_number']+$userInfo['available_number']);
	                $da['ratio'] = round(($data['price'] - $da['cost_price']) / abs($da['cost_price'])*100,3);
	                $da['last_time'] = date("Y-m-d H:i:s");
	                $da['position_number'] = $da['freeze_number'] + $userInfo['available_number'];
	                $UserPosition = new UserPosition;
	               	$UserPosition->allowField(true)->where(['id'=>$userInfo['id']])->update($da);
	               	$data['pid'] = $userInfo['id'];
	                //添加订单到数据库
	                $Trans = new Transaction;
	                $Trans->allowField(true)->save($data);
	                Db::commit();
	                $result = json(['status'=>'success','data'=>'委托成功']);
            	}else{
            		//添加成交的订单到持仓表
            		$da['fee'] = $data['fee'];
            		$da['cost'] = $data['price'] * $data['number'] + $data['fee'];
	                $da['assets'] = $data['price'] * $data['number'];
	                $da['stock'] = $data['stock'];
	                $da['stock_name'] = $data['stock_name'];
	                $da['freeze_number'] = $data['number'];
	                $da['cost_price'] = round($da['cost'] / $data['number'],3);
	                $da['uid'] = $data['uid'];
	                $da['time'] = date("Y-m-d H:i:s",time());
	                $da['sorts'] = $data['sorts'];
	                $da['position_number'] = $da['freeze_number'];
	                $da['ratio'] = round(($data['price'] - $da['cost_price'])/abs($da['cost_price'])*100,3);
	                $da['last_time'] = date("Y-m-d H:i:s");
	                $UserPosition = new UserPosition;
                	$UserPosition->allowField(true)->save($da);
                	$data['pid'] = $UserPosition->id;
                	//添加订单到交易表
                	$Trans = new Transaction;
                	$Trans->allowField(true)->save($data);
                	Db::commit();
                	$result = json(['status'=>'success','data'=>'委托成功']);
            	}
	    		//入库到交易表
	       	} catch (\Exception $e) {
	       		Db::rollback();
	       		$result = json(['status'=>'failed','data'=>'委托多次失败请联系管理员']);
	       	}
        }
        return $result;
    }

    /**
     * [noSellOrder 卖出没有成交的订单]
     * @param  [array] $data      [订单的详情]
     * @param  [array] $stockData [当前股票的信息]
     * @param  [array] $funds     [用户的账户信息]
     * @return [json]            [提示信息]
     */
    protected function noSellOrder($data,$stockData){
        $scale = $this->_base->_scale;
        Db::startTrans();
        try {
        	$info = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            //卖出没有成交的处理
            $data['stock_name'] = $stockData[$data['stock']][0];
            $data['time'] = date("Y-m-d H:i:s");
            $data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            //减少可卖数量
            UserPosition::where(['id'=>$info['id']])->update(['available_number'=>$info['available_number']-$data['number']]);
            $data['pid'] = $info['id'];
            $Trans = new Transaction;
            $Trans->allowField(true)->save($data);
            $da = $Trans->where(['id'=>$Trans->id])->find();
            $redis = new Redis();
            $redis->set("noSellOrder_".$Trans->id."_".$data['uid'],$da);
            Db::commit();
            $result = json(['status'=>'success','data'=>'委托成功']);
        } catch (\Exception $e) {
        	echo $e;
            Db::rollback();
            $result = json(['status'=>'failed','data'=>'下单失败']);
        }
        return $result;
    }

    /**
     * [sellProcess 卖出交易]
     * @param  [array] $data      [卖出订单详情]
     * @param  [array] $stockData [股票信息]
     * @param  [array] $funds     [账户资金]
     * @return [json]            [返回信息]
     */
    public function sellProcess($data,$stockData,$auto=false){
        //手续费比例
        $scale = $this->_base->_scale;
        if($auto){
        	//开启事务
        	Db::startTrans();
        	try {
        		//订单参数
        		$data['status'] = 1;
        		$data['price'] = $stockData[1];
        		$data['time'] = date("Y-m-d H:i:s");
        		//手续费最低为5元
            	$data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
        		$funds = UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->find();
        		$data['available_funds'] = $funds['available_funds'] + $data['price']*$data['number'] - $data['fee'];
        		//为用户增加卖出金额
        		$d['funds'] = $funds['funds'] - $data['fee'];
        		$d['available_funds'] = $data['available_funds'];
        		$d['total_rate'] = round(($d['funds'] - $this->_base->_stockFunds)/$this->_base->_stockFunds * 100,3);
        		UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update($d);
        		//更改订单状态
        		Transaction::update($data);
        		//获取用户持有股票的信息
            	$userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            	$number = Transaction::where(['pid'=>$userInfo['id'],'type'=>2,'status'=>0])->value('sum(number) as number');
            	if($userInfo['available_number'] == $data['number'] && $userInfo['freeze_number'] == 0 && is_null($number)){
            		//计算所有的买入订单
            		$buyInfo = Transaction::where(['pid'=>$userInfo['id'],'type'=>1,'status'=>1])->select();
            		foreach ($buyInfo as $key => $value) {
                        $tmpTotal[] = $value['price'] * $value['number'] + $value['fee'];
                        $tmpNum[] = $value['number'];
                    }
                    $total = array_sum($tmpTotal);
                    $num = array_sum($tmpNum);
                    //计算所有的卖出市值
                    $sellInfo = Transaction::where(['pid'=>$userInfo['id'],'type'=>2,'status'=>1])->select();
                    $tmp = [0];
                    if($sellInfo){
                    	foreach ($sellInfo as $key => $value) {
                        	$tmp[] = $data['price'] * $value['number'] - $value['fee'];
	                    }
	                    $profits = array_sum($tmp);
                    }else{
                    	$profits = 0;
                    }
                    //组装持仓信息
            		$da['available_number'] = 0;
            		$da['position_number'] = 0;
            		$da['is_position'] = 2;
            		$da['assets'] = $profits + $data['price'] * $number;
            		$da['fee'] = $userInfo['fee'] + $data['fee'];
            		$da['cost'] = $total;
            		$da['cost_price'] = round($da['cost'] / $num,3);
            		$da['ratio'] = round(($da['assets'] - $da['cost'])/$da['cost']*100,3);
            		$da['last_time'] = date("Y-m-d H:i:s");
                	//更改持仓信息到数据库
                 	UserPosition::where(['id'=>$userInfo['id']])->update($da);
                	Db::commit();
            	}else{
            		//组装持仓信息
            		$da['assets'] = $data['price'] * ($userInfo['available_number'] + $userInfo['freeze_number'] + $number);
            		$da['position_number'] = $userInfo['available_number'] + $userInfo['freeze_number'] + $number;
            		$da['fee'] = $userInfo['fee'] + $data['fee'];
            		$da['cost'] = $userInfo['cost'] - $data['price'] * $data['number'] + $data['fee'];
            		$da['cost_price'] = round($da['cost'] / ($userInfo['freeze_number']+$userInfo['available_number']+$number),3);
            		$da['ratio'] = round(($data['price'] - $da['cost_price'])/abs($da['cost_price'])*100,3);
            		$da['last_time'] = date("Y-m-d H:i:s");
            		//更改持仓信息到数据库
                 	UserPosition::where(['id'=>$userInfo['id']])->update($da);
                	Db::commit();
            	}
        	} catch (\Exception $e) {
        		echo $e;
        		Db::rollback();
        	}
        }else{
        	//开启事务
        	Db::startTrans();
        	try {
        		//订单参数
            	$data['status'] = 1;
            	$data['price'] = $stockData[$data['stock']][1];
            	$data['time'] = date("Y-m-d H:i:s");
            	$data['stock_name'] = $stockData[$data['stock']][0];
            	//手续费最低为5元
            	$data['fee'] = $data['price']*$data['number']*$scale >=5?$data['price']*$data['number']*$scale:5;
            	$funds = UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->find();
            	$data['available_funds'] = $funds['available_funds'] + $data['price']*$data['number'] - $data['fee'];
            	//为用户增加卖出金额
            	$d['funds'] = $funds['funds'] - $data['fee'];
            	$d['available_funds'] = $data['available_funds'];
            	$d['total_rate'] = round(($d['funds'] - $this->_base->_stockFunds)/$this->_base->_stockFunds * 100,3);
            	UserFunds::where(['uid'=>$data['uid'],'sorts'=>$data['sorts']])->update($d);
            	//获取用户持有股票的信息
            	$userInfo = UserPosition::where(['uid'=>$data['uid'],'stock'=>$data['stock'],'is_position'=>1,'sorts'=>$data['sorts']])->find();
            	$number = Transaction::where(['pid'=>$userInfo['id'],'type'=>2,'status'=>0])->value('sum(number) as number');
            	//添加订单到数据库
            	$data['pid'] = $userInfo['id'];
            	$Trans = new Transaction;
            	$Trans->allowField(true)->save($data);
            	//判断是否清仓完全卖出
            	if($userInfo['available_number'] == $data['number'] && $userInfo['freeze_number'] == 0 && is_null($number)){
            		//计算所有的买入订单
            		$buyInfo = Transaction::where(['pid'=>$userInfo['id'],'type'=>1,'status'=>1])->select();
            		foreach ($buyInfo as $key => $value) {
                        $tmpTotal[] = $value['price'] * $value['number'] + $value['fee'];
                        $tmpNum[] = $value['number'];
                    }
                    $total = array_sum($tmpTotal);
                    $num = array_sum($tmpNum);
                    //计算所有的卖出市值
                    $sellInfo = Transaction::where(['pid'=>$userInfo['id'],'type'=>2,'status'=>1])->select();
                    $tmp = [0];
                    if($sellInfo){
                    	foreach ($sellInfo as $key => $value) {
                        	$tmp[] = $data['price'] * $value['number'] - $value['fee'];
	                    }
	                    $profits = array_sum($tmp);
                    }else{
                    	$profits = 0;
                    }
     				//组装持仓信息
            		$da['available_number'] = 0;
            		$da['is_position'] = 2;
            		$da['assets'] = $profits + $stockData[$data['stock']][1] * $number;
            		$da['fee'] = $userInfo['fee'] + $data['fee'];
            		$da['cost'] = $total;
            		$da['cost_price'] = round($da['cost'] / $num,3);
            		$da['ratio'] = round(($da['assets'] - $da['cost'])/$da['cost']*100,3);
            		$da['last_time'] = date("Y-m-d H:i:s");
                	//更改持仓信息到数据库
                 	UserPosition::where(['id'=>$userInfo['id']])->update($da);
                	Db::commit();
                 	return json(['status'=>'success','data'=>'委托成功']);
            	}else{
            		//组装持仓信息
            		$da['available_number'] = $userInfo['available_number'] - $data['number'];
            		$da['position_number'] = $da['available_number'] + $userInfo['freeze_number'] + $number;
            		$da['assets'] = $stockData[$data['stock']][1] * ($da['available_number'] + $userInfo['freeze_number'] + $number);
            		$da['fee'] = $userInfo['fee'] + $data['fee'];
            		$da['cost'] = $userInfo['cost'] - $stockData[$data['stock']][1] * $data['number'] + $data['fee'];
                    $da['cost_price'] = round($da['cost'] / ($userInfo['freeze_number']+$da['available_number']+$number),3);
                    $da['ratio'] = round(($stockData[$data['stock']][1] - $da['cost_price'])/abs($da['cost_price'])*100,3);
                    $da['last_time'] = date("Y-m-d H:i:s");
                    UserPosition::where(['id'=>$userInfo['id']])->update($da);
                    Db::commit();
                 	return json(['status'=>'success','data'=>'委托成功']);
            	}
        	} catch (\Exception $e) {
        		echo $e;
        		Db::rollback();
        		return json(['status'=>'failed','data'=>'下单失败，多次失败请联系管理员']);
        	}
        }
    }

    /**
     * [isTrans 是否能交易]
     * @return boolean [description]
     */
    public function isTrans(){
    	// $t1 = strtotime(date("Y-m-d 9:30:00"));
     //    $t2 = strtotime(date("Y-m-d 11:30:00"));
     //    $t3 = strtotime(date("Y-m-d 13:00:00"));
     //    $t4 = strtotime(date("Y-m-d 15:00:00"));
     //    if(($t1 <= time() && $t2 >= time()) || ($t3 <= time() && $t4 >= time())){
        	return true;
        // }else{
        // 	return false;
        // }
    }
}
?>