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

function getAvatar(uid) {
	var avatar = 'http://www.sjqcj.com/data/upload/avatar/';

    var str = md5.hex(String(uid));
    avatar += str.substring(0, 2) + '/' + str.substring(2, 4) + '/' + str.substring(4, 6);
    avatar += '/original_200_200.jpg';

    return avatar;
}
