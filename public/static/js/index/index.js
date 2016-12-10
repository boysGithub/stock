var index = new Vue({
    el: "#index",
    data: {
        cache_t: 600000,//毫秒
        ad_slider: [],//轮播
        proclamation: [],//公告
        recommend: [],//牛人推荐
        talent_dynamic: [],//牛人动态
        week_rate: [],//周赛
        month_rate: [],//月赛
        total_rate_5: [],//总收益榜
        total_rate_10: [],//总盈利率
        success_rate: [],//选股牛人
        week_avg_profit_rate: [],//常胜牛人
        matchs: []//赛场
    },
    methods: {
        ad_slider(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('ad_slider');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/ad',{type:1},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var ad_slider = [];
                        for (var i = 0; i < ret.length; i++) {
                            ad_slider.push({
                                href: ret[i].url,
                                title: ret[i].title,
                                img: ret[i].image
                            });
                        }

                        localStorage.setItem('ad_slider',JSON.stringify({timestamp: timestamp + _this.cache_t, data: ad_slider}));
                        _this.ad_slider = ad_slider;
                    }
                });
            } else {
                this.ad_slider = data.data;
            } 
            setTimeout(function(){
                $('#tr-slider-ad').flexslider({
                    directionNav: false,
                    slideshowSpeed:3000
                });
            }, 100);
        },
        updateProclamation(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('proclamation');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/ad',{type:2},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var proclamation = [];
                        for (var i = 0; i < ret.length; i++) {
                            proclamation.push({
                                href: ret[i].url,
                                title: ret[i].title
                            });
                        }

                        localStorage.setItem('proclamation',JSON.stringify({timestamp: timestamp + _this.cache_t, data: proclamation}));
                        _this.proclamation = proclamation;
                    }
                });
            } else {
                this.proclamation = data.data;
            }    
            setTimeout(proclamationSlider, 300);
        },
        recommend(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('recommend');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/user/getRecommend',{},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var recommend = [];
                        for (var i = 0; i < ret.length; i++) {
                            recommend.push({
                                user_name: ret[i].username,
                                week_rate: ret[i].week_rate + '%',
                                week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                ranking: ret[i].ranking,
                                avatar: ret[i].avatar,
                                reason: ret[i].reason,
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('recommend',JSON.stringify({timestamp: timestamp + _this.cache_t, data: recommend}));
                        _this.recommend = recommend;
                    }
                });
            } else {
                this.recommend = data.data;
            } 
            setTimeout(function(){
                $('#tr-slider').flexslider({
                     itemWidth: 320, 
                     itemMargin: 20, 
                     slideshow: false, 
                     controlNav: false
                });
            }, 200);
        },
        updateTalentDynamic(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('talent_dynamic');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/orders',{},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var talent_dynamic = [];
                        var length = (ret.length > 10) ? 10 : ret.length;
                        for (var i = 0; i < length; i++) {
                            var state = '';
                            var state_class = '';
                            if(ret[i].type == 1){
                                state = '买入';
                                state_class = 'tr-color-buy';
                            } else {
                                state = '卖出';
                                state_class = 'tr-color-sale';
                            }
                            talent_dynamic.push({
                                user_name: ret[i].username,
                                stock: ret[i].stock_name+'('+ret[i].stock+')',
                                state: state,
                                state_class: state_class,
                                price: ret[i].price,
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('talent_dynamic',JSON.stringify({timestamp: timestamp + _this.cache_t, data: talent_dynamic}));
                        _this.talent_dynamic = talent_dynamic;
                    }
                });
            } else {
                this.talent_dynamic = data.data;
            }    
        },
        week_rate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('week_rate');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/match/detail',{type:1, np: 1, limit: 10},function(data){
                    if(data.status == 'success'){
                        var ret = data.data.rankList;
                        var week_rate = [];
                        if(ret.length > 0){
                            for (var i = 0; i < ret.length; i++) {
                                week_rate.push({
                                    user_name: ret[i].user_name,
                                    ranking: ret[i].ranking,
                                    ranking_icon: (ret[i].ranking < 4) ? ' tr-icon' : '',
                                    total_rate: ret[i].total_rate + '%',
                                    total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }

                            localStorage.setItem('week_rate',JSON.stringify({timestamp: timestamp + _this.cache_t, data: week_rate}));
                        }
                        _this.week_rate = week_rate;
                    }
                });
            } else {
                this.week_rate = data.data;
            } 
        },
        month_rate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('month_rate');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/match/detail',{type:2, np: 1, limit: 10},function(data){
                    if(data.status == 'success'){
                        var ret = data.data.rankList;
                        var month_rate = [];
                        if(ret.length > 0){
                            for (var i = 0; i < ret.length; i++) {
                                month_rate.push({
                                    user_name: ret[i].user_name,
                                    ranking: ret[i].ranking,
                                    ranking_icon: (ret[i].ranking < 4) ? ' tr-icon' : '',
                                    total_rate: ret[i].total_rate + '%',
                                    total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }

                            localStorage.setItem('month_rate',JSON.stringify({timestamp: timestamp + _this.cache_t, data: month_rate}));
                        }
                        _this.month_rate = month_rate;
                    }
                });
            } else {
                this.month_rate = data.data;
            }
        },
        updateTotalRate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('total_rate_5');
            var data_10 = localStorage.getItem('total_rate_10');
            data = JSON.parse(data);
            data_10 = JSON.parse(data_10);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'total_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var total_rate_5 = [];
                        var total_rate_10 = [];
                        var length = (ret.length > 10) ? 10 : ret.length;
                        for (var i = 0; i < length; i++) {
                            if(i < 5){
                                total_rate_5.push({
                                    user_name: ret[i].username,
                                    portrait: getAvatar(ret[i].uid),
                                    total_rate: ret[i].total_rate+'%',
                                    total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }
                            total_rate_10.push({
                                user_name: ret[i].username,
                                rownum: ret[i].rownum,
                                rownum_class: (ret[i].rownum > 3) ? '' : ' tr-icon',
                                total_rate: ret[i].total_rate+'%',
                                total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                success_rate: ret[i].success_rate+'%',
                                avg_position_day: ret[i].avg_position_day,
                                week_avg_profit_rate: ret[i].week_avg_profit_rate+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('total_rate_5',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: total_rate_5}));
                        localStorage.setItem('total_rate_10',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: total_rate_10}));
                        _this.total_rate_5 = total_rate_5;
                        _this.total_rate_10 = total_rate_10;
                    }    
                });
            } else {
                this.total_rate_5 = data.data;
                var lg = data_10.data;
                this.total_rate_10 = data_10.data;
            }    
        },
        updateSuccessRate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('success_rate');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'success_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var success_rate = [];
                        var length = (ret.length > 5) ? 5 : ret.length;
                        for (var i = 0; i < length; i++) {
                            success_rate.push({
                                user_name: ret[i].username,
                                portrait: getAvatar(ret[i].uid),
                                success_rate: ret[i].success_rate+'%',
                                success_rate_class: (ret[i].success_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('success_rate',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: success_rate}));
                        _this.success_rate = success_rate;
                    }    
                });
            } else {
                this.success_rate = data.data;
            }    
        },
        week_avg_profit_rate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('week_avg_profit_rate');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'week_avg_profit_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var week_avg_profit_rate = [];
                        var length = (ret.length > 5) ? 5 : ret.length;
                        for (var i = 0; i < length; i++) {
                            week_avg_profit_rate.push({
                                user_name: ret[i].username,
                                portrait: getAvatar(ret[i].uid),
                                week_avg_profit_rate: ret[i].week_avg_profit_rate+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('week_avg_profit_rate',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: week_avg_profit_rate}));
                        _this.week_avg_profit_rate = week_avg_profit_rate;
                    }    
                });
            } else {
                this.week_avg_profit_rate = data.data;
            }    
        },
        updateMatchs(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('matchs');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){                
                var _this = this;
                $.getJSON(api_host + '/match/index',{limit:4},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var matchs = [];
                        for (var i = 0; i < ret.length; i++) {
                            matchs.push({
                                name: ret[i].name,
                                image: ret[i].image,
                                id: ret[i].id,
                                start_date: ret[i].start_date,
                                end_date: ret[i].end_date,
                                status_name: ret[i].status_name
                            });
                        }

                        localStorage.setItem('matchs',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: matchs}));
                        _this.matchs = matchs;
                    }    
                });
            } else {
                this.matchs = data.data;
            } 
        } 
    },
    created(){
        this.ad_slider();
        this.updateProclamation();
        this.recommend();
        this.updateTalentDynamic();
        this.week_rate();
        this.month_rate();
        this.updateTotalRate();
        this.updateSuccessRate();
        this.week_avg_profit_rate();
        this.updateMatchs();
    }
});


function proclamationSlider(){
    var _scroll = {
        delay: 1000,
        easing: 'linear',
        items: 1,
        duration: 0.04,
        timeoutDuration: 0,
        pauseOnHover: 'immediate'
    };
    $('#ticker-1').carouFredSel({
        width: 783,
        align: false,
        items: {
            width: 'variable',
            height: 24,
            visible: 1,
            margin: 0
        },
        scroll: _scroll
    });
} 
 
