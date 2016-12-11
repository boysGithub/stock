var sale = new Vue({
    el: '#sale',
    data: {
        uid: 11643,//header.user.uid,
        sale_price: '',//卖出价格
        sale_num: '',//卖出数量
        stock_list: [],
        sale_info: {title: '----', changeClass: ''},
        stock: {sellPrice1: '--', sellNum1: '--', sellPrice2: '--', sellNum2: '--', sellPrice3: '--', sellNum3: '--', sellPrice4: '--',sellNum4: '--', sellPrice5: '--', sellNum5: '--', buyPrice1: '--', buyNum1: '--', buyPrice2: '--', buyNum2: '--', buyPrice3: '--', buyNum3: '--', buyPrice4: '--', buyNum4: '--', buyPrice5: '--', buyNum5: '--', price: '--', todayPrcie: '--', prec: '--', maxPrice: '--', minPrice: '--', highLimit: '--', lowerLimit: '--', turnoverRate: '--', turnover: '--'},
    },
    computed: {
        saleFunds: function(){
            var buy_funds = this.buy_price * this.buy_num;
            return buy_funds ? buy_funds : '';
        }
    },
    methods: {
        getStocks(){
            var _this = this;

            $.getJSON(api_host + '/users',{uid: _this.uid}, function(data){
                if(data.status == 'success'){
                    var ret = data.data; 
                    var stock_list = [];
                    for (var i = 0; i < ret.length; i++) {
                        stock_list.push({code: ret[i].stock, stock_name: ret[i].stock_name});
                    }

                    _this.stock_list = stock_list;
                }
            });    
        },
        updateStockInfo: function(e){
            var code = e.currentTarget.value;
            var _this = this;
            var s_code = '';
            if(parseInt(code.substring(0,1)) != 6){
                s_code = 'sz' + code;
            } else {
                s_code = 'sh' + code;
            }

            var sale_info = {};

            $.ajax({//股票交易信息
                url: 'http://hq.sinajs.cn?list='+s_code+',s_'+s_code,
                type: 'get',
                dataType: 'script',
                cache: true,
                success: function(){
                    if(eval('hq_str_'+s_code) != '' || eval('hq_str_s_'+s_code) != ''){
                        var brief = eval('hq_str_s_'+s_code).split(',');
                        var detail = eval('hq_str_'+s_code).split(',');
                        var price = parseFloat(brief['1']);//现价
                    
                        sale_info = {
                            stockName: brief['0'],//名称 
                            title: brief['0']+'('+code+')',//title
                            price: price, 
                            changePrice: brief['2'], //涨幅
                            changeRate: brief['3']+'%', //涨幅
                            changeClass: (brief['2'] < 0) ? ' tr-color-lose' : ' tr-color-win',
                            turnoverNum:  brief['4'], //交易笔数
                            turnoverMoney:  brief['5']//交易额
                        };
                        var stockInfo = {
                            sellPrice1: parseFloat(detail['21']),
                            sellNum1: detail['20'],
                            sellPrice2: parseFloat(detail['23']),
                            sellNum2: detail['22'],
                            sellPrice3: parseFloat(detail['25']),
                            sellNum3: detail['24'],
                            sellPrice4: parseFloat(detail['27']),
                            sellNum4: detail['26'],
                            sellPrice5: parseFloat(detail['29']),
                            sellNum5: detail['28'],
                            buyPrice1: parseFloat(detail['11']),
                            buyNum1: detail['10'],
                            buyPrice2: parseFloat(detail['13']),
                            buyNum2: detail['12'],
                            buyPrice3: parseFloat(detail['15']),
                            buyNum3: detail['14'],
                            buyPrice4: parseFloat(detail['17']),
                            buyNum4: detail['16'],
                            buyPrice5: parseFloat(detail['19']),
                            buyNum5: detail['18'],
                            price: parseFloat(detail['3']), 
                            todayPrcie: parseFloat(detail['1']), 
                            prec: parseFloat(detail['2']), 
                            maxPrice: parseFloat(detail['4']), 
                            minPrice: parseFloat(detail['5']), 
                            highLimit: parseFloat(detail['2'] * 1.1).toFixed(2),  
                            lowerLimit: parseFloat(detail['2'] * 0.9).toFixed(2), 
                        }

                        $.getJSON(api_host + '/user/isOptional', {uid: _this.uid, stock: code}, function(data){//股票账户信息
                            sale_info['available'] = (data.available != null && data.available != '') ? data.available : 0;
                            _this.sale_info = sale_info;
                        });

                        _this.sale_price = sale_info.sale_price;
                        _this.stock = stockInfo;
                    }
                }    
            });

        },
        order: function(){
            var _this = this;
            if(parseInt(_this.buy_num / 100) != _this.buy_num / 100){
                alert('购买数量必须为100的倍数');
                return;
            }/*
            $.post(api_host + '/orders', {
                uid:$("#uid").val(),
                stock: _this.stock_code,
                price: _this.buy_price,
                number: _this.buy_num,
                type: 1,
                sorts: 1,
                isMarket: 2,
                token: header.user.token,
            }, function(data){
                if(data.status == 'success'){
                    alert('委托成功');
                    window.location.reload(true);
                } else {
                    alert(data.data);
                }
            });*/
        }
    },
    mounted: function(){
        this.getStocks();
    }
});