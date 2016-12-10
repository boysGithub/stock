var ranking = new Vue({
	el: "#ranking",
	data: {
		cache_t: 600000,
		rankingList: []//排行榜
	},
	methods: {
		updateRanking(){              
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
                            total_rate: ret[i].total_rate + '%',
                            total_rate_class: (ret[i].total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                            success_rate: ret[i].success_rate + '%',
                            week_avg_profit_rate: ret[i].week_avg_profit_rate + '%',
                            avg_position_day: ret[i].avg_position_day,
                            ranking: ret[i].rownum,
                            ranking_class: (ret[i].rownum < 4) ? ' tr-icon' : ''
                        });
                    }

                    _this.rankingList = rankingList;
                }    
            });
		}
	},
	created(){
		this.updateRanking();
	}
});