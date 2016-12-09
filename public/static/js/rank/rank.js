var ranking = new Vue({
	el: "#ranking",
	data: {
		cache_t: 600000,
		rankingList: []//排行榜
	},
	methods: {
		updateRanking(){
			var timestamp = new Date().getTime();
            var data = localStorage.getItem('rankingList');
            data = JSON.parse(data);
            if(data == null || data.timestamp < timestamp){                
                var _this = this;
                var order = $("#order").val();
                $.getJSON(api_host + '/rank/getRankList',{condition: order, p: 1, limit:100},function(data){
                    if(data.status == 'success'){
                        var ret = data.data;
                        var rankingList = [];
                        for (var i = 0; i < ret.length; i++) {
                            rankingList.push({
                                uid: ret[i].uid,
                                username: ret[i].username,
                                total_rate: ret[i].total_rate . '%',
                                total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                                success_rate: ret[i].success_rate . '%',
                                week_avg_profit_rate: ret[i].week_avg_profit_rate . '%',
                                avg_position_day: ret[i].avg_position_day,
                                ranking: ret[i].ranking
                            });
                        }

                        localStorage.setItem('rankingList',JSON.stringify({timestamp: new Date().getTime() + _this.cache_t, data: rankingList}));
                        _this.rankingList = rankingList;
                    }    
                });
            } else {
                this.rankingList = data.data;
            } 
		}
	},
	created(){
		this.updateRanking();
	}
});