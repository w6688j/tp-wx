db = require('./db.js'),
// 出错时等待 10
    xml2js = require('xml2js');
    exports.errorSleepTime = 5;
// 重启时间间隔，以秒为单位，0为不重启
exports.restartTime = 179;
conf = require("./conf.js")

var moment = require('moment');

var tool =require('./tool.js');

function sleep(delay) {
    var start = (new Date()).getTime();
    while ((new Date()).getTime() - start < delay) {
        continue;
    }
}
exports.cp = [
    /*{
        title: '北京赛车(PK10)',
        source: '168',
        name: 'pk10',
        enable: true,
        timer: 'pk10',
        option: getOption('pk10'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'pk10');
        },
        jiesuan: function (data) {

        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('北京赛车(PK10)解析数据不正确');
            }
        }
    },*/
    /*{
        title: '北京赛车(PK10)',
        source: 'self',
        name: 'pk10',
        enable: true,
        timer: 'pk10',
        option: getSelf('pk10'),
        formatdb: function (pk_data) {
            //console.log(pk_data)
            // console.log(pk_data);
            return getFormatDb(pk_data, 'pk10');
        },
        jiesuan: function (data) {

        },
        parse: function (str,resolve) {

            try {
                var parseString = require('xml2js').parseString;
                var defaultObject = {}
                var isSuc = false
                parseString(str, {trim: true}, function (err, result) {
                    var js = JSON.stringify(result)
                    var json = JSON.parse(js)

                    var sql = "SELECT t.* FROM think_game_date t WHERE l_id = 4 and draw_time > '"+moment().format("HH:mm:ss")+"' order by expect asc limit 1"

                    db.query({
                        sql:sql,
                        error:function(err){
                            console.log(err)
                        },
                        success:function(res){
                            console.log(json.ReturnMsg.Data[0].Expect[0])
                            console.log(res[0])
                            defaultObject = {
                                time:parseInt(new Date().getTime() / 1000,10),
                                game:'pk10',
                                current:{
                                    //开奖号码
                                    awardNumbers:json.ReturnMsg.Data[0].Result[0],
                                    //期号
                                    periodNumber:json.ReturnMsg.Data[0].Expect[0],
                                    //moment().format("YYYY-MM-DD HH:mm:ss")
                                    awardTime:moment().format("YYYY-MM-DD HH:mm:ss"),
                                },
                                next:{
                                    //开奖号码
                                    awardTime:moment(moment().format("YYYY-MM-DD")+" "+res[0].draw_time,"YYYY-MM-DD HH:mm:ss").add(30,"s").format("YYYY-MM-DD HH:mm:ss"),
                                    //期号
                                    periodNumber:parseInt(json.ReturnMsg.Data[0].Expect[0],10)+1,
                                    delayTimeInterval:0,
                                    awardTimeInterval:0,
                                }
                            }
                            var tt= moment(defaultObject.next.awardTime,"YYYY-MM-DD HH:mm:ss")
                            defaultObject.next.awardTimeInterval = (moment(defaultObject.next.awardTime,"YYYY-MM-DD HH:mm:ss").format("x")- new Date().getTime())
                            defaultObject.next.awardTimeInterval = parseInt(defaultObject.next.awardTimeInterval,10)
                            resolve(defaultObject)
                        }
                    })
                });
            } catch (err) {
                throw('北京赛车(PK10)self解析数据不正确');
            }
            //console.log(str)

        }
    },*/
    /*{
        title: '幸运飞艇(fei)',
        source: '168',
        name: 'fei',
        enable: true,
        timer: 'fei',
        option: getOption('fei'),
        formatdb: function (pk_data) {
            // console.log(pk_data);
            return getFormatDb(pk_data, 'fei');
        },
        jiesuan: function (data) {
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('幸运飞艇(fei)解析数据不正确');
            }
        }
    },*/
    /*{
        title: '幸运飞艇(fei)',
        source: 'self',
        name: 'fei',
        enable: true,
        timer: 'fei',
        option: getSelf('fei'),
        formatdb: function (pk_data) {
            //console.log(pk_data)
            // console.log(pk_data);
            return getFormatDb(pk_data, 'fei');
        },
        jiesuan: function (data) {

        },
        parse: function (str,resolve) {

            try {
                var parseString = require('xml2js').parseString;
                var defaultObject = {}
                var isSuc = false
                parseString(str, {trim: true}, function (err, result) {

                    var js = JSON.stringify(result)
                    var json = JSON.parse(js)

                    var sql = "SELECT t.* FROM think_game_date t WHERE l_id = 27 and draw_time > '"+moment().format("HH:mm:ss")+"' order by draw_time asc limit 1"

                    db.query({
                        sql:sql,
                        error:function(err){
                            console.log("self err fei")
                            console.log(err)
                        },
                        success:function(res){
                            console.log(json.ReturnMsg.Data[0].Expect[0])
                            console.log(res[0])
                            var nowTime = moment()
                            /!*if(parseInt(json.ReturnMsg.Data[0].Expect[0].substring(8),10) > 132) {
                                nowTime = moment().add("-1","day")
                            }*!/
                            defaultObject = {
                                time:parseInt(new Date().getTime() / 1000,10),
                                game:'fei',
                                current:{
                                    //开奖号码
                                    awardNumbers:json.ReturnMsg.Data[0].Result[0],
                                    //期号
                                    periodNumber:json.ReturnMsg.Data[0].Expect[0],
                                    //moment().format("YYYY-MM-DD HH:mm:ss")
                                    awardTime:nowTime.format("YYYY-MM-DD HH:mm:ss"),
                                },
                                next:{
                                    //开奖号码
                                    awardTime:moment(nowTime.format("YYYY-MM-DD")+" "+res[0].draw_time,"YYYY-MM-DD HH:mm:ss").add(30,"s").format("YYYY-MM-DD HH:mm:ss"),
                                    //期号
                                    periodNumber:parseInt(json.ReturnMsg.Data[0].Expect[0],10)+1,
                                    delayTimeInterval:0,
                                    awardTimeInterval:0,
                                }
                            }
                            var tt= moment(defaultObject.next.awardTime,"YYYY-MM-DD HH:mm:ss")
                            defaultObject.next.awardTimeInterval = (moment(defaultObject.next.awardTime,"YYYY-MM-DD HH:mm:ss").format("x")- new Date().getTime())/1000
                            console.log(defaultObject)
                            resolve(defaultObject)
                        }
                    })
                });
            } catch (err) {
                throw('self幸运飞艇(fei)解析数据不正确');
            }
            //console.log(str)

        }
    },*/
    {
        title: '北京28(bj28)',
        source: '168',
        name: 'bj28',
        enable: true,
        timer: 'bj28',
        option: getOption('bj28'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'bj28');
        },
        jiesuan: function (data) {
            // log("aaaaammm");
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('幸运飞艇(fei)解析数据不正确');
            }
        }
    },
    {
        title: '加拿大28(jnd28)',
        source: '168',
        name: 'jnd28',
        enable: true,
        timer: 'jnd28',
        option: getOption('jnd28'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'jnd28');
        },
        jiesuan: function (data) {
            // log("aaaaammm");
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('加拿大28(jnd28)解析数据不正确');
            }
        }
    },
    {
        title: '时时彩(ssc)',
        source: '168',
        name: 'ssc',
        enable: true,
        timer: 'ssc',
        option: getOption('ssc'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'ssc');
        },
        jiesuan: function (data) {
            // log("aaaaammm");
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('时时彩(ssc)解析数据不正确');
            }
        }
    },
    {
        title: '时时彩(ssc)',
        source: 'self',
        name: 'ssc',
        enable: true,
        timer: 'ssc',
        option: getSelf('ssc'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'ssc');
        },
        jiesuan: function (data) {
            // log("aaaaammm");
        },
        parse: function (str,resolve) {
            try {
                var parseString = require('xml2js').parseString;
                var defaultObject = {}
                var isSuc = false
                parseString(str, {trim: true}, function (err, result) {
                    var js = JSON.stringify(result)
                    var json = JSON.parse(js)

                    var sql = "SELECT t.* FROM think_game_date t WHERE l_id = 2 and draw_time > '"+moment().format("HH:mm:ss")+"' order by expect asc limit 1"

                    db.query({
                        sql:sql,
                        error:function(err){
                            console.log(err)
                        },
                        success:function(res){
                            console.log("l_id2")
                            console.log(json.ReturnMsg.Data[0].Expect[0])

                            defaultObject = {
                                time:new Date().getTime() / 1000,
                                game:'ssc',
                                current:{
                                    //开奖号码
                                    awardNumbers:json.ReturnMsg.Data[0].Result[0],
                                    //期号
                                    periodNumber:json.ReturnMsg.Data[0].Expect[0],
                                    //moment().format("YYYY-MM-DD HH:mm:ss")
                                    awardTime:moment().format("YYYY-MM-DD HH:mm:ss"),
                                },
                                next:{
                                    //开奖号码
                                    awardTime:moment(moment().format("YYYY-MM-DD")+" "+res[0].draw_time,"YYYY-MM-DD HH:mm:ss").add(30,"s").format("YYYY-MM-DD HH:mm:ss"),
                                    //期号
                                    periodNumber:moment().format("YYYYMMDD")+res[0].expect,
                                    delayTimeInterval:0,
                                    awardTimeInterval:0,
                                }
                            }

                            /*if(defaultObject.next.periodNumber.toString() == moment().format("YYYYMMDD")+"121")
                            {
                                defaultObject.next.periodNumber = moment().add(1,"day").format("YYYYMMDD")+"001"
                            }*/

                            /*if(defaultObject.next.periodNumber.toString() == moment().format("YYYYMMDD")+"024")
                            {
                                defaultObject.next.periodNumber = moment().add(1,"day").format("YYYYMMDD")+"001"
                            }*/
                            //var tt= moment(defaultObject.next.awardTime,"YYYY-MM-DD HH:mm:ss")
                            defaultObject.next.awardTimeInterval = (moment(defaultObject.next.awardTime,"YYYY-MM-DD HH:mm:ss").format("x")- new Date().getTime())/1000
                            console.log(defaultObject)
                            resolve(defaultObject)
                        }
                    })
                });
            } catch (err) {
                throw('时时彩(ssc)解析数据不正确');
            }
        }
    },
    {
        title: '江苏快三(kuai3)',
        source: '168',
        name: 'kuai3',
        enable: true,
        timer: 'kuai3',
        option: getOption('kuai3'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'kuai3');
        },
        jiesuan: function (data) {
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('江苏快三(kuai3)解析数据不正确');
            }
        }
    },
    {
        title: '六合彩(lhc)',
        source: '168',
        name: 'lhc',
        enable: true,
        timer: 'lhc',
        option: getOption('lhc'),
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'lhc');
        },
        jiesuan: function (data) {
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('六合彩(lhc)解析数据不正确');
            }
        }
    },
    /*{
        title: '极速赛车(jscar)',
        source: '168',
        name: 'jscar',
        enable: true,
        timer: 'jscar',
        option: {
            host: conf.submit.host,
            port:conf.submit.port,
            timeout: 5000,
            path: '/admin/auto/to_nodejs_jscar',
            headers: {
                "Content-Type": "application/json;charset=UTF-8",
                "User-Agent": "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)"
            }
            },
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'jscar');
        },
        jiesuan: function (data) {
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('极速赛车(jscar)解析数据不正确');
            }
        }
    },*/
    /*{
        title: '极速时时彩(jsssc)',
        source: '168',
        name: 'jsssc',
        enable: true,
        timer: 'jsssc',
        option: {
            host: conf.submit.host,
            port:conf.submit.port,
            timeout: 5000,
            path: '/admin/auto/to_nodejs_jssscs',
            headers: {
                "Content-Type": "application/json;charset=UTF-8",
                "User-Agent": "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)"
            }
        },
        formatdb: function (pk_data) {
            return getFormatDb(pk_data, 'jsssc');
        },
        jiesuan: function (data) {
        },
        parse: function (str) {
            try {
                var json = {};
                if (json = JSON.parse(str)) {
                    return json;
                }
            } catch (err) {
                throw('极速时时彩(jsssc)解析数据不正确');
            }
        }
    },*/



];

