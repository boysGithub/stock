var match_details = new Vue({
    el: "#match_details",
    data: {
        cache_t: 600000,//毫秒
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
                $.getJSON(api_host + '/match/detail',{id: id},function(data){
                    if(data.status == 'success'){
                        var ret = data.data.rankList;
                        var match = data.data.match;
                        var ranking = [];
                        for (var i = 0; i < ret.length; i++) {
                            ranking.push({
                                uid: ret[i].uid,
                                user_name: ret[i].user_name,
                                total_rate: ret[i].total_rate + '%',
                                total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                ranking: ret[i].ranking,
                                ranking_class: (ret[i].ranking < 4) ? ' tr-icon' : ''
                            });
                        }

                        localStorage.setItem('matchRanking',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, ranking: ranking, match: match}));
                        _this.match = match;
                        _this.ranking = ranking;
                    }    
                });
            } else {
                this.match = data.match;
                this.ranking = data.ranking;
            } 
        } 
    },
    created(){
        this.updateMatch();
    }
});
