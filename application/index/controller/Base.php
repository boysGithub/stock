<?php

namespace app\index\controller;

use think\Controller;
use think\Request;

/**
* 
*/
class Base extends Controller
{
	public $_limit = 20; //显示的条数
	public $_stockFunds = 1000000; //股票账户初始金额
	public $_scale = 0.0003; //股票手续
    // public function __construct()
    // {
        
    // }
}
