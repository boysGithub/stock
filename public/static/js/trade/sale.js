var sale = new Vue({
    el: '#sale',
    data: {
        count: 0,//计数
        close: '',//定时器
        stock_code: '',//卖出价格
        sale_price: '',//卖出价格
        sale_num: '',//卖出数量
        isSelect: true, //
        max_num: 0,
        stock_list: [],
        sale_info: {},
        stock: { title: '----', turnoverNum: '--',sellPrice1: '--', sellNum1: '--', sellPrice2: '--', sellNum2: '--', sellPrice3: '--', sellNum3: '--', sellPrice4: '--',sellNum4: '--', sellPrice5: '--', sellNum5: '--', buyPrice1: '--', buyNum1: '--', buyPrice2: '--', buyNum2: '--', buyPrice3: '--', buyNum3: '--', buyPrice4: '--', buyNum4: '--', buyPrice5: '--', buyNum5: '--', price: '--', todayPrcie: '--', prec: '--', maxPrice: '--', minPrice: '--', highLimit: '--', lowerLimit: '--', turnoverRate: '--', turnover: '--'},
    },
    computed: {
        saleFunds: function(){
            var sale_funds = (this.sale_price * this.sale_num).toFixed(2);
            return sale_funds != 0.00 ? parseFloat(sale_funds) : '';
        }
    },
    methods: {
        getStocks(){
            var _this = this;

            $.getJSON(api_host + '/users',{uid: header.user.uid}, function(data){
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
        selectStock: function(e){
            var code = e.currentTarget.value;
            if(code != '0'){
                this.stock_code = code;
                this.isSelect = true;
                this.updateStockInfo();
            }
        },
        updateStockInfo: function(){
            var _this = this;
            var s_code = '';
            if(parseInt(_this.stock_code.substring(0,1)) != 6){
                s_code = 'sz' + _this.stock_code;
            } else {
                s_code = 'sh' + _this.stock_code;
            }

            var sale_info = {};

            $.ajax({//股票交易信息
                url: api_host + '/index/index/quiet/stock/'+s_code,
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
                            price: price, 
                        };
                        var stockInfo = {
                            title: brief['0']+'('+_this.stock_code+')',//title
                            changePrice: parseFloat(brief['2']), //涨幅
                            changeRate: brief['3']+'%', //涨幅
                            changeClass: (brief['2'] < 0) ? ' tr-color-lose' : ' tr-color-win',
                            turnoverNum: detail['8'], //
                            turnoverMoney: parseFloat(detail['9']), //
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
                            highLimit: (brief['0'].indexOf('ST') != -1) ? parseFloat((detail['2'] * 1.05).toFixed(2)) : parseFloat((detail['2'] * 1.1).toFixed(2)),
                            lowerLimit: (brief['0'].indexOf('ST') != -1) ? parseFloat((detail['2'] * 0.95).toFixed(2)) : parseFloat((detail['2'] * 0.9).toFixed(2)),
                        }

                        if(_this.isSelect){
                            $.getJSON(api_host + '/user/isOptional', {uid: header.user.uid, stock: _this.stock_code}, function(data){//股票账户信息
                                sale_info['available'] = (data.available != null && data.available != '') ? data.available : 0;
                                _this.max_num = sale_info.available;
                                _this.sale_num = 0;
                                _this.sale_info = sale_info;
                            });
                            _this.sale_price = price;
                        }

                        _this.stock = stockInfo;

                        _this.isSelect = false;
                        setTimeout(_this.updateStockInfo, 10000);
                    }
                }    
            });
        },
        updateSaleNum: function(e){
            var rate = e.currentTarget.value;
            if(rate > 0){
                var sale_num = Math.floor(this.max_num / rate / 100) * 100;
                this.sale_num = sale_num;
            }
        },
        order: function(market){//卖出
            var _this = this;
            if(!(_this.sale_num > 0 && _this.sale_num <= _this.max_num)){
                modal.imitateAlert('请输入正确的卖出数量');
                return;
            }
            
            $.post(api_host + '/orders', {
                uid: header.user.uid,
                stock: _this.stock_code,
                price: _this.sale_price,
                number: _this.sale_num,
                type: 2,
                sorts: 1,
                isMarket: market,
                token: header.user.token
            }, function(data){
                if(data.status == 'success'){
                     modal.imitateAlert('委托提交成功',function(){window.location.reload(true);});
                } else {
                     modal.imitateAlert(data.data);
                }
            });
        },
        getSale: function(){
            if(header.logined){
                this.getStocks();
            } else {
                if(this.count < 20){
                    this.close = setTimeout(this.getSale, 300);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function(){
        this.getSale();
    }
});