var report = new Vue({
    el: "#report",
    data: {
        cache_t: 600000,//毫秒
        reports: [],//
        page: {p:0, total:0},//
    },
    methods: {
        updateReports(){               
            var _this = this;
            $.getJSON('http://www.tp5.com/ad/index.html',{type:5},function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var reports = [];
                    for (var i = 0; i < ret.length; i++) {
                        reports.push({
                            href: ret[i].url,
                            title: ret[i].title,
                            time: ret[i].time
                        });
                    }

                    _this.reports = reports;
                }    
            });
        } 
    },
    mounted: function(){
        this.updateReports();
    }
});
