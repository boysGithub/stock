var personal = new Vue({
    el: "#personal",
    data: {
        count: 0,
        close: '',
        info: {},//用户信息
        market_value: '',//市值
        positions: [],//用户持仓
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
                        position: ret.position + '%',//持仓
                        win_rate: ret.win_rate + '%',//胜率
                        shares: ret.shares,//当日盈亏
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
        positions(){
            var _this = this;
            $.getJSON(api_host + '/users',{uid: _this.uid},function(data){
                if(data.status == 'success'){
                    var stock_num = [];//各股持仓
                    var positions = [];//持仓信息
                    var stock_key = [];//查询关键字

                    for (var i = 0; i < data.data.length; i++) {
                        var stock = data.data[i];
                        var num = parseInt(stock.available_number) + parseInt(stock.freeze_number)
                        positions.push({
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            ratio: stock.ratio + '%',
                            ratio_class: (stock.ratio < 0) ? 'tr-color-lose' : 'tr-color-win',
                            available_number: num,
                            cost_price: stock.cost_price,
                            assets: stock.assets,
                            cost: stock.cost,
                        });

                        var key = 's_sz' + stock.stock;
                        if(parseInt(stock.stock.substring(0,1)) == 6){
                            key = 's_sh' + stock.stock;
                        }
                        stock_key.push(key);
                        stock_num.push({code: key, num: num});
                    }

                    $.getScript(//股票交易信息
                        api_host + '/index/index/getStocks.html?stock='+stock_key.join(','),
                        function(){
                            var market_value = 0;
                            for (var i = 0; i < stock_num.length; i++) {
                                if(stock_num[i] != '' && eval('hq_str_'+stock_num[i].code) != ''){
                                    var brief = eval('hq_str_'+stock_num[i].code).split(',');
                                    var price = parseFloat(brief['1']);//现价
                                    market_value += price * stock_num[i].num;
                                }
                            }

                            market_value.toFixed(2);
                            _this.market_value = market_value;
                        }
                    );

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
        getPersonal(){
            if(this.uid > 0){
                this.info();
                this.positions();
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
        }
    },
    mounted: function(){
        this.getPersonal();
    }
});