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
        }
    },
    mounted: function() {
       this.checkLogin();
    }
});