global.log = function (log) {
    var date = new Date();
    console.log('[' + date.toLocaleDateString() + ' ' + date.toLocaleTimeString() + '] ' + log)
}


function getFormatDb(data, stype) {
                switch (stype) {
                    case 'jscar':
                        return save_pk10_fei_jscar(data, stype);
                        break;
                    case 'pk10':
                        return save_pk10_fei_jscar(data, stype);
                        break;
                    case 'fei':
                        return save_pk10_fei_jscar(data, stype);
                        break;
                    case 'bj28':
                        return save_bj28_jnd28(data, stype);
                        break
                    case 'jnd28':
                        return save_bj28_jnd28(data, stype);
                        break
                    case 'ssc':
                        return save_ssc_jsssc(data, stype);
                        break
                    case 'jsssc':
                        return save_ssc_jsssc(data, stype);
                        break
                    case 'kuai3':
                        return save_kuai3(data, stype);
                        break
                    case 'lhc':
                        return save_lhc(data, stype);
                        break
                }


}

function save_pk10_fei_jscar(data, game) {
    var sql = "";
    var info = data.current.awardNumbers.split(',');
    var map = [];
    var lh = [];
    map['number'] = JSON.stringify(info);
    if (info[0] > info[9]) {
        lh[0] = '龙';
    } else {
        lh[0] = '虎';
    }
    if (info[1] > info[8]) {
        lh[1] = '龙';
    } else {
        lh[1] = '虎';
    }
    if (info[2] > info[7]) {
        lh[2] = '龙';
    } else {
        lh[2] = '虎';
    }
    if (info[3] > info[6]) {
        lh[3] = '龙';
    } else {
        lh[3] = '虎';
    }
    if (info[4] > info[5]) {
        lh[4] = '龙';
    } else {
        lh[4] = '虎';
    }
    map['lh'] = JSON.stringify(lh);
    map['tema'] = Number(info[0]) + Number(info[1]);
    if (map['tema'] % 2 == 0) {
        map['tema_ds'] = '双';
    } else {
        map['tema_ds'] = '单';
    }
    if (map['tema'] >= 12) {
        map['tema_dx'] = '大';
    } else {
        map['tema_dx'] = '小';
    }
    if (map['tema'] >= 3 && map['tema'] <= 7) {
        map['tema_dw'] = 'A';
    }
    if (map['tema'] >= 8 && map['tema'] <= 14) {
        map['tema_dw'] = 'B';
    }
    if (map['tema'] >= 15 && map['tema'] <= 19) {
        map['tema_dw'] = 'C';
    }
    if (info[0] > info[1]) {
        map['zx'] = '庄';
    } else {
        map['zx'] = '闲';
    }
    map['game'] = game;
    var str = "'" + data.current.periodNumber + "','" + data.current.awardTime + "','" + data.current.awardNumbers + "','"
        + map['lh'] + "','" + map['tema'] + "','" + map['tema_dx'] + "','" + map['tema_ds'] + "','" + map['zx'] + "','" + map['tema_dw'] + "','"
        + map['number'] + "','" + data.time + "','" + map['game'] + "'";
    sql = "insert into think_number(periodnumber,awardtime,awardnumbers,lh,tema,tema_dx,tema_ds,zx,tema_dw,number,time,game) values(" + str + ")";
    //log(sql);

    return sql;
}
function save_bj28_jnd28(data, game) {
    var info = data.current.awardNumbers.split(',');
    var n1 = info[0];
    var n2 = info[1];
    var n3 = info[2];
    var alln = Number(n1) + Number(n2) + Number(n3);
    //总和，赋值给总和.
    var map = [];
    map['zonghe'] = alln;
    //判断单双
    var jiou = '';
    if (alln % 2 == 0) {
        jiou = "双";
    } else {
        jiou = "单";
    }
    map['danshuang'] = jiou;
    //判断大小单双
    var daxiaodanshuang = '';
    if (jiou == "双") {
        if (0 <= alln && alln <= 13) {
            daxiaodanshuang = "小双";
        } else {
            daxiaodanshuang = "大双";
        }
    }
    if (jiou == "单") {
        if (0 <= alln && alln <= 13) {
            daxiaodanshuang = "小单";
        } else {
            daxiaodanshuang = "大单";
        }
    }

    //储存大小单双到服务器
    map['dxds'] = daxiaodanshuang;
    // 判断极值
    var jizhi = "";
    if (0 <= alln && alln <= 5) {
        jizhi = "极小";
    }
    if (5 < alln && alln < 22) {
        jizhi = "非极";
    }
    if (22 <= alln && alln <= 27) {
        jizhi = "极大";
    }
    //判断是否为顺子
    var shunzi = "";
    var ss = n1 + n2 + n3;
    var re = /^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/;
    if (re.test(ss)) {
        shunzi = "顺子";
    } else {
        var data_num = [n1, n2, n3];
        var result = bubbleSort(data_num);
        if (result == '0,1,9' || result == '0,8,9' || result == '0,1,2') {
            shunzi = "顺子";
        } else {
            shunzi = "非顺子";
        }
    }
    //判断是否为豹子
    var bz = "";
    if (n1 == n2 && n1 == n3 && n2 == n3) {
        bz = "豹子";
    } else {
        bz = "非豹子";
    }
    //判断为的大小
    var dx = "";
    if (alln <= 13) {
        dx = "小";
    } else {
        dx = "大";
    }
    //判断对子
    var dz = "";
    var duizinum = 0;
    if (n1 == n2) {
        duizinum = duizinum + 1;
    }
    if (n1 == n3) {
        duizinum = duizinum + 1;
    }
    if (n2 == n3) {
        duizinum = duizinum + 1;
    }
    if (duizinum == 1) {
        dz = "对子";
    } else {
        dz = "非对子";
    }
    map['dz'] = dz;
    map['dx'] = dx;
    map['bz'] = bz;
    map['sz'] = shunzi;
    map['jz'] = jizhi;
    map['number'] = "55";
    map['game'] = game;
    var str = "'" + data.current.periodNumber + "','" + data.current.awardTime + "','" + data.current.awardNumbers + "','"
        + map['zonghe'] + "','" + map['danshuang'] + "','" + map['dxds'] + "','" + map['jz'] + "','" + map['number'] + "','" + map['sz'] + "','"
        + map['bz'] + "','" + map['dx'] + "','" + map['dz'] + "','" + data.time + "','" + map['game'] + "'";
    var sql = "insert into think_dannumber(periodnumber,awardtime,awardnumbers,zonghe,danshuang,dxds,jz,number,sz,bz,dx,dz,time,game) values(" + str + ")";
    return sql;
}

