var played = {}, playedalias = {}, setting = {}, C_set = {},
    mysql = require('mysql'),
    http = require('http'),
    url = require('url'),
    crypto = require('crypto'),
    querystring = require('querystring'),
    config = require('./config.js'),
    db = require('./db.js'),
    calc = require('./kj-data/kj-calc-time.js'),
    exec = require('child_process').exec,
    execPath = process.argv.join(" "),
    tool = require('./tool.js'),
    parse = require('./kj-data/parse-calc-count.js');
var Promise = require('promise');
require('./String-ext.js');
// 抛出未知出错时处理
process.on('uncaughtException', function (e) {
    console.log(e.stack);
});
// 自动重启
if (config.restartTime) {
    setTimeout(function (exec, execPath) {
        exec(execPath);
        process.exit(0);
    }, config.restartTime * 1000, exec, execPath);
}
var timers = {};		// 任务记时器列表
var timers2 = {};     //佣金和反水
var encrypt_key = 'cc40bfe6d972ce96fe3a47d0f7342cb0';
var zjCount = 0;
var alias = '';
var animalsYear = '';



http.request = (function (_request) {
    return function (options, callback) {
        var timeout = options['timeout'],
            timeoutEventId;
        var req = _request(options, function (res) {
            res.on('end', function () {
                clearTimeout(timeoutEventId);
            });
            res.on('close', function () {
                clearTimeout(timeoutEventId);
            });
            res.on('abort', function () {
            });
            callback(res);
        });
        //超时
        req.on('timeout', function () {
            req.end();
        });
        //如果存在超时
        timeout && (timeoutEventId = setTimeout(function () {
            req.emit('timeout', {message: 'have been timeout...'});
        }, timeout));
        return req;
    };
})(http.request);
getPlayedFun(runTask);
function getPlayedFun(cb) {
    try {
        var client = createMySQLClient();
        //console.log(client)
    } catch (err) {
        log(err);
        return;
    }
    client.query("select * from think_game_config where id=1", function (err, data) {
        if (err) {
            log('读取玩法赔率出错：' + err.message);
        } else {
            played = data[0];
            if (cb) cb();
        }
    });
    client.query("select * from think_config where id=1", function (err, data) {
        C_set = data[0];
    })
    client.end();
}

// function getsetting() {
//     var client = createMySQLClient();
//     client.query("select  from think_config", function (err, data) {
//         if (err) {
//             log('读取本年生肖配置出错：' + err.message);
//         } else {
//             data.forEach(function (v) {
//                 setting[v.name] = v.value;
//             });
//         }
//     });
//     client.end();
// }

function runTask() {
    if (config.cp.length) {
        config.cp.forEach(function (conf) {
            timers[conf.name] = {};
            timers[conf.name][conf.timer] = {timer: null, option: conf};
            try {
                if (conf.enable) {
                    run(conf);
                }
            } catch (err) {
                restartTask(conf, config.errorSleepTime);
            }
        });
        var h= (new Date()).getHours();
        if(h>=12&&h<=15){
            timers2['yj']={timer: null};
            timers2['fs']={timer: null};
            if(timers2['yj'].timer) clearTimeout(timers2['yj'].timer);
            if(timers2['fs'].timer) clearTimeout(timers2['yj'].timer);
            yonjinjs();
            fanshui();
            log('+++++++++++++++++++结算佣金和流水+++++++++++++++++++++++++')
        }
    }
}

function restartTask(conf, sleep, flag) {
    if (sleep <= 0) sleep = config.errorSleepTime;
    if (!timers[conf.name]) timers[conf.name] = {};
    if (!timers[conf.name][conf.timer]) timers[conf.name][conf.timer] = {timer: null, option: conf};
    if (flag) {
        var opt;
        for (var t in timers[conf.name]) {
            opt = timers[conf.name][t].option;
            clearTimeout(timers[opt.name][opt.timer].timer);
            timers[opt.name][opt.timer].timer = setTimeout(run, sleep * 1000, opt);
            log('休眠' + sleep + '秒后从' + opt.source + '采集' + opt.title + '数据...');
        }
    } else {
        clearTimeout(timers[conf.name][conf.timer].timer);
        timers[conf.name][conf.timer].timer = setTimeout(run, sleep * 1000, conf);
        log('休眠' + sleep + '秒后从' + conf.source + '采集' + conf.title + '数据...');
    }
}

function run(conf) {
    if (timers[conf.name][conf.timer].timer) clearTimeout(timers[conf.name][conf.timer].timer);
    log('开始从' + conf.source + '采集' + conf.title + '数据');
    var option = conf.option;
    //option.path+='?'+(new Date()).getTime();
    http.request(option, function (res) {
        var data = "";
        res.on("data", function (_data) {
            data += _data.toString();
        });
        res.on("end", function () {
            try {
                if (conf.source == "self")
                {
                    var promise = new Promise(function(resolve, reject) {
                        // 异步处理
                        // 处理结束后、调用resolve 或 reject
                        conf.parse(data,resolve)
                    }).then(function(resData){
                        try {
                            submitData(resData, conf);
                        } catch (err) {
                            throw('提交出错：错误游戏为：' + conf.title + '----' + err + '错误：' + conf.source);
                        }
                    });

                } else {
                    try {
                        data = conf.parse(data);
                    } catch (err) {
                        throw('解析' + conf.title + '数据出错：' + err);
                    }

                    try {

                        submitData(data, conf);
                    } catch (err) {
                        throw('提交出错：错误游戏为：' + conf.title + '----' + err + '错误：' + conf.source);
                    }
                }

            } catch (err) {
                log('运行出错：%s，休眠%f秒错误游戏为：%b'.format(err, config.errorSleepTime, conf.title));
                restartTask(conf, config.errorSleepTime);
            }
        });

        res.on("error", function (err) {
            log(err);
            restartTask(conf, config.errorSleepTime);
        });
    }).on('timeout', function (err) {
        log('从' + conf.source + '采集' + conf.title + '数据超时');
        restartTask(conf, config.errorSleepTime);
    }).on("error", function (err) {
        // 一般网络出问题会引起这个错
        log(err);
        restartTask(conf, config.errorSleepTime);
    }).end();
}
function submitData(data, conf) {
    log('提交从' + conf.source + '采集的' + conf.title + '第' + data.current.periodNumber + "期" + '数据：' + data.current.awardNumbers);
    // requestKj(data);

    if (data.current.awardNumbers == null || data.current.awardNumbers == ""||data.current.awardNumbers==data.next.awardNumbers) {
        log('提交从' + conf.source + '采集的' + conf.title + '第' + '数据为空');
        restartTask(conf, config.errorSleepTime);
    } else {
        db.select({
            table: 'think_gamebefore',
            where: 'periodnumber=' + data.current.periodNumber + ' and game=\'' + conf.name + '\'',
            success: function (data_before) {
                if (data_before.length > 0) {
                    data.current.awardNumbers = data_before[0]['awardnumbers']
                }
                var sql = conf.formatdb(data)
                db.query({
                    sql: sql,
                    success: function (result) {
                        try {
                            sleep = Date.parse(new Date(data.next.awardTime)) - Date.parse(new Date());
                        } catch (err) {
                            log('解析下期数据出错：' + err);
                            restartTask(conf, config.errorSleepTime);
                            return;
                        }
                        log('写入' + conf['title'] + '第' + data.current.periodNumber + '期数据成功');
                        restartTask(conf, sleep / 1000, true);
                        //通知php更新，结算
                        requestKj(data);
                        console.log('开始结算' + conf.name);
                        calcJ(conf.name);
                    },
                    error: function (err) {
                        // 普通出错
                        log('运行出错：' + err.message);

                        restartTask(conf, config.errorSleepTime);
                       /* if (err.number == 1062) {
                            // 数据已经存在
                            // 正常休眠
                            try {
                                sleep = Date.parse(new Date(data.next.awardTime)) - Date.parse(new Date());//（开奖时间-当前时间）;//calc[conf.name](data);
                                //if (sleep < 0) sleep = config.errorSleepTime * 1000;
                            } catch (err) {
                                restartTask(conf, 5);
                                return;
                            }
                            log(conf['title'] + '第' + data.current.periodNumber + '期数据已经存在数据');

                            restartTask(conf, sleep / 1000, true);
                        } else {

                        }*/
                    }
                });
            }
        });
    }
}
function yuegengxin(number) {
    number.gengxin =number.game;
    var postData = JSON.stringify(number);
    var option = {
        host: conf.submit.host,
        port:conf.submit.port,
        path: conf.submit.path,
        method: 'POST',
        headers: {
            "Content-Type": 'application/json',
            "Content-Length": Buffer.byteLength(postData)
        }
    };
    var req = http.request(option, function (res) {
        res.on('data', function () {
        });
        res.on('end', function () {
            console.log('前端余额更新------------------------------------');
        });
    });
    req.write(postData);
    req.end();
}
function requestKj(number) {
    var postData = JSON.stringify(number);
    console.log("通知PHP更新")
    console.log(postData)
    var option = {
        host: conf.submit.host,
        port: conf.submit.port,
        path: conf.submit.path,
        method: 'POST',
        headers: {
            "Content-Type": 'application/json',
            "Content-Length": Buffer.byteLength(postData)
        }
    };
    console.log(option)
    var req = http.request(option, function (res) {
        res.on('data', function (dataRes) {
            //console.log("dat")
            console.log(dataRes)
        });
        res.on('end', function () {
            console.log('成功前端------------------------------------');
        });
    });
    req.write(postData);
    req.end();
    setTimeout(function () {
        yuegengxin(number);
    },1000)
}

