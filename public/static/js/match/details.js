var match_details = new Vue({
    el: "#match_details",
    data: {
        cache_t: 600000,//毫秒
        uid: header.user.uid,
        match: [],//比赛
        ranking: []//比赛排行
    },
    methods: {
        updateMatch(){
            var timestamp = new Date().getTime();
            var data = localStorage.getItem('matchRanking');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){                
                var _this = this;
                var id = $("#match_id").val();
                var r_data = {id: id};
                if(_this.uid > 0){
                    r_data.uid = _this.uid;
                }
                $.getJSON(api_host + '/match/detail',r_data,function(data){
                    if(data.status == 'success'){
                        var ret = data.data.rankList;
                        var ranking = [];
                        for (var i = 0; i < ret.length; i++) {
                            ranking.push({
                                user_name: ret[i].username,
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

                        localStorage.setItem('matchRanking',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, ranking: ranking, match: data.data.match}));
                        _this.match = data.data.match;
                        _this.ranking = ranking;
                    }    
                });
            } else {
                this.match = data.match;
                this.ranking = data.ranking;
            } 
        } 
    },
    mounted: function(){
        this.updateMatch();
    }
});
