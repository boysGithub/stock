
var index = new Vue({
    el: "#index",
    data: {
        cache_t: 600000,//毫秒
        count: 0,
        close: '',
        ad_slider: [],//轮播
        proclamation: [],//公告
        recommend: [],//牛人推荐
        talent_dynamic: [],//牛人动态
        days_rate: [],//日盈利率
        week_rate: [],//周赛排名
        month_rate: [],//月赛排名
        total_rate_5: [],//总收益榜
        total_rate_10: [],//总盈利率
        success_rate: [],//选股牛人
        index_banner: {},//banner
        week_avg_profit_rate: [],//常胜牛人
        fans: [],//人气牛人
        week_matchs: [],//周赛
        month_matchs: []//月赛
    },
    computed: {
        logined: function(){
            return header.logined;
        }
    },
    methods: {
        updateAdSlider(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('ad_slider');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp){
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

                        //localStorage.setItem('ad_slider',JSON.stringify({timestamp: timestamp + _this.cache_t, data: ad_slider}));
                        _this.ad_slider = ad_slider;
                    }
                });
            // } else {
            //     this.ad_slider = data.data;
            // } 
            setTimeout(function(){
                $('#tr-slider-ad').flexslider({
                    directionNav: false,
                    slideshowSpeed:3000
                });
            }, 300);
        },
        updateProclamation(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('proclamation');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp){
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

                        //localStorage.setItem('proclamation',JSON.stringify({timestamp: timestamp + _this.cache_t, data: proclamation}));
                        _this.proclamation = proclamation;
                    }
                });
            // } else {
            //     this.proclamation = data.data;
            // }    
            setTimeout(proclamationSlider, 200);
        },
        updateRecommend(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('recommend');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/user/getRecommend',{},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var recommend = [];
                        for (var i = 0; i < ret.length; i++) {
                            recommend.push({
                                user_name: ret[i].username,
                                week_rate: (ret[i].week_rate).toFixed(2) + '%',
                                week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                ranking: ret[i].ranking,
                                avatar: ret[i].avatar,
                                reason: ret[i].reason,
                                uid: ret[i].uid
                            });
                        }

                        //localStorage.setItem('recommend',JSON.stringify({timestamp: timestamp + _this.cache_t, data: recommend}));
                        _this.recommend = recommend;
                    }
                });
            // } else {
            //     this.recommend = data.data;
            // } 
            setTimeout(function(){
                $('#tr-slider').flexslider({
                     itemWidth: 320, 
                     itemMargin: 20, 
                     slideshow: false, 
                     controlNav: false
                });
            }, 250);
        },
        updateTalentDynamic(){
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
                            portrait: ret[i].avatar,
                            stock: ret[i].stock_name+'('+ret[i].stock+')',
                            stock_url: header.getStockUrl(ret[i].stock),
                            state: state,
                            state_class: state_class,
                            price: (ret[i].price).toFixed(2),
                            time: ret[i].time.substring(0,16),
                            uid: ret[i].uid
                        });
                    }

                    _this.talent_dynamic = talent_dynamic;
                }
            });    
        },
        updateMatchRank(type){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('week_rate');
            // if(type == 2){
            //     data = localStorage.getItem('month_rate');
            // }
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host + '/match/detail',{type:type, np: 1, limit: 10},function(data){
                    if(data.status == 'success'){
                        var ret = data.data.rankList;
                        var match_rate = [];
                        if(ret.length > 0){
                            for (var i = 0; i < ret.length; i++) {
                                match_rate.push({
                                    user_name: ret[i].username,
                                    portrait: ret[i].avatar,
                                    ranking: ret[i].ranking,
                                    ranking_icon: (ret[i].ranking < 4) ? ' tr-icon' : '',
                                    week_rate: (ret[i].week_rate).toFixed(2) + '%',
                                    week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    days_rate: (ret[i].days_rate).toFixed(2) + '%',
                                    days_rate_class: (ret[i].days_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    month_rate: (ret[i].month_rate).toFixed(2) + '%',
                                    month_rate_class: (ret[i].month_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    total_rate: (ret[i].total_profit_rate).toFixed(2) + '%',
                                    total_rate_class: (ret[i].total_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    success_rate: (ret[i].success_rate).toFixed(2) + '%',
                                    avg_position_day: ret[i].avg_position_day,
                                    week_avg_profit_rate: (ret[i].week_avg_profit_rate).toFixed(2) + '%',
                                    week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }
                        }    
                        switch(type){
                            case 1:
                                //localStorage.setItem('week_rate',JSON.stringify({timestamp: timestamp + _this.cache_t, data: match_rate}));
                                _this.week_rate = match_rate;
                            break;
                            case 2:
                                //localStorage.setItem('month_rate',JSON.stringify({timestamp: timestamp + _this.cache_t, data: match_rate}));
                                _this.month_rate = match_rate;
                            break;
                        }
                    }
                });
            // } else {    
            //     switch(type){
            //         case 1:
            //             this.week_rate = data.data;
            //         break;
            //         case 2:
            //             this.month_rate = data.data;
            //         break;
            //     }
            // } 
        },
        updateDaysRate(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('days_rate_10');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host+'/rank/rateRank',{limit: 10},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var days_rate = [];
                        if(ret.length > 0){
                            for (var i = 0; i < ret.length; i++) {
                                days_rate.push({
                                    user_name: ret[i].username,
                                    portrait: ret[i].avatar,
                                    ranking: ret[i].ranking,
                                    ranking_icon: (ret[i].ranking < 4) ? ' tr-icon' : '',
                                    week_rate: (ret[i].week_rate).toFixed(2) + '%',
                                    week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    days_rate: (ret[i].days_rate).toFixed(2) + '%',
                                    days_rate_class: (ret[i].days_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    month_rate: (ret[i].month_rate).toFixed(2) + '%',
                                    month_rate_class: (ret[i].month_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    total_rate: (ret[i].total_rate).toFixed(2) + '%',
                                    total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    success_rate: (ret[i].success_rate).toFixed(2) + '%',
                                    avg_position_day: ret[i].avg_position_day,
                                    week_avg_profit_rate: (ret[i].week_avg_profit_rate).toFixed(2) + '%',
                                    week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }
                        }    

                        //localStorage.setItem('days_rate_10',JSON.stringify({timestamp: timestamp + _this.cache_t, data: days_rate}));
                        _this.days_rate = days_rate;
                    }
                });
            // } else {    
            //     this.days_rate = data.data;
            // } 
        },
        updateTotalRate(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('total_rate_5');
            // var data_10 = localStorage.getItem('total_rate_10');
            // data = JSON.parse(data);
            // data_10 = JSON.parse(data_10);
            // if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host+'/rank/getRankList',{condition:'total_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var total_rate_5 = [];
                        var total_rate_10 = [];
                        var length = (ret.length > 10) ? 10 : ret.length;
                        for (var i = 0; i < length; i++) {
                            if(i < 5){
                                total_rate_5.push({
                                    user_name: ret[i].username,
                                    portrait: ret[i].avatar,
                                    total_rate: (ret[i].total_rate).toFixed(2)+'%',
                                    total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }
                            total_rate_10.push({
                                user_name: ret[i].username,
                                portrait: ret[i].avatar,
                                rownum: ret[i].rownum,
                                rownum_class: (ret[i].rownum > 3) ? '' : ' tr-icon',
                                week_rate: (ret[i].week_rate).toFixed(2) + '%',
                                week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                days_rate: (ret[i].days_rate).toFixed(2) + '%',
                                days_rate_class: (ret[i].days_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                month_rate: (ret[i].month_rate).toFixed(2) + '%',
                                month_rate_class: (ret[i].month_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                total_rate: (ret[i].total_rate).toFixed(2)+'%',
                                total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                success_rate: (ret[i].success_rate).toFixed(2)+'%',
                                avg_position_day: ret[i].avg_position_day,
                                week_avg_profit_rate: (ret[i].week_avg_profit_rate).toFixed(2)+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        //localStorage.setItem('total_rate_5',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: total_rate_5}));
                        //localStorage.setItem('total_rate_10',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: total_rate_10}));
                        _this.total_rate_5 = total_rate_5;
                        _this.total_rate_10 = total_rate_10;
                    }    
                });
            // } else {
            //     this.total_rate_5 = data.data;
            //     this.total_rate_10 = data_10.data;
            // }    
        },
        updateSuccessRate(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('success_rate');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'success_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var success_rate = [];
                        var length = (ret.length > 5) ? 5 : ret.length;
                        for (var i = 0; i < length; i++) {
                            success_rate.push({
                                user_name: ret[i].username,
                                portrait: ret[i].avatar,
                                success_rate: (ret[i].success_rate).toFixed(2)+'%',
                                success_rate_class: (ret[i].success_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        //localStorage.setItem('success_rate',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: success_rate}));
                        _this.success_rate = success_rate;
                    }    
                });
            // } else {
            //     this.success_rate = data.data;
            // }    
        },
        updateIndexBanner(){
            var _this = this;
            $.getJSON(api_host + '/ad',{type:3},function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    if(ret.length > 0){
                        _this.index_banner = {url:ret[0].url, title: ret[0].title, image: ret[0].image};
                    }
                }
            }); 
        },
        updateweekAvgRate(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('week_avg_profit_rate');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp){                
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'week_avg_profit_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var week_avg_profit_rate = [];
                        var length = (ret.length > 5) ? 5 : ret.length;
                        for (var i = 0; i < length; i++) {
                            week_avg_profit_rate.push({
                                user_name: ret[i].username,
                                portrait: ret[i].avatar,
                                week_avg_profit_rate: (ret[i].week_avg_profit_rate).toFixed(2)+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        //localStorage.setItem('week_avg_profit_rate',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: week_avg_profit_rate}));
                        _this.week_avg_profit_rate = week_avg_profit_rate;
                    }    
                });
            // } else {
            //     this.week_avg_profit_rate = data.data;
            // }    
        },
        updateFans(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('fans');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp){                
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'fans'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var fans = [];
                        var length = (ret.length > 5) ? 5 : ret.length;
                        for (var i = 0; i < length; i++) {
                            fans.push({
                                user_name: ret[i].username,
                                portrait: ret[i].avatar,
                                fans: ret[i].fans,
                                uid: ret[i].uid
                            });
                        }

                        //localStorage.setItem('fans',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: fans}));
                        _this.fans = fans;
                    }    
                });
            // } else {
            //     this.fans = data.data;
            // }    
        },
        updateMatchs: function(type){               
            var _this = this;
            var r_data = {type: type, limit:2};
            if(this.logined){
                r_data.uid = header.user.uid;
            }
            $.getJSON(api_host+'/match/index',r_data,function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var matchs = [];
                    var sc = {1: ' tr-status-underway',2: ' tr-status-end', 3: ' tr-status-end'};
                    for (var i = 0; i < ret.length; i++) {
                        matchs.push({
                            name: ret[i].name,
                            image: ret[i].image,
                            id: ret[i].id,
                            start_date: ret[i].start_date.substring(0,10),
                            end_date: ret[i].end_date.substring(0,10),
                            joined: typeof(ret[i].joined) == 'undefined' ? 0 : ret[i].joined,
                            ranking: typeof(ret[i].ranking) == 'undefined' ? 0 : ret[i].ranking,
                            status: ret[i].status,
                            status_name: ret[i].status_name,
                            status_class: sc[ret[i].status]
                        });
                    }

                    switch(type){
                        case '1':
                            _this.week_matchs = matchs;
                            break;
                        case '2':
                            _this.month_matchs = matchs;
                            break;
                    }
                }    
            });
        },
        join_match: function(e){
            var id = e.currentTarget.id;
            var url = e.currentTarget.attributes["href-url"].nodeValue;
            if(header.logined){
                $.post(api_host + '/match/join',{id: id, uid: header.user.uid, token: header.user.token},function(data){
                    if(data.status == 'success'){
                        modal.imitateAlert(data.data, function(){
                            window.location.href = url; 
                        }); 
                    } else {
                        modal.imitateAlert('参加失败');
                    }  
                }, 'json');
            }else{
                window.location.href = url;
            }
        },
        updateIndex: function(){
            if(this.logined){
                this.updateMatchs('1');
                this.updateMatchs('2');
            } else {
                if(this.count < 10){
                    this.close = setTimeout(this.updateIndex, 300);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.updateAdSlider();
        this.updateProclamation();
        this.updateRecommend();
        this.updateTalentDynamic();
        this.updateDaysRate();
        this.updateMatchRank(1);
        this.updateMatchRank(2);
        this.updateTotalRate();
        this.updateSuccessRate();
        this.updateIndexBanner();
        this.updateweekAvgRate();
        this.updateFans();
        this.updateMatchs('1');
        this.updateMatchs('2');
        setTimeout(this.updateIndex, 100);
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
        width: 840,
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
 
