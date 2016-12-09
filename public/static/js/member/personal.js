var personal = new Vue({
    el: "#personal",
    data: {
        info: {},//用户信息
        positions: [],//用户持仓
        entrust: []//用户委托
    },
    methods: {
        info(){
            var _this = this;
            $.getJSON(api_host + '/users/11643',{},function(data){
                if(data.status == 'success'){
                    var ret = data.data;
                    var info = {
                        user_name: ret.username, 
                        position: ret.position + '%',
                        win_rate: ret.win_rate + '%', 
                        time: ret.time, 
                        operationTime: ret.operationTime, 
                        funds: ret.funds, 
                        available_funds: ret.available_funds, 
                        total_rate: ret.total_rate + '%',
                        total_rate_class: (ret.total_rate < 0) ? 'tr-color-lose' : 'tr-color-win',
                    };

                    _this.info = info;
                }    
            });
        },
        positions(){
            var _this = this;
            $.getJSON(api_host + '/users',{uid: 11643},function(data){
                if(data.status == 'success'){
                    var positions = [];
                    for (var i = 0; i < data.data.length; i++) {
                        var stock = data.data[i];
                        positions.push({
                            stock: stock.stock,
                            stock_name: stock.stock_name,
                            ratio: stock.ratio + '%',
                            ratio_class: (stock.ratio < 0) ? 'tr-color-lose' : 'tr-color-win',
                            available_number: stock.available_number,
                            cost_price: stock.cost_price,
                            assets: stock.assets,
                            cost: stock.cost,
                        });
                    }

                    var entrust = [];
                    for (var i = 0; i < data.nData.length; i++) {
                        var et = data.nData[i];
                        entrust.push({
                            uid: et.uid,
                            stock: et.stock,
                            stock_name: et.stock_name,
                            price: et.price,
                            number: et.number,
                            time: et.time,
                            type: (et.type == 1) ? '买入' : '卖出',
                            status: et.status,
                            fee: et.fee
                        });
                    }

                    _this.positions = positions;
                    _this.entrust = entrust;
                }    
            });
        }
    },
    created(){
        this.info();
        this.positions();
    }
})

//k线图
var myChart = echarts.init(document.getElementById('main')); 
var option = {
    title : {
        text: '账户总盈亏',
        textStyle: {
            fontSize: 12,
        }
    },
    tooltip : {
        trigger: 'axis',
        formatter: function (params) {
            var res = params[0].seriesName + ' ' + params[0].name;
            res += '<br/>  开盘 : ' + params[0].value[0] + '  最高 : ' + params[0].value[3];
            res += '<br/>  收盘 : ' + params[0].value[1] + '  最低 : ' + params[0].value[2];
            return res;
        }
    },
    legend: {
        data:['上证指数']
    },
    toolbox: {
        show : false,
        feature : {
            mark : {show: true},
            dataZoom : {show: true},
            dataView : {show: true, readOnly: false},
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    xAxis : [
        {
            type : 'category',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : [
                "2013/5/9", "2013/5/10", "2013/5/13", "2013/5/14", "2013/5/15", 
                "2013/5/16", "2013/5/17", "2013/5/20", "2013/5/21", "2013/5/22", 
                "2013/5/23", "2013/5/24", "2013/5/27", "2013/5/28", "2013/5/29", 
                "2013/5/30", "2013/5/31", "2013/6/3", "2013/6/4", "2013/6/5", 
                "2013/6/6", "2013/6/7", "2013/6/13"
            ]
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            boundaryGap: [0.01, 0.01]
        }
    ],
    series : [
        {
            name:'上证指数',
            type:'k',
            barMaxWidth: 20,
            itemStyle: {
                normal: {
                    color: 'red',           // 阳线填充颜色
                    color0: 'lightgreen',   // 阴线填充颜色
                    lineStyle: {
                        width: 2,
                        color: 'orange',    // 阳线边框颜色
                        color0: 'green'     // 阴线边框颜色
                    }
                },
                emphasis: {
                    color: 'black',         // 阳线填充颜色
                    color0: 'white'         // 阴线填充颜色
                }
            },
            data:[ // 开盘，收盘，最低，最高
                {
                    value:[2320.26,2302.6,2287.3,2362.94],
                    itemStyle: {
                        normal: {
                            color0: 'blue',         // 阴线填充颜色
                            lineStyle: {
                                width: 3,
                                color0: 'blue'      // 阴线边框颜色
                            }
                        },
                        emphasis: {
                            color0: 'blue'          // 阴线填充颜色
                        }
                    }
                },
                [2300,2291.3,2288.26,2308.38],
                [2295.35,2346.5,2295.35,2346.92],
                [2347.22,2358.98,2337.35,2363.8],
                [2360.75,2382.48,2347.89,2383.76],
                [2383.43,2385.42,2371.23,2391.82],
                [2377.41,2419.02,2369.57,2421.15],
                [2425.92,2428.15,2417.58,2440.38],
                [2411,2433.13,2403.3,2437.42],
                [2432.68,2434.48,2427.7,2441.73],
                [2430.69,2418.53,2394.22,2433.89],
                [2416.62,2432.4,2414.4,2443.03],
                [2441.91,2421.56,2415.43,2444.8],
                [2420.26,2382.91,2373.53,2427.07],
                [2383.49,2397.18,2370.61,2397.94],
                [2378.82,2325.95,2309.17,2378.82],
                [2322.94,2314.16,2308.76,2330.88],
                [2320.62,2325.82,2315.01,2338.78],
                [2313.74,2293.34,2289.89,2340.71],
                [2297.77,2313.22,2292.03,2324.63],
                [2322.32,2365.59,2308.92,2366.16],
                [2364.54,2359.51,2330.86,2369.65],
                [2332.08,2273.4,2259.25,2333.54],
            ],
            markPoint : {
                symbol: 'star',
                //symbolSize:20,
                itemStyle:{
                    normal:{label:{position:'top'}}
                },
                data : [
                    {name : '最高', value : 2444.8, xAxis: '2013/2/18', yAxis: 2466}
                ]
            }
        }
    ]
}; 

// 为echarts对象加载数据 
myChart.setOption(option); 