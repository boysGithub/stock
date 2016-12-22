var api_host = 'https://moni.sjqcj.com';
var header = new Vue({
    el: "#header",
    data: {
        logined: false,
        user: {

        }
    },
    methods: {
        checkLogin: function() {
            var _this = this;
            $.getJSON(api_host+"/index/index/doLogin", function(msg) {
                if (msg.status == "success") {
                    _this.logined = true;
                    
                	_this.user = msg.data;
                    
                }else{
                    _this.logined = false;
                }
            });
        },
        getStockUrl: function(stock){
            var url = 'http://finance.sina.com.cn/realstock/company/';
            if(parseInt(stock.substring(0,1)) == 6){
                url += 'sh' + stock;
            } else {
                url += 'sz' + stock;
            }
            url += '/nc.shtml';

            return url;
        },
        logout:function(){
            $.get(
                api_host + "/index/base/logout", function(msg){
                    if(msg.status == "success"){
                        setTimeout(function(){
                            location.href = "/";
                        },500)
                    }else{
                        alert("退出失败");
                    }
                },
                'json'
            );
        }
    },
    mounted: function() {
        this.checkLogin();
    }
});
