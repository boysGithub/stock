<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:86:"/Users/ducong/nginxroot/stock/public/../application/index/view/match/matchDetails.html";i:1479798617;s:72:"/Users/ducong/nginxroot/stock/public/../application/index/view/base.html";i:1479455582;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/header.html";i:1479700274;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/footer.html";i:1479187842;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<meta name="description" content="">
<meta name="keywords" content="">
<title>比赛名称</title>

<!-- Amaze ui -->
<link rel="stylesheet" href="/static/amaze/css/amazeui.min.css">
<link rel="stylesheet" href="/static/amaze/css/app.css">


<link rel="stylesheet" type="text/css" href="/static/amaze/css/pagination.css" />
<style type="text/css">
.tr-match-name h2{color: #cd3333; font-size: 2rem;}
.tr-tabs .am-tabs-nav li a{font-size: 16px; border-radius: 4px 4px 0 0; background-color: #eee;border-width: 1px; border-color: #999 #999 #39f; border-style: solid;}
.tr-tabs .am-nav>li>a:focus, .tr-tabs .am-nav>li>a:hover{background-color: #39f; color: #fff; border-color: #39f;}
.tr-tabs .am-nav-tabs>li.am-active>a, .tr-tabs .am-nav-tabs>li.am-active>a:focus, .tr-tabs .am-nav-tabs>li.am-active>a:hover{background-color: #39f; color: #fff; border-color: #39f;}
.tr-tabs .am-tabs-bd,.tr-tabs .am-nav-tabs{border-color: #39f;border-radius: 0 2px 2px;}
.tr-tabs .am-table{margin-bottom: 0;}
.tr-table-ranking>tbody>tr>td{height: 42px; line-height: 42px;}
.tr-table-ranking>thead>tr>th{background:#f6f6f6; height: 28px; line-height: 28px; font-size: 1.4rem;font-weight: 400;}
.tr-table-ranking .tr-icon{width: 24px; height: 28px;}
.tr-table-ranking .tr-icon-1th{background-position: 3px -6px;}
.tr-table-ranking .tr-icon-2th{background-position: 3px -36px;}
.tr-table-ranking .tr-icon-3th{background-position: 3px -66px;}
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
				    <li class="<?php if(\think\Request::instance()->controller() == 'match' and \think\Request::instance()->action() == 'matchpage'): ?> am-active<?php endif; ?>"><a href="<?php echo url('stocks/match/matchpage'); ?>">模拟赛场</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'ranking' and \think\Request::instance()->action() == 'rankingList'): ?> am-active<?php endif; ?>"><a href="<?php echo url('stocks/ranking/rankingList'); ?>">牛人排行榜</a></li>
				    <li class="<?php if(\think\Request::instance()->controller() == 'page' and \think\Request::instance()->action() == 'index'): ?> am-active<?php endif; ?>"><a href="<?php echo url('stocks/page/index', array('key'=>'rule')); ?>">交易规则</a></li>
				</ul>
				</div>
			</div>
		</div>
	</div>
</header>

<div class="am-g tr-main">
    <div class="am-container">
        <div class="am-g">
            <div class="am-u-lg-12 am-text-center tr-match-name"><h2>比赛名称</h2></div>
        </div>
        <div class="am-tabs tr-tabs" id="tr-tabs">
            <ul class="am-tabs-nav am-nav am-nav-tabs">
                <li class="am-active"><a href="<?php echo url('stocks/ranking/rankingList', array('order'=>'day')); ?>">日盈利率排名</a></li>
                <li class="am-active"><a href="<?php echo url('stocks/ranking/rankingList', array('order'=>'week')); ?>">周盈利率排名</a></li>
                <li class="am-active"><a href="<?php echo url('stocks/ranking/rankingList', array('order'=>'month')); ?>">月盈利率排名</a></li>
                <li class="am-active"><a href="<?php echo url('stocks/ranking/rankingList', array('order'=>'total')); ?>">总盈利率排名</a></li>
            </ul>
            <div class="am-tabs-bd">
                <div class="am-tab-panel am-active">
                    <table class="am-table am-table-centered tr-table-ranking">
                        <thead>
                            <tr>
                                <th>排名</th>
                                <th>昵称</th>
                                <th class="tr-color-order">周收益率</th>
                                <th class="tr-color-order">月收益率</th>
                                <th class="tr-color-order">日收益率</th>
                                <th class="tr-color-order">总收益率</th>
                                <th>选股成功率</th>
                                <th>动态</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <tr>
                                <td><span class="tr-rank"></span></td>
                                <td><a href="<?php echo url('stocks/member/personal', array('id'=>1)); ?>" title=""></a></td>
                                <td><span class="tr-color-lose tr-color-order">%</span></td>
                                <td><span class="tr-color-lose tr-color-order">%</span></td>
                                <td><span class="tr-color-lose tr-color-order">%</span></td>
                                <td><span class="tr-color-win tr-color-order">%</span></td>
                                <td><span class="">%</span></td>
                                <td><span class=""><a href="<?php echo url('stocks/member/personal',array('id'=> 1)); ?>" title="">追踪可看</a></span></td>
                            </tr>
                            
                        </tbody>
                    </table>
                    <hr>
                    <div class="page am-text-center">
                        
                    </div>
                </div>    
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



</body>
</html>