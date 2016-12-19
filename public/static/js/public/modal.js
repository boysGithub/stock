var modal = new Vue({
    el: "#modal",
    data: {
        modal: {msg:'提示'},
    },
    methods: {
        imitateAlert: function(msg){
            var refresh = arguments[1] || false;
            this.modal.msg = msg;
            $('#my-alert').modal({});
            $('#my-alert').on('closed.modal.amui', function(){
                if(refresh){
                    window.location.reload(true);
                }
            });
        },
        imitateConfirm: function(msg, callback_OK, callback_CANCEL){
            this.modal.msg = msg;
            $('#my-confirm').modal({
                relatedTarget: this,
                onConfirm: function() {
                    eval(callback_OK);
                }
            });
        }
    },
    mounted: function() {
    }
});