if (!window["Lottery"]) window["Lottery"] = new Object();
if (!window["Game"]) window["Game"] = new Object();
var img_path = "/play/";
function setCookie(name,value){
	var Days = 30;
	var exp = new Date();
	exp.setTime(exp.getTime() + Days*24*60*60*1000);
	document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
function getCookie(name){
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
	if(arr=document.cookie.match(reg))
		return unescape(arr[2]);
	else
		return null;
}
function delCookie(name){
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval=getCookie(name);
	if(cval!=null)
	document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}
(function (ns) {
    ns["Star7"] = ns["Fantang"] = ns["ChungKing"] = ns["Pcegg"] = ns["Gd10"] = new Class({
        "Implements": [Events, Options],
        "options": {
            // 封单时间
            "stop": 60
        },
        "dom": {
            "element": null,
            // 倒计时
            "countdown": null,
            // 倒计时元素
            "countdownObj": new Object(),
            // 上一期开奖结果
            "lastnumber": new Object(),
            // 开奖期号
            "betindex": null,
            // 本期开奖结果
            "number": new Object(),
            // 数字的类型
            "numbertype": new Object(),
            // 下一期开奖期号
            "nextindex": new Object()
        },
        // 播放声音
        "sound": function (name, loop) {
        		if(getCookie(type+"_sound") == 0){return;}
            var t = this;
            if (!name) {
                UI.Sound();
                t.data.sound = null;
                return;
            }
            if (t.data.sound == name) return;
            var url = img_path + "images/"+media_path+"/" + name + ".mp3";
            UI.Sound(url, {
                "loop": loop
            });
            t.data.sound = name;
        },
        // 缓存数据
        "data": {
            "status": null,
            // 状态枚举
            "STATUS": null,
            // 倒计时
            "countdown": 0,
            // 整个屏幕的宽度
            "width": null,
            // 开奖结果
            "result": null,
            // 倒计时时间
            "countdown": 0,
            // 当前的可投注期
            "betindex": null,
            // 当前的开奖期
            "index": null,
            // 当前播放的声音
            "sound": null
        },
        "initialize": function (el, options) {
            var t = this;
            t.dom.element = el = $(el);
            t.dom.countdown = t.dom.element.getElement(".countdown");
            t.dom.countdown.getElements("em").each(function (item, index) {
                item.set("data-index", index);
                t.dom.countdownObj[index] = item;
            });
            t.dom.element.getElements(".top .number em").each(function (item, index) {
                t.dom.lastnumber[index] = item;
            });
            t.dom.element.getElement(".sound").set("html","<img src='"+img_path + "images/"+media_path+"/v14.png'><img class='m2' src='"+img_path + "images/"+media_path+"/v15.png'>").addEvent("click",function(){
            		this.toggleClass("off");
            		if(this.hasClass('off')){
            			UI.Sound();
            			setCookie(type+'_sound',0);
            		}else{
            			setCookie(type+'_sound',1);
            		}
            });
            if(getCookie(type+"_sound") == 0) t.dom.element.getElement(".sound").addClass('off');
            t.dom.betindex = t.dom.element.getElement("[data-dom=betindex]");
            t.dom.lottery = t.dom.element.getElement("[data-dom=lottery]");
            t.dom.element.getElements(".content em").each(function (item, index) {
                t.dom.number[index] = item;
            });
            t.dom.element.getElements(".content label").each(function (item, index) {
                t.dom.numbertype[index] = item;
            });
            t.dom.countdown.getElements("i").each(function (item, index) {
                t.dom.nextindex[index] = item;
            });
            // 设置屏幕缩放比例
            !function () {
                t.data.width = el.getSize().x;
                t.data.height = el.getSize().y;
                var parent = el.getParent();
                if (parent.get("data-scale") != "false") {
//                  var scale = window.screen.width / t.data.width;
					var scale = document.body.offsetWidth / t.data.width;
                    var zoom = "scale(" + scale + ")";
                    el.setStyles({
                        "-webkit-transform": zoom
                    });
                    parent.setStyle("height", t.data.height * scale);
                } else {
                    el.getElements(".money, .online, .bet").each(function (item) {
                        item.setStyle("display", "none");
                    });
                }
            }();

            t.data.STATUS = {
                "loading": "status-loading",
                "show": "status-show"
            };
            t.timer.apply(t);
        },
        // 改变状态
        "setStatus": function (status, options) {
            var t = this;
            t.data.status = status;
            if (options) {
                Object.forEach(options, function (value, key) {
                    t.data[key] = value;
                });
            }
        },
        "setNumber": function () {
            var t = this;
            if (!t.data.result) return;
        },
        // 执行定时器任务
        "timer": function () {
            var t = this;
            Object.forEach(t.data.STATUS, function (value, key) {
                if (key != t.data.status) {
                    t.dom.element.removeClass(value);
                } else {
                    t.dom.element.addClass(value);
                }
            });
            if (t.apply[t.data.status]) t.apply[t.data.status].apply(t);
            t.timer.delay(1000, t);
        },
        "apply": {
            // 等待效果
            "loading": function () {
                var t = this;
                t.dom.element.set("data-show", null);
                t.sound("loading", true);
            },
            // 显示结果
            "show": function () {
                var t = this;
                // 设置倒计时
                var countdown = Math.round(t.data.countdown).toString();
                t.dom.countdown.set("data-length", countdown.length);
                for (var index = 0; index < countdown.length; index++){
                    if (t.dom.countdownObj[index].hasClass("hide")) t.dom.countdownObj[index].removeClass("hide");
                    t.dom.countdownObj[index].set("class", "t" + countdown[index]);
                }
                if (t.dom.element.get("data-show")) return;
                t.data.result.each(function (item, index) {
	                    t.dom.number[index].set("class", "n" + item.toInt());
                    	 	try{t.dom.lastnumber[index].set("class", "n" + item.toInt());}catch(e){}
                    		t.dom.numbertype[index * 2].set("class", item.toInt() >= 5 ? "r0" : "r1");
	                    t.dom.numbertype[index * 2 + 1].set("class", item.toInt() % 2 ? "r2" : "r3");
                });
                var qihao = t.data.index;
                switch(type){
                		case 'cqssc':
                		case 'chungking':
                			qihao = t.data.index.substring(6);
                			break;
                    case 'txffc':
                    		qihao = t.data.index.substring(4);
                    		break;
                }
                t.dom.element.getElements(".r_data .i0").set("html", '<i>'+t.data.otime+'</i><i>'+ qihao +'</i>');
                if(t.data.tema) t.dom.element.getElements(".r_data .i1").set("html",'<i>'+t.data.tema.join("</i><i>")+'</i>');
                t.data.lhs.each(function (item, index){
                		t.dom.element.getElements(".r_data .i"+(index+2)).set("text",item);
                });
                t.dom.betindex.set("text", t.data.index);
				t.dom.lottery.set("text",t.data.lottery);
                var betindex = t.data.betindex;
                for (var index = 0; index < betindex.length; index++) {
                    t.dom.nextindex[index].set("class", "i" + betindex[index]);
                }
                t.sound("show");
                t.dom.element.set("data-show", true);
            }
        }
    });
})(Game);

// 开奖器
(function (ns) {
    ns.Time = new Class({
        Implements: [Events, Options],
        "options": {
            // 当前彩种
            "type": null,
            "callback": function () { }
        },
        // 当前运行的缓存数据
        "data": {
            "result": null,
            "time": {
                // 可投注时间
                "bet": null,
                // 开奖倒计时
                "open": null
            }
        },
        // 当前提交的对象
        "request": null,
        "initialize": function (options) {
            var t = this;
            t.setOptions(options);
            t.request = new Request.JSON({
                "url": "/ajax/data",
                "onRequest": function () {
                    if (t.data.result) {
                        t.data.result["OpenNumber"] = "";
                    }
                },
                "onSuccess": function (result) {
                    if (!result.success) {
                        return;
                    }
                    t.data.result = result.info;
                    // 服务器上面的时间
                    var serverTime = new Date().AddSecond(result.info["ServerTime"]);
                    t.data.time.bet = new Date().AddSecond(result.info["BetTime"]);
                    t.data.time.open = new Date().AddSecond(result.info["OpenTime"]);
                    t.show();
                }
            });
            t.gettime();
        },
        // 获取服务端时间的回调事件
        "gettime": function () {
            var t = this;
            t.request.post({
                "Game": t.options.type
            });
        },
        // 在本地计算时间
        "localtime": function () {
            var t = this;
            t.data.result["BetTime"] = t.data.time.bet.getDateDiff(new Date()).TotalSecond;
            t.data.result["OpenTime"] = t.data.time.open.getDateDiff(new Date()).TotalSecond;
            t.show();
        },
        // 显示信息
        "show": function () {
            var t = this;
            t.options.callback.apply(t);
            if (t.data.result["BetTime"] <= 0 || t.data.result["OpenTime"] <= 0 || !t.data.result["OpenNumber"]) {
                t.gettime.delay(1000, t);
            } else {
                t.localtime.delay(1000, t);
            }
        },
        // 注销此定时器
        "dispose": function () {
            var t = this;
            t.request.cancel();
            t.running = false;
        }
    });

})(Lottery);

BW.callback["lottery-chungking"] = function () {
    var t = this;
    var game = new Game["ChungKing"](t.dom.element.getElement(".lottery"));
    var complete = false;
    var loading = false;

    new Lottery.Time({
        "type": type,
        "callback": function () {
            var t = this;
            var result = t.data.result;
            if (!result.OpenNumber) {
                game.setStatus("loading", {
                    "index": result.OpenIndex
                });
                loading = true;
            } else {
                game.setStatus("show", {
                		"lottery" : t.data.result['Name'],
                    "betindex": t.data.result["BetIndex"],
                    "otime"	  : t.data.result['OpenDateTime'],
                    "index":    t.data.result["OpenIndex"],
                    "result":   t.data.result["OpenNumber"].split(","),
                    "tema":     t.data.result["OpenTm"].split(","),
                    "lhs":      t.data.result["OpenLh"].split(","),
                    "countdown": t.data.result["OpenTime"]
                });
            }
        }
    });
};

BW.callback["lottery-star7"] = function () {
    var t = this;
    var game = new Game["Star7"](t.dom.element.getElement(".lottery"));
    var complete = false;
    var loading = false;

    new Lottery.Time({
        "type": "Star7",
        "callback": function () {
            var t = this;
            var result = t.data.result;
            if (!result.OpenNumber) {
                game.setStatus("loading", {
                    "index": result.OpenIndex
                });
                loading = true;
            } else {
                game.setStatus("show", {
                    "lottery" : t.data.result['Name'],
                    "betindex": t.data.result["BetIndex"],
                    "otime"	  : t.data.result['OpenDateTime'],
                    "index":    t.data.result["OpenIndex"],
                    "result":   t.data.result["OpenNumber"].split(","),
                    "tema":     t.data.result["OpenTm"].split(","),
                    "lhs":      t.data.result["OpenLh"].split(","),
                    "countdown": t.data.result["OpenTime"]
                });
            }
        }
    });
};

BW.callback["lottery-pcegg"] = function () {
    var t = this;
    var game = new Game["Pcegg"](t.dom.element.getElement(".lottery"));
    var complete = false;
    var loading = false;

    new Lottery.Time({
        "type": type,
        "callback": function () {
            var t = this;
            var result = t.data.result;
            if (!result.OpenNumber) {
                game.setStatus("loading", {
                    "index": result.OpenIndex
                });
                loading = true;
            } else {
                game.setStatus("show", {
                    "lottery" : t.data.result['Name'],
                    "betindex": t.data.result["BetIndex"],
                    "otime"	  : t.data.result['OpenDateTime'],
                    "index":    t.data.result["OpenIndex"],
                    "result":   t.data.result["OpenNumber"].split(","),
                    "tema":     t.data.result["OpenTm"].split(","),
                    "lhs":      t.data.result["OpenLh"].split(","),
                    "countdown": t.data.result["OpenTime"]
                });
            }
        }
    });
};

BW.callback["lottery-fantang"] = function () {
    var t = this;
    var game = new Game["Fantang"](t.dom.element.getElement(".fantang"));
    var complete = false;
    var loading = false;

    new Lottery.Time({
        "type": type,
        "callback": function () {
            var t = this;
            var result = t.data.result;
            if (!result.OpenNumber) {
                game.setStatus("loading", {
                    "index": result.OpenIndex
                });
                loading = true;
            } else {
                game.setStatus("show", {
                    "lottery" : t.data.result['Name'],
                    "betindex": t.data.result["BetIndex"],
                    "otime"	  : t.data.result['OpenDateTime'],
                    "index":    t.data.result["OpenIndex"],
                    "result":   t.data.result["OpenNumber"].split(","),
                    "tema":     t.data.result["OpenTm"].split(","),
                    "lhs":      t.data.result["OpenLh"].split(","),
                    "countdown": t.data.result["OpenTime"]
                });
            }
        }
    });
};

BW.callback["lottery-gd10"] = function () {
    var t = this;
    var game = new Game["Gd10"](t.dom.element.getElement(".lottery"));
    var complete = false;
    var loading = false;

    new Lottery.Time({
        "type": "Gd10",
        "callback": function () {
            var t = this;
            var result = t.data.result;
            if (!result.OpenNumber) {
                game.setStatus("loading", {
                    "index": result.OpenIndex
                });
                loading = true;
            } else {
                game.setStatus("show", {
                    "lottery" : t.data.result['Name'],
                    "betindex": t.data.result["BetIndex"],
                    "otime"	  : t.data.result['OpenDateTime'],
                    "index":    t.data.result["OpenIndex"],
                    "result":   t.data.result["OpenNumber"].split(","),
                    "tema":     t.data.result["OpenTm"].split(","),
                    "lhs":      t.data.result["OpenLh"].split(","),
                    "countdown": t.data.result["OpenTime"]
                });
            }
        }
    });
};