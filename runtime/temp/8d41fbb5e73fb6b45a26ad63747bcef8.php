<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:83:"/Users/ducong/nginxroot/stock/public/../application/index/view/match/matchpage.html";i:1479798127;s:72:"/Users/ducong/nginxroot/stock/public/../application/index/view/base.html";i:1479455582;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/header.html";i:1479798992;s:81:"/Users/ducong/nginxroot/stock/public/../application/index/view/public/footer.html";i:1479187842;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<meta name="description" content="">
<meta name="keywords" content="">
<title>模拟赛场</title>

<!-- Amaze ui -->
<link rel="stylesheet" href="/static/amaze/css/amazeui.min.css">
<link rel="stylesheet" href="/static/amaze/css/app.css">


<link rel="stylesheet" type="text/css" href="/static/amaze/css/pagination.css" />
<style type="text/css">
    .tr-list{}
    .tr-list .tr-list-li,.tr-list .tr-list-li a{font-size: 14px;}
    .tr-list .tr-list-li{border:1px solid #e8e8e8;margin-top: 1rem; padding: 1rem;}
    .tr-list .tr-list-li .tr-list-main{border-right: 1px solid #e8e8e8;}
    .tr-list .tr-list-li .tr-list-button{ padding-top: 2rem;}


    @media only screen and (max-width: 1024px){  
        .tr-list .tr-list-li .tr-list-main{border-right: none;}
    }
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
            <div class="am-u-md-10">
                <div class="am-margin-bottom am-text-center">
                    <a href="" target="_blank" title="参加比赛">
                    <img src="/static/img/bisai_img.gif" alt="比赛广告"></a>
                </div>
                <div class="am-list-news-bd tr-list">
                    <ul class="am-list">
                        
                            <li class="am-g tr-list-li">
                                <div class="am-u-lg-10 tr-list-main">
                                    <div class="am-u-md-3 am-list-thumb am-text-center">
                                        <a href="<?php echo url('stocks/match/matchDetails'); ?>" title=""><img src="/static/img/u3890.png" alt="比赛广告" /></a>
                                    </div>
                                    <div class="am-u-md-9 am-list-main am-padding-left">
                                        <div class="am-u-md-12">
                                            <label>比赛名称：</label><a href="<?php echo url('stocks/match/matchDetails'); ?>" title=""><span></span></a>
                                        </div>
                                        <div class="am-g">
                                            <div class="am-u-md-7">
                                                <label>比赛时间：</label><span></span>
                                            </div>
                                            <div class="am-u-md-5">
                                                <label>我在比赛中的排名：</label><span>名</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="am-u-lg-2 am-list-main am-text-center tr-list-button">
                                    
                                        <a href="<?php echo url('stocks/match/matchDetails'); ?>" class="am-btn am-btn-warning am-btn-lg" onclick="jumpDetails('',0)">参加比赛</a>
                                    
                                        <!-- <a href="<?php echo url('stocks/match/matchDetails'); ?>" class="am-btn am-btn-danger am-btn-lg" onclick="jumpDetails('',1)">已参加</a> -->
                                    
                                </div>
                            </li>
                        
                    </ul>
                </div>    
                <div class="pages am-text-center">
                    
                </div>
            </div>
            <div class="am-u-md-3">

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


    <script>
        /**
         *
         * @param id 数据id
         * @param type 参加类型 0 为未参加 1为已参加
         */
        function jumpDetails(id,type){
            var url  = "matchds/"+id+"/"+type+"";
            window.location.href = url;
        }
    </script>

</body>
</html>