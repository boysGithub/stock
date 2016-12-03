<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:78:"/Users/ducong/nginxroot/stock/public/../application/index/view/page/index.html";i:1479805602;s:72:"/Users/ducong/nginxroot/stock/public/../application/index/view/base.html";i:1480646252;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/header.html";i:1480644725;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/footer.html";i:1480644253;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<meta name="description" content="">
<meta name="keywords" content="">
<title>交易规则</title>

<!-- Amaze ui -->
<link rel="stylesheet" href="/static/css/public/amazeui.min.css">
<link rel="stylesheet" href="/static/css/public/app.css">


<style type="text/css">
.am-article{font-size: 14px;padding: 3rem 2rem;}
.am-article-main p {line-height: 26px;}
</style>


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

<div class="am-g">
    <div class="am-g am-container">
    	<div class="am-u-md-12 am-article">
    		<h1 class="am-article-title am-text-center">模拟交易规则</h1>
    		<div class="am-article-main">
    			<h2>交易规则</h2>
    			<p>
				<strong>1、交易时间：</strong>每天上午9：30-11：30,下午13：00-15：00 <br>
				模拟炒股接受24小时委托（清算时间除外），非交易时间和清算时间，用户的委托将参加下一次开市后的撮合。<br>
				清算时间：每日15：20-15：45，清算时间内不允许下单委托<br>
				交易时间和交易所规定的交易时间是同步的，在国家法定节假日只接受委托，但不会撮合成交
				</p>
				<p>
				<strong>2、交易制度</strong>
				</p>
				<p>
				交易种类：支持上交所和深交所两大交易所上市的A股股票、封闭式基金、国债、企业债券<br>
				注：清算同证券营业部基本一致。即证券T+1，权证T+0，资金T+0
				</p>
				<strong>交易类型：</strong>
				</p>
				<p>
				支持分红、派息、送股等业务，只针对于A股。比例根据交易所公布的公告来执行。<br>
				不支持新股申购、市值配售、增发申购、配股等交易。不支持派送权证、行权等操作
				</p>
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



</body>
</html>