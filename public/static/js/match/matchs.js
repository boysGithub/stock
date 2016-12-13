var matchs = new Vue({
    el: "#matchs",
    data: {
        count: 0,
        close: '',
        matchList: []//赛场
    },
    computed: {
        logined: function(){
            return header.logined;
        }
    },
    methods: {
        join_match: function(e){
            var id = e.currentTarget.id;
            var url = e.currentTarget.attributes["href-url"].nodeValue;
            $.post(api_host + '/match/join',{id: id, uid: header.user.uid, token: header.user.token},function(data){
                if(data.status == 'success'){
                    window.location.href = url;
                }else{
                    alert(data.data);
                }     
            }, 'json');
        },
        updateMatchs(){               
            var _this = this;
            var r_data = {};
            if(header.user.uid > 0){
                r_data = {uid: header.user.uid};
            }
            $.getJSON(api_host + '/match/index',r_data,function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var matchList = [];
                    for (var i = 0; i < ret.length; i++) {
                        matchList.push({
                            name: ret[i].name,
                            image: ret[i].image,
                            id: ret[i].id,
                            start_date: ret[i].start_date,
                            joined: typeof(ret[i].joined) == 'undefined' ? 0 : ret[i].joined,
                            ranking: typeof(ret[i].ranking) == 'undefined' ? 0 : ret[i].ranking,
                            end_date: ret[i].end_date,
                            status: ret[i].status,
                            status_name: ret[i].status_name
                        });
                    }

                    _this.matchList = matchList;
                }    
            });
        },
        getMatchs(){
            if(this.logined){
                this.updateMatchs();
            } else {
                if(this.i < 10){
                    this.close = setTimeout(getMatchs, 300);
                    this.i += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.updateMatchs();
        setTimeout(getMatchs, 100);
    }
});
