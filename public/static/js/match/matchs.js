var matchs = new Vue({
    el: "#matchs",
    data: {
        count: 0,
        close: '',
        match_banner: {},
        matchList: [],//赛场
        match_page: {page:0, page_total:0}//赛场
    },
    computed: {
        logined: function(){
            return header.logined;
        }
    },
    components: {
        'match-page': Vnav
    },
    methods: {
        join_match: function(e){
            var id = e.currentTarget.id;
            var url = e.currentTarget.attributes["href-url"].nodeValue;
            $.post(api_host + '/match/join',{id: id, uid: header.user.uid, token: header.user.token},function(data){
                if(data.status == 'success'){
                    modal.imitateAlert(data.data, function(){
                        window.location.href = url; 
                    }); 
                } else {
                    modal.imitateAlert('参加失败');
                }      
            }, 'json');
        },
        updateMatchs(page){               
            var _this = this;
            var r_data = {np:page};
            if(header.user.uid > 0){
                r_data = {uid: header.user.uid};
            }
            $.getJSON(api_host + '/match/index',r_data,function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var matchList = [];
                    var sc = {1: ' tr-status-underway',2: ' tr-status-end', 3: ' tr-status-end'};
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
                            status_name: ret[i].status_name,
                            status_class: sc[ret[i].status]
                        });
                    }

                    _this.match_page = {page: page, page_total: data.pageTotal};
                    _this.matchList = matchList;
                }    
            });
        },
        updateMatchBanner(){
            var _this = this;
            $.getJSON(api_host + '/ad',{type:4},function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    if(ret.length > 0){
                        _this.match_banner = {url:ret[0].url, title: ret[0].title, image: ret[0].image};
                    }    
                }
            }); 
        },
        getMatchs(){
            if(this.logined){
                this.updateMatchs(1);
            } else {
                if(this.count < 10){
                    this.close = setTimeout(this.getMatchs, 300);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.updateMatchs(1);
        this.updateMatchBanner();
        setTimeout(this.getMatchs, 100);
    }
});
