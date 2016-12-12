var i = 0;
var close = setTimeout(getStock, 100);

function getStock(){
    if(header.user.uid > 0){
        var trStock = new Vue({
            el: '#tr-stock',
            data: {
                usableFunds: 1000000, //可用资金
                usable_funds: '', //可用资金
                funds: 1000000, //总资金
                stock_code: '',//股票代码
                buy_price: '',//买入价格
                buy_num: '',//购买数量
                stockList: [],
                buyInfo: {title: '----'},
                stock : {sellPrice1: '--', sellNum1: '--', sellPrice2: '--', sellNum2: '--', sellPrice3: '--', sellNum3: '--', sellPrice4: '--',sellNum4: '--', sellPrice5: '--', sellNum5: '--', buyPrice1: '--', buyNum1: '--', buyPrice2: '--', buyNum2: '--', buyPrice3: '--', buyNum3: '--', buyPrice4: '--', buyNum4: '--', buyPrice5: '--', buyNum5: '--', price: '--', todayPrcie: '--', prec: '--', maxPrice: '--', minPrice: '--', highLimit: '--', lowerLimit: '--', turnoverRate: '--', turnover: '--'},
            },
            computed: {
                maxBuy: function(){
                    var max_buy = 0;
                    if(this.buy_price != '' && this.buy_price > 0){
                        if(this.funds * 0.4 > this.usableFunds){
                            max_buy = Math.floor(this.usableFunds / this.buy_price / 100) * 100;
                        } else {
                            max_buy = Math.floor(this.funds * 0.4 / this.buy_price / 100) * 100;
                        }
                    } else {
                        max_buy = '';
                    }

                    this.buy_num = max_buy;
                    return max_buy;  
                },
                buyFunds: function(){
                    var buy_funds = this.buy_price * this.buy_num;
                    return buy_funds ? buy_funds : '';
                },
            },
            methods: {
                getUserInfo(){
                    var _this = this;

                    $.getJSON(api_host + '/users/'+header.user.uid,{}, function(data){
                        if(data.status == 'success'){
                            _this.usableFunds = data.data.available_funds;
                            _this.funds = data.data.funds;
                        }
                    });  
                },
                updateStockList: function(e){
                    var value = e.currentTarget.value;
                    var _this = this;

                    $.getScript('http://suggest3.sinajs.cn/suggest/?type=111&key='+value+'&name=suggestdata',
                        function(){
                            if(suggestdata != ''){
                                var data = suggestdata.split(";"); 
                                var list = [];
                                for (var i = 0; i < data.length; i++) {
                                    if(data[i] == ''){
                                        continue;
                                    }
                                    var val = data[i].split(',');
                                    var type = 'A股';
                                    switch(val['1']){
                                        case '111':
                                            type = 'A股';
                                            break;
                                    }
                                    list.push({ id: data[i], option: val['0'], type: type, code: val['2'], name: val['4']});
                                }

                                _this.stockList = list;
                                $(".tr-stock-table").show();
                            } else {
                                $(".tr-stock-table").hide();
                            }
                    });    
                },
                updateStockInfo: function(e){
                    var id = e.currentTarget.id;
                    var _this = this;
                    var stock = id.split(',');
                    
                    $.ajax({
                        url: 'http://hq.sinajs.cn?list='+stock['3']+',s_'+stock['3'],
                        type: 'get',
                        dataType: 'script',
                        cache: true,
                        success: function(){
                            if(eval('hq_str_'+stock['3']) != '' || eval('hq_str_s_'+stock['3']) != ''){
                                var brief = eval('hq_str_s_'+stock['3']).split(',');
                                var detail = eval('hq_str_'+stock['3']).split(',');
                                var price = brief['1'];//现价
                            
                                var buyInfo = {
                                    stockName: brief['0'],//名称 
                                    code: stock['2'], //代码
                                    title: brief['0']+'('+stock['2']+')',//title
                                    price: price, 
                                    changePrice: brief['2'], //涨幅
                                    changeRate: brief['3']+'%', //涨幅
                                    changeClass: (brief['2'] < 0) ? ' tr-color-lose' : ' tr-color-win',
                                    turnoverNum:  brief['4'], //
                                    turnoverMoney:  brief['5']//
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
                                    /*turnoverRate: detail[''], 
                                    turnover: detail['']*/
                                }

                                _this.stock_code = buyInfo.code;
                                _this.buy_price = buyInfo.price;
                                _this.usable_funds = _this.usableFunds;
                                _this.stock = stockInfo;
                                _this.buyInfo = buyInfo;
                            }
                        }    
                    });
                },
                order: function(){
                    var _this = this;
                    if(parseInt(_this.buy_num / 100) != _this.buy_num / 100){
                        alert('购买数量必须为100的倍数');
                        return;
                    }
                    $.post(api_host + '/orders', {
                        uid: header.user.uid,
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
                    });
                }
            },
            mounted: function(){
                this.getUserInfo();
            }
        });
    } else {
        if(i < 20){
            close = setTimeout(getStock, 200);
            i++;
        } else {
            clearTimeout(close);
        }
    }
}