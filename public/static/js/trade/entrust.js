var entrust = new Vue({
    el: '#entrust',
    data: {
        uid: header.user.uid,
        today_entrust: [],//今日委托
        today_turnover: [],//今日成交
        history_entrust: [],//历史委托
        history_turnover: [],//历史成交
    },
    computed: {
    },
    methods: {
        updateOrder(type){
            var _this = this;
            var r_data = {type: type};
            if($.inArray(type, ['historical','trans']) != -1){
                r_data['stime'] = '2016-12-01';
                r_data['etime'] = '2017-12-01';
            }    

            $.getJSON(api_host + '/orders/'+_this.uid, r_data, function(data){
                if(data.status == 'success'){
                    var ret = data.data; 
                    var orders = [];
                    for (var i = 0; i < ret.length; i++) {
                        orders.push({
                            id: ret[i].id, 
                            title: ret[i].stock_name + '(' + ret[i].stock + ')', 
                            type_label: (ret[i].type == 1) ? '买入' : '卖出', 
                            type_class: (ret[i].type == 1) ? 'tr-color-buy' : 'tr-color-sale', 
                            price: parseFloat(ret[i].price), 
                            number: ret[i].number,
                            status_name: ret[i].status_name,
                            status: ret[i].status,
                            turnover: parseFloat((ret[i].number * ret[i].price).toFixed(2)),
                            number: ret[i].number, 
                            time: ret[i].time
                        });
                    }

                    switch(type){
                        case 'entrust':
                            _this.today_entrust = orders;
                            break;
                        case 'deal':
                            _this.today_turnover = orders;
                            break;
                        case 'historical':
                            _this.history_entrust = orders;
                            break;
                        case 'trans':
                            _this.history_turnover = orders;
                            break;
                    }
                }
            });    
        }
    },
    mounted: function(){
        this.updateOrder('entrust');
        this.updateOrder('deal');
        this.updateOrder('historical');
        this.updateOrder('trans');
    }
});