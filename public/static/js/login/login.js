var vue = new Vue({
    el: "#login",
    data: {
        username: '',
        password: '',
        remember: 0, 
        url: api_host + "/index/base/doLogin"
    },
    methods: {
        doLogin: function() {
            if(this.check()){
               $.post(
                    this.url, {
                        login_email:this.username,
                        login_password:this.password,
                        login_remember:this.remember
                    },
                    function(msg) {
                        if(msg.status == "success"){
                            modal.imitateAlert(msg.data);
                            setTimeout(function(){
                                location.href = "http://moni.local.com";
                            },1000);
                        }else{
                            modal.imitateAlert(msg.data);
                        }
                    },
                    'json'
                ); 
            }
            
        },
        check:function(){
            if(this.username == ''){
                modal.imitateAlert("用户名不能为空");
                return false;
            }else if(this.password == ''){
                modal.imitateAlert("密码不能为空");
                return false;
            }else{
                return true;
            }
        }
    }
});
