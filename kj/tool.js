/**
 * Created by smile on 2018/1/23.
 */
exports.get_lhc_sebo = function (tema) {
    //当期的色波
    var sebo = '';
    var redbo = '1,2,7,8,12,13,18,19,23,24,29,30,34,35,40,45,46';
    var bluebo = '3,4,9,10,14,15,20,25,26,31,36,37,41,42,47,48';
    if (in_array(tema, redbo.split(','))) {
        sebo = "红";
    } else if (in_array(tema, bluebo.split(','))) {
        sebo = '蓝';
    } else {
        sebo = "绿";
    }
    return sebo;
}
exports.get_lhc_wuxing = function (tema) {
    var wuxing = '';
    //获取当期的金木水火土
    var wuxingjin = '3,4,17,18,25,26,33,34,47,48';
    var wuxingmu = '7,8,15,16,29,30,37,38,45,46';
    var wuxingshui = '5,6,13,14,21,22,35,36,43,44';
    var wuxingtu = '11,12,19,20,27,28,41,42,49';
//             wuxinghuo ='1,2,9,10,23,24,31,32,39,40';
    if (in_array(tema, wuxingjin.split(','))) {
        wuxing = "金";
    } else if (in_array(tema, wuxingmu.split(','))) {
        wuxing = '木';
    } else if (in_array(tema, wuxingshui.split(','))) {
        wuxing = '水';
    } else if (in_array(tema, wuxingtu.split(','))) {
        wuxing = '土';
    } else {
        wuxing = "火";
    }
    return wuxing;
}
exports.get_all_shengxiao = function (periodnumber) {
    var arr = periodnumber.split(',');
    var list = [];
    arr.forEach(function (value, key) {
        var res = seeshengxiao(value);
        list.push(res);
    })
    return list;
}
function seeshengxiao(tema) {
    //查看当前开的是什么生肖
    var jinnian = '鸡';
    var jinnianling = '';
    var arr = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
    arr.forEach(function (value, key) {
        if (jinnian == value) {
            jinnianling = key;
        }
    })
    var sum = 0;
    var chuli = '';
    while (sum < tema) {
        chuli = jinnianling;
        jinnianling--;
        if (jinnianling < 0) {
            jinnianling = 11;
        }
        sum++;
    }
    return arr[chuli];
}
exports.seeshengxiao = function (tema) {
    //查看当前开的是什么生肖
    var jinnian = '鸡';
    var jinnianling = '';
    var arr = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
    arr.forEach(function (value, key) {
        if (jinnian == value) {
            jinnianling = key;
        }
    })
    var sum = 0;
    var chuli = '';
    while (sum < tema) {
        chuli = jinnianling;
        jinnianling--;
        if (jinnianling < 0) {
            jinnianling = 11;
        }
        sum++;
    }
    return arr[chuli];
}
function in_array(stringToSearch, arrayToSearch) {
    for (var s = 0; s < arrayToSearch.length; s++) {
        var thisEntry = arrayToSearch[s].toString();
        if (thisEntry == stringToSearch) {
            return true;
        }
    }
    return false;
}

exports.split_pv = function (value, fengefu) {
    var shengxiao_arr = value.split(',');
    var shengxiao_view = new Object();
    shengxiao_arr.forEach(function (value, key) {
        var exp = value.split(fengefu);
        shengxiao_view[exp[0]] = exp[1];
    })
    var res = shengxiao_view;
    return res;
}

exports.checkslhc =function(data,kjdata)
{
    var datas = data.split('.');
    var kjdataarr =  kjdata.split(',');
    var jichu = 0;
    var aa =[];
    kjdataarr.forEach(function (value,key) {
        datas.forEach(function (value1,key1) {
            if(value ==value1 && aa[key] ==null){
                jichu++
                aa[key] ='yes';
            }
        })
    })
    return jichu;
}