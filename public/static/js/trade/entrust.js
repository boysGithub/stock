var entrust = new Vue({
    el: '#entrust',
    data: {
        count: 0,//计数
        close: '',//定时器
        type: '',
        positions: [],//当前持仓
        positions_c: [],//用户持仓-计算前
        pc_index: 0,//持仓递归计数
        today_entrust: [],//今日委托
        today_turnover: [],//今日成交
        history_entrust: [],//历史委托
        history_entrust_page: {page: 0, page_total: 0},//历史委托
        history_turnover: [],//历史成交
        history_turnover_page: {page: 0, page_total: 0},//历史成交
    },
    computed: {
    },
    components: {
        'turnover-page': Vnav,
        'entrust-page': Vnav
    },
    methods: {
        orderPage(page){
            this.updateOrder(this.type, page);
        },
        updateOrder(type, page){
            var _this = this;
            var r_data = {type: type, p:page};
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
                            stock_url: header.getStockUrl(ret[i].stock), 
                            type_label: (ret[i].type == 1) ? '买入' : '卖出', 
                            type_class: (ret[i].type == 1) ? 'tr-color-buy' : 'tr-color-sale', 
                            price: ret[i].price, 
                            entrustment: ret[i].entrustment,
                            number: ret[i].number,
                            status_name: ret[i].status_name,
                            status: ret[i].status,
                            turnover: parseFloat((ret[i].number * ret[i].price).toFixed(2)),
                            number: ret[i].number,
                            deal_time: ret[i].deal_time.substring(0,16),
                            time: ret[i].time.substring(0,16)
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
                            _this.history_entrust_page = {page: page, page_total: data.totalPage};
                            _this.history_entrust = orders;
                            break;
                        case 'trans':
                            _this.history_turnover_page = {page: page, page_total: data.totalPage};
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
                    var market_value = 0; //市值
                    var positions = [];//持仓信息
                    var stock_key = [];
                    
                    for (var i = 0; i < data.data.length; i++) {
                        var stock = data.data[i];
                        var num = parseInt(stock.position_number);
                        var key = 'sz' + stock.stock;
                        if(parseInt(stock.stock.substring(0,1)) == 6){
                            key = 'sh' + stock.stock;
                        }

                        
                        positions.push({
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            stock_key: key,
                            title: stock.stock_name+'('+stock.stock+')',
                            stock_url: header.getStockUrl(stock.stock),
                            position_number: num,//持仓
                            available_number: parseInt(stock.available_number),//持仓
                            cost_price: (stock.cost_price).toFixed(3),//成本价
                            cost: parseFloat(stock.cost),//成本价
                            time: stock.time.substring(0,10),
                            assets: parseFloat((stock.assets).toFixed(2)),
                            profit: parseFloat((stock.assets - stock.cost).toFixed(2)),
                            price: 0,
                            ratio: (stock.ratio).toFixed(2) + '%',
                            ratio_class: ((stock.assets - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win'
                        });

                        stock_key.push(key);
                    }

                    $.getScript(api_host+'/index/index/getStocks?stock='+stock_key.join(','),function(){
                        for (var i = 0; i < positions.length; i++) {
                            var stock = positions[i];
                            if(eval('hq_str_'+stock.stock_key)){
                                var detail = eval('hq_str_'+stock.stock_key).split(',');
                                var price = (detail['3'] > 0) ? detail['3'] : detail['2'];//现价
                              
                                stock.assets = (price * stock.position_number).toFixed(2);//市值
                                stock.price = parseFloat(price);
                                stock.profit = parseFloat((price * stock.position_number - stock.cost).toFixed(2));//盈亏
                                stock.ratio = ((price * stock.position_number - stock.cost) / stock.cost * 100).toFixed(2) + '%';
                                stock.ratio_class = ((price * stock.position_number - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win';
                                
                                positions[i] = stock;
                            }
                        }

                        _this.positions = positions
                    });
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
                        modal.imitateAlart('撤单成功', function(){window.location.reload(true);});
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
                this.updateOrder('entrust',1);
                this.updateOrder('deal',1);
                this.updateOrder('historical',1);
                this.updateOrder('trans',1);
            } else {
                if(this.count < 10){
                    this.close = setTimeout(this.getEntrust, 200);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.getEntrust();
        var _this = this;
        $('#tr-tabs').find('a.item').on('opened.tabs.amui', function(e) {
            _this.type = $(this).attr('type');
        })
    }
});