var ranking = new Vue({
	el: "#ranking",
	data: {
		cache_t: 600000,
        talent_dynamic: [],//牛人动态
        week_rate: [],//周赛
        month_rate: [],//月赛
        total_rate: [],//总盈利率
	},
	methods: {
		updateTalentDynamic(){
            var _this = this;
            $.getJSON(api_host + '/orders',{},function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var talent_dynamic = [];
                    var length = ret.length;
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

                    _this.talent_dynamic = talent_dynamic;
                }
            });    
        },
        updateMatchRank(type){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('week_rate_100');
            if(type == 2){
                data = localStorage.getItem('month_rate_100');
            }
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host + '/match/detail',{type:type, np: 1, limit: 10},function(data){
                    if(data.status == 'success'){
                        var ret = data.data.rankList;
                        var match_rate = [];
                        if(ret.length > 0){
                            for (var i = 0; i < ret.length; i++) {
                                match_rate.push({
                                    user_name: ret[i].username,
                                    ranking: ret[i].ranking,
                                    ranking_icon: (ret[i].ranking < 4) ? ' tr-icon' : '',
                                    week_rate: ret[i].week_rate + '%',
                                    week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    days_rate: ret[i].days_rate + '%',
                                    days_rate_class: (ret[i].days_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    month_rate: ret[i].month_rate + '%',
                                    month_rate_class: (ret[i].month_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    total_rate: ret[i].total_rate + '%',
                                    total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    success_rate: ret[i].success_rate + '%',
                                    avg_position_day: ret[i].avg_position_day,
                                    week_avg_profit_rate: ret[i].week_avg_profit_rate + '%',
                                    week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                    uid: ret[i].uid
                                });
                            }
                        }    
                        switch(type){
                            case 1:
                                localStorage.setItem('week_rate_100',JSON.stringify({timestamp: timestamp + _this.cache_t, data: match_rate}));
                                _this.week_rate = match_rate;
                            break;
                            case 2:
                                localStorage.setItem('month_rate_100',JSON.stringify({timestamp: timestamp + _this.cache_t, data: match_rate}));
                                _this.month_rate = match_rate;
                            break;
                        }
                    }
                });
            } else {    
                switch(type){
                    case 1:
                        this.week_rate = data.data;
                    break;
                    case 2:
                        this.month_rate = data.data;
                    break;
                }
            } 
        },
        updateTotalRate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('total_rate_100');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:'total_rate'},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var total_rate = [];
                        var length = (ret.length > 100) ? 100 : ret.length;
                        for (var i = 0; i < length; i++) {
                            total_rate.push({
                                user_name: ret[i].username,
                                rownum: ret[i].rownum,
                                rownum_class: (ret[i].rownum > 3) ? '' : ' tr-icon',
                                week_rate: ret[i].week_rate + '%',
                                week_rate_class: (ret[i].week_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                days_rate: ret[i].days_rate + '%',
                                days_rate_class: (ret[i].days_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                month_rate: ret[i].month_rate + '%',
                                month_rate_class: (ret[i].month_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                total_rate: ret[i].total_rate+'%',
                                total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                success_rate: ret[i].success_rate+'%',
                                avg_position_day: ret[i].avg_position_day,
                                week_avg_profit_rate: ret[i].week_avg_profit_rate+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('total_rate_100',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: total_rate}));
                        _this.total_rate = total_rate;
                    }    
                });
            } else {
                this.total_rate = data.data;
            }    
        },
	},
	mounted: function(){
        this.updateTalentDynamic();
        this.updateMatchRank(1);
        this.updateMatchRank(2);
        this.updateTotalRate();
	}
});