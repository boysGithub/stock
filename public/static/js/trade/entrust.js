var entrust = new Vue({
    el: '#entrust',
    data: {
        count: 0,//计数
        close: '',//定时器
        positions: [],//当前持仓
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

            $.getJSON(api_host + '/orders/'+header.user.uid, r_data, function(data){
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
        },
        getPositions: function(){
            var _this = this;
            $.getJSON(api_host + '/users',{uid: header.user.uid},function(data){
                if(data.status == 'success'){
                    var positions = [];//持仓信息

                    for (var i = 0; i < data.data.length; i++) {
                        var stock = data.data[i];
                        var num = parseInt(stock.available_number) + parseInt(stock.freeze_number)
                        var position = {
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            available_number: num,//持仓
                            cost_price: parseFloat(stock.cost_price),//成本价
                            time: stock.time.substring(0,10)
                        };

                        var key = 'sz' + stock.stock;
                        if(parseInt(stock.stock.substring(0,1)) == 6){
                            key = 'sh' + stock.stock;
                        }

                        //股票交易信息
                        $.getScript(api_host + '/index/index/quiet?stock='+key,function(){
                            if(eval('hq_str_'+key)){
                                var detail = eval('hq_str_'+key).split(',');
                                var price = (detail['3'] > 0) ? detail['3'] : detail['2'];//现价
                                
                                position.assets = parseFloat(price * num);//市值
                                position.price = parseFloat(price);
                                position.profit = parseFloat((price * num - stock.cost).toFixed(2));//盈亏
                                position.ratio = parseFloat(((price * num - stock.cost) / stock.cost * 100).toFixed(2)) + '%';
                                position.ratio_class = ((price * num - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win';
                            } else {
                                position.assets = parseFloat(stock.assets);
                                position.profit = parseFloat(stock.assets - stock.cost);
                                position.price = 0;
                                position.ratio = parseFloat(stock.ratio.toFixed(2)) + '%';
                                position.ratio_class = ((stock.assets - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win';
                            }

                            _this.positions.push(position);
                        });
                    };
                }    
            });
        },
        order: function(id){
            var _this = this;

            $.ajax({
                url: api_host + '/orders/'+id,
                type: 'PUT',
                dataType: 'json',
                data: {id: id,uid: header.user.uid,status: 2,token: header.user.token,},
                success: function(data){
                    if(data.status == 'success'){
                        modal.imitateAlart('撤单成功', true);
                    } else {
                        modal.imitateAlart(data.data);
                    }
                }
            });
        },
        revoke: function(e){
            var id = e.currentTarget.id;
            modal.imitateConfirm('您确定撤单吗？', 'entrust.order("'+id+'")', '');
        },
        getEntrust: function(){
            if(header.logined){
                this.getPositions();
                this.updateOrder('entrust');
                this.updateOrder('deal');
                this.updateOrder('historical');
                this.updateOrder('trans');
            } else {
                if(this.count < 20){
                    this.close = setTimeout(this.getEntrust, 300);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.getEntrust();
    }
});