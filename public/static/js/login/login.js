var vue = new Vue({
    el: "#login",
    data: {
        username: '',
        password: '',
        remember: 0, 
        url: "http://www.sjqcj.com/index.php?app=public&mod=Passport&act=doLogin"
    },
    methods: {
        doLogin: function() {
            $.post(
                this.url, {
                    login_email:this.username,
                    login_password:this.password,
                    login_remember:this.remember
                },
                function(msg) {
                    if(msg.status == 1){
                        alert(msg.info);
                        setTimeout(function(){
                            location.href = "/index";
                        },1000);
                    }else{
                        alert(msg.info);
                    }
                }
            )
        }
    }
});
