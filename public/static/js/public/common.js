var api_host = 'https://moni.sjqcj.com';
var header = new Vue({
	el: "#header",
	data: {
		logined: false
	}
});

function getAvatar(uid) {
	var avatar = 'http://www.sjqcj.com/data/upload/avatar/';

    var str = md5.hex(String(uid));
    avatar += str.substring(0, 2) + '/' + str.substring(2, 4) + '/' + str.substring(4, 6);
    avatar += '/original_200_200.jpg';

    return avatar;
}