function save_ssc_jsssc(data, game) {
    var map=[];
    var info = data.current.awardNumbers.split(',');
    var da = "";
    for (var i = 0; i < info.length; i++) {
        if (info[i] <= 4) {
            da = da + "小/";
        } else {
            da = da + "大/";
        }
    }
    var dansuan = "";
    for (var b = 0; b < info.length; b++) {
        if ((info[b]) % 2 == 0) {
            dansuan = dansuan +"双/";
        } else {
            dansuan = dansuan + "单/";
        }
    }
    var zuhe = "";
    for (var i = 0; i < info.length; i++) {
      var sum = info[i];
        if (sum <= 4) {
            if (sum % 2 == 0) {
                zuhe = zuhe+"小双/";
            } else {
                zuhe = zuhe +"小单/";
            }
        } else {
            if (sum % 2 !== 0) {
                zuhe = zuhe +"大单/";
            } else {
                zuhe = zuhe + "大双/";
            }
        }
    }
    //特码大小
    var tema = '';
    var tema_dx ='';
    var tema_ds ='';
    var tema_abc ='';
    var ssc_lh ='';
    for (var i = 0; i < info.length; i++) {
        tema = Number(tema) + Number(info[i]);
    }
    if (tema >= 23) {
        tema_dx = '大';
    } else {
        tema_dx = '小';
    }
    //特码单双
    if (tema % 2 == 0) {
        tema_ds = '双';
    } else {
        tema_ds = '单';
    }
    if (tema <= 15) {
        tema_abc = 'A';
    }
    if (tema >= 16 && tema <= 29) {
        tema_abc = 'B';
    }
    if (tema >= 30 && tema <= 45) {
        tema_abc = 'C';
    }
    //龙虎储存
    if (info[0] - info[4] > 0) {
        ssc_lh = '龙';
    }
    if (info[0] - info[4] < 0) {
        ssc_lh = '虎';
    }
    if (info[0] - info[4] == 0) {
        ssc_lh = '和';
    }
    var str = "'" + data.current.periodNumber + "','" + data.current.awardTime + "','" + data.current.awardNumbers + "','"
        +  data.time + "','" + da + "','" + zuhe + "','" + dansuan + "','" + game+ "','" + tema_dx + "','"
        + tema_ds + "','"+ tema_abc+ "','" + ssc_lh+ "'";
    var sql = "insert into think_sscnumber(periodnumber,awardtime,awardnumbers,time,dx,zuhe,ds,game,tema_dx,tema_ds,tema_abc,lh) values(" + str + ")";
    return sql;
}
function save_kuai3(data, game) {
   var info =data.current.awardNumbers.split(',');
   var n1 = info[0];
   var n2 = info[1];
   var n3 = info[2];
    //总和，赋值给总和.
   var alln = Number(n1) + Number(n2) + Number(n3);
    //判断是否为顺子
   var ss = n1 + n2 + n3;
   var shunzi = '';
    var re = /^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/;
    if (re.test(ss)) {
        shunzi = "顺子";
    } else {
        shunzi = "非顺子";
    }
    //判断是否为豹子
    var bz = '二同号';
    if (n1 == n2 && n1 == n3 && n2 == n3) {
        bz = "豹子";
    } else if (n1 !== n2 && n1 !== n3 && n2 !== n3){
        bz = "三不同";
    }
    //判断对子
   var duizinum = 0;
    if (n1 == n2) {
        duizinum = duizinum + 1;
    }
    if (n1 == n3) {
        duizinum = duizinum + 1;
    }
    if (n2 == n3) {
        duizinum = duizinum + 1;
    }
    var erbutongdan = '二不同';
    var dz = '';
    if (duizinum == 1) {
        dz = "二同号";
        if (n1 == n2 && n1 !== n3) {
            erbutongdan = n1;
        } else if (n1 == n3 && n1 !== n2) {
            erbutongdan = n1;
        } else if (n1 !== n2 && n1 !== n3 && n2 == n3) {
            erbutongdan = n2;
        }
    } else {
        dz = "二不同";
    }
    var dx='';
    if (alln >= 3 && alln <= 10) {
        dx = '小';
    } else if (alln >= 11 && alln <= 18) {
        dx = "大";
    }
    var ds ='';
    if (alln % 2 == 0) {
        ds = '双';
    } else {
        ds = '单';
    }

    var str = "'" + data.current.periodNumber + "','" + data.current.awardTime + "','" + data.current.awardNumbers + "','"
        +  alln + "','" + data.time + "','" + dz + "','" + erbutongdan + "','" + bz+ "','" + shunzi + "','"
        + game+ "','"+ dx+ "','" + ds+ "'";
    var sql = "insert into think_kuainumber(periodnumber,awardtime,awardnumbers,zonghe,time,ertonghao,erbutongdan,santonghaotong,sz,game,dx,ds) values(" + str + ")";
    return sql;
}
function save_lhc(data, game) {
    var number1 =data.current.awardNumbers.split(',');
    //当期特码
    var tema = number1[6];
    var tema_sebo = tool.get_lhc_sebo(tema);
    var tema_wuxing  = tool.get_lhc_wuxing(tema);
    var shengxiao_all =tool. get_all_shengxiao(data.current.awardNumbers);
    var tema_shengxiao = tool.seeshengxiao(tema);
    var str = "'" + data.current.periodNumber + "','" + data.current.awardTime + "','" + data.current.awardNumbers + "','"
        +  data.time + "','" + game + "','" + tema_sebo + "','" + tema_shengxiao + "','" + shengxiao_all+ "','" + tema_sebo + "','"
        + tema_wuxing+"'";
    var sql = "insert into think_lhcnumber(periodnumber,awardtime,awardnumbers,time,game,sebo,tema_shengxiao,shengxiao_all,tema_sebo,tema_wuxing) values(" + str + ")";
    return sql;
}
/*
 js 冒泡
 */
