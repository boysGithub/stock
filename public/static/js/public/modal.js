var modal = new Vue({
    el: "#modal",
    data: {
        modal: {msg:'提示'},
    },
    methods: {
        imitateAlert: function(msg,callback){
            var callback = arguments[1] || false;
            this.modal.msg = msg;
            $('#my-alert').modal({});
            $('#my-alert').on('closed.modal.amui', function(){
                if (typeof (callback) == 'function') {
                    callback();
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