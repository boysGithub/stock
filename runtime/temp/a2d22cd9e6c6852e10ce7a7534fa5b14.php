<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:79:"/Users/ducong/nginxroot/stock/public/../application/index/view/index/index.html";i:1479803721;s:72:"/Users/ducong/nginxroot/stock/public/../application/index/view/base.html";i:1479455582;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/header.html";i:1479798992;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/footer.html";i:1479187842;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<meta name="description" content="">
<meta name="keywords" content="">
<title>模拟炒股首页</title>

<!-- Amaze ui -->
<link rel="stylesheet" href="/static/amaze/css/amazeui.min.css">
<link rel="stylesheet" href="/static/amaze/css/app.css">


<style type="text/css">
.tr-slider-ad{}
.tr-slider-ad .am-slider-default{margin: 0;padding: 2px 4px;}
.tr-slider-ad .am-slider-default .am-control-nav{bottom: 5px;}
.tr-proclamation{position: relative;padding: .2rem .6rem; height: 28px; overflow: hidden;}
.tr-proclamation dl{background-color: #eee;}
.tr-proclamation dd{display: block;float: left;margin-right:30px;}
.tr-proclamation dd a{color: #aa0805;}
.tr-proclamation dd a:hover{color: #fb7471;}
.am-titlebar-default .am-titlebar-title{font-weight: 400;}
.am-titlebar-default .am-titlebar-title:before{border-color: #03c;}

.tr-slider{box-shadow: none; padding: 1rem 0;}
.tr-slider li{font-size: 14px;}
.tr-slider .am-viewport{margin: 0 28px;}
.tr-slider .tr-recommend-reason{height: 36px; width: 100%; line-height: 18px; overflow: hidden;padding-right: 2rem;}
.am-slider-default.tr-slider .am-direction-nav .am-prev{left: -5px;}
.am-slider-default.tr-slider .am-direction-nav .am-next{right: -5px;}
.tr-slider h1, h2, h3, h4, h5, h6{margin: 0;}
.tr-slider-item{margin-bottom: 5px;}

.tr-tabs{position: relative;}
.tr-tabs .tr-more{position: absolute; top: 10px; right: 10px;}
.tr-tabs .tr-more a:hover{color: gray;}
.tr-tabs .am-tabs-nav li a{font-size: 16px; border-radius: 4px 4px 0 0; background-color: #eee;border-width: 1px; border-color: #999 #999 #39f; border-style: solid;}
.tr-tabs .am-nav>li>a:focus, .tr-tabs .am-nav>li>a:hover{background-color: #39f; color: #fff; border-color: #39f;}
.tr-tabs .am-nav-tabs>li.am-active>a, .tr-tabs .am-nav-tabs>li.am-active>a:focus, .tr-tabs .am-nav-tabs>li.am-active>a:hover{background-color: #39f; color: #fff; border-color: #39f;}
.tr-tabs .am-tabs-bd,.tr-tabs .am-nav-tabs{border-color: #39f;border-radius: 0 2px 2px;}
.tr-tabs .am-table{margin-bottom: 0;}
.tr-tabs .am-tab-panel{padding-bottom: 19px;}

.tr-left-ranking .am-titlebar-default a:hover{color: gray;}
.tr-left-ranking .am-table-bordered{border:none;}
.tr-left-ranking .am-table-bordered>tbody>tr>td{border: 1px solid #fff;}

.tr-simulation a.tr-simulation-name{font-size: 14px;}
.tr-simulation li{font-size: 14px; padding: 20px 15px;border:none; border-top: 1px dashed #999;}
.tr-simulation li.first{border: none;}
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

<div class="am-g">
    <div class="am-g am-container">
        <div class="am-u-md-9 am-margin-top-sm">
            <div class="am-panel am-panel-default tr-slider-ad">
                <div class="am-slider am-slider-default" data-am-flexslider>
                  <ul class="am-slides">
                    <li><a href="#" title=""><img src="/static/img/bing-4.jpg" alt="" /></a></li>
                    <li><a href="#" title=""><img src="/static/img/bing-4.jpg" alt="" /></a></li>
                    <li><a href="#" title=""><img src="/static/img/bing-4.jpg" alt="" /></a></li>
                  </ul>
                </div>
                <footer class="am-panel-footer tr-proclamation">
                    <dl id="ticker-1">
                        <dd><a href="http://www.sjqcj.com/weibo/715816" title="水晶球牛人选股大赛第57周战况：名人组花荣顺利夺冠 温州叶荣添奇正藏药5天3板夺魁" target="_blank">水晶球牛人选股大赛第57周战况：名人组花荣顺利夺冠 温州叶荣添奇正藏药5天3板夺魁</a></dd>
                        <dd><a href="http://www.sjqcj.com/weibo/712715" title="水晶球2016推股高手排行榜出炉：金一平擒四川双马成第一高手！" target="_blank">水晶球2016推股高手排行榜出炉：金一平擒四川双马成第一高手！</a></dd>
                        <dd><a href="http://www.sjqcj.com/weibo/714541" title="金一平：最看好的高送转潜力股" target="_blank">金一平：最看好的高送转潜力股</a></dd>
                        <dd><a href="http://www.sjqcj.com/weibo/715149" title="选股比赛播报（11.18）：高送转第一枪打响，涨停板接踵而至" target="_blank">选股比赛播报（11.18）：高送转第一枪打响，涨停板接踵而至</a></dd>
                    </dl>
                </footer>
            </div>
            <div class="am-panel am-panel-default tr-tab-three">
                <header class="am-panel-hd am-padding-vertical-0 am-padding-horizontal-xs">
                    <div data-am-widget="titlebar" class="am-titlebar am-titlebar-default am-margin-horizontal-0">
                        <h2 class="am-titlebar-title">
                            高手推荐
                        </h2>
                    </div>
                </header>
                <div class="am-slider am-slider-default am-margin-bottom-0 am-slider-carousel tr-slider" data-am-flexslider="{itemWidth: 230, itemMargin: 4, slideshow: false, controlNav: false}">
                    <ul class="am-slides">
                        <li>
                            <div class="am-container tr-slider-item">
                                <div class="am-u-sm-5">
                                    <a href="" title="personal">
                                        <img src="/static/img/portrait.gif" alt="portrait">
                                    </a>
                                </div>
                                <div class="am-u-sm-6 am-u-end" style="margin-left: 10px;">
                                    <a href="" title="personal" class="am-block">阿西八1</a>
                                    <p>
                                        排名：258 <br>
                                        周盈利率:25.24%
                                    </p>
                                    <a href="" class="am-btn am-btn-block am-btn-primary am-btn-sm am-radius" title="模拟炒股" width="50%">他的模拟炒股</a>
                                </div>
                            </div>
                            <div class="am-container">
                                <h3>推荐理由</h3>
                                <p class="tr-recommend-reason">
                                    选股有独特的眼光，并操作优秀,懂啊框架阿凯假两件阿赛况
                                </p>
                            </div>
                        </li>
                        
                    </ul>
                </div>
            </div>
            <div class="am-tabs tr-tabs" data-am-tabs="{noSwipe: 1}" id="tr-tabs">
                <ul class="am-tabs-nav am-nav am-nav-tabs">
                    <li class="am-active"><a href="javascript:;">牛人动态</a></li>
                    <li><a href="javascript:;">周赛排名</a></li>
                    <li><a href="javascript:;">月赛排名</a></li>
                    <li><a href="javascript:;">总盈利率</a></li>
                </ul>
                <div class="tr-more">
                    <a href="<?php echo url('index/index/rankingList'); ?>" alt="查看百强排名">查看百强排名&gt;&gt;</a>
                </div>
                <div class="am-tabs-bd">
                    <div class="am-tab-panel am-active">
                        <table class="am-table am-table-centered">
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>用户名</th>
                                    <th>股票名称</th>
                                    <th>状态</th>
                                    <th>价格(&yen;)</th>
                                    <th>今日动态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-1th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-2th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-3th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">4</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">5</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-buy">买入</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">6</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-buy">买入</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">7</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">8</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">9</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">10</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-shares-name">兔宝宝(002572)</span></td>
                                    <td><span class="tr-color-sale">卖出</span></td>
                                    <td><span class="tr-price">7.52</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-tab-panel">
                        <table class="am-table am-table-centered">
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>用户名</th>
                                    <th>总盈利率</th>
                                    <th>月盈利率</th>
                                    <th>周盈利率</th>
                                    <th>选股成功率</th>
                                    <th>今日动态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-1th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-2th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-3th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">4</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">5</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">6</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">7</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">8</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">9</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">10</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-tab-panel">
                        <table class="am-table am-table-centered">
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>用户名</th>
                                    <th>总盈利率</th>
                                    <th>月盈利率</th>
                                    <th>周盈利率</th>
                                    <th>选股成功率</th>
                                    <th>今日动态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-1th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-2th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-3th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">4</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">5</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">6</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">7</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">8</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">9</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">10</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-tab-panel">
                        <table class="am-table am-table-centered">
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>用户名</th>
                                    <th>总盈利率</th>
                                    <th>月盈利率</th>
                                    <th>周盈利率</th>
                                    <th>选股成功率</th>
                                    <th>今日动态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($profit_ranking) || ($profit_ranking instanceof \think\Collection && $profit_ranking->isEmpty())): ?>
                                    <tr>
                                        <td colspan="7"><span>暂无数据</span></td>
                                    </tr>
                                <?php else: if(is_array($profit_ranking) || $profit_ranking instanceof \think\Collection): $i = 0; $__LIST__ = $profit_ranking;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                                        <tr>
                                            <td><span class="tr-rank tr-icon tr-icon-1th"></span></td>
                                            <td><a href="<?php echo url('stocks/member/personal', array('id'=>$val['uid'])); ?>" title=""><?php echo $val['username']; ?>}</a></td>
                                            <td><span class="tr-color-lose">-27.28%</span></td>
                                            <td><span class="tr-color-win">2.35%</span></td>
                                            <td><span class="tr-color-<?php if(2.35 < 0): ?>lose<?php else: ?>win<?php endif; ?> ">2.35%</span></td>
                                            <td><span class="">70%</span></td>
                                            <td><a href="" title="">追踪可看</a></td>
                                        </tr>
                                    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-2th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank tr-icon tr-icon-3th"></span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">4</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">5</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">6</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">7</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">8</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">9</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                                <tr>
                                    <td><span class="tr-rank">10</span></td>
                                    <td><a href="" title="">寻梦6188</a></td>
                                    <td><span class="tr-color-lose">-27.28%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="tr-color-win">2.35%</span></td>
                                    <td><span class="">70%</span></td>
                                    <td><a href="" title="">追踪可看</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="am-panel am-panel-default tr-tab-three" style="margin-top: 20px;">
                <header class="am-panel-hd am-padding-vertical-0 am-padding-horizontal-xs">
                    <div data-am-widget="titlebar" class="am-titlebar am-titlebar-default am-margin-horizontal-0">
                        <h2 class="am-titlebar-title">
                            模拟赛场
                        </h2>
                    </div>
                </header>
                <div class="am-panel-bd tr-simulation">
                    <ul class="am-list">
                        <li class="am-g first">
                            <div class="am-u-lg-5">
                                <a href="#"><img src="/static/img/bisai_img.gif" alt="第2期周赛" width="100%" /></a>
                            </div>
                            <div class="am-u-lg-7" style="padding: 0 30px;">
                                <div class="am-u-sm-12">
                                    <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                                    <span class="">比赛中</span>
                                </div>
                                <p class="am-margin-vertical-0">
                                    报名时间：2016-06-01 到 2016-06-30
                                    <br>
                                    比赛时间：2016-06-01 到 2016-06-30
                                </p>
                            </div>
                        </li>
                        <li class="am-g">
                            <div class="am-u-lg-5">
                                <a href="#"><img src="/static/img/bisai_img.gif" alt="第2期周赛" width="100%" /></a>
                            </div>
                            <div class="am-u-lg-7" style="padding: 0 30px;">
                                <div class="am-u-sm-12">
                                    <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                                    <span class="">比赛中</span>
                                </div>
                                <p class="am-margin-vertical-0">
                                    报名时间：2016-06-01 到 2016-06-30
                                    <br>
                                    比赛时间：2016-06-01 到 2016-06-30
                                </p>
                            </div>
                        </li>
                        <li class="am-g">
                            <div class="am-u-lg-5">
                                <a href="#"><img src="/static/img/bisai_img.gif" alt="第2期周赛" width="100%" /></a>
                            </div>
                            <div class="am-u-lg-7" style="padding: 0 30px;">
                                <div class="am-u-sm-12">
                                    <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                                    <span class="">比赛中</span>
                                </div>
                                <p class="am-margin-vertical-0">
                                    报名时间：2016-06-01 到 2016-06-30
                                    <br>
                                    比赛时间：2016-06-01 到 2016-06-30
                                </p>
                            </div>
                        </li>
                        <li class="am-g">
                            <div class="am-u-lg-5">
                                <a href="#"><img src="/static/img/bisai_img.gif" alt="第2期周赛" width="100%" /></a>
                            </div>
                            <div class="am-u-lg-7" style="padding: 0 30px;">
                                <div class="am-u-sm-12">
                                    <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                                    <span class="">比赛中</span>
                                </div>
                                <p class="am-margin-vertical-0">
                                    报名时间：2016-06-01 到 2016-06-30
                                    <br>
                                    比赛时间：2016-06-01 到 2016-06-30
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>           
        </div>
        <div class="am-u-md-3 tr-left-ranking am-padding-left-sm am-margin-top-sm">
            <section class="am-panel am-panel-default">
                <div class="am-panel-bd" style="padding: 3.4rem 1.25rem;">
                    <a href="#login" title="" class="am-btn am-btn-block am-btn-primary am-btn-lg am-radius am-margin-bottom-lg">登录</a>
                    <a href="#reg" title="" class="am-btn am-btn-block am-btn-primary am-btn-lg am-radius am-margin-bottom-lg">立即注册</a>
                    <a href="<?php echo url('index/index/tradeCenter'); ?>" title="" class="am-btn am-btn-block am-btn-warning am-btn-lg am-radius">交易中心</a>
                </div>
            </section>
            <section class="am-panel am-panel-default tr-tab-one">
                <div data-am-widget="titlebar" class="am-titlebar am-titlebar-default am-margin-horizontal-0 am-padding-left-xs" >
                    <h2 class="am-titlebar-title ">
                        选股牛人
                    </h2>
                    <nav class="am-titlebar-nav">
                        <a href="#more" class="">更多&gt;&gt;</a>
                    </nav>
                </div>
                <div class="tr-ranking-table" style="padding: .75rem 0;">
                    <table class="am-table am-table-centered am-table-bordered am-table-compact am-margin-vertical-xs">
                        <tbody>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>选股成功率</td>
                                <td>75%</td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>选股成功率</td>
                                <td>75%</td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>选股成功率</td>
                                <td>75%</td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>选股成功率</td>
                                <td>75%</td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>选股成功率</td>
                                <td>75%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="am-panel am-panel-default tr-tab-one" style="margin: 4.9rem 0 1rem;">
                <div data-am-widget="titlebar" class="am-titlebar am-titlebar-default am-margin-horizontal-0 am-padding-left-xs" >
                    <h2 class="am-titlebar-title ">
                        总收益榜
                    </h2>
                    <nav class="am-titlebar-nav">
                        <a href="#more" class="">更多&gt;&gt;</a>
                    </nav>
                </div>
                <div class="tr-ranking-table">
                    <table class="am-table am-table-centered am-table-bordered am-table-compact am-margin-vertical-xs">
                        <tbody>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>总收益率</td>
                                <td><span class="tr-color-win">75.32%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>总收益率</td>
                                <td><span class="tr-color-win">75.32%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>总收益率</td>
                                <td><span class="tr-color-lose">-75.32%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>总收益率</td>
                                <td><span class="tr-color-win">75.32%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>总收益率</td>
                                <td><span class="tr-color-win">75.32%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="am-panel am-panel-default tr-tab-one">
                <div data-am-widget="titlebar" class="am-titlebar am-titlebar-default am-margin-horizontal-0 am-padding-left-xs" >
                    <h2 class="am-titlebar-title ">
                        常胜牛人
                    </h2>
                    <nav class="am-titlebar-nav">
                        <a href="#more" class="">更多&gt;&gt;</a>
                    </nav>
                </div>
                <div class="tr-ranking-table">
                    <table class="am-table am-table-centered am-table-bordered am-table-compact am-margin-vertical-xs">
                        <tbody>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>周均收益</td>
                                <td><span class="tr-color-win">15.32%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>周均收益</td>
                                <td><span class="tr-color-win">15.32%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>周均收益</td>
                                <td><span class="tr-color-lose">-8.2%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>周均收益</td>
                                <td><span class="tr-color-lose">-8.2%</span></td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>周均收益</td>
                                <td><span class="tr-color-lose">-8.2%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="am-panel am-panel-default tr-tab-one">
                <div data-am-widget="titlebar" class="am-titlebar am-titlebar-default am-margin-horizontal-0 am-padding-left-xs" >
                    <h2 class="am-titlebar-title ">
                        人气牛人
                    </h2>
                    <nav class="am-titlebar-nav">
                        <a href="#more" class="">更多&gt;&gt;</a>
                    </nav>
                </div>
                <div class="tr-ranking-table">
                    <table class="am-table am-table-centered am-table-bordered am-table-compact am-margin-vertical-xs">
                        <tbody>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>粉丝数</td>
                                <td>133</td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>粉丝数</td>
                                <td>133</td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>粉丝数</td>
                                <td>133</td>
                            </tr>
                            <tr class="tr-tab-bg-2">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>粉丝数</td>
                                <td>133</td>
                            </tr>
                            <tr class="tr-tab-bg-1">
                                <td><img src="/static/img/portrait.gif" width="18" alt=""></td>
                                <td><a href="" title="mo_2057">mo_2057**</a></td>
                                <td>粉丝数</td>
                                <td>133</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
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


<script type="text/javascript" src="/static/js/carouFredSel.js"></script>
<script type="text/javascript">
    var _scroll = {
        delay: 1000,
        easing: 'linear',
        items: 1,
        duration: 0.04,
        timeoutDuration: 0,
        pauseOnHover: 'immediate'
    };
    $('#ticker-1').carouFredSel({
        width: 732,
        align: false,
        items: {
            width: 'variable',
            height: 24,
            visible: 1,
            margin: 0
        },
        scroll: _scroll
    });
</script>

</body>
</html>