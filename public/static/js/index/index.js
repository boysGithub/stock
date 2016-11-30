var index = new Vue({
    el: "#index",
    data: {
        proclamation: [],//公告
        talent_dynamic: [
            {
                user_name: '寻梦',
                stock: '兔宝宝',
                state: '买入',
                state_class: 'tr-color-sale',
                price: 5.5,
                url: '#',
            }
        ]//牛人动态
    }
});

$(function(){
    //公告
    var timestamp = new Date().getTime();
    var proclamation = localStorage.getItem('proclamation');
    proclamation = JSON.parse(proclamation);
    if(proclamation == null || proclamation.timestamp < timestamp){
        var data = [
            {href: 'http://www.sjqcj.com/weibo/715816', title: '水晶球牛人选股大赛第57周战况：名人组花荣顺利夺冠 温州叶荣添奇正藏药5天3板夺魁'},
            {href: 'http://www.sjqcj.com/weibo/712715', title: '水晶球2016推股高手排行榜出炉：金一平擒四川双马成第一高手！'},
            {href: 'http://www.sjqcj.com/weibo/714541', title: '金一平：最看好的高送转潜力股'},
            {href: 'http://www.sjqcj.com/weibo/715149', title: '选股比赛播报（11.18）：高送转第一枪打响，涨停板接踵而至'}
        ];
        localStorage.setItem('proclamation',JSON.stringify({timestamp: timestamp + 10000, data: data}));
        index.proclamation = data;
    } else {
        index.proclamation = proclamation.data;
    }
    setTimeout(proclamationSlider, 100);

    //牛人动态
    /*$.ajax({
        url: "http://www.stocks.com/orders.html",
        data: {'dtype': 'jsonp'},
        type: "GET",
        dataType: 'jsonp',
        success: function(data) {
        alert(data.status);
        }
    });*/
    $.getJSON('https://moni.sjqcj.com/orders',{},function(data){
        alert(data.status);
    });
});

function proclamationSlider(){
    var _scroll = {
        delay: 1000,
        easing: 'linear',
        items: 1,
        duration: 0.04,
        timeoutDuration: 0,
        pauseOnHover: 'immediate'
    };
    $('#ticker-1').carouFredSel({
        width: 783,
        align: false,
        items: {
            width: 'variable',
            height: 24,
            visible: 1,
            margin: 0
        },
        scroll: _scroll
    });
}   
