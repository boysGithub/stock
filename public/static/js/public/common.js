var api_host = 'https://moni.sjqcj.com';
var header = new Vue({
    el: "#header",
    data: {
        logined: false
        user: {

        }
    },
    methods: {
        checkLogin: function() {
            $.getJSON("/index/index/doLogin", function(msg) {
                if (msg.status == "success") {
                	user = msg.data;
                }
            });
        }
    },
    ready: function() {
        $this.checkLogin();
    }
});
