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
        week_rate(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('week_rate');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){
                var _this = this;
                $.getJSON(api_host + '/match/detail',{type:1, np: 1, limit: 100},function(data){
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
                $.getJSON(api_host + '/match/detail',{type:2, np: 1, limit: 100},function(data){
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
            var data = localStorage.getItem('total_rate');
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
                                total_rate: ret[i].total_rate+'%',
                                total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                success_rate: ret[i].success_rate+'%',
                                avg_position_day: ret[i].avg_position_day,
                                week_avg_profit_rate: ret[i].week_avg_profit_rate+'%',
                                week_avg_profit_rate_class: (ret[i].week_avg_profit_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                uid: ret[i].uid
                            });
                        }

                        localStorage.setItem('total_rate',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: total_rate}));
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
        this.week_rate();
        this.month_rate();
        this.updateTotalRate();
	}
});