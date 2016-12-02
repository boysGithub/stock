<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:79:"/Users/ducong/nginxroot/stock/public/../application/index/view/index/index.html";i:1480645434;s:72:"/Users/ducong/nginxroot/stock/public/../application/index/view/base.html";i:1480659030;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/header.html";i:1480644725;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/footer.html";i:1480644253;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<meta name="description" content="">
<meta name="keywords" content="">
<title>模拟炒股首页</title>

<!-- Amaze ui -->
<link rel="stylesheet" href="/static/css/public/amazeui.min.css">
<link rel="stylesheet" href="/static/css/public/app.css">
<link rel="stylesheet" type="text/css" href="/static/css/public/mobile.css">
 <link rel="stylesheet" type="text/css" href="/static/css/index/index.css" /> 

</head>
<body>
<header class="am-topbar am-topbar-fixed-top" id="header">
	<div class="am-g tr-login-topbar">
		<div class="am-container">
			<div class="am-u-sm-12">
				<div class="am-nav am-nav-pills am-topbar-nav am-fl">
					欢迎来到水晶球！
				</div>
				<div class="am-topbar-right am-fr">
					<template v-if="logined">
						您好，
						<a href="<?php echo url('stocks/member/index'); ?>" title="个人中心" class="topbar-login">username</a>
						<a href="<?php echo url('stocks/member/loginout'); ?>" title="退出" class="topbar-out">退出</a>
					</template>	
					<template v-else>
						<a href="<?php echo url('index/index/login'); ?>" title="登录" class="topbar-login">登录</a>
						&nbsp;|&nbsp;
						<a href="<?php echo url('stocks/member/register'); ?>" title="注册" class="topbar-register">注册</a>
					</template>
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
				    <li class="home<?php if(\think\Request::instance()->controller() == 'Index' and \think\Request::instance()->action() == 'index'): ?> am-active<?php endif; ?>"><a href="/" title="模拟炒股首页">首页</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'Index' and \think\Request::instance()->action() == 'matchlist'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/matchList'); ?>">模拟赛场</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'Index' and \think\Request::instance()->action() == 'rankinglist'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/rankingList'); ?>">牛人排行榜</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'Index' and \think\Request::instance()->action() == 'tradecenter'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/tradeCenter'); ?>">交易中心</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'Index' and \think\Request::instance()->action() == 'tradingrules'): ?> am-active<?php endif; ?>"><a href="<?php echo url('index/index/tradingRules'); ?>">交易规则</a></li>
				</ul>
				</div>
			</div>
		</div>
	</div>
</header>

