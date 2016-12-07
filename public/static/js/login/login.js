var vue = new Vue({
    el: "#login",
    data: {
        username: '',
        password: '',
        remember: 0,
        url: "http://cc.sjqcj.com/index.php?app=public&mod=Passport&act=doLogin"
    },
    methods: {
        doLogin: function() {
            $.getJSON(
                this.url, {
                    login_email:this.username,
                    login_password:this.password,
                    login_remember:this.remember
                },
                function(msg) {
                    console.log(msg);
                }
            )
        }
    }
});
