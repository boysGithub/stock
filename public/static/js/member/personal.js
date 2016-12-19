var personal = new Vue({
    el: "#personal",
    data: {
        count: 0,
        close: '',
        info: {},//用户信息
        market_value: '',//市值
        positions: [],//用户持仓
        history_positions: [],//历史持仓
        entrust: [],//用户委托
        chart_date: [],//分时图日期
        chart_rate: []//分时图盈亏
    },
    computed: {
        uid: function(){
            var uid = $("#uid").val();
            return (uid > 0) ? uid : header.user.uid;
        }
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
                        position: ret.position + '%',//持仓
                        win_rate: ret.win_rate + '%',//胜率
                        shares: ret.shares,//当日盈亏
                        shares_rate: "1%",//当日盈亏比例
                        week_avg_profit_rate: ret.week_avg_profit_rate,//周平均率
                        success_rate: ret.success_rate + '%',//选股成功率
                        time: ret.time.substring(0,10), 
                        operationTime: ret.operationTime.substring(0,10), 
                        funds: ret.funds, //总资产
                        available_funds: ret.available_funds, //可用资金
                        total_rate: ret.total_rate + '%',
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

                            _this.market_value += position.assets; 
                            _this.positions.push(position);
                        });
                    }
                }    
            });
        },
        getHistoryPositions(){
            var _this = this;
            $.getJSON(api_host + '/share/historicalPosition',{uid: _this.uid},function(data){
                if(data.status == 'success'){
                    var positions = [];//持仓信息

                    for (var i = 0; i < data.data.length; i++) {
                        var stock = data.data[i];
                       
                        positions.push{
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            time: stock.time.substring(0,10),
                            update_time: stock.update_time.substring(0,10),
                            profit: parseFloat((stock.assets * stock.ratio).toFixed(2)),//盈亏
                            ratio: stock.ratio + '%',
                            ratio_class: (stock.ratio < 0) ? 'tr-color-lose' : 'tr-color-win'
                        };
 
                    }
                    
                    _this.positions = positions;
                }    
            });
        },
        getEntrust(){
            var _this = this;
            var r_data = {uid: _this.uid, type: 'trans',stime: '2016-12-01', etime:'2017-12-01'};
            
            $.getJSON(api_host + '/orders/'+ _this.uid, r_data, function(data){
                if(data.status == 'success'){
                    var entrust = [];//委托信息
                    for (var i = 0; i < data.data.length; i++) {
                        var et = data.data[i];
                        entrust.push({
                            uid: et.uid,
                            stock: et.stock,
                            stock_name: et.stock_name,
                            price: et.price,
                            number: et.number,
                            time: et.time,
                            type_label: (et.type == 1) ? '买入' : '卖出', 
                            type_class: (et.type == 1) ? 'tr-color-buy' : 'tr-color-sale', 
                            status: et.status,
                            fee: et.fee
                        });
                    }

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
                    text: '账户总盈亏'
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
                this.getHistoryPositions();
                this.getEntrust();
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