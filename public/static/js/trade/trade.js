var trStock = new Vue({
    el: '#tr-stock',
    data: {
        count: 0,//计数
        close: '',//定时器
        usableFunds: 1000000, //可用资金
        usable_funds: '', //可用资金
        funds: 1000000, //总资金
        stock_code: '', //股票代码
        buy_price: '', //买入价格
        buy_num: '', //购买数量
        isSelect: true, //
        stockList: [],
        buyInfo: {},
        stock: { title: '----', turnoverNum: '--',sellPrice1: '--', sellNum1: '--', sellPrice2: '--', sellNum2: '--', sellPrice3: '--', sellNum3: '--', sellPrice4: '--', sellNum4: '--', sellPrice5: '--', sellNum5: '--', buyPrice1: '--', buyNum1: '--', buyPrice2: '--', buyNum2: '--', buyPrice3: '--', buyNum3: '--', buyPrice4: '--', buyNum4: '--', buyPrice5: '--', buyNum5: '--', price: '--', todayPrcie: '--', prec: '--', maxPrice: '--', minPrice: '--', highLimit: '--', lowerLimit: '--', turnoverRate: '--', turnover: '--' },
    },
    computed: {
        maxBuy: function() {
            var max_buy = 0;
            if (this.buy_price != '' && this.buy_price > 0) {
                max_buy = Math.floor(this.usableFunds * 0.999 / this.buy_price / 100) * 100;
            } else {
                max_buy = '';
            }

            return max_buy;
        },
        buyFunds: function() {
            var buy_funds = this.buy_price * this.buy_num;
            return buy_funds ? parseFloat(buy_funds.toFixed(2)) : '';
        },
    },
    methods: {
        getUserInfo() {
            var _this = this;

            $.getJSON(api_host + '/users/' + header.user.uid, {}, function(data) {
                if (data.status == 'success') {
                    _this.usableFunds = data.data.available_funds;
                    _this.funds = data.data.funds;
                }
            });
        },
        updateStockList: function(e) {
            var value = e.currentTarget.value;
            var _this = this;

            $.getScript(api_host + '/index/index/search/stock/' + value,
                function() {
                    if (suggestdata != '') {
                        var data = suggestdata.split(";");
                        if(data.length == 1){
                            $(".tr-stock-table").hide();
                            var stock = data[0].split(',');
                            _this.stock_key = stock['3'];
                            _this.stock_code = stock['2'];
                            _this.isSelect = true;
                            _this.updateStockInfo();
                        } else {
                            var list = [];
                            for (var i = 0; i < data.length; i++) {
                                if (data[i] == '') {
                                    continue;
                                }
                                var val = data[i].split(',');
                                var type = 'A股';
                                switch (val['1']) {
                                    case '111':
                                        type = 'A股';
                                        break;
                                }
                                list.push({ id: data[i], option: val['0'], type: type, code: val['2'], name: val['4'] });
                            }

                            _this.stockList = list;
                            $(".tr-stock-table").show();
                        }    
                    } else {
                        $(".tr-stock-table").hide();
                    }
                });
        },
        selectStock: function(e){
            var id = e.currentTarget.id;
            var stock = id.split(',');
            this.stock_key = stock['3'];
            this.stock_code = stock['2'];
            this.isSelect = true;
            this.updateStockInfo();
        },
        updateStockInfo: function() {
            var _this = this;

            $.ajax({
                url: api_host + '/index/index/quiet/stock/' + _this.stock_key,
                type: 'get',
                dataType: 'script',
                cache: true,
                success: function() {
                    if (eval('hq_str_' + _this.stock_key) != '' || eval('hq_str_s_' + _this.stock_key) != '') {
                        var brief = eval('hq_str_s_' + _this.stock_key).split(',');
                        var detail = eval('hq_str_' + _this.stock_key).split(',');
                        var price = brief['1']; //现价

                        var buyInfo = {
                            stockName: brief['0'], //名称 
                            code: _this.stock_code, //代码
                            price: price,
                        };
                        var stockInfo = {
                            title: brief['0'] + '(' + _this.stock_code + ')', //title
                            turnoverNum: detail['8'], //
                            turnoverMoney: parseFloat(detail['9']), //
                            changePrice: parseFloat(brief['2']), //涨幅
                            changeRate: brief['3'] + '%', //涨幅
                            changeClass: (brief['2'] < 0) ? ' tr-color-lose' : ' tr-color-win',
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
                            _this.buy_price = buyInfo.price;
                            _this.usable_funds = _this.usableFunds;
                            _this.buyInfo = buyInfo;
                            _this.buy_num = 0;
                        }
                        _this.stock = stockInfo;

                        _this.isSelect = false;
                        setTimeout(_this.updateStockInfo, 10000);
                    }
                }
            });
        },
        updateBuyNum: function(e){
            var rate = e.currentTarget.value;
            if(rate > 0){
                var buy_num = Math.floor(this.maxBuy / rate / 100) * 100;
                this.buy_num = buy_num;
            }
        },
        order: function(market) {
            var _this = this;
            if (_this.buy_num == '' || _this.buy_num == 0 || parseInt(_this.buy_num / 100) != _this.buy_num / 100 || _this.buy_num > _this.maxBuy) {
                modal.imitateAlert('请输入正确的购买数量');
                return;
            }
            if(market == 2 && (_this.buy_price == '' || _this.buy_price == 0)){
                modal.imitateAlert('请输入正确的委托价格');
                return;  
            }
            $.post(api_host + '/orders', {
                uid: header.user.uid,
                stock: _this.stock_code,
                price: (_this.buy_price > 0) ? _this.buy_price : 0,
                number: _this.buy_num,
                type: 1,
                sorts: 1,
                isMarket: market,
                token: header.user.token,
            }, function(data) {
                if (data.status == 'success') {
                    modal.imitateAlert('委托提交成功', function(){window.location.reload(true);});
                } else {
                    modal.imitateAlert(data.data);
                }
            });
        },
        getTrade: function(){
            if(header.logined){
                this.getUserInfo();
            } else {
                if(this.count < 20){
                    this.close = setTimeout(this.getTrade, 300);
                    this.count += 1;
                } else {
                    clearTimeout(this.close);
                }
            }
        }
    },
    mounted: function() {
        this.getTrade();
    }
});