function createMySQLClient() {
    try {
        return mysql.createConnection(conf.dbinfo).on('error', function (err) {
            throw('连接数据库失败');
        });
    } catch (err) {
        log('连接数据库失败：' + err);
        return false;
    }
}
function calcJ(type) {
    db.select({
        table: 'think_order',
        where: "is_add=0 and game='" + type + "'",
        success: function (order) {
            if (order.length == 0) {
                log(type + '已经全部结算！！！！');
            }
            switch (type) {
                case 'pk10':
                    pk10_js(order, '');
                    break;
                case 'fei':
                    fei_js(order, '_fei')
                    break;
                case 'jscar':
                    pk10_js(order, 'jscar_')
                    break;
                case 'bj28':
                    bj28_jnd28(order, 'dan_');
                    break;
                case 'jnd28':
                    bj28_jnd28(order, 'jnd_');
                    break;
                case 'ssc':
                    ssc_jsssc(order, 'ssc_');
                    break;
                case 'jsssc':
                    ssc_jsssc(order, 'jsssc_');
                    break;
                case 'kuai3':
                    kuai3_js(order, '');
                    break;
                case 'lhc' :
                    lhc_js(order, '');
                    break;
            }
        }
    })
}
function pk10_js(order, qz) {
    log('开始遍历结算');
    order.forEach(function (value, key) {
        //获取该期期号的数据
        var sql = 'select * from think_number where periodnumber =' + value['number'] + ' and game =\'' + value['game'] + '\'';
        db.query({
            sql: sql,
            error: function (err) {
                log('pk10查询相关期数错误');
                console.log(err);
            },
            success: function (result1) {
                if (result1.length == 0 || result1 == '') {
                    return false;
                }
                log('真的开始结算了')
                //获取十位数的大小单双
                if (!result1[0]['awardnumbers']) {
                    return false;
                }
                var peilv = JSON.parse(played[value['game']]);
                var number = [];
                var number1 = result1[0]['awardnumbers'].split(',');
                for (var y = 0; y < number1.length; y++) {
                    number[y] = [];
                    if (number1[y] % 2 == 0) {
                        number[y]['ds'] = '双';
                        if (number1[y] >= 6) {
                            number[y]['zuhe'] = '大双';
                        } else {
                            number[y]['zuhe'] = '小双';
                        }
                    } else {
                        number[y]['ds'] = '单';
                        if (number1[y] >= 6) {
                            number[y]['zuhe'] = '大单';
                        } else {
                            number[y]['zuhe'] = '小单';
                        }
                    }
                    if (number1[y] >= 6) {
                        number[y]['dx'] = '大';
                    } else {
                        number[y]['dx'] = '小';
                    }
                }
                var lh = JSON.parse(result1[0]['lh']);
                var addjine = '';
                db.select({
                    table: 'think_order',
                    field: 'sum(add_points) as add_points',
                    where: 'is_add=0 and userid=' + value['userid'] + ' and number =\'' + value['number'] + '\'',
                    success: function (data) {
                        addjine = data[0]['add_points']
                        switch (value['type']) {
                            //车号大小单双(12345/双/100)  测试成功
                            case 1:
                                var start1 = value['jincai'].split('/');
                                var num1 = 0;
                                var starts1 = start1[0].split('');
                                if (start1[1] == '单' || start1[1] == '双') {
                                    for (var a = 0; a < starts1.length; a++) {
                                        if (starts1[a] == 0) {
                                            var hao1 = '9';
                                        } else {
                                            hao1 = starts1[a] - 1;
                                        }
                                        if (number[hao1]['ds'] == start1[1]) {
                                            num1++;
                                        }
                                    }
                                } else {
                                    for (var a = 0; a < starts1.length; a++) {
                                        if (starts1[a] == 0) {
                                            hao1 = '9';
                                        } else {
                                            hao1 = starts1[a] - 1;
                                        }
                                        if (number[hao1]['dx'] == start1[1]) {
                                            num1++;
                                        }
                                    }
                                }
                                if (num1 > 0) {
                                    var points = num1 * start1[2] * peilv[qz + 'dxds'];
                                    add_points(value['id'], value['userid'], points, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 2:
                                //组合(890/大单/50)
                                var starts = value['jincai'].split('/');
                                var starts3 = starts[0].split('');
                                var num3 = 0;
                                var hao3 = 0;
                                // console.log(starts3);return false;
                                for (var a = 0; a < starts3.length; a++) {
                                    if (starts3[a] == 0) {
                                        hao3 = '9';
                                    } else {
                                        hao3 = starts3[a] - 1;
                                    }
                                    if (number[hao3]['zuhe'] == starts[1]) {
                                        num3++;
                                    }
                                }
                                var points3 = 0
                                if (num3 > 0) {
                                    if (starts[1] == '大单' || starts[1] == '小双') {
                                        points3 = num3 * starts[2] * peilv[qz + 'zuhe_1'];
                                    } else {
                                        points3 = num3 * starts[2] * peilv[qz + 'zuhe_2'];
                                    }
                                    add_points(value['id'], value['userid'], points3, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 3:
                                //车号(12345/89/20)
                                var start2 = value['jincai'].split('/');
                                var chehao2 = start2[1].split('');
                                var starts2 = start2[0].split('');
                                var num2 = 0;
                                var hao2 = 0;
                                for (var s = 0; s < chehao2.length; s++) {
                                    for (a = 0; a < starts2.length; a++) {
                                        if (starts2[a] == 0) {
                                            hao2 = '9';
                                        } else {
                                            hao2 = starts2[a] - 1;
                                        }
                                        if (chehao2[s] == 0) {
                                            chehao2[s] = 10;
                                        }
                                        if (chehao2[s] == Number(number1[hao2])) {
                                            num2++;
                                        }
                                    }
                                }
                                //
                                if (num2 > 0) {
                                    var points2 = num2 * start2[2] * peilv[qz + 'chehao'];
                                    add_points(value['id'], value['userid'], points2, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 4:
                                //龙虎  12345/龙/10
                                var start4 = value['jincai'].split('/');
                                var starts4 = start4[0].split('');
                                var num4 = 0;
                                var hao4 = 0;
                                for (a = 0; a < starts4.length; a++) {
                                    if (starts4[a] == 0) {
                                        hao4 = '9';
                                    } else {
                                        hao4 = starts4[a] - 1;
                                    }
                                    if (lh[hao4] == start4[1]) {
                                        num4++;
                                    }
                                }
                                if (num4 > 0) {
                                    var points4 = num4 * start4[2] * peilv[qz + 'lh'];
                                    add_points(value['id'], value['userid'], points4, addjine);
                                } else {
                                    del_points(value['id']);
                                }

                                break;
                            //冠亚庄闲(庄/200)
                            case 5:
                                var start5 = value['jincai'].split('/');
                                if (result1[0]['zx'] == start5[0]) {
                                    var points5 = start5[1] * peilv[qz + 'zx'];
                                    add_points(value['id'], value['userid'], points5, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 7:
                                //特码大小单双(和双100)
                                var start7 = value['jincai'].substr(1, 1);
                                var starts7 = value['jincai'].substr(2);
                                var points7 = 0;
                                var num7 = 0;
                                if (start7 == '大' || start7 == '小') {
                                    if (result1[0]['tema_dx'] == start7) {
                                        num7 = 1;
                                    }
                                } else {
                                    if (result1[0]['tema_ds'] == start7) {
                                        num7 = 1;
                                    }
                                }
                                if (num7 > 0) {
                                    if (start7 == '大' || start7 == '双') {
                                        points7 = starts7 * peilv[qz + 'tema_1'];
                                    } else {
                                        points7 = starts7 * peilv[qz + 'tema_2'];
                                    }
                                    add_points(value['id'], value['userid'], points7, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;

//                             //特码数字(和5.6.7/100)
                            case 8:
                                var tema1 = ['03', '04', '18', '19'];
                                var tema2 = ['5', '6', '16', '17'];
                                var tema3 = ['7', '8', '14', '15'];
                                var tema4 = ['9', '10', '12', '13'];
                                var tema5 = ['11'];
                                var start8 = value['jincai'].split('/');
                                var starts8 = start8[0].substr(1);
                                var num8 = 0;
                                var points8 = '';
                                var tlist = starts8.split('.');
                                for (a = 0; a < tlist.length; a++) {
                                    if (result1[0]['tema'] == tlist[a]) {
                                        if (in_array(tlist[a], tema1)) {
                                            points8 = start8[1] * peilv[qz + 'tema_sz_1'];
                                        }
                                        if (in_array(tlist[a], tema2)) {
                                            points8 = start8[1] * peilv[qz + 'tema_sz_2'];
                                        }
                                        if (in_array(tlist[a], tema3)) {
                                            points8 = start8[1] * peilv[qz + 'tema_sz_3'];
                                        }
                                        if (in_array(tlist[a], tema4)) {
                                            points8 = start8[1] * peilv[qz + 'tema_sz_4'];
                                        }
                                        if (in_array(tlist[a], tema5)) {
                                            points8 = start8[1] * peilv[qz + 'tema_sz_5'];
                                        }
                                        num8 = 1;
                                    }
                                }

                                if (num8 > 0) {
                                    add_points(value['id'], value['userid'], points8, addjine);

                                } else {
                                    del_points(value['id']);
                                }
                                break;
//
//                             //特码区段(BC/100)
                            case 9:
                                var start9 = value['jincai'].split('/');
                                var num9 = 0;
                                var points9 = '';
                                var starts9 = start9[0].split('');
                                for (a = 0; a < starts9.length; a++) {
                                    if (result1[0]['tema_dw'] == starts9[a]) {
                                        if (starts9[a] == 'A' || starts9[a] == 'C') {
                                            points9 = start9[1] * peilv[qz + 'tema_qd_1'];
                                        } else {
                                            points9 = start9[1] * peilv[qz + 'tema_qd_2'];
                                        }
                                        num9 = 1;
                                    }
                                }
                                if (num9 > 0 && points9) {
                                    add_points(value['id'], value['userid'], points9, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;


                        }
                    }
                })
            }
        });
    })
}
function fei_js(order, qz) {
    log('开始遍历结算');
    order.forEach(function (value, key) {
        //获取该期期号的数据
        var sql = 'select * from think_number where periodnumber =' + value['number'] + ' and game =\'' + value['game'] + '\'';
        db.query({
            sql: sql,
            error: function (err) {
                log('pk10查询相关期数错误');
                console.log(err);
            },
            success: function (result1) {
                if (result1.length == 0 || result1 == '') {
                    return false;
                }
                log('真的开始结算了')
                //获取十位数的大小单双
                if (!result1[0]['awardnumbers']) {
                    return false;
                }
                // console.log(result1[0]['awardnumbers']);
                var peilv = JSON.parse(played[value['game']]);
                var number = [];
                var number1 = result1[0]['awardnumbers'].split(',');
                for (var y = 0; y < number1.length; y++) {
                    number[y] = [];
                    if (number1[y] % 2 == 0) {
                        number[y]['ds'] = '双';
                        if (number1[y] >= 6) {
                            number[y]['zuhe'] = '大双';
                        } else {
                            number[y]['zuhe'] = '小双';
                        }
                    } else {
                        number[y]['ds'] = '单';
                        if (number1[y] >= 6) {
                            number[y]['zuhe'] = '大单';
                        } else {
                            number[y]['zuhe'] = '小单';
                        }
                    }
                    if (number1[y] >= 6) {
                        number[y]['dx'] = '大';
                    } else {
                        number[y]['dx'] = '小';
                    }
                }
                var lh = result1[0]['lh'];
                lh=JSON.parse(lh);
                var addjine = '';
                db.select({
                    table: 'think_order',
                    field: 'sum(add_points) as add_points',
                    where: 'is_add=0 and userid=' + value['userid'] + ' and number =\'' + value['number'] + '\'',
                    success: function (data) {
                        addjine = data[0]['add_points']
                        switch (value['type']) {
                            //车号大小单双(12345/双/100)  测试成功
                            case 1:
                                var start1 = value['jincai'].split('/');
                                var num1 = 0;
                                var starts1 = start1[0].split('');
                                if (start1[1] == '单' || start1[1] == '双') {
                                    for (var a = 0; a < starts1.length; a++) {
                                        if (starts1[a] == 0) {
                                            var hao1 = '9';
                                        } else {
                                            hao1 = starts1[a] - 1;
                                        }
                                        if (number[hao1]['ds'] == start1[1]) {
                                            num1++;
                                        }
                                    }
                                } else {
                                    for (var a = 0; a < starts1.length; a++) {
                                        if (starts1[a] == 0) {
                                            hao1 = '9';
                                        } else {
                                            hao1 = starts1[a] - 1;
                                        }
                                        if (number[hao1]['dx'] == start1[1]) {
                                            num1++;
                                        }
                                    }
                                }
                                if (num1 > 0) {
                                    var points = num1 * start1[2] * peilv['dxds' + qz];
                                    add_points(value['id'], value['userid'], points, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 2:
                                //组合(890/大单/50)
                                var starts = value['jincai'].split('/');
                                var starts3 = starts[0].split('');
                                var num3 = 0;
                                var hao3 = 0;
                                // console.log(starts3);return false;
                                for (var a = 0; a < starts3.length; a++) {
                                    if (starts3[a] == 0) {
                                        hao3 = '9';
                                    } else {
                                        hao3 = starts3[a] - 1;
                                    }
                                    if (number[hao3]['zuhe'] == starts[1]) {
                                        num3++;
                                    }
                                }
                                var points3 = 0
                                if (num3 > 0) {
                                    if (starts[1] == '大单' || starts[1] == '小双') {
                                        points3 = num3 * starts[2] * peilv['zuhe_1' + qz];
                                    } else {
                                        points3 = num3 * starts[2] * peilv['zuhe_2' + qz];
                                    }
                                    add_points(value['id'], value['userid'], points3, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 3:
                                //车号(12345/89/20)

                                var start2 = value['jincai'].split('/');
                                var chehao2 = start2[1].split('');
                                var starts2 = start2[0].split('');
                                var num2 = 0;
                                var hao2 = 0;
                                for (var s = 0; s < chehao2.length; s++) {
                                    for (a = 0; a < starts2.length; a++) {
                                        if (starts2[a] == 0) {
                                            hao2 = '9';
                                        } else {
                                            hao2 = starts2[a] - 1;
                                        }
                                        if (chehao2[s] == 0) {
                                            chehao2[s] = 10;
                                        }
                                        if (Number(chehao2[s]) == Number(number1[hao2])) {
                                            num2++;
                                        }
                                    }
                                }
                                if (num2 > 0) {
                                    var points2 = num2 * start2[2] * peilv['chehao' + qz];
                                    add_points(value['id'], value['userid'], points2, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 4:
                                //龙虎
                                var start4 = value['jincai'].split('/');
                                var starts4 = start4[0].split('');
                                var num4 = 0;
                                var hao4 = 0;
                                for (a = 0; a < starts4.length; a++) {
                                    if (starts4[a] == 0) {
                                        hao4 = '9';
                                    } else {
                                        hao4 = starts4[a] - 1;
                                    }
                                    if (lh[hao4] == start4[1]) {
                                        num4++;
                                    }
                                }
                                if (num4 > 0) {
                                    var points4 = num4 * start4[2] * peilv['lh' + qz];
                                    add_points(value['id'], value['userid'], points4, addjine);
                                } else {
                                    del_points(value['id']);
                                }

                                break;
                            //冠亚庄闲(庄/200)
                            case 5:
                                var start5 = value['jincai'].split('/');
                                if (result1[0]['zx'] == start5[0]) {
                                    var points5 = start5[1] * peilv['zx' + qz];
                                    add_points(value['id'], value['userid'], points5, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                            case 7:
                                //特码大小单双(和双100)
                                var start7 = value['jincai'].substr(1, 1);
                                var starts7 = value['jincai'].substr(2);
                                var points7 = 0;
                                var num7 = 0;
                                if (start7 == '大' || start7 == '小') {
                                    if (result1[0]['tema_dx'] == start7) {
                                        num7 = 1;
                                    }
                                } else {
                                    if (result1[0]['tema_ds'] == start7) {
                                        num7 = 1;
                                    }
                                }
                                if (num7 > 0) {
                                    if (start7 == '大' || start7 == '双') {
                                        points7 = starts7 * peilv['tema_1' + qz];
                                    } else {
                                        points7 = starts7 * peilv['tema_2' + qz];
                                    }
                                    add_points(value['id'], value['userid'], points7, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;

//                             //特码数字(和5.6.7/100)
                            case 8:
                                var tema1 = ['03', '04', '18', '19'];
                                var tema2 = ['5', '6', '16', '17'];
                                var tema3 = ['7', '8', '14', '15'];
                                var tema4 = ['9', '10', '12', '13'];
                                var tema5 = ['11'];
                                var start8 = value['jincai'].split('/');
                                var starts8 = start8[0].substr(1);
                                var num8 = 0;
                                var points8 = '';
                                var tlist = starts8.split('/');
                                for (a = 0; a < tlist.length; a++) {
                                    if (result1[0]['tema'] == tlist[a]) {
                                        if (in_array(tlist[a], tema1)) {
                                            points8 = start8[1] * peilv['tema_sz_1' + qz];
                                        }
                                        if (in_array(tlist[a], tema2)) {
                                            points8 = start8[1] * peilv['tema_sz_2' + qz];
                                        }
                                        if (in_array(tlist[a], tema3)) {
                                            points8 = start8[1] * peilv['tema_sz_3' + qz];
                                        }
                                        if (in_array(tlist[a], tema4)) {
                                            points8 = start8[1] * peilv['tema_sz_4' + qz];
                                        }
                                        if (in_array(tlist[a], tema5)) {
                                            points8 = start8[1] * peilv['tema_sz_5' + qz];
                                        }
                                        num8 = 1;
                                    }
                                }

                                if (num8 > 0) {
                                    add_points(value['id'], value['userid'], points8, addjine);

                                } else {
                                    del_points(value['id']);
                                }
                                break;
//
//                             //特码区段(BC/100)
                            case 9:
                                var start9 = value['jincai'].split('/');
                                var num9 = 0;
                                var points9 = '';
                                var starts9 = start9[0].split('');
                                for (a = 0; a < starts9.length; a++) {
                                    if (result1[0]['tema_dw'] == starts9[a]) {
                                        if (starts9[a] == 'A' || starts9[a] == 'C') {
                                            points9 = start9[1] * peilv['tema_qd_1' + qz];
                                        } else {
                                            points9 = start9[1] * peilv['tema_qd_2' + qz];
                                        }
                                        num9 = 1;
                                    }
                                }
                                if (num9 > 0 && points9) {
                                    add_points(value['id'], value['userid'], points9, addjine);
                                } else {
                                    del_points(value['id']);
                                }
                                break;
                        }
                    }
                })
            }
        });

    })

}
function bj28_jnd28(order, qz) {
    order.forEach(function (value, key) {
        //获取该期期号的数据
        var sql = 'select * from think_dannumber where periodnumber =' + value['number'] + ' and game =\'' + value['game'] + '\'';
        db.query({
            sql: sql,
            error: function (err) {
                log('pk10查询相关期数错误');
                console.log(err);
            },
            success: function (gamedata) {
                if (gamedata.length == 0 || gamedata == '') {
                    return false;
                }
                var addjine = '';
                db.select({
                    table: 'think_order',
                    field: 'sum(add_points) as add_points,sum(del_points)as del_points',
                    where: 'is_add=0 and userid=' + value['userid'] + ' and number =\'' + value['number'] + '\'',
                    success: function (data) {
                        addjine = data[0]['add_points'];
                        var deljine = data[0]['del_points'];
                        var peilv = JSON.parse(played[value['game']]);
                        var number1 = gamedata[0]['awardnumbers'].split(',');
                        var jincai = value['jincai'].split('/');
                        switch (value['type']) {
                            //第一种为单双判断     单/20    如果判断正确
                            case 1:

                                var points1 = '';
                                var start1 = jincai;
                                //如果这局不是等于13 ， 14 那么就按照正常的程序去走
                                if (gamedata[0]['zonghe'] != 13 && gamedata[0]['zonghe'] != 14) {
                                    if (gamedata[0]['danshuang'] == start1[0]) {
                                        var points1 = start1[1] * peilv[qz + 'dx'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    //如果这局开的是13 或者14 那么就按照13 ，14 处理
                                } else {
                                    if (gamedata[0]['danshuang'] == start1[0]) {
                                        //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                        if (deljine <= peilv[qz + 'ds_jq_1']) {
                                            var points1 = start1[1] * peilv[qz + 'ds_jq_x1_bl'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        }
                                        if (deljine > peilv[qz + 'ds_jq_1'] && deljine <= peilv[qz + 'ds_jq_2']) {
                                            var points1 = start1[1] * peilv[qz + 'ds_jq_1_bl'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        }
                                        //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                        if (deljine > peilv[qz + 'ds_jq_2'] && deljine <= peilv[qz + 'ds_jq_3']) {
                                            var points1 = start1[1] * peilv[qz + 'ds_jq_2_bl'];
                                            add_points(value['id'], value['userid'], points1, addjine);

                                        }
                                        //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                        if (deljine > peilv[qz + 'ds_jq_3']) {
                                            var points1 = start1[1] * peilv[qz + 'ds_jq_3_bl'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;
                            //type = 2 时 ， 判断大小单双。 // ------------------测试成功------------------------------------
                            case 2:
                                start1 = jincai;

                                if (start1[0] == '大单' || start1[0] == '大双' || start1[0] == '小单' || start1[0] == '小双') {
                                    if (gamedata[0]['dxds'] == start1[0]) {
                                        //判断是不是13 ， 或者14
                                        //分算法

                                        switch (Number(peilv[qz + 'dxds_swich'])) {
                                            case 1:
                                                //同一算法 13 14 特别
                                                if (gamedata[0]['zonghe'] != 13 && gamedata[0]['zonghe'] != 14) {
                                                    var points1 = start1[1] * peilv[qz + 'dxds'];
                                                    add_points(value['id'], value['userid'], points1, addjine);
                                                } else {
                                                    //如果用户投的是  大小单双正确，且是综合为13,14的就按照特殊情况处理
                                                    var points1 = start1[1] * peilv[qz + 'dxds_13_14'];
                                                    add_points(value['id'], value['userid'], points1, addjine);
                                                }
                                                break;
                                            case 2:
                                                //第二种算法，看金额的大小去计算倍率
                                                if (gamedata[0]['zonghe'] != 13 && gamedata[0]['zonghe'] != 14) {
                                                    if (start1[0] == '大双') {
                                                        var peil = '';
                                                        if (qz == 'jnd_') {
                                                            peil = peilv[qz + 'dxds_dsxd'];
                                                        } else {
                                                            peil = peilv[qz + 'dxds_ds'];
                                                        }
                                                        var points1 = start1[1] * peil;
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }
                                                    if (start1[0] == '小双') {
                                                        if (qz == 'jnd_') {
                                                            peil = peilv[qz + 'dxds_xsdd'];
                                                        } else {
                                                            peil = peilv[qz + 'dxds_xs'];
                                                        }
                                                        var points1 = start1[1] * peil;
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }
                                                    if (start1[0] == '大单') {
                                                        if (qz == 'jnd_') {
                                                            peil = peilv[qz + 'dxds_xsdddss'];
                                                        } else {
                                                            peil = peilv[qz + 'dxds_dd'];
                                                        }
                                                        var points1 = start1[1] * peil;
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }
                                                    if (start1[0] == '小单') {
                                                        if (qz == 'jnd_') {
                                                            peil = peilv[qz + 'dxds_dsxdxiaodan'];
                                                        } else {
                                                            peil = peilv[qz + 'dxds_xd'];
                                                        }
                                                        var points1 = start1[1] * peil;
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }

                                                } else {
                                                    //如果开的值为13,14的时候，后台设置，赔率为多少。
                                                    if (deljine <= peilv[qz + 'dxds_jq_1']) {
                                                        var points1 = start1[1] * peilv[qz + 'dxds_jq_x1_bl'];
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }
                                                    if (deljine > peilv[qz + 'dxds_jq_1'] && start1[1] <= peilv[qz + 'dxds_jq_2']) {
                                                        var points1 = start1[1] * peilv[qz + 'dxds_jq_1_bl'];
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }
                                                    if (deljine > peilv[qz + 'dxds_jq_2'] && start1[1] <= peilv[qz + 'dxds_jq_3']) {
                                                        var points1 = start1[1] * peilv[qz + 'dxds_jq_3_bl'];
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }
                                                    if (deljine > peilv[qz + 'dxds_jq_3']) {
                                                        var points1 = start1[1] * peilv[qz + 'dxds_jq_3_bl'];
                                                        add_points(value['id'], value['userid'], points1, addjine);
                                                    }

                                                }
                                                break;
                                        }

                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;
                            //type  = 3 极大值，极小值，判断 -----------------------------测试成功----------------------------
                            case 3:
                                start1 = jincai;
                                if (start1[0] == '极大' || start1[0] == '极小') {
                                    if (gamedata[0]['jz'] == start1[0]) {
                                        var points1 = start1[1] * peilv[qz + 'jz'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;
                            // type= 4 和值的判断 9/10 --------------------------- 测试成功-------------------
                            case 4:
                                start1 = jincai;
                                if (0 <= start1[0] || start1[0] <= 27) {
                                    if (gamedata[0]['zonghe'] == start1[0]) {
                                        if (qz == 'dan_') {
                                            qz = '';
                                        }
                                        var he_res = tool.split_pv(peilv[qz + 'hezhi_bv'], '=')
                                        //乘配置文件的数据
                                        var points1 = start1[1] * he_res[start1[0]];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;
                            //豹子  type = 5  999/20    -----------------------------测试成功-----------------------------
                            case 5:
                                start1 = jincai;
                                if (start1[0] == '豹子') {
                                    if (gamedata[0]['bz'] == start1[0]) {
                                        var points1 = start1[1] * peilv[qz + 'bz'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;
                            //  顺子判断   type = 6    顺子 ---------------------------------测试成功---------------------------
                            case 6:
                                start1 = jincai;
                                if (start1[0] == '顺子') {
                                    if (gamedata[0]['sz'] == start1[0]) {
                                        var points1 = start1[1] * peilv[qz + 'sz'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;
                            // //判断大小，-------------------------------未测试------------------------------
                            case 7:
                                start1 = jincai;
                                if (start1[0] == '大' || start1[0] == '小') {
                                    //如果输入的值不是13 ， 14 那么走正常的程序
                                    if (gamedata[0]['zonghe'] != 13 && gamedata[0]['zonghe'] != 14) {
                                        if (gamedata[0]['dx'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'dx'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        if (gamedata[0]['dx'] == start1[0]) {
                                            if (deljine <= peilv[qz + 'ds_jq_1']) {
                                                var points1 = start1[1] * peilv[qz + 'ds_jq_x1_bl'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            }
                                            //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                            if (deljine > peilv[qz + 'ds_jq_1'] && deljine <=peilv[qz + 'ds_jq_2']) {
                                                var points1 = start1[1] * peilv[qz + 'ds_jq_1_bl'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            }
                                            //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                            if (deljine > peilv[qz + 'ds_jq_2'] && deljine <= peilv[qz + 'ds_jq_3']) {
                                                var points1 = start1[1] * peilv[qz + 'ds_jq_2_bl'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            }
                                            //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                            if (deljine > peilv[qz + 'ds_jq_3']) {
                                                var points1 = start1[1] * peilv[qz + 'ds_jq_3_bl'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            }
                                            //否者如果玩者输入的不对，删除投注的金额。
                                        } else {
                                            del_points(value['id']);
                                        }

                                    }
                                }
                                break;
                            case 8:
                                start1 = jincai;
                                if (start1[0] == '对子') {
                                    if (gamedata[0]['dz'] == start1[0]) {
                                        var points1 = start1[1] * peilv[qz + 'dz'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                }
                                break;

                        }

                    }
                })


            }
        });
    });
}
function ssc_jsssc(order, qz) {
    order.forEach(function (value, key) {
        db.select({
            table: 'think_sscnumber',
            where: 'periodnumber = \'' + value['number'] + '\' and game=\'' + value['game'] + '\'',
            success: function (gamedata) {
                if (gamedata.length == 0 || gamedata == '') {
                    console.log(value['game'] + '没有投注')
                } else {
                    if (!gamedata[0]['awardnumbers']) {
                        return false;
                    }
                    var addjine = '';
                    db.select({
                        table: 'think_order',
                        field: 'sum(add_points) as add_points,sum(del_points)as del_points',
                        where: 'is_add=0 and userid=' + value['userid'] + ' and number =\'' + value['number'] + '\'',
                        success: function (data) {
                            addjine = data[0]['add_points'];
                            var deljine = data[0]['del_points'];
                            var peilv=JSON.parse(played[value['game']]);
                            var number1 = gamedata[0]['awardnumbers'].split(',');
                            switch (value['type']) {
                                case 1:
                                    var start1 = value['jincai'].split('/');
                                    //再次判断是否为单双正确的，和数据库里ds 判断
                                    if (start1[1] == '单' || start1[1] == '双') {
                                        var ds = gamedata[0]['ds'].split('/');
                                        var starts4 = start1[0].split('');
                                        var num4 = 0;
                                        for (var a = 0; a < starts4.length; a++) {
                                            var hao4 = starts4[a] - 1;
                                            if (ds[hao4] == start1[1]) {
                                                num4++;
                                            }
                                        }
                                        if (num4 > 0) {
                                            var points4 = num4 * start1[2] * peilv[qz + 'bl_dxds'];
                                            add_points(value['id'], value['userid'], points4, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }

                                    }
                                    //如果为大小的单独和数据库字段dx 的判断
                                    if (start1[1] == '大' || start1[1] == '小') {
                                        //拆分ds字段的數組。
                                        var dx = gamedata[0]['dx'].split('/');
                                        starts4 = start1[0].split('');
                                        num4 = 0;
                                        for (var a = 0; a < starts4.length; a++) {
                                            var hao4 = starts4[a] - 1;
                                            if (dx[hao4] == start1[1]) {
                                                num4++;
                                            }
                                        }
                                        if (num4 > 0) {
                                            points4 = num4 * start1[2] * peilv[qz + 'bl_dxds'];
                                            add_points(value['id'], value['userid'], points4, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }

                                    break;
                                //type = 2 时 ， 判断大小单双。 // ------------------测试成功---------------------------------------
                                case 2:
                                    var start1 = value['jincai'].split('/');
                                    //再次判断是否为单双正确的，和数据库里ds 判断
                                    if (start1[1] == '大单' || start1[1] == '大双' || start1[1] == '小双' || start1[1] == '小单') {
                                        //判断第几个数字
                                        var selectsum = start1[0] - 1;
                                        //拆分ds字段的數組。
                                        var zuhe = gamedata[0]['zuhe'].split('/');
                                        if (zuhe[selectsum] == start1[1]) {
                                            var points1 = start1[2] * peilv[qz + 'bl_zuhe'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                                //type  = 3 时候， 判断数字的大小。
                                //12/56/500  在万位和千位的是为5，6 球四柱
                                case 3:
                                    //获取用户下注的数字。
                                    var start1 = value['jincai'].split('/');
                                    var chehao2 = start1[1].split('');
                                    var starts2 = start1[0].split('');
                                    var num2 = 0;
                                    for (var s = 0; s < chehao2.length; s++) {
                                        for (var a = 0; a < starts2.length; a++) {
                                            //第i个位置有 没有
                                            var hao2 = starts2[a] - 1;
                                            if (chehao2[s] == number1[hao2]) {
                                                num2++;
                                            }
                                        }
                                    }
                                    if (num2 > 0) {
                                        //猜数字的倍率
                                        var points2 = num2 * start1[2] * peilv[qz + 'bl_sum'];
                                        add_points(value['id'], value['userid'], points2, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                case 4:
                                    //总/大/50
                                    var start1 = value['jincai'].split('/');
                                    if (start1[1] == '大' || start1[1] == '小') {
                                        if (start1[1] == gamedata[0]['tema_dx']) {
                                            //大小
                                            var pointsdx = start1[2] * peilv[qz + 'zonghe_dxds'];
                                            add_points(value['id'], value['userid'], pointsdx, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    if (start1[1] == '单' || start1[1] == '双') {
                                        if (start1[1] == gamedata[0]['tema_ds']) {
                                            //单双
                                            var pointsdx = start1[2] * peilv[qz + 'zonghe_dxds'];
                                            add_points(value['id'], value['userid'], pointsdx, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                                case 5://测试成功
                                    //abc
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == 'B' || start1[0] == 'A' || start1[0] == 'C') {
                                        if (start1[0] == gamedata[0]['tema_abc']) {
                                            if (start1[0] == 'A') {
                                                var pointsdx = start1[1] * peilv[qz + 'a_dxds'];
                                                add_points(value['id'], value['userid'], pointsdx, addjine);
                                            }
                                            if (start1[0] == 'B') {
                                                pointsdx = start1[1] * peilv[qz + 'b_dxds'];
                                                add_points(value['id'], value['userid'], pointsdx, addjine);
                                            }
                                            if (start1[0] == 'C') {
                                                pointsdx = start1[1] * peilv[qz + 'c_dxds'];
                                                add_points(value['id'], value['userid'], pointsdx, addjine);
                                            }
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;

                                case 6://测试成功
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '龙' || start1[0] == '虎' || start1[0] == '和') {
                                        if (start1[0] == gamedata[0]['lh']) {
                                            if (start1[0] == '龙') {
                                                pointsdx = start1[1] * peilv[qz + 'lh_sum'];
                                                add_points(value['id'], value['userid'], pointsdx, addjine);
                                            }
                                            if (start1[0] == '虎') {
                                                pointsdx = start1[1] * peilv[qz + 'laohu_sum'];
                                                add_points(value['id'], value['userid'], pointsdx, addjine);
                                            }
                                            if (start1[0] == '和') {
                                                pointsdx = start1[1] * peilv[qz + 'he_sum'];
                                                add_points(value['id'], value['userid'], pointsdx, addjine);
                                            }

                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;

                                case 7:  //测试成功
                                    //前/对子/50
                                    var start1 = value['jincai'].split('/');
                                    //前三个值
                                    if (start1[0] == '前') {
                                        var checksum = number1[0] + ',' + number1[1] + ',' + number1[2];
                                        var qiansan = checkqzh(checksum);
                                        if (qiansan == start1[1]) {
                                            pointsdx = 0;
                                            if (start1[1] == '豹子') {
                                                pointsdx = start1[2] * peilv[qz + 'baozi_sqsum'];
                                            }
                                            if (qiansan == '顺子') {
                                                pointsdx = start1[2] * peilv[qz + 'sz_sqsum'];
                                            }
                                            if (qiansan == '对子') {
                                                pointsdx = start1[2] * peilv[qz + 'duizi_sqsum'];
                                            }
                                            if (qiansan == '半顺') {
                                                pointsdx = start1[2] * peilv[qz + 'banshun_sqsum'];
                                            }
                                            if (qiansan == '杂六') {
                                                pointsdx = start1[2] * peilv[qz + 'liu_sqsum'];
                                            }
                                            add_points(value['id'], value['userid'], pointsdx, addjine);

                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    if (start1[0] == '中') {
                                        var checksum = number1[1] + ',' + number1[2] + ',' + number1[3];
                                        var qiansan = checkqzh(checksum);
                                        if (qiansan == start1[1]) {
                                            var pointsdx = 0;
                                            if (start1[1] == '豹子') {
                                                pointsdx = start1[2] * peilv[qz + 'baozi_sqsum'];
                                            }
                                            if (start1[1] == '顺子') {
                                                pointsdx = start1[2] * peilv[qz + 'sz_sqsum'];
                                            }
                                            if (start1[1] == '对子') {
                                                pointsdx = start1[2] * peilv[qz + 'duizi_sqsum'];
                                            }
                                            if (start1[1] == '半顺') {
                                                pointsdx = start1[2] * peilv[qz + 'banshun_sqsum'];
                                            }
                                            if (start1[1] == '杂六') {
                                                pointsdx = start1[2] * peilv[qz + 'liu_sqsum'];
                                            }
                                            add_points(value['id'], value['userid'], pointsdx, addjine);

                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    if (start1[0] == '后') {
                                        checksum = number1[2] + ',' + number1[3] + ',' + number1[4];
                                        qiansan = checkqzh(checksum);
                                        if (qiansan == start1[1]) {
                                            var pointsdx = 0;
                                            if (start1[1] == '豹子') {
                                                pointsdx = start1[2] * peilv[qz + 'baozi_sqsum'];
                                            }
                                            if (start1[1] == '顺子') {
                                                pointsdx = start1[2] * peilv[qz + 'sz_sqsum'];
                                            }
                                            if (start1[1] == '对子') {
                                                pointsdx = start1[2] * peilv[qz + 'duizi_sqsum'];
                                            }
                                            if (start1[1] == '半顺') {
                                                pointsdx = start1[2] * peilv[qz + 'banshun_sqsum'];
                                            }
                                            if (start1[1] == '杂六') {
                                                pointsdx = start1[2] * peilv[qz + 'liu_sqsum'];
                                            }
                                            add_points(value['id'], value['userid'], pointsdx, addjine);

                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                            }
                        }
                    })
                }
            }
        })
    });
}
function kuai3_js(order, qz) {
    order.forEach(function (value, key) {
        db.select({
            table: 'think_kuainumber',
            where: 'periodnumber = "' + value['number'] + '" and game="' + value['game'] + '"',
            success: function (gamedata) {
                if (gamedata.length == 0 || gamedata == '') {
                    console.log(value['game'] + '没有投注')
                } else {
                    if (!gamedata[0]['awardnumbers']) {
                        return false;
                    }
                    var addjine = '';
                    db.select({
                        table: 'think_order',
                        field: 'sum(add_points) as add_points,sum(del_points)as del_points',
                        where: 'is_add=0 and userid=' + value['userid'] + ' and number =\'' + value['number'] + '\'',
                        success: function (data) {
                            addjine = data[0]['add_points'];
                            var deljine = data[0]['del_points'];
                            var peilv = JSON.parse(played[value['game']]);
                            var number1 = gamedata[0]['awardnumbers'].split(',');
                            switch (value['type']) {
                                //和值
                                case 1:
                                    var start1 = value['jincai'].split('/');
                                    if (4 <= start1[0] || start1[0] <= 17) {
                                        if (gamedata[0]['zonghe'] == start1[0]) {
                                            var data = peilv[qz + 'kuai3_hezhi_bv'].split(',');
                                            var touzhushuzi = start1[0];
                                            var dd = data[touzhushuzi];
                                            var chaifendeshuzi = dd.split('=');
                                            var he_res = chaifendeshuzi[1];
                                            //乘配置文件的数据
                                            var points1 = start1[1] * he_res;
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //三不同通选
                                case 2:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '三不同') {
                                        if (gamedata[0]['santonghaotong'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'kuai3_sbt'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                                //豹子/123/600
                                case 3:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == gamedata[0]['santonghaotong']) {
                                        //这期什么豹
                                        var wahtbao = number1[0];
                                        var starts4 = start1[1].split('');
                                        var cshih = 0;
                                        for (var bz = 0; bz < starts4.length; bz++) {
                                            if (starts4[bz] == wahtbao) {
                                                cshih++;
                                            }
                                        }
                                        if (cshih > 0) {
                                            var points1 = start1[2] * peilv[qz + 'kuai_bz'] * cshih;
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //三连号：
                                case 4:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '顺子') {
                                        if (gamedata[0]['sz'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'kuai3_sz_bv'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //二相同通选
                                case 5:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '二同号') {
                                        if (gamedata[0]['ertonghao'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'kuai3_ertonghaotong_bv'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //二不同通选
                                case 6:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '二不同') {
                                        if (gamedata[0]['ertonghao'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'kuai3_erbutongtong_bv'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;

                                //短牌 type==9
                                case 9:
                                    var start1 = value['jincai'].split('/');
                                    //二同号为继续
                                    if (gamedata[0]['ertonghao'] == '二同号') {
                                        if (start1[0] == '短牌') {
                                            if (start1[1] == gamedata[0]['erbutongdan']) {
                                                var points1 = start1[2] * peilv[qz + 'kuai3_duanp_bv'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            } else {
                                                del_points(value['id']);
                                            }
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                case 10:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '单选') {
                                        var dai = start1[1];
                                        var add = '';
                                        for (var i = 0; i < number1.length; i++) {
                                            if (dai == number1[i]) {
                                                add += 1;
                                            }
                                        }
                                        //中一个情况
                                        if (add == 1) {
                                            points1 = start1[2] * peilv[qz + 'kuai_dx1_bv'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                        //中两个情况
                                        if (add == 2) {
                                            points1 = start1[2] * peilv[qz + 'kuai_dx2_bv'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                        //中三个情况
                                        if (add == 3) {
                                            var points1 = start1[2] * peilv[qz + 'kuai_dx3_bv'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                case 11:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '大' || start1[0] == '小') {

                                        if (gamedata[0]['dx'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'kuai3_dxds'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else if (start1[0] == '单' || start1[0] == '双') {
                                        if (gamedata[0]['ds'] == start1[0]) {
                                            var points1 = start1[1] * peilv[qz + 'kuai3_dxds'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                                case 12://长牌/13/500
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '长牌') {
                                        if (checkss(start1[1], gamedata[0]['awardnumbers']) == 2) {
                                            var points1 = start1[2] * peilv[qz + 'kuai_bz_changpai'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                                case 13:
                                    //三军/123/500
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '三军') {
                                        var jichu = 0;
                                        // foreach (str_split(start1[1]) as value) {
                                        //     if (this->checks(value, current_number['awardnumbers']) >= 1) {
                                        //         jichu++;
                                        //     }
                                        // }
                                        if (checkss(start1[1], gamedata[0]['awardnumbers']) == 1) {
                                            var points1 = start1[2] * peilv[qz + 'kuai_bz_sanjun'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                case 15:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '全豹') {
                                        if (gamedata[0]['santonghaotong'] == '豹子') {
                                            var points1 = start1[1] * peilv[qz + 'kuai_bz_quan'];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                            }

                        }
                    })
                }
            }
        })
    });
}
function lhc_js(order, qz) {
    order.forEach(function (value, key) {
        db.select({
            table: 'think_lhcnumber',
            where: 'periodnumber = "' + value['number'] + '" and game="' + value['game'] + '"',
            success: function (gamedata) {
                if (gamedata.length == 0 || gamedata == '') {
                    console.log(value['game'] + '没有投注')
                } else {
                    if (!gamedata[0]['awardnumbers']) {
                        return false;
                    }
                    var addjine = '';
                    db.select({
                        table: 'think_order',
                        field: 'sum(add_points) as add_points,sum(del_points)as del_points',
                        where: 'is_add=0 and userid=' + value['userid'] + ' and number =\'' + value['number'] + '\'',
                        success: function (data) {
                            addjine = data[0]['add_points'];
                            var deljine = data[0]['del_points'];
                            var peilv = JSON.parse(played[value['game']]);
                            var number1 = gamedata[0]['awardnumbers'].split(',');
                            var tema = number1[6];
                            var tema_chai = tema.split('');
                            if (tema < 10 && tema.length > 2) {
                                tema = tema.substr();
                            }
                            var sebo = gamedata[0]['tema_sebo'];
                            var wuxing = gamedata[0]['tema_wuxing'];
                            var shengxiao = gamedata[0]['tema_shengxiao'];
                            var shengxiao_all = gamedata[0]['shengxiao_all'];
                            switch (value['type']) {
                                //特码/2/50  测试完成
                                case 1:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[1] == tema) {
                                        peilv = tool.split_pv(peilv[qz + 'lhc_tema_bv'], '=');
                                        var points1 = start1[2] * peilv[start1[1]];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //色波 色波/红/500  测试完成
                                case 2:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[1] == sebo) {
                                        peilv = tool.split_pv(peilv[qz + 'lhc_sebo_bv'], ':');
                                        var points1 = start1[2] * peilv[start1[1]];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //五行/金/500
                                case 3:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[1] == wuxing) {
                                        var points1 = start1[2] * peilv[qz + 'lhc_wuxing_bv'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //头/0/50  测试
                                case 4:
                                    var start1 = value['jincai'].split('/');
                                    if (start1[0] == '头') {
                                        if (start1[1] == 0) {
                                            if (tema < 10) {
                                                var points1 = start1[2] * peilv[qz + 'lhc_tou0_bv'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            } else {
                                                del_points(value['id']);
                                            }
                                        } else {
                                            var chaitema = tema.split('');
                                            if (start1[1] == chaitema[0]) {
                                                var points1 = start1[2] * peilv[qz + 'lhc_tou1_bv'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            } else {
                                                del_points(value['id']);
                                            }
                                        }

                                    } else if (start1[0] == '尾') {
                                        var w = '';
                                        if (tema < 10) {
                                            w = tema;
                                        } else {
                                            w = tema.toString().substr(1);
                                        }
                                        if (start1[1] == 0) {
                                            if (start1[1] == w) {
                                                var points1 = start1[2] * peilv[qz + 'lhc_wei0_bv'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            } else {
                                                del_points(value['id']);
                                            }
                                        } else {
                                            if (start1[1] == w) {
                                                var points1 = start1[2] * peilv[qz + 'lhc_wei1_bv'];
                                                add_points(value['id'], value['userid'], points1, addjine);
                                            } else {
                                                del_points(value['id']);
                                            }
                                        }
                                    }
                                    break;
                                //生肖/狗/500  测试完成
                                case 5:
                                    var start1 = value['jincai'].split('/');
                                    if (shengxiao == start1[1]) {
                                        var peilvsx = tool.split_pv(peilv[qz + 'lhc_shengxiao_bv'], ':');
                                        var points1 = start1[2] * peilvsx[start1[1]];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //平特肖/狗/5000
                                case 6:
                                    var start1 = value['jincai'].split('/');
                                    var all_shengxiao_array = shengxiao_all.split(',');
                                    var xiazhu = start1[1];
                                    var jishu = 0;
                                    all_shengxiao_array.forEach(function (value, key) {
                                        if (value == xiazhu) {
                                            jishu = 1;
                                        }
                                    })
                                    if (jishu == 1) {
                                        var peitx = tool.split_pv(peilv[qz + 'lhc_pingtexiao_bv'], ':');
                                        points1 = start1[2] * peitx[start1[1]];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                        del_points(value['id']);
                                    }
                                    break;
                                //两面/特大/500    两面/合大/500 测试
                                case 7:
                                    var start1 = value['jincai'].split('/');
                                    //特码：
                                    var te_dx = '';
                                    if (tema >= 25 && tema <= 49) {
                                        te_dx = '特大';
                                    } else {
                                        te_dx = '特小';
                                    }
                                    var te_ds = '';
                                    if (tema % 2 == 0) {
                                        te_ds = '特双';
                                    } else {
                                        te_ds = '特单';
                                    }
                                    //合码：
                                    var dataed = '';
                                    if (tema < 10) {
                                        dataed = tema;
                                    } else {
                                        dataed = '0' + tema;
                                    }
                                    var hemas = dataed.split('');
                                    var hema = hemas[0] + hemas[1];
                                    var he_dx = ''
                                    if (hema >= 7) {
                                        he_dx = '合大';
                                    } else {
                                        he_dx = '合小';
                                    }
                                    var he_ds = '';
                                    if (hema % 2 == 0) {
                                        he_ds = '合双';
                                    } else {
                                        he_ds = '合单';
                                    }
                                    //两面的赔率
                                    var lmplv = tool.split_pv(peilv[qz + 'lhc_liangmian_bv'], ':');
                                    if (start1[1] == '特大' || start1[1] == '特小') {
                                        //特码大小单双
                                        if (start1[1] == te_dx) {
                                            points1 = start1[2] * lmplv[start1[1]];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }

                                    } else if (start1[1] == '特单' || start1[1] == '特双') {
                                        //特码大小单双
                                        if (start1[1] == te_ds) {
                                            points1 = start1[2] * lmplv[start1[1]];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }

                                    } else if (start1[1] == '合大' || start1[1] == '合小') {
                                        //特码的合码大小单双
                                        if (start1[1] == he_dx) {
                                            points1 = start1[2] * lmplv[start1[1]];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    } else if (start1[1] == '合单' || start1[1] == '合双') {
                                        //特码的合码大小单双
                                        if (start1[1] == he_ds) {
                                            points1 = start1[2] * lmplv[start1[1]];
                                            add_points(value['id'], value['userid'], points1, addjine);
                                        } else {
                                            del_points(value['id']);
                                        }
                                    }
                                    break;
                                case 8:
                                     var start1 = value['jincai'].split('/');
                                    if (start1[0] == '三中二' && tool.checkslhc(start1[1], gamedata[0]['awardnumbers']) >= 2) {
                                      var points1 = start1[2] * peilv[qz +'lhc_sze_bv'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                       del_points(value['id']);
                                    }
                                    break;
                                // 三全中/234/10
                                case 12:
                                     var start1 = value['jincai'].split('/');
                                    if (start1[0] == '三全中' && tool.checkslhc(start1[1], gamedata[0]['awardnumbers']) == 3) {
                                       var points1 = start1[2] * peilv[qz +'lhc_sqz_bv'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                       del_points(value['id']);
                                    }
                                    break;
                                //二中特/2.3/232
                                case 9:
                                     var start1 = value['jincai'].split('/');
                                    if (start1[0] == '二中特' && tool.checkslhc(start1[1], tema) == 1) {
                                       var points1 = start1[2] * peilv[qz +'lhc_ezt_bv'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                       del_points(value['id']);
                                    }
                                    break;
                                //二全中
                                case 10:
                                     var start1 = value['jincai'].split('/');
                                    if (start1[0] == '二全中' && tool.checkslhc(start1[1], gamedata[0]['awardnumbers']) == 2) {
                                       var points1 = start1[2] * peilv[qz +'lhc_eqz_bv'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                       del_points(value['id']);
                                    }
                                    break;
                                //四全中/50/50
                                case 11:
                                     var start1 = value['jincai'].split('/');
                                    if (start1[0] == '四全中' && tool.checkslhc(start1[1], gamedata[0]['awardnumbers']) >= 4) {
                                       var points1 = start1[2] * peilv[qz +'lhc_siqz_bv'];
                                        add_points(value['id'], value['userid'], points1, addjine);
                                    } else {
                                       del_points(value['id']);
                                    }
                                    break;
                            }

                        }
                    })
                }
            }
        })
    });
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
function add_points(id, userid, points, addjie) {
    //判断封顶
    if (Number(addjie) + Number(points) >= C_set['add_fengding']) {
        points = C_set['add_fengding'] - addjie;
    }

    db.select({
        table: "think_order",
        where: "id=" + id + " and is_add=0 and state=1",
        success: function (orderdata) {
            if (orderdata.length != '') {

                db.update({
                    table: 'think_user',
                    where: 'id=' + userid,
                    field: 'points = points+' + points,
                    success: function (data) {
                        if (data) {
                            console.log('结算更新完数据');
                            change_order_id_add(id, points);
                            //开始分销
                            commission(userid, id, points, orderdata[0]['del_points'], orderdata[0]['game'], orderdata[0]['number']);
                        }
                    }
                })

            }
        }
    });
}
function del_points(id) {
    db.select({
        table: 'think_order',
        where: 'id=\'' + id + '\'',
        success: function (orderdata) {
            change_order_id_add(id, 0);
            commission(orderdata[0]['userid'], id, 0, orderdata[0]['del_points'], orderdata[0]['game'], orderdata[0]['number']);
        }
    })


}
//分销的方法
function commission(userid, orderid, points, del_points, game, qihao) {
    var peilv = JSON.parse(played[game]);

    db.select({
        table: 'think_order',
        field: 'sum(del_points) as del',
        where: 'userid=' + userid + ' and state=1',
        success: function (data) {
            log('查询总输赢成功！！');
            if (Number(data[0]['del']) >=Number(C_set['liushui_zu'])) {
                db.update({
                    table: 'think_user',
                    where: 'id=' + userid,
                    field: 'ls_zu=1',
                    sucess: function (data) {
                        log('达到条件，分组成功');
                    }
                })
            }
        }
    });
    if (peilv[game + '_is_fenxiao'] == 0 || C_set['fenxiao'] == 0) {
        return false;
    }
    db.select({
        table: 'think_user',
        where: "id='" + userid + "'",
        success: function (user) {
            if (user[0]['t_id'] != 0 || user[0]['iskefu'] !=1) {
                db.select({
                    table: 'think_user',
                    where: 'id=\'' + user[0]['t_id'] + '\'',
                    success: function (t1) {
                      if(t1[0]['iskefu'] !=1){
                        db.insert({
                            table: 'think_commisssion',
                            key: ['points', 'time', 'id_add', 'uid', 'headimgurl', 'nickname', 'money', 'qihao', 'status', 'd_id', 'td_id'],
                            value: [del_points * C_set['fenxiao']*0.01, Date.parse(new Date()) / 1000, userid, t1[0]['id'], t1[0]['headimgurl'], t1[0]['nickname'], t1[0]['points'], qihao, 0, t1[0]['d_id'], t1[0]['td_id']],
                            success: function (add_com) {
                                if (add_com) {
                                    if (C_set['fenxiaosan'] != 0) {
                                        if (t1[0]['t_id'] == 0) {
                                            return false;
                                        }
                                        //查找第二级信息
                                        db.select({
                                            table: 'think_user',
                                            where: 'id=\'' + t1[0]['t_id'] + '\'',
                                            success: function (t2) {
                                                db.insert({
                                                    table: 'think_commisssion',
                                                    key: ['points', 'time', 'id_add', 'uid', 'headimgurl', 'nickname', 'money', 'qihao', 'status', 'd_id', 'td_id'],
                                                    value: [del_points * C_set['fenxiaosan']*0.01, Date.parse(new Date()) / 1000, userid, t2[0]['id'], t2[0]['headimgurl'], t2[0]['nickname'], t2[0]['points'], qihao, 0, t2[0]['d_id'], t2[0]['td_id']],
                                                    success: function (add_com2) {
                                                        if (add_com2) {
                                                            log('添加二级佣金');
                                                        }
                                                    }
                                                });
                                            }
                                        })
                                    }
                                } else {
                                    log('添加一级佣金失败');
                                }
                            }
                        })

                        }
                    }
                })
            }
            if (user[0]['d_id'] == null) {
                return false;
            }
            if (!empty(user[0]['d_id']) && user[0]['iskefu'] != 0) {
                db.updateAll({
                    table: 'think_agent',
                    where: [['id=' + user[0]['d_id']], ['id=' + user[0]['td_id']]],
                    field: [['spoints =spoints +' + points + ', xpoints=xpoints+' + del_points], ['spoints =spoints +' + points + ', xpoints=xpoints+' + del_points]],
                    sucess: function (data) {
                        log('插入成功代理统计');
                    }
                })
            }

        }
    });
}

function empty(exp) {
    if (!exp && typeof(exp) != "undefined" && exp != 0 && exp != '') {
        return true;
    } else {
        return false;
    }
}

//修改订单状态
function change_order_id_add(id, points) {
    db.update({
        table: 'think_order',
        field: 'is_add=1,add_points=\'' + points + '\'',
        where: 'id=' + id,
        success: function (data) {
            if (data) {
                console.log('修改订单状态成功');
            } else {
                console.log('修改订单状态失败');
            }
        }
    })
}

function fanshui() {
    //数据库中没有数据， 根据条件插入数据  //今天00点的时间
    var time = new Date(new Date().setHours(5, 0, 0, 0)) / 1000;
    //前一天的时间
    var olddate = new Date(new Date().setHours(5, 0, 0, 0)) / 1000 - 86400;
    db.select({
        table: 'think_order',
        field: 'sum(add_points) as add_points,userid,sum(del_points) as del_points,count(userid) as count,sum(del_points)-sum(add_points) as del_data,game',
        where: "time>=" + olddate + " and time<=" + time + " and state=1 and is_add=1 and is_order=0 and is_kefu =0 GROUP BY userid,game",
        limit: '10',
        success: function (res) {
            if (res.length == 0) {
                return false;
            }

            var type_game = {
                "pk10": "pk",
                "fei": 'fei',
                "ssc": "ssc",
                "jnd28": "jnd",
                "bj28": "bj",
                "kuai3": "kuai3",
                'lhc': "lhc",
                "jsssc": "jsssc",
                "jscar": "jscar"
            };
            res.forEach(function (gropdata,key) {
                var data = [];
                data['userid'] = gropdata['userid'];
                data['time'] = Date.parse(new Date())/1000;
                data['order_time'] = new Date(new Date().setHours(5, 0, 0, 0)) / 1000;
                //把user信息传递给order day 表中
                db.select({
                    table: 'think_user',
                    field: 'headimgurl,nickname',
                    where: "id =" + gropdata['userid'],
                    success: function (user_data) {
                        var config = JSON.parse(played[gropdata['game']]);
                        data['headimgurl'] = user_data[0]['headimgurl'];
                        data['nickname'] = user_data[0]['nickname'];
                        data['shuying'] = Number(gropdata['del_points']);
                        data['game'] = gropdata['game'];

                        if (config[type_game[data['game']] + '_is_fanshui'] == 0) {
                            return false;
                        }

                        data['fanshui'] = 0;
                        if (gropdata['count'] >= Number(config[type_game[gropdata['game']] + '_fs_jushu']) && Number(gropdata['del_points']) >= Number(config[type_game[gropdata['game']] + '_fs_jine_1'])) {

                            if (Number(gropdata['del_points']) >= Number(config[type_game[gropdata['game']] + '_fs_jine_1'] && Number(gropdata['del_points']) <= config[type_game[data['game']] + '_fs_jine_2'])) {
                                data['fanshui'] = Number(gropdata['del_points']) * config[type_game[data['game']] + '_fs_bl_1'] / 100;
                            }
                            if (Number(gropdata['del_points']) >= Number(config[type_game[gropdata['game']] + '_fs_jine_2'] && Number(gropdata['del_points']) <= config[type_game[data['game']] + '_fs_jine_3'])) {
                                data['fanshui'] = Number(gropdata['del_points']) * config[type_game[data['game']] + '_fs_bl_2'] / 100;
                            }
                            if (Number(gropdata['del_points']) >= Number(config[type_game[gropdata['game']] + '_fs_jine_3'])) {
                                data['fanshui'] = Number(gropdata['del_points']) * Number(config[type_game[gropdata['game']] + '_fs_bl_3'] / 100);
                            }
                        }
                        if (data['fanshui'] != 0) {
                            db.update({
                                table: 'think_user',
                                where: 'id=' + gropdata['userid'],
                                field: 'points = points+' + data['fanshui'],
                                success: function (updatajiafanshui) {
                                    if (updatajiafanshui) {
                                        db.update({
                                            table: 'think_order',
                                            where: ' time>=' +olddate+ ' and time<='+time+ ' and'+' userid=' + gropdata['userid'] + ' and game='+'\''+ gropdata['game']+'\'',
                                            field: 'is_order =1',
                                            success: function (editorder) {
                                                if (editorder) {
                                                    console.log('反水成功');
                                                    db.insert({
                                                        table:'think_order_day',
                                                        key: ['userid', 'shuying', 'fanshui', 'time', 'headimgurl', 'nickname', 'order_time', 'game', 'd_id', 'td_id'],
                                                        value: [Number(gropdata['userid']),Number(gropdata['del_points']),data['fanshui'],Date.parse(new Date()) / 1000,data['headimgurl'],data['nickname'],data['order_time'],gropdata['game'],user_data[0]['d_id'],user_data[0]['d_id']],
                                                        success:function (data) {
                                                            if(data){
                                                                console.log('反水添加成功');
                                                            }
                                                        }
                                                    })
                                                }
                                            }
                                        })
                                    }
                                }
                            })
                        }

                    },
                    error: function (err) {
                        log('反水出错,休眠'+config.errorSleepTime+'秒后重新运行1');
                        log(err);
                        clearTimeout(timers2['fs'].timer);
                        timers2['fs'].timer=setTimeout(fanshui, config.errorSleepTime * 1000);//错误几秒后执行自己
                    }
                });
            })
        },
        error: function (err) {
            log('反水出错,休眠'+config.errorSleepTime+'秒后重新运行2');
            log(err);
            clearTimeout(timers2['fs'].timer);
            timers2['fs'].timer=setTimeout(fanshui, config.errorSleepTime * 1000);//错误几秒后执行自己
        }
    });
    clearTimeout(timers2['fs'].timer);
    log('休眠五秒后计算下一个反水');
    timers2['fs'].timer=setTimeout(fanshui,10 * 1000);
}
function yonjinjs() {
    //数据库中没有数据， 根据条件插入数据  //今天00点的时间
    var time = new Date(new Date().setHours(5, 0, 0, 0)) / 1000;
    var olddate = new Date(new Date().setHours(5, 0, 0, 0)) / 1000 - 86400;
    db.select({
        table: 'think_commisssion',
        field: 'sum(points) as points,uid',
        where: "time>=" + olddate + " and time<=" + time + " and status=0 GROUP BY uid",
        limit: '20',
        success: function (res) {
            if (res.length == 0) {
                return false;
            }
            res.forEach(function (value,key) {
                if (value['points'] <= 0) {
                    db.update({
                        table:'think_commisssion',
                        field:'status =1',
                        where:'time>="'+ olddate + '" and time<="' + time +'" and uid="'+value['uid']+'"',
                        success:function (xyl) {
                            if(xyl){
                               return false
                            }
                        }
                    })
                    return false;
                }else {


                    var sql = "update think_user set t_add = t_add + "+value['points']+",commission = commission+"+value['points']+" where id = "+value['uid']
                    db.query({
                        sql:sql,
                        error:function(err){
                            console.log('佣金错误')
                            console.log(err)
                        },
                        success:function(res){
                            console.log(res)
                            if(res){
                                db.update({
                                    table:'think_commisssion',
                                    field:'status=1',
                                    where:'time>="'+ olddate + '" and time<="' + time +'" and uid="'+value['uid']+'"',
                                    success:function (xyl) {
                                        if(xyl){
                                            return false
                                        }
                                    }
                                })
                            }
                        }
                    })
                    /*db.updateAll({
                        table:'think_user',
                        field:[["commission=commission+"+value['points']],["t_add=t_add+"+value['points']]],
                        where:[[""+value['uid']],['id='+value['uid']]],
                        success:function (res) {
                            if(res){
                                db.update({
                                    table:'think_commisssion',
                                    field:'status=1',
                                    where:'time>="'+ olddate + '" and time<="' + time +'" and uid="'+value['uid']+'"',
                                    success:function (xyl) {
                                        if(xyl){
                                            return false
                                        }
                                    }
                                })
                            }
                        },
                        error:function(res){
                            console.log('佣金错误')
                            console.log(res)
                        }
                    })*/


                }
            })
            clearTimeout(timers2['yj'].timer);
            log('休眠五秒后计算下一个佣金');
            timers2['yj'].timer=setTimeout(yonjinjs, 5 * 1000);//错误几秒后执行自己
        },
        error:function(err){
            log('佣金出错,休眠'+config.errorSleepTime+'秒后重新运行');
            log(err);
            clearTimeout(timers2['yj'].timer);
            timers2['yj'].timer=setTimeout(fanshui, config.errorSleepTime * 1000);//错误几秒后执行自己
        }}
    );
}
function checkqzh(data) {
    var vars = data.split(',');
    var res = '';
    if (vars[0] == vars[1] && vars[0] == vars[2] && vars[1] == vars[2]) {
        res = '豹子';
    } else {
        var dz = '';
        if (vars[0] == vars[1]) {
            dz++;
        }
        if (vars[0] == vars[2]) {
            dz++;
        }
        if (vars[1] == vars[2]) {
            dz++;
        }
        if (dz == 1) {
            res = '对子';
        } else {
            var bb = 0;
            if (Math.abs(vars[0] - vars[1]) == 1) {
                bb++;
            }
            if (Math.abs(vars[0] - vars[2]) == 1) {
                bb++;
            }
            if (Math.abs(vars[1] - vars[2]) == 1) {
                bb++;
            }
            if (bb == 0) {
                res = '杂六';
            }
            if (bb == 1) {
                res = '半顺';
            }
            if (bb == 2) {
                res = '顺子';
            }
        }
    }
    return res;
}
function checkss(data, kjdata) {
    var kjdataarr = kjdata.split(',');
    var jichu = 0;
    data = data.split('');
    var o = 0;
    var p = 0;
    var l = 0;
    var y = 0;
    var u = 0;
    var h = 0;
    for (var i = 0; i < data.length; i++) {
        for (var b = 0; b < kjdataarr.length; b++) {
            if (data[i] == kjdataarr[b]) {
                //是否标记成功的
                if (i == 0 && o == 0) {
                    if (b == 0 && y == 0) {
                        jichu++;
                        o = 1;
                        y = 1;
                    }
                    if (b == 1 && u == 0) {
                        jichu++;
                        o = 1;
                        u = 1;
                    }
                    if (b == 2 && h == 0) {
                        jichu++;
                        o = 1;
                        h = 1;
                    }

                }
                if (i == 1 && p == 0) {
                    if (b == 0 && y == 0) {
                        jichu++;
                        p = 1;
                        y = 1;
                    }
                    if (b == 1 && u == 0) {
                        jichu++;
                        p = 1;
                        u = 1;
                    }
                    if (b == 2 && h == 0) {
                        jichu++;
                        p = 1;
                        h = 1;
                    }
                }
                if (i == 2 && l == 0) {
                    if (b == 0 && y == 0) {
                        jichu++;
                        l = 1;
                        y = 1;
                    }
                    if (b == 1 && u == 0) {
                        jichu++;
                        l = 1;
                        u = 1;
                    }
                    if (b == 2 && h == 0) {
                        jichu++;
                        l = 1;
                        h = 1;
                    }

                }
            }
        }
    }
    return jichu;
}