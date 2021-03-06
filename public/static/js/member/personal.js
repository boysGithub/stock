var personal = new Vue({
    el: "#personal",
    data: {
        count: 0,
        close: '',
        info: {},//用户信息
        market_value: 0,//市值
        positions: [],//用户持仓
        positions_c: [],//用户持仓-计算前
        pc_index: 0,//持仓递归计数
        history_positions: [],//历史持仓
        history: {page:0, page_total:0},//历史持仓分页
        entrust: [],//用户委托
        entrust_page: {page:0, page_total:0},//用户委托分页
        chart_date: [],//分时图日期
        chart_rate: []//分时图盈亏
    },
    computed: {
        uid: function(){
            var uid = $("#uid").val();
            return (uid > 0) ? uid : header.user.uid;
        }
    },
    components: {
        'history-page': Vnav,
        'entrust-page': Vnav
    },
    methods: {
        info(){
            var _this = this;
            $.getJSON(api_host + '/users/'+_this.uid,{},function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var info = {
                        avatar: ret.avatar,
                        user_name: ret.username, 
                        account: ret.account, 
                        position: (ret.position).toFixed(2) + '%',//持仓
                        win_rate: (ret.win_rate).toFixed(2) + '%',//胜率
                        shares: ret.shares,//当日盈亏
                        shares_class: (ret.shares < 0) ? 'tr-color-lose' : 'tr-color-win',//当日盈亏
                        shares_rate: (ret.shares / (ret.funds - ret.shares) * 100).toFixed(2) + '%',//当日盈亏比例
                        week_avg_profit_rate: (ret.week_avg_profit_rate).toFixed(2) + '%', //周平均率
                        success_rate: (ret.success_rate).toFixed(2) + '%',//选股成功率
                        time: ret.time.substring(0,10), 
                        operationTime: ret.operationTime.substring(0,10), 
                        funds: ret.funds, //总资产
                        available_funds: ret.available_funds, //可用资金
                        total_rate: (ret.total_rate).toFixed(2) + '%',
                        total_rate_class: (ret.total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                        total_profit_rank: ret.total_profit_rank//总排名
                    };

                    _this.info = info;
                }    
            });
        },
        getPositions(){
            var _this = this;
            $.getJSON(api_host + '/users',{uid: _this.uid},function(data){
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
                            stock_label: stock.stock_name+'('+stock.stock+')',
                            stock_url: header.getStockUrl(stock.stock),
                            available_number: num,//持仓
                            cost_price: stock.cost_price.toFixed(3),//成本价
                            cost: parseFloat(stock.cost),//成本价
                            time: stock.time.substring(0,10),
                            assets: parseFloat((stock.assets).toFixed(2)),
                            profit: parseFloat((stock.assets - stock.cost).toFixed(2)),
                            price: 0,
                            ratio: stock.ratio.toFixed(2) + '%',
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
                              
                                stock.assets = parseFloat((price * stock.available_number).toFixed(2));//市值
                                stock.price = parseFloat(price);
                                stock.profit = parseFloat((price * stock.available_number - stock.cost).toFixed(2));//盈亏
                                stock.ratio = ((price * stock.available_number - stock.cost) / stock.cost * 100).toFixed(2) + '%';
                                stock.ratio_class = ((price * stock.available_number - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win';
                                
                                market_value += stock.assets
                                positions[i] = stock;
                            }
                        }

                        _this.market_value = market_value;
                        _this.positions = positions
                    });
                }    
            });
        },
        getHistoryPositions(page){
            var _this = this;
            $.getJSON(api_host + '/share/historicalPosition',{uid: _this.uid, p: page},function(data){
                if(data.status == 'success'){
                    var positions = [];//持仓信息

                    for (var i = 0; i < data.data.length; i++) {
                        var stock = data.data[i];
                       
                        positions.push({
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            stock_label: stock.stock_name+'('+stock.stock+')',
                            stock_url: header.getStockUrl(stock.stock),
                            time: stock.time.substring(0,10),
                            update_time: stock.update_time.substring(0,10),
                            profit: (stock.cost * stock.ratio / 100).toFixed(2),//盈亏
                            ratio: (stock.ratio).toFixed(2) + '%',
                            ratio_class: (stock.ratio < 0) ? 'tr-color-lose' : 'tr-color-win'
                        });
 
                    }
                    
                    _this.history = {page: page, page_total: data.pageTotal};
                    _this.history_positions = positions;
                }    
            });
        },
        getEntrust(page){
            var _this = this;
            var r_data = {uid: _this.uid, p:page, type: 'trans',stime: '2016-12-01', etime:'2017-12-01'};
            
            $.getJSON( api_host + '/orders/'+ _this.uid, r_data, function(data){
                if(data.status == 'success'){
                    var entrust = [];//委托信息
                    for (var i = 0; i < data.data.length; i++) {
                        var et = data.data[i];
                        entrust.push({
                            uid: et.uid,
                            stock: et.stock,
                            stock_name: et.stock_name,
                            stock_label: et.stock_name+'('+et.stock+')',
                            stock_url: header.getStockUrl(et.stock),
                            price: et.price,
                            entrustment: et.entrustment,
                            number: et.number,
                            deal_time: et.deal_time.substring(0,16),
                            time: et.time.substring(0,16),
                            type_label: (et.type == 1) ? '买入' : '卖出', 
                            type_class: (et.type == 1) ? 'tr-color-buy' : 'tr-color-sale', 
                            status: et.status,
                            status_name: et.status_name,
                            fee: et.fee
                        });
                    }

                    _this.entrust_page = {page: page, page_total: data.totalPage};
                    _this.entrust = entrust;
                }    
            });
        },
        getTimeChart(){
            var _this = this;
            
            $.getJSON(api_host + '/share/getTimeChart', {uid: _this.uid}, function(data){
                if(data.status == 'success'){
                    var chart_date = [];
                    var chart_rate = [];
                    for (var i = 0; i < data.data.length; i++) {
                        var et = data.data[i];

                        chart_date.push(et.time);
                        chart_rate.push(parseFloat(et.endFunds.toFixed(2)));
                    }

                    _this.chart_date = chart_date;
                    _this.chart_rate = chart_rate;
                    _this.drawing();
                }    
            });
        },
        drawing: function(){
            //k线图
            var myChart = echarts.init(document.getElementById('main')); 
            var option = {
                title: {
                    text: ''
                },
                tooltip: {
                    trigger: 'axis',
                    formatter: function(param){
                        var res = param['0'].seriesName + ': ' + param['0'].data + '%';
                        return res;
                    }    
                },
                legend: {
                    data:['总收益']
                },
                xAxis:  {
                    type: 'category',
                    boundaryGap: false,
                    data: this.chart_date
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}%'
                    }
                },
                series: [
                    {
                        name:'总盈亏',
                        type:'line',
                        data:this.chart_rate
                    }
                ]
            };

            // 为echarts对象加载数据 
            myChart.setOption(option); 
        },
        getPersonal: function(){
            if(this.uid > 0){
                this.info();
                this.getPositions();
                this.getHistoryPositions(1);
                this.getEntrust(1);
                this.getTimeChart();
            } else {
                if(this.count < 20){
                    this.close = setTimeout(this.getPersonal, 300);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.getPersonal();
    }
});
