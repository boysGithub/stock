var index = new Vue({
    el: "#index",
    data: {
        proclamation: [],//公告
        talent_dynamic: [],//牛人动态
        total_rate_5: [],//总收益榜
        total_rate_10: [],//总盈利率
        success_rate: [],//选股牛人
        week_avg_profit_rate: []//常胜牛人
    }
});
var cache_t = 600000;//毫秒

$(function(){
    var AStorage = getLocalStorage(['proclamation','talent_dynamic','total_rate_5','total_rate_10','success_rate','week_avg_profit_rate']);

    //公告
    if(AStorage.proclamation == true){
        var data = [
            {href: 'http://www.sjqcj.com/weibo/715816', title: '水晶球牛人选股大赛第57周战况：名人组花荣顺利夺冠 温州叶荣添奇正藏药5天3板夺魁'},
            {href: 'http://www.sjqcj.com/weibo/712715', title: '水晶球2016推股高手排行榜出炉：金一平擒四川双马成第一高手！'},
            {href: 'http://www.sjqcj.com/weibo/714541', title: '金一平：最看好的高送转潜力股'},
            {href: 'http://www.sjqcj.com/weibo/715149', title: '选股比赛播报（11.18）：高送转第一枪打响，涨停板接踵而至'}
        ];
        localStorage.setItem('proclamation',JSON.stringify({timestamp: new Date().getTime() + cache_t, data: data}));
        index.proclamation = data;
    } else {
        index.proclamation = AStorage.proclamation;
    }
    setTimeout(proclamationSlider, 100);

    //牛人动态
    if(AStorage.talent_dynamic == true){
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
                        url: '#'+ret[i].uid
                    });
                }

                localStorage.setItem('talent_dynamic',JSON.stringify({timestamp: new Date().getTime() + cache_t, data: talent_dynamic}));
                index.talent_dynamic = talent_dynamic;
            }
        });
    } else {
        index.talent_dynamic = AStorage.talent_dynamic;
    }

    //总盈利率排行
    if(AStorage.total_rate_5 == true || AStorage.total_rate_10 == true){
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
                            portrait: '/static/img/portrait.gif',
                            total_rate: ret[i].total_rate+'%',
                            total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                            url: '#'+ret[i].uid
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
                        url: '#'+ret[i].uid
                    });
                }

                localStorage.setItem('total_rate_5',JSON.stringify({timestamp: new Date().getTime() + cache_t, data: total_rate_5}));
                localStorage.setItem('total_rate_10',JSON.stringify({timestamp: new Date().getTime() + cache_t, data: total_rate_10}));
                index.total_rate_5 = total_rate_5;
                index.total_rate_10 = total_rate_10;
            }    
        });
    } else {
        index.total_rate_5 = AStorage.total_rate_5;
        index.total_rate_10 = AStorage.total_rate_10;
    }

    //选股牛人
    if(AStorage.success_rate == true){
        $.getJSON(api_host + '/rank/getRankList',{condition:'success_rate'},function(data){
            if(data.status == 'success'){
                var ret = data.data;
                var success_rate = [];
                var length = (ret.length > 5) ? 5 : ret.length;
                for (var i = 0; i < length; i++) {
                    success_rate.push({
                        user_name: ret[i].username,
                        portrait: '/static/img/portrait.gif',
                        success_rate: ret[i].success_rate+'%',
                        success_rate_class: (ret[i].success_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                        url: '#'+ret[i].uid
                    });
                }

                localStorage.setItem('success_rate',JSON.stringify({timestamp: new Date().getTime() + cache_t, data: success_rate}));
                index.success_rate = success_rate;
            }    
        });
    } else {
        index.success_rate = AStorage.success_rate;
    }

    //常胜牛人
    if(AStorage.week_avg_profit_rate == true){
        $.getJSON(api_host + '/rank/getRankList',{condition:'week_avg_profit_rate'},function(data){
            if(data.status == 'success'){
                var ret = data.data;
                var week_avg_profit_rate = [];
                var length = (ret.length > 5) ? 5 : ret.length;
                for (var i = 0; i < length; i++) {
                    week_avg_profit_rate.push({
                        user_name: ret[i].username,
                        portrait: '/static/img/portrait.gif',
                        week_avg_profit_rate: ret[i].week_avg_profit_rate+'%',
                        week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                        url: '#'+ret[i].uid
                    });
                }

                localStorage.setItem('week_avg_profit_rate',JSON.stringify({timestamp: new Date().getTime() + cache_t, data: week_avg_profit_rate}));
                index.week_avg_profit_rate = week_avg_profit_rate;
            }    
        });
    } else {
        index.week_avg_profit_rate = AStorage.week_avg_profit_rate;
    }
});

function getLocalStorage(keys){
    var timestamp = new Date().getTime();
    var ret = [];
    for (var i = 0; i < keys.length; i++) {
        var data = localStorage.getItem(keys[i]);
        data = JSON.parse(data);
        var res = {};
        if(data == null || data.timestamp < timestamp){
            ret[keys[i]] = true;
        } else {
            ret[keys[i]] = data.data;
        }
    }

    return ret;
}

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
