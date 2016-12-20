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
        'vue-nav': Vnav
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
                        shares_rate: parseFloat((ret.shares / (ret.funds - ret.shares) * 100).toFixed(2)) + '%',//当日盈亏比例
                        week_avg_profit_rate: ret.week_avg_profit_rate + '%', //周平均率
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
                        positions.push({
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            available_number: num,//持仓
                            cost_price: parseFloat(stock.cost_price),//成本价
                            cost: parseFloat(stock.cost),//成本价
                            time: stock.time.substring(0,10),
                            assets: parseFloat(stock.assets),
                            profit: parseFloat(stock.assets - stock.cost),
                            price: 0,
                            ratio: parseFloat(stock.ratio.toFixed(2)) + '%',
                            ratio_class: ((stock.assets - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win'
                        });
                    }

                    _this.positions_c = positions;
                    _this.getMarketValue();
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
                            time: stock.time.substring(0,10),
                            update_time: stock.update_time.substring(0,10),
                            profit: parseFloat((stock.cost * stock.ratio / 100).toFixed(2)),//盈亏
                            ratio: stock.ratio + '%',
                            ratio_class: (stock.ratio < 0) ? 'tr-color-lose' : 'tr-color-win'
                        });
 
                    }
                    
                    _this.history = {page: page, page_total: data.pageTotal};
                    _this.history_positions = positions;
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
        getMarketValue(){
            var _this = this;
            var index = _this.pc_index;
            if(index >= _this.positions_c.length){   
                return;  
            } 
            var stock = _this.positions_c[index];

            var key = 'sz' + stock.stock;
            if(parseInt(stock.stock.substring(0,1)) == 6){
                key = 'sh' + stock.stock;
            }

            //股票交易信息
            $.getScript(api_host + '/index/index/quiet?stock='+key,function(){
                if(eval('hq_str_'+key)){
                    var detail = eval('hq_str_'+key).split(',');
                    var price = (detail['3'] > 0) ? detail['3'] : detail['2'];//现价
                    
                    stock.assets = parseFloat((price * stock.available_number).toFixed(2));//市值
                    stock.price = parseFloat(price);
                    stock.profit = parseFloat((price * stock.available_number - stock.cost).toFixed(2));//盈亏
                    stock.ratio = parseFloat(((price * stock.available_number - stock.cost) / stock.cost * 100).toFixed(2)) + '%';
                    stock.ratio_class = ((price * stock.available_number - stock.cost) < 0) ? 'tr-color-lose' : 'tr-color-win';
                }
                
                _this.market_value = parseFloat(_this.market_value) + stock.assets; 
                _this.positions.push(stock);
                _this.pc_index++;
                _this.getMarketValue();
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