function bubbleSort(arr) {
    var len = arr.length;
    for (var i = 0; i < len; i++) {
        for (var j = 0; j < len - 1 - i; j++) {
            if (arr[j] > arr[j + 1]) {        // 相邻元素两两对比
                var temp = arr[j + 1];        // 元素交换
                arr[j + 1] = arr[j];
                arr[j] = temp;
            }
        }
    }
    return arr;
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

function getSelf(type) {
    var host = "45.76.65.76";
    var uri = "";
    switch (type) {
        case 'pk10':
            uri = "/api/result?name=bjpk10";
            break;
        case 'fei':
            uri = "/api/result?name=mlaft";
            break;
        /*case 'bj28':
            uri = "/?game=bj28";
            break;
        case 'jnd28':
            uri = "/?game=jnd28";
            break;*/
        case 'ssc':
            uri = "/api/result?name=cqssc";
            break;
        /*case 'jsssc':
            uri = "/?game=jsssc";
            break;
        case 'kuai3':
            uri = "/?game=kuai3";
            break;*/
        /*case 'lhc':
            uri = "/?game=lhc";
            break;*/
    }
    return {
        host: host,
        port:800,
        timeout: 5000,
        path: uri,
        headers: {
            "Content-Type": "application/json;charset=UTF-8",
            "User-Agent": "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)"
        }
    }
}

function getOption(type) {
    var host = "api.17gx.cn";
    var uri = "";
    switch (type) {
        case 'pk10':
            uri = "/?game=pk10";
            break;
        case 'fei':
            uri = "/?game=fei";
            break;
        case 'bj28':
            uri = "/?game=bj28";
            break;
        case 'jnd28':
            uri = "/?game=jnd28";
            break;
        case 'ssc':
            uri = "/?game=ssc";
            break;
        case 'jsssc':
            uri = "/?game=jsssc";
            break;
        case 'kuai3':
            uri = "/?game=kuai3";
            break;
        case 'lhc':
            uri = "/?game=lhc";
            break;
    }
    return {
        host: host,
        timeout: 5000,
        path: uri,
        headers: {
            "Content-Type": "application/json;charset=UTF-8",
            "User-Agent": "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)"
        }
    }
}

function getData(type, json) {
    var data = {};
    if (json.errorCode == 0 && json.result.businessCode == 0) {
        data = json.result.data;
        var numbers = data.preDrawIssue.toString();
        // if (type == 61 || type == 21) {
        //     numbers = numbers.substr(2);
        // }
        // if (type == 65) {
        //     data.preDrawCode = data.preDrawCode.substr(0, data.preDrawCode.length - 3)
        // }
        // if (type == 70) {
        //     var arr = data.preDrawCode.split(",");
        //     for (var i = 0, length = arr.length; i < length; i++) {
        //         if (arr[i].toString().length == 1) {
        //             arr[i] = '0' + arr[i];
        //         }
        //     }
        //  data.preDrawCode = arr.toString();
        // }
        return {
            type: type,
            time: getNowTime(),
            number: numbers,
            data: data.preDrawCode,
        };
    }
}
function getNowTime() {
    var myDate = new Date();
    var year = myDate.getFullYear();       //年
    var month = myDate.getMonth() + 1;     //月
    var day = myDate.getDate();            //日
    if (month < 10) month = "0" + month;
    if (day < 10) day = "0" + day;
    var mytime = year + "-" + month + "-" + day + " " + myDate.toLocaleTimeString();
    return mytime;
}
function getFrompcdd(str, type) {
    var exp_data = /var latest_draw_result = {"red":\[([0-9\[\]\,\s"]+)\]/;
    var exp_phase = /var latest_draw_phase = '(\d+)';/;
    var exp_time = /var latest_draw_time = '([0-9\-\:\s]+)';/;
    var m_data = str.match(exp_data);
    var m_phase = str.match(exp_phase);
    var m_time = str.match(exp_time);
    if (m_data && m_phase && m_time) {
        var mytime = m_time[1];
        var mynumber = m_phase[1];
        var data = m_data[1].replace(/"/g, '');
    }
    if (!mytime || !mynumber || !data) throw new Error('PC蛋蛋数据不正确');
    data = data.split(',').sort();
    var kj1 = 0, kj2 = 0, kj3 = 0;
    for (var i = 0 in data) {
        if (i < 6) {
            kj1 += parseInt(data[i], 10);
        } else if (i >= 6 && i < 12) {
            kj2 += parseInt(data[i], 10);
        } else if (i >= 12 && i <= 17) {
            kj3 += parseInt(data[i], 10);
        }
    }
    if (kj1 >= 10) {
        kj1 = kj1.toString().substr(-1);
    }
    if (kj2 >= 10) {
        kj2 = kj2.toString().substr(-1);
    }
    if (kj3 >= 10) {
        kj3 = kj3.toString().substr(-1);
    }
    if (kj1 < 0 || kj3 < 0) throw new Error('PC蛋蛋开奖数据不正确');
    data = kj1 + ',' + kj2 + ',' + kj3;
    try {
        var data = {
            type: type,
            time: mytime,
            number: mynumber,
            data: data
        };
        //console.log(data);
        return data;
    } catch (err) {
        throw('解析PC蛋蛋数据失败');
    }
}
function empty(exp) {
    if (!exp && typeof(exp) != "undefined" && exp != 0 && exp != '') {
        return true;
    } else {
        return false;
    }
}

