<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:85:"/Users/ducong/nginxroot/stock/public/../application/index/view/trade/tradeCenter.html";i:1479804293;s:72:"/Users/ducong/nginxroot/stock/public/../application/index/view/base.html";i:1479455582;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/header.html";i:1479798992;s:84:"/Users/ducong/nginxroot/stock/public/../application/index/view/trade/trade-left.html";i:1479803938;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/footer.html";i:1479187842;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<meta name="description" content="">
<meta name="keywords" content="">
<title>交易中心</title>

<!-- Amaze ui -->
<link rel="stylesheet" href="/static/amaze/css/amazeui.min.css">
<link rel="stylesheet" href="/static/amaze/css/app.css">


    <style type="text/css">
    .tr-buy .am-form-group{margin: .5rem 0; line-height: 28px;}
    .tr-buy .am-form-group .am-form-label{font-size: 1.4rem; text-align: right; padding-right: 1rem;}
    .tr-buy .am-form-group .am-form-field{font-size: 1.2rem; width: 66%;}
    .tr-buy .tr-stock-btn{padding: 1rem 0 0 1.6rem;}
    .tr-stock-search{position: relative;}
    .tr-stock-table{width:66%; display: none;position: absolute; top: 27px; left: 0;z-index: 99;background: #fff;}
    .tr-stock-table .am-table{border-left: 1px solid #ddd;}
    .tr-stock-table .am-table>thead>tr>th{font-weight: normal; background: #f3f3f3;padding: 1px;border-bottom: none;}
    .tr-stock-table .am-table>tbody>tr>td{padding: 1px;border:none; cursor: pointer;}
    .tr-stock-table .am-table>tbody>tr:first-child td{background-color: #f1f5fC;}
    .tr-stock-table .am-table-hover>tbody>tr:hover>td{background-color: #87cefa;}

    .tr-stock-state .am-panel-hd{height: 45px; line-height: 30px;}
    .tr-stock-state .am-panel-hd .tr-stock-name{font-size: 1.6rem;color: #000;}
    .tr-stock-state .am-panel-hd .tr-stock-price{font-size: 2.2rem;color: red;}
    .tr-stock-state .am-panel-hd .tr-stock-increase{}
    .tr-stock-state .am-panel-hd .tr-stock-increase p{height: 15px; line-height: 15px;}
    .tr-stock-state .tr-stock-entrust{border-right: 1px solid #eee;}
    .tr-stock-state .am-table{margin: 0;}
    .tr-stock-state .am-table>tbody>tr>td{border: none; padding: .2rem .7rem;}
    .tr-stock-state .am-table>tbody>tr>td.price{color: red;}
    </style>

</head>
<body>
<header class="am-topbar am-topbar-fixed-top">
	<div class="am-g tr-login-topbar">
		<div class="am-container">
			<div class="am-u-sm-12">
				<div class="am-nav am-nav-pills am-topbar-nav am-fl">
					<a href="http://www.sjqcj.com/" target="_blank" title="水晶球网">水晶球首页</a>&nbsp;|&nbsp;<a href="http://www.baidu.com" target="_blank">CCC</a>
				</div>
				<div class="am-topbar-right am-fr">
					<?php if(false): ?>
						欢迎来到模拟炒股，请&nbsp;
						<a href="<?php echo url('stocks/member/login'); ?>" title="登录" class="topbar-login">登录</a>
						&nbsp;|&nbsp;
						<a href="<?php echo url('stocks/member/register'); ?>" title="注册" class="topbar-register">注册</a>
					<?php else: ?>
						您好，
						<a href="<?php echo url('stocks/member/index'); ?>" title="个人中心" class="topbar-login">username</a>
						<a href="<?php echo url('stocks/member/loginout'); ?>" title="退出" class="topbar-out">退出</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="am-g tr-menu-topbar">
		<div class="am-container">
			<h1 class="am-topbar-brand">
				<a href="/" title="logo"><img src="/static/img/logo1.png" alt="logo" /></a>
			</h1>
			<button class="am-topbar-btn am-topbar-toggle am-btn am-btn-sm am-btn-secondary am-show-sm-only am-collapsed" data-am-collapse="{target: '#tr-header-nav'}"><span class="am-sr-only">导航切换</span> <span class="am-icon-bars"></span></button>
			<div class="am-collapse am-topbar-collapse tr-menu-nav" id="tr-header-nav">
				<div class="tr-menu-nav-item">
				<ul class="am-nav am-nav-pills am-topbar-nav">
				    <li class="home<?php if(\think\Request::instance()->controller() == 'index' and \think\Request::instance()->action() == 'index'): ?> am-active<?php endif; ?>"><a href="/" title="模拟炒股首页">首页</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'index' and \think\Request::instance()->action() == 'matchList'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/matchList'); ?>">模拟赛场</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'index' and \think\Request::instance()->action() == 'rankingList'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/rankingList'); ?>">牛人排行榜</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'index' and \think\Request::instance()->action() == 'index'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/tradingRules'); ?>">交易规则</a></li>
				</ul>
				</div>
			</div>
		</div>
	</div>
</header>

<div class="am-g tr-main">
    <div class="am-container">
        <div class="am-g">
            <div class="am-u-md-2 am-padding-right">
                <!--左边列表-->
<style type="text/css">
    
    @media only screen and (min-width: 641px) {
      .am-offcanvas {
        display: block;
        position: static;
        background: none;
      }

      .am-offcanvas-bar {
        position: static;
        width: auto;
        background: none;
        -webkit-transform: translate3d(0, 0, 0);
        -ms-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
        border-right: 1px solid #eee;
      }
      .am-offcanvas-bar:after {
        content: none;
      }

    .am-offcanvas-bar .am-nav>li>a {
        font-size: 1.6rem;
      }
      .am-offcanvas-bar .am-nav>li.am-active>a {
        background: #f8f8f8;
        color: #1E6BC5;
      }

    }

    @media only screen and (max-width: 640px) {
      .am-offcanvas-bar .am-nav>li>a {
        color:#ccc;
        border-radius: 0;
        border-top: 1px solid rgba(0,0,0,.3);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.05)
      }

      .am-offcanvas-bar .am-nav>li>a:hover {
        background: #1a1a1a;
        color: #fff
      }

      .am-offcanvas-bar .am-nav>li.am-nav-header {
        color: #777;
        background: #404040;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
        text-shadow: 0 1px 0 rgba(0,0,0,.5);
        border-top: 1px solid rgba(0,0,0,.3);
        font-weight: 400;
        font-size: 75%
      }

      .am-offcanvas-bar .am-nav>li.am-active>a {
        background: #1a1a1a;
        color: #fff;
        box-shadow: inset 0 1px 3px rgba(0,0,0,.3)
      }

      .am-offcanvas-bar .am-nav>li+li {
        margin-top: 0;
      }
      .tr-left-sidebar{position: fixed; right: 1rem; bottom: 1rem; z-index: 999;}
    }
</style>
<div class="am-offcanvas am-text-center" id="sidebar">
    <div class="am-offcanvas-bar">
        <ul class="am-nav">
            <li ><a href="">买入</a></li>
            <li><a href="#">卖出</a></li>
            <li ><a href="">撤单</a></li>
            <li ><a href="">查询</a></li>
        </ul>
    </div>
</div>
<a href="#sidebar" class="am-btn am-btn-sm am-btn-success am-icon-bars am-show-sm-only my-button tr-left-sidebar" data-am-offcanvas><span class="am-sr-only">侧栏导航</span></a>
            </div>
            <div class="am-u-md-10 am-padding-horizontal-sm" id="tr-stock">
                <form action="#" method="post" class="am-form">
                <div class="am-u-md-6 tr-buy">
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">证券代码：</label>
                        <div class="am-u-sm-9 tr-stock-search">
                            <div class="tr-stock-table">
                                <table class="am-table am-table-centered am-table-bordered am-table-hover" id="stockTable">
                                    <thead>
                                        <tr>
                                            <th>选项</th><th>类型</th><th>代码</th><th>中文名称</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-on:mousedown="updateStockInfo" v-for="item in stockList" v-bind:id="item.id">
                                            <td>{{ item.option }}</td>
                                            <td>{{ item.type }}</td>
                                            <td>{{ item.code }}</td>
                                            <td>{{ item.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <input type="text" v-on:keyup="updateStockList" placeholder="请输入股票名称/代码/拼音" onfocus="if($(this).val() != ''){$('.tr-stock-table').show()}" onblur="$('.tr-stock-table').hide()" class="am-form-field">
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">证券名称：</label>
                        <div class="am-u-sm-9">
                            <span class="" id="">{{ buyInfo.stockName }}</span>
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">当前价格：</label>
                        <div class="am-u-sm-9">
                            <span class="" id="">{{ buyInfo.price }}</span>
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">买入价格：</label>
                        <div class="am-u-sm-9">
                            <input type="text" class="am-form-field" v-bind:value=" buyInfo.price">
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">可用资金：</label>
                        <div class="am-u-sm-9">
                            <span class="" id="">{{ buyInfo.usableFunds }}</span>
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">最大可买：</label>
                        <div class="am-u-sm-9">
                            <span class="" id="">{{ buyInfo.maxBuy }}</span>
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">买入数量：</label>
                        <div class="am-u-sm-9">
                            <input type="text" class="am-form-field" v-bind:value="buyInfo.maxBuy">
                            <p class="">
                                <label>
                                    <input type="radio" name="p01_form_percount" value="1" checked="checked">
                                    全部可买
                                </label>
                                <label>
                                    <input type="radio" name="p01_form_percount" value="2">
                                    1/2
                                </label>
                                <label>
                                    <input type="radio" name="p01_form_percount" value="3">
                                    1/3
                                </label>
                                <label>
                                    <input type="radio" name="p01_form_percount" value="4">
                                    1/4
                                </label>
                                <label>
                                    <input type="radio" name="p01_form_percount" value="5">
                                    1/5
                                </label>
                            </p>
                        </div>
                    </div>
                    <div class="am-g am-form-group">
                        <label class="am-u-sm-3 am-form-label">购买金额：</label>
                        <div class="am-u-sm-9">
                            <span class="" id="">{{ buyInfo.useFunds }}</span>
                        </div>
                    </div>
                    <div class="am-g am-form-group tr-stock-btn">
                        <input type="button" class="am-btn am-btn-sm am-btn-success" value="下单确认">
                        <input type="reset" class="am-btn am-btn-sm am-btn-danger am-margin-left-sm" value="清空重置">
                    </div>
                </div>
                <div class="am-u-md-6 am-padding-left-sm tr-stock-state">
                    <div class="am-panel am-panel-default">
                        <div class="am-panel-hd am-g">
                            <div class="am-u-sm-9 tr-stock-name">
                                <span>{{ buyInfo.title }}</span>
                            </div>
                            <div class="am-u-sm-2 am-text-right am-padding-right-sm tr-stock-price ">
                                <span>{{ buyInfo.price }}</span><span class="am-icon-"></span>
                            </div>
                            <div v-bind:class="'am-u-sm-1 tr-stock-increase' + changeClass">
                                <p>{{ buyInfo.changePrice }}</p>
                                <p>{{ buyInfo.changeRate }}</p>
                            </div>
                        </div>
                        <div class="am-panel-bd am-g">
                            <div class="am-u-sm-7 am-padding-right tr-stock-entrust">
                                <table class="am-table">
                                    <tbody>
                                        <tr>
                                            <td>卖⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>卖⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>卖⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>卖⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>卖⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                <table class="am-table">
                                    <tbody>
                                        <tr>
                                            <td>买⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>买⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>买⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>买⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                        <tr>
                                            <td>买⑤(元/股)</td>
                                            <td class="price">9.12</td>
                                            <td>2029123</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="am-u-sm-5">
                                <ul>
                                    <li><span>现&nbsp;&nbsp;&nbsp;价 : </span><span class="tr-color-win">9.12</span></li>
                                    <li><span>今&nbsp;&nbsp;&nbsp;开 : </span><span class="tr-color-lose">9.10</span></li>
                                    <li><span>昨&nbsp;&nbsp;&nbsp;收 : </span><span>9.12</span></li>
                                    <li><span>最&nbsp;&nbsp;&nbsp;高 : </span><span class="tr-color-win">9.12</span></li>
                                    <li><span>最&nbsp;&nbsp;&nbsp;低 : </span><span class="tr-color-lose">9.12</span></li>
                                    <li><span>涨停价 : </span><span class="tr-color-win">9.32</span></li>
                                    <li><span>跌停价 : </span><span class="tr-color-lose">9.02</span></li>
                                    <li><span>换手率 : </span><span class="menu-right-centent-tebale3-margin">0.18%</span></li>
                                    <li><span>成交量 : </span><span class="menu-right-centent-tebale3-margin">912562</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>    
    </div>   
</div>     

<footer>
	<br>
	<hr >
	<br>
	<div class="am-g am-text-center">
		&copy;2016 成都水晶球股份有限公司  All rights reserved.
	</div>
	<br>
</footer>

<!--[if (gte IE 9)|!(IE)]><!-->
<script src="/static/amaze/js/jquery.min.js"></script>
<!--<![endif]-->
<script src="/static/amaze/js/amazeui.min.js"></script>
<script src="/static/js/vue.js"></script>


    <script type="text/javascript">
        var trStock = new Vue({
            el: '#tr-stock',
            data: {
                stockList: [],
                buyInfo: {title: '----'},
                changeClass: ' tr-color-win'
            },
            methods: {
                updateStockList: function(e){
                    var value = e.currentTarget.value;
                    var _this = this;

                    $.getScript('http://suggest3.sinajs.cn/suggest/?type=111&key='+value+'&name=suggestdata',
                        function(){
                            if(suggestdata != ''){
                                var data = suggestdata.split(";"); 
                                var list = [];
                                for (var i = 0; i < data.length; i++) {
                                    if(data[i] == ''){
                                        continue;
                                    }
                                    var val = data[i].split(',');
                                    var type = 'A股';
                                    switch(val['1']){
                                        case '111':
                                            type = 'A股';
                                            break;
                                    }
                                    list.push({ id: data[i], option: val['0'], type: type, code: val['2'], name: val['4']});
                                }

                                _this.stockList = list;
                                $(".tr-stock-table").show();
                            } else {
                                $(".tr-stock-table").hide();
                            }
                    });    
                },
                updateStockInfo: function(e){
                    var id = e.currentTarget.id;
                    var _this = this;
                    var stock = id.split(',');
                    
                    $.ajax({
                        url: 'http://hq.sinajs.cn?list='+stock['3']+',s_'+stock['3'],
                        type: 'get',
                        dataType: 'script',
                        cache: true,
                        success: function(){
                            if(eval('hq_str_'+stock['3']) != '' || eval('hq_str_s_'+stock['3']) != ''){
                                var brief = eval('hq_str_s_'+stock['3']).split(',');
                                var detail = eval('hq_str_'+stock['3']).split(',');
                                var usableFunds = {}; //可用资金
                                var maxBuy = 10000;
                                if(brief['2'] < 0){
                                    _this.changeClass = ' tr-color-lose';
                                } else {
                                    _this.changeClass = ' tr-color-win';
                                }

                                var buyInfo = {
                                    stockName: brief['0'], 
                                    code: stock['2'], 
                                    title: brief['0']+'('+stock['2']+')',
                                    price: brief['1'], 
                                    changePrice: brief['2'], 
                                    changeRate: brief['3']+'%', 
                                    usableFunds: {}, 
                                    maxBuy: maxBuy, 
                                    useFunds: brief['1'] * maxBuy,
                                    turnoverNum:  brief['4'], 
                                    turnoverMoney:  brief['5']
                                };


                                _this.buyInfo = buyInfo;
                            }
                        }    
                    });
                }
            }
        });
    </script>

</body>
</html>