<div class="am-g" id="index">
    <div class="am-g am-container">
        <div class="am-slider am-slider-default tr-slider-ad" data-am-flexslider="{directionNav: false,slideshowSpeed:3000}">
            <ul class="am-slides">
                <li>
                    <a href="#" title=""><img src="/static/img/bing-1.png" alt="" /></a>
                </li>
                <li>
                    <a href="#" title=""><img src="/static/img/bing-1.png" alt="" /></a>
                </li>
                <li>
                    <a href="#" title=""><img src="/static/img/bing-4.jpg" alt="" /></a>
                </li>
            </ul>
        </div>
        <div class="tr-proclamation">
            <div class="am-u-md-2 tr-pro-t">
                <span class="am-icon-volume-up"></span>
                <span class="tr-pro-t-c">通知公告</span>
            </div>
            <div class="am-u-md-10">
                <dl id="ticker-1">
                    <dd v-for="item in proclamation"><a :href="item.href" :title="item.title" target="_blank">{{ item.title }}</a></dd>
                </dl>
            </div>
        </div>
        <div class="am-panel am-panel-default tr-talent">
            <div class="am-panel-hd">
                <h2 class="am-panel-title">
                    高手推荐
                </h2>
            </div>
            <div class="am-slider am-slider-default am-margin-bottom-0 am-slider-carousel tr-slider" data-am-flexslider="{itemWidth: 320, itemMargin: 20, slideshow: false, controlNav: false}">
                <ul class="am-slides">
                    <li>
                        <div class="am-g tr-talent-info am-padding">
                            <div class="am-u-sm-5 am-padding-right">
                                <a href="<?php echo url('index/index/personal'); ?>" title="personal">
                                    <img src="/static/img/portrait.gif" alt="portrait" class="am-circle">
                                </a>
                            </div>
                            <div class="am-u-sm-7">
                                <p class="tr-talent-name">阿西八1</p>
                                <p class="tr-talent-ranking">排名：258</p>
                                <p class="tr-talent-rate">周盈利率：<span class="tr-color-win">25.24%</span></p>
                                <p class="tr-talent-btn"><a href="" class="am-btn am-btn-danger am-btn-sm am-radius" title="模拟炒股" width="50%">他的模拟炒股</a></p>
                            </div>
                        </div>
                        <div class="tr-talent-reason am-padding-vertical-sm am-padding-horizontal">
                            推荐理由：选股有独特的眼光，并操作优秀,懂啊框架阿凯假两件阿赛况
                        </div>
                    </li>
                    <li>
                        <div class="am-g tr-talent-info am-padding">
                            <div class="am-u-sm-5 am-padding-right">
                                <a href="<?php echo url('index/index/personal'); ?>" title="personal">
                                    <img src="/static/img/portrait.gif" alt="portrait" class="am-circle">
                                </a>
                            </div>
                            <div class="am-u-sm-7">
                                <p class="tr-talent-name">阿西八1</p>
                                <p class="tr-talent-ranking">排名：258</p>
                                <p class="tr-talent-rate">周盈利率：<span class="tr-color-win">25.24%</span></p>
                                <p class="tr-talent-btn"><a href="" class="am-btn am-btn-danger am-btn-sm am-radius" title="模拟炒股" width="50%">他的模拟炒股</a></p>
                            </div>
                        </div>
                        <div class="tr-talent-reason am-padding-vertical-sm am-padding-horizontal">
                            推荐理由：选股有独特的眼光，并操作优秀,懂啊框架阿凯假两件阿赛况
                        </div>
                    </li>
                    <li>
                        <div class="am-g tr-talent-info am-padding">
                            <div class="am-u-sm-5 am-padding-right">
                                <a href="<?php echo url('index/index/personal'); ?>" title="personal">
                                    <img src="/static/img/portrait.gif" alt="portrait" class="am-circle">
                                </a>
                            </div>
                            <div class="am-u-sm-7">
                                <p class="tr-talent-name">阿西八1</p>
                                <p class="tr-talent-ranking">排名：258</p>
                                <p class="tr-talent-rate">周盈利率：<span class="tr-color-win">25.24%</span></p>
                                <p class="tr-talent-btn"><a href="" class="am-btn am-btn-danger am-btn-sm am-radius" title="模拟炒股" width="50%">他的模拟炒股</a></p>
                            </div>
                        </div>
                        <div class="tr-talent-reason am-padding-vertical-sm am-padding-horizontal">
                            推荐理由：选股有独特的眼光，并操作优秀,懂啊框架阿凯假两件阿赛况
                        </div>
                    </li>
                    <li>
                        <div class="am-g tr-talent-info am-padding">
                            <div class="am-u-sm-5 am-padding-right">
                                <a href="<?php echo url('index/index/personal'); ?>" title="personal">
                                    <img src="/static/img/portrait.gif" alt="portrait" class="am-circle">
                                </a>
                            </div>
                            <div class="am-u-sm-7">
                                <p class="tr-talent-name">阿西八1</p>
                                <p class="tr-talent-ranking">排名：258</p>
                                <p class="tr-talent-rate">周盈利率：<span class="tr-color-win">25.24%</span></p>
                                <p class="tr-talent-btn"><a href="" class="am-btn am-btn-danger am-btn-sm am-radius" title="模拟炒股" width="50%">他的模拟炒股</a></p>
                            </div>
                        </div>
                        <div class="tr-talent-reason am-padding-vertical-sm am-padding-horizontal">
                            推荐理由：选股有独特的眼光，并操作优秀,懂啊框架阿凯假两件阿赛况
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
                                <th>用户名</th>
                                <th>股票名称</th>
                                <th>状态</th>
                                <th>价格(&yen;)</th>
                                <th>今日动态</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="talent_dynamic.length > 0">
                                <tr v-for="item in talent_dynamic">
                                    <td><a :href="item.url" :title="item.user_name">{{ item.user_name }}</a></td>
                                    <td><span class="tr-shares-name">{{ item.stock }}</span></td>
                                    <td><span :class="item.state_class">{{ item.state }}</span></td>
                                    <td><span class="tr-color-price">{{ item.price }}</span></td>
                                    <td><a :href="item.url" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="5"><span>暂无数据</span></td>
                                </tr>
                            </template>
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
                                <td><span class="tr-rank tr-icon">1</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank tr-icon">2</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank tr-icon">3</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">4</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">5</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">6</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">7</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">8</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">9</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">10</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
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
                                <td><span class="tr-rank tr-icon">1</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank tr-icon">2</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank tr-icon">3</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">4</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">5</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">6</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">7</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">8</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">9</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                            </tr>
                            <tr>
                                <td><span class="tr-rank">10</span></td>
                                <td><a href="" title="">寻梦6188</a></td>
                                <td><span class="tr-color-lose">-27.28%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="tr-color-win">2.35%</span></td>
                                <td><span class="">70%</span></td>
                                <td><a href="" title="" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
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
                                <th>选股成功率</th>
                                <th>平均交易天数</th>
                                <th>周平均率</th>
                                <th>今日动态</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="total_rate_10.length > 0">
                                <tr v-for="item in total_rate_10">
                                    <td><span :class="'tr-rank'+item.rownum_class">{{ item.rownum }}</span></td>
                                    <td><a :href="item.url" :title="item.user_name">{{ item.user_name }}</a></td>
                                    <td><span :class="item.total_rate_class">{{ item.total_rate }}</span></td>
                                    <td><span class="">{{ item.success_rate }}</span></td>
                                    <td><span>{{ item.avg_position_day }}</span></td>
                                    <td><span :class="item.week_avg_profit_rate_class">{{ item.week_avg_profit_rate }}</span></td>
                                    <td><a :href="item.url" class="am-btn am-btn-primary am-btn-xs am-radius">追踪可看</a></td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="7"><span>暂无数据</span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="am-g">
            <div class="am-u-md-6 am-padding-right-sm">
                <section class="am-panel am-panel-default">
                    <div class="am-panel-hd">
                        <h2 class="am-panel-title ">
                            总收益榜
                        </h2>
                    </div>
                    <div class="tr-ranking-table">
                        <table class="am-table am-table-centered am-margin-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>用户名</th>
                                    <th>总收益率</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in total_rate_5">
                                    <td class="tr-ranking-portrait"><img :src="item.portrait" class="am-circle" width="30" alt=""></td>
                                    <td><a :href="item.url" :title="item.user_name">{{ item.user_name }}</a></td>
                                    <td><span :class="item.total_rate_class">{{ item.total_rate }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="am-u-md-6 am-padding-left-sm">
                <section class="am-panel am-panel-default">
                    <div class="am-panel-hd">
                        <h2 class="am-panel-title ">
                            选股牛人
                        </h2>
                    </div>
                    <div class="tr-ranking-table">
                        <table class="am-table am-table-centered am-margin-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>用户名</th>
                                    <th>选股成功率</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in success_rate">
                                    <td class="tr-ranking-portrait"><img :src="item.portrait" class="am-circle" width="30" alt=""></td>
                                    <td><a :href="item.url" :title="item.user_name">{{ item.user_name }}</a></td>
                                    <td><span :class="item.success_rate_class">{{ item.success_rate }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
        <p class="tr-img-ad">
            <a href="#" title="" target="_blank"><img src="/static/img/ad-img-1.png" alt="" class="am-img-responsive" /></a>
        </p>
        <div class="am-g">
            <div class="am-u-md-6 am-padding-right-sm">
                <section class="am-panel am-panel-default">
                    <div class="am-panel-hd">
                        <h2 class="am-panel-title ">
                            常胜牛人
                        </h2>
                    </div>
                    <div class="tr-ranking-table">
                        <table class="am-table am-table-centered am-margin-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>用户名</th>
                                    <th>周均收益</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in week_avg_profit_rate">
                                    <td class="tr-ranking-portrait"><img :src="item.portrait" class="am-circle" width="30" alt=""></td>
                                    <td><a :href="item.url" :title="item.user_name">{{ item.user_name }}</a></td>
                                    <td><span :class="item.week_avg_profit_rate_class">{{ item.week_avg_profit_rate }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="am-u-md-6 am-padding-left-sm">
                <section class="am-panel am-panel-default">
                    <div class="am-panel-hd">
                        <h2 class="am-panel-title ">
                            人气牛人
                        </h2>
                    </div>
                    <div class="tr-ranking-table">
                        <table class="am-table am-table-centered am-margin-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>用户名</th>
                                    <th>粉丝数</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="first">
                                    <td class="tr-ranking-portrait"><img src="/static/img/portrait.gif" class="am-circle" width="30" alt=""></td>
                                    <td><a href="" title="mo_2057">mo_2057**</a></td>
                                    <td>121</td>
                                </tr>
                                <tr>
                                    <td class="tr-ranking-portrait"><img src="/static/img/portrait.gif" class="am-circle" width="30" alt=""></td>
                                    <td><a href="" title="mo_2057">mo_2057**</a></td>
                                    <td>155</td>
                                </tr>
                                <tr>
                                    <td class="tr-ranking-portrait"><img src="/static/img/portrait.gif" class="am-circle" width="30" alt=""></td>
                                    <td><a href="" title="mo_2057">mo_2057**</a></td>
                                    <td>33</td>
                                </tr>
                                <tr>
                                    <td class="tr-ranking-portrait"><img src="/static/img/portrait.gif" class="am-circle" width="30" alt=""></td>
                                    <td><a href="" title="mo_2057">mo_2057**</a></td>
                                    <td>22</td>
                                </tr>
                                <tr>
                                    <td class="tr-ranking-portrait"><img src="/static/img/portrait.gif" class="am-circle" width="30" alt=""></td>
                                    <td><a href="" title="mo_2057">mo_2057**</a></td>
                                    <td>11</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
        <div class="am-panel am-panel-default tr-match">
            <div class="am-panel-hd">
                <h2 class="am-panel-title">
                    模拟赛场
                </h2>
            </div>
            <div class="am-panel-bd am-container">
                <div class="am-u-md-6 am-padding-sm">
                    <div class="am-u-sm-5">
                        <a href="#"><img src="/static/img/match.png" alt="第2期周赛" width="100%" /></a>
                    </div>
                    <div class="am-u-sm-7 am-padding-left-sm am-margin-top">
                        <div class="am-u-sm-12">
                            <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                            <span class="">比赛中</span>
                        </div>
                        <p class="am-margin-vertical-0">
                            报名时间：2016-06-01 到 2016-06-30
                            <br> 比赛时间：2016-06-01 到 2016-06-30
                        </p>
                    </div>
                </div>
                <div class="am-u-md-6 am-padding-sm">
                    <div class="am-u-sm-5">
                        <a href="#"><img src="/static/img/match.png" alt="第2期周赛" width="100%" /></a>
                    </div>
                    <div class="am-u-sm-7 am-padding-left-sm am-margin-top">
                        <div class="am-u-sm-12">
                            <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                            <span class="">比赛中</span>
                        </div>
                        <p class="am-margin-vertical-0">
                            报名时间：2016-06-01 到 2016-06-30
                            <br> 比赛时间：2016-06-01 到 2016-06-30
                        </p>
                    </div>
                </div>
                <div class="am-u-md-6 am-padding-sm">
                    <div class="am-u-sm-5">
                        <a href="#"><img src="/static/img/match.png" alt="第2期周赛" width="100%" /></a>
                    </div>
                    <div class="am-u-sm-7 am-padding-left-sm am-margin-top">
                        <div class="am-u-sm-12">
                            <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                            <span class="">比赛中</span>
                        </div>
                        <p class="am-margin-vertical-0">
                            报名时间：2016-06-01 到 2016-06-30
                            <br> 比赛时间：2016-06-01 到 2016-06-30
                        </p>
                    </div>
                </div>
                <div class="am-u-md-6 am-padding-sm">
                    <div class="am-u-sm-5">
                        <a href="#"><img src="/static/img/match.png" alt="第2期周赛" width="100%" /></a>
                    </div>
                    <div class="am-u-sm-7 am-padding-left-sm am-margin-top">
                        <div class="am-u-sm-12">
                            <a href="#" title="第2期周赛" class="am-margin-right tr-simulation-name">第2期周赛</a>
                            <span class="">比赛中</span>
                        </div>
                        <p class="am-margin-vertical-0">
                            报名时间：2016-06-01 到 2016-06-30
                            <br> 比赛时间：2016-06-01 到 2016-06-30
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
	<div class="am-g am-text-center tr-copy">
		&copy;2016 成都水晶球股份有限公司  All rights reserved.
	</div>
</footer>

<!--[if (gte IE 9)|!(IE)]><!-->
<script src="/static/js/public/jquery.min.js"></script>
<!--<![endif]-->
<script src="/static/js/public/amazeui.min.js"></script>
<script src="/static/js/public/vue.js"></script>

<script src="/static/js/public/common.js"></script>

 <script type="text/javascript" src="/static/js/widget/carouFredSel.js"></script> <script type="text/javascript" src="/static/js/index/index.js"></script> 
</body>
</html>