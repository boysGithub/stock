var ranking = new Vue({
	el: "#ranking",
	data: {
		cache_t: 600000,
        talent_dynamic: [],//牛人动态
        days_rate: [],//日盈利率
        week_rate: [],//周赛
        month_rate: [],//月赛
        total_rate: [],//总盈利率
        success_rate: [],//选股牛人
        week_avg_profit_rate: [],//常胜牛人
        fans: [],//人气牛人
	},
	methods: {
		updateTalentDynamic(){
            var _this = this;
            $.getJSON(api_host + '/orders',{limit:100},function(data){
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
        updateDaysRate(){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('days_rate_100');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host + '/rank/rateRank',{limit: 100},function(data){
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

                        //localStorage.setItem('days_rate_100',JSON.stringify({timestamp: timestamp + _this.cache_t, data: days_rate}));
                        _this.days_rate = days_rate;
                    }
                });
            // } else {    
            //     this.days_rate = data.data;
            // } 
        },
        updateMatchRank(type){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem('week_rate_100');
            // if(type == 2){
            //     data = localStorage.getItem('month_rate_100');
            // }
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host + '/match/detail',{type:type, np: 1, limit: 100},function(data){
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
                                //localStorage.setItem('week_rate_100',JSON.stringify({timestamp: timestamp + _this.cache_t, data: match_rate}));
                                _this.week_rate = match_rate;
                            break;
                            case 2:
                                //localStorage.setItem('month_rate_100',JSON.stringify({timestamp: timestamp + _this.cache_t, data: match_rate}));
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
        updateTotalRate(order){
            // var timestamp = new Date().getTime();
            // var data = localStorage.getItem(order + '_100');
            // data = JSON.parse(data);
            // if(data == null || data.timestamp < timestamp || data.data.length < 1){
                var _this = this;
                $.getJSON(api_host + '/rank/getRankList',{condition:order},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var ranking = [];
                        var length = (ret.length > 100) ? 100 : ret.length;
                        for (var i = 0; i < length; i++) {
                            ranking.push({
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
                                fans: ret[i].fans,
                                week_avg_profit_rate: (ret[i].week_avg_profit_rate).toFixed(2)+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }


                        switch(order){
                            case 'total_rate':
                                //localStorage.setItem('total_rate_100',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: ranking}));
                                _this.total_rate = ranking;
                                break;
                            case 'success_rate':
                                //localStorage.setItem('success_rate_100',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: ranking}));
                                _this.success_rate = ranking;
                                break;
                            case 'week_avg_profit_rate':
                                //localStorage.setItem('week_avg_profit_rate_100',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: ranking}));
                                _this.week_avg_profit_rate = ranking;
                                break;
                            case 'fans':
                                //localStorage.setItem('fans_100',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: ranking}));
                                _this.fans = ranking;
                                break;
                        }
                    }    
                });
            // } else {
            //     switch(order){
            //         case 'total_rate':
            //             this.total_rate = data.data;
            //             break;
            //         case 'success_rate':
            //             this.success_rate = data.data;
            //             break;
            //         case 'week_avg_profit_rate':
            //             this.week_avg_profit_rate = data.data;
            //             break;
            //         case 'fans':
            //             this.fans = data.data;
            //             break;
            //     }
            // }    
        },
	},
	mounted: function(){
        var order = $("#order").val() ? $("#order").val() : 'total_rate';
        switch(order){
            case 'talent_dynamic':
                this.updateTalentDynamic();
            break;
            case 'days_rank':
                this.updateDaysRate();
            break;
            case 'week_rank':
                this.updateMatchRank(1);
            break;
            case 'month_rank':
                this.updateMatchRank(2);
            break;
            case 'total_rate':
                this.updateTotalRate('total_rate');
            break;
            case 'success_rate':
                this.updateTotalRate('success_rate');
            break;
            case 'week_avg_profit_rate':
                this.updateTotalRate('week_avg_profit_rate');
            break;
            case 'fans':
                this.updateTotalRate('fans');
            break;
        }
	}
});