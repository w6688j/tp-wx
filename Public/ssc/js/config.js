//工程名不可更改如果要变更请联系开发人员！
var config = {
	publicUrl: "http://www.pkbird.com", //线上正式环境
	imgUrl: "", //线上正式环境
	listTime: 1500,
	firstLoad:false,//判断是不是第一次加载页面
	ifdebug: false //当为true的时候是调试模式
}
var lotCode = {
	pk10: 10001, //北京赛车PK拾（pk10）
	aozxy10: 10012, //澳洲幸运10（pk10）
	jisusc: 10037, //极速赛车（pk10）

	cqssc: 10002, //重庆时时彩（时时彩）
	tjssc: 10003, //天津时时彩（时时彩）
	xjssc: 10004, //新疆时时彩（时时彩）
	aozxy5: 10010, //澳洲幸运5 （时时彩）
	jisussc: 10036, //极速时时彩 （时时彩）

	gdklsf: 10005, //广东快乐十分（快乐十分）
	aozxy8: 10011, //澳洲幸运8 （快乐十分）
	tjklsf: 10034, //天津快乐十分（快乐十分）
	cqxync: 10009, //重庆幸运农场（快乐十分）
	gxklsf: 10038, //广西快乐十分（快乐十分）

	gdsyxw: 10006, //广东11选5 （11选5）
	jxef: 10015, //江西11选5（11选5）
	jsef: 10016, //江苏11选5（11选5）
	ahef: 10017, //安徽11选5（11选5）
	shef: 10018, //上海11选5（11选5）
	lnef: 10019, //辽宁11选5（11选5）
	hbef: 10020, //湖北11选5（11选5）
	cqef: 10021, //重庆11选5（11选5）
	gxef: 10022, //广西11选5（11选5）
	jlef: 10023, //吉林11选5（11选5）
	nmgef: 10024, //内蒙古11选5（11选5）
	zjef: 10025, //浙江11选5（11选5）
	sdsyydj: 10008, //十一运夺金（11选5）

	jsksan: 10007, //江苏快3（快3）
	gxft: 10026, //广西快3（快3）
	jlft: 10027, //吉林快3（快3）
	hebft: 10028, //河北快3（快3）
	nmgft: 10029, //内蒙古快3（快3）
	ahft: 10030, //安徽快3（快3）
	fjft: 10031, //福建快3（快3）
	hubft: 10032, //湖北快3（快3）
	bjft: 10033, //北京快3（快3）

	aozxy20: 10013, //澳洲幸运20
	bjkl8: 10014, //北京快乐8
	twbg: 10047, //台湾滨果

	fcssq: 10039, // 福彩双色球
	cjdlt: 10040, // 超级大乐透
	fcsd: 10041, // 福彩3D
	fcqlc: 10042, // 福彩七乐彩
	pailie3: 10043, // 体彩排列3
	pailie5: 10044, // 体彩排列5
	qxc: 10045, // 体彩七星彩
	egxy28: 10046, // pc蛋蛋幸运28

}

//设为首页
function SetHome(url) {
	if(document.all) {
		document.body.style.behavior = 'url(#default#homepage)';
		document.body.setHomePage(url);
	} else {
		alert("您好,您的浏览器不支持自动设置页面为首页功能,请您手动在浏览器里设置该页面为首页!");
	}
}
//收藏本站
function addFavorite2() {
	var url = window.location;
	var title = document.title;
	var ua = navigator.userAgent.toLowerCase();
	if(ua.indexOf("360se") > -1) {
		alert("由于360浏览器功能限制，请按 Ctrl+D 手动收藏！");
	} else if(ua.indexOf("msie 8") > -1) {
		window.external.AddToFavoritesBar(url, title); //IE8
	} else if(document.all) {
		try {
			window.external.addFavorite(url, title);
		} catch(e) {
			alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
		}
	} else if(window.sidebar) {
		window.sidebar.addPanel(title, url, "");
	} else {
		alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
	}
}

$(function() {
	if($("#littleimg").length >= 1) {
		if($("#ifindex").val() != "index") {
			tools.bannerImg(); //添加轮播图
			$("#littleimg").find(".swiper-container").addClass("swiperother");
		}
	}
	publicmethod.fixBox();
	publicmethod.loadAdvert();
	//微信二维码出现
	$(".wxkefuicon").on("mouseover",function(){
		$(".wxewmicon").css("display","inline-block");
	})
	$(".wxkefuicon").on("mouseout",function(){
		$(".wxewmicon").css("display","none");
	})
	/*if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion.split(";")[1].replace(/[ ]/g, "") == "MSIE8.0") {
		alert("IE 8.0"+navigator.appVersion.split(";")[1].replace(/[ ]/g, ""));
	}*/
	$("#localyears").text(new Date().getFullYear());
	//用户反馈
	//用户反馈 初始化数据
	//当为主页时，不加载用户反馈html
	// if($("#ifindex").val() == "index") {} else {
	// 	$("#user_adv").load("../public/user_adv.html?v=2017731625", function() {});
	// }
	//声音设置
	$("#soundSet").on("click", ".soundbtn", function(event) {
		$(this).find(".soundpanel").show("200");
		event.stopPropagation(); //阻止事件冒泡
	});
	$(".bodybox").on("click", function(event) {
		$(this).parent().parent().find(".soundpanel").hide("200");
	})
	$("#soundSet").on("click", ".close", function(event) {
		$(this).parent().parent().find(".soundpanel").hide("200");
		event.stopPropagation();
	});
	$("#soundSet").on("click", "input", function() {
		if($(this).val() == "none") {
			$("#soundSet").find(".soundicon").addClass("stopsound");
		} else {
			$("#soundSet").find(".soundicon").removeClass("stopsound");
			$("#soundSet").find("audio").attr("src", "/statics/168/media/" + $(this).val() + ".wav")
		}
	});
	//只有当页面为操盘界面时执行固定开奖区域为fixed
	if($("#operator").val() == "operator") {
		$(window).on("scroll", function() {
			if($(this).scrollTop() > 195) {
				$(".haomabox").addClass("fixedHead");
			} else {
				$(".haomabox").removeClass("fixedHead");
			}
		})
	}
	setTimeout(function() {
		try {
			tools.addSund();
		} catch(e) {}
	}, 1000);
	//如为最新版本就添加版本号
	//tools.clearHC();
	try{
		loadotherData(); //加载其他数据
	}catch(e){}
	setTimeout(function(){
		config.firstLoad=true;
	},2000), tools.initListen();
});

//公共URL
var publicUrl = config.publicUrl;
//用户反馈信息
var yonghufankui = {};
//publicmethod
var publicmethod = {};
//公用工具方法
var tools = {};
yonghufankui.createList = function(data) {
	//请求成功
	$("#btn_submiting").hide();
	var data = JSON.parse(data);
	if(data.errorCode == "0") {
		var current_time = getDate(); //string
		localStorage.current_time = current_time; //存入时间
		$("#info1").hide();
		$("#info2").show();
	} else {

	}
}
//将日期转换成月/日
function currentDay(day) {
	//点击昨天：列表中显示具体日期
	var date1 = day.split("-");
	var month = parseInt(date1[1]);
	var day = parseInt(date1[2]);
	var current_date = month + "/" + day;
	return current_date;
}
//得到系统时间
function getDate() {
	var current_time = "";
	var date = new Date();
	var year = date.getFullYear();
	var month = date.getMonth() + 1;
	var day = date.getDate();
	var hour = date.getHours();
	var minute = date.getMinutes();
	var second = date.getSeconds();
	current_time = year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
	return current_time;
}
//当开奖号码为空就执行这一方法
function ifNumIsNull(preDrawCode, id) {
	if(preDrawCode == "") {
		if($(id).find(".errorbox").length != 0) {
			$(id).find(".errorbox").remove();
		}
		$(id).find(".rowbox2").append("<span class='errorbox' style='font-size:11px;color:orangered;'>开奖号码未开出，请尝试刷新页面或稍后再试！</span>");
		$(id).find(".kajianhao").hide();
		return true;
	} else {
		$(id).find(".errorbox").hide();
		$(id).find(".kajianhao").show();
		return false;
	}
}
publicmethod.loadAdvert = function() {
	$.ajax({
		//url: publicUrl + "focusPicture/findNewestFocusPicture.do?type=0",
		url:"http://www.pkbird.com/Home/api/getssc",
		type: "GET",
		dataType: 'json',
		data: {},
		timeout: 60000,
		beforeSend: function() {
			//$("#bannerContent").text("努力加载中...");
		},
		success: function(data) {
            tools.advertImg(data); //添加轮播图
		},
		error: function(xhr) {
			//$("#bannerContent").empty().text("正在加载...");
			//setTimeout(indexObj.loadAdvert(),1000);
		},
		complete: function(xmlobj, state) {
			xmlobj = null;
		}
	});
}
publicmethod.fixBox = function() {
	var obj = $(".fixedgoBack").find(".fix1200");
	var obj1 = $(".fixedgoBack").find(".leftright");
	$(obj).empty();
	$(obj1).empty();
	var fixedBox = '<div class = "wxewmicon"></div><ul>' +
		'<li>' +
		'<a class="backold" target="_blank" href="http://168kai.com/" target="_blank"></a>' +
		'</li>' +
		'<li>' +
		'<a class="wxkefuicon" target="_blank"></a>' +
		'</li>' +
		'<li>' +
		'<a class="kefuicon" target="_blank" href="http://crm2.qq.com/page/portalpage/wpa.php?lang=&uin=800057725&cref=http://www.168kai.cc&ref=&pt=168%E5%BC%80%E5%A5%96%E7%BD%91&f=1&ty=1&ap=&as="></a>' +
		'</li>' +
		'<li>' +
		'<!--用户反馈模态框-->' +
		'<span class="fankuicon fankuicon_a" data-toggle="modal" data-target="#myModal">' +
		'<span class="fankuicons"></span>' +
		'</span>' +
		'</li>' +
		'<li>' +
		'<a class="topicon" id="gotop" href="javascript:"></a>' +
		'</li>' +
		'</ul>';
	var fixedPointer = '<ul class="ul_pre">' +
		'<li class="prev_li">' +
		'</li>' +
		'<li class="next_li">' +
		'</li>' +
		'</ul>';
	$(obj).append(fixedBox);
	if(obj1.length != 0) {
		$(obj).append(fixedPointer);
	}
}
//时时彩系列头部开奖渲染数据
publicmethod.insertHeadSsC = function(jsondata, id) {
	var data = tools.parseObj(jsondata);
		var timeResult = tools.operatorTime2(data.next.awardTime == "" ? "0" :data.next.awardTime, data.time); //得到时间差
		if(timeResult <= 0) {
			throw new Error("error");
		}
    console.log("dfghjk2"+id);
		var totalCount = 120;
		$(id).find(".preDrawIssue").text(data.current.periodNumber);
		$(id).find(".nextIssue").text(data.next.periodNumber);
		if($("#drawTime").val() !== undefined) {
			$("#drawTime").val(data.next.awardTime.substr(data.next.awardTime.length - 8, 8));
            var zong=data.current.awardNumbers.split(",");
            var zong2=Number(zong[0])+Number(zong[1])+Number(zong[2])+Number(zong[3])+Number(zong[4]);
			$(id).find("#sumNum").val(zong2);
			var danshuang=zong2%2;
			if(zong2>=23){
				var daxiao="大";
			}else {
                var daxiao="小";
			}
			$(id).find("#sumSingleDouble").val(danshuang == 0 ? "单" : "双");
			$(id).find("#sumBigSmall").val(daxiao);
            var lhj= Number(zong[0])-Number(zong[4]);
            var lh='';
            if(lhj<0){lh ='虎';}if(lhj>0){lh ='龙';}if(lhj ==0){lh ='和';}
			$(id).find("#dragonTiger").val(lh);
		}
		/*
		sumSingleDouble 总合单双:0单、1双
		sumBigSmall 总合大小:0大、1小
		dragonTiger 龙虎：0龙、1虎、2和
		firstBigSmall 第一名大小:0大、1小
		behindThree:前三：0杂六、1半顺、2顺子、3对子、4豹子
		betweenThree:中三: 0杂六、1半顺、2顺子、3对子、4豹子
		lastThree:后三: 0杂六、1半顺、2顺子、3对子、4豹子
		**/
		//总共
		// $(id).find(".totalCount").text(totalCount);
		//已开
		// $(id).find(".drawCount").text(data.drawCount);
		//未开
		// $(id).find(".sdrawCount").text(totalCount * 1 - (data.drawCount) * 1);
		//打印下一期
		if(config.ifdebug) {
			console.log("nextIssue:" + localStorage.nextIssue);
		}
		$(".lenresinli").removeClass("checked"); //冷热分析中是否显示热号码出现的次数
		//添加倒计时data.next.awardTime == "" ? "0" :data.next.awardTime, data.time
		tools.countDown(data.next.awardTime, data.time, id);
		//添加结束动画
	var num=data.current.awardNumbers.split(',');
    animate.sscAnimateEnd(num, id);
		$("#waringbox").hide(300); //显示网络waring提示
		setTimeout(function() {
			if(tools.ifCheckedOnToday()) {
				// loadotherData(); //加载其他数据
			}
			
		}, config.listTime);

}
//时时乐系列头部开奖渲染数据
publicmethod.insertHeadSsl = function(jsondata, id) {
	var data = tools.parseObj(jsondata);
	if(data.result.businessCode == "100002") {
		throw new Error("error");
	} else {
		data = data.result.data;
		var timeResult = tools.operatorTime(data.drawTime == "" ? "0" : data.drawTime, data.serverTime); //得到时间差
		if(timeResult <= 0) {
			throw new Error("error");
		}
		var totalCount = data.totalCount;
		$(id).find(".preDrawIssue").text(data.preDrawIssue);
		$(id).find(".nextIssue").text(data.drawIssue);
		if($("#drawTime").val() !== undefined) {
			$("#drawTime").val(data.drawTime.substr(data.drawTime.length - 8, 8));
		}
		
		//总共
		$(id).find(".totalCount").text(totalCount);
		//已开
		$(id).find(".drawCount").text(data.drawCount);
		//未开
		$(id).find(".sdrawCount").text(totalCount * 1 - (data.drawCount) * 1);
		//打印下一期
		if(config.ifdebug) {
			console.log("nextIssue:" + localStorage.nextIssue);
		}

		//添加倒计时
		tools.countDown(data.drawTime, data.serverTime, id);
		//添加结束动画
		animateMethod.sslAnimateEnd(data.preDrawCode, id);
		$("#waringbox").hide(300); //显示网络waring提示
		setTimeout(function() {
			if(tools.ifCheckedOnToday()) {
				loadotherData(); //加载其他数据
			}
			
		}, config.listTime);
	}
}
tools.ifselectedOpacity = function(obj) {
	var selectedOpacity = $(obj).hasClass("selectedOpacity");
	if(selectedOpacity) {
		$(obj).removeClass();
		$(obj).addClass("selectedOpacity");
	} else {
		$(obj).removeClass();
	}
}
//选择大小单双
tools.bigOrSmall = function(id, num) {
	$("#jrsmhmtj .blueqiu li").each(function(index) {
		var number = $(this).text();
		//是否为单双
		var ifds = number % 2 == 0 ? true : false;
		//是否为大小
		var ifdx = number >= num ? true : false;
		$(this).find("i").hide();
		if(id == "xshm") {
			tools.ifselectedOpacity($(this));
			//样式名为numsm+01到10
			$(this).addClass("gxnumblue");
			if((index + 1) % 10 == 0) {
				$(this).addClass("li_after");
			}
			if(lotCode == "10038") {
				if(number == 1 || number == 4 || number == 7 || number == 10 || number == 13 || number == 16 || number == 19) {
					$(this).addClass("gxnumred");
				} else if(number == 3 || number == 6 || number == 9 || number == 12 || number == 15 || number == 18 || number == 21) {
					$(this).addClass("gxnumgreen");
				}
			} else if(lotCode == "10011" || lotCode == "10005" || lotCode == "10034") {
				if(number >= 19) {
					$(this).addClass("numredkong");
				}
			} else { //时时彩、11选5系列
				$(this).addClass("sscnumblue");
			}
			$(this).find("i").show();
		} else if(id == "xsdx") {
			tools.ifselectedOpacity($(this));
			if(ifdx) {
				//当为广西快乐十分：号码21为和
				if(number == 21) {
					$(this).addClass("bluetotle");
				} else {
					$(this).addClass("bluebig");
				}
				if((index + 1) % 10 == 0) {
					$(this).addClass("bluebig li_after");
				}
			} else {
				$(this).addClass("bluesmall");
				if((index + 1) % 10 == 0) {
					$(this).addClass("bluesmall li_after");
				}
			}
		} else if(id == "xsds") {
			tools.ifselectedOpacity($(this));
			if(ifds) {
				$(this).addClass("blueeven");
				if((index + 1) % 10 == 0) {
					$(this).addClass("blueeven li_after");
				}
			} else {
				//当为广西快乐十分：号码21为和
				if(number == 21) {
					$(this).addClass("bluetotle");
				}
				$(this).addClass("bluesingular");
				if((index + 1) % 10 == 0) {
					$(this).addClass("bluesingular li_after");
				}
			}
		}

	});
}
//json转成对象
tools.parseObj = function(jsondata) {
	var data = null;
	if(typeof jsondata != "object") {
		data = JSON.parse(jsondata);
	} else {
		data = JSON.stringify(jsondata);
		data = JSON.parse(data);
	}
	return data;
}
//播放开奖前N秒提示音
tools.playSound = function(time) {
	var medio = $("#soundSet").find("input:[checked='checked']").val();
	if(medio != "none" && medio != undefined) {
		var audioPlay = $("#audioid");
		if(time == "begin" && ($("#soundSet").find("select").val() == "begin")) {
			audioPlay[0].play();
		} else {
			if($("#soundSet").find("select").val() == (time - 1 * 1)) {
				audioPlay[0].play();
			}
		}
	}
}
//重复请求数据
tools.repeatAjaxt = {
	kuai3: function(id) {
		clearInterval(animateID[id]);
		setTimeout(function() {
			ajaxRequst($(id).find(".nextIssue").text(), $(id).attr("id")); //请求后台加载数据传入一下期期数
		}, 5000);
	},
	qiu: function(id) {
		clearInterval(animateID[id]); //清除开奖动画
		var liarr = "";
		$(id).find(".kajianhao li").each(function() {
			liarr += $(this).text() + ",";
		});
		animateMethod.sscAnimateEnd(liarr, id);
		setTimeout(function() {
			ajaxRequst($(id).find(".nextIssue").text(), $(id).attr("id"));
		}, 5000);
	},
	pk10: function(id) {
		clearInterval(animateID[id]);
		setTimeout(function() {
			ajaxRequst($(".nextIssue").text()); //请求后台加载数据传入一下期期数
		}, 5000);
	},
	cqnc: function(id) {
		clearInterval(animateID[id]);
		setTimeout(function() {
			ajaxRequst($(".nextIssue").text()); //请求后台加载数据传入一下期期数$(".nextIssue").text()要请求的期数
		}, 5000);
	}
}

tools.repeatIndexAjax = {
	kuai3: function(boxid) {
		setTimeout(function() {
			ajaxRequst($(boxid).find(".nextIssue").text(), $(boxid).attr("id")); //请求后台加载数据传入一下期期数
		}, 5000);
	},
	qiu: function(boxid) {
		var liarr = "";
		$(boxid).find(".kajianhao li").each(function() {
			liarr += $(this).text() + ",";
		});
		animateMethod.sscAnimateEnd(liarr, boxid);
		setTimeout(function() {
			ajaxRequst($(boxid).find(".nextIssue").text(), $(boxid).attr("id")); //请求后台加载数据传入一下期期数
		}, 5000);
	},
	qiuam: function(boxid) { //执行暂时动画
		var liarr = "";
		$(boxid).find(".kajianhao li").each(function() {
			liarr += $(this).text() + ",";
		});
		animateMethod.sscAnimateEnd(liarr, boxid);
	},
	pk10: function(boxid) {
		setTimeout(function() {
			ajaxRequst($(boxid).find(".nextIssue").text(), $(boxid).attr("id")); //请求后台加载数据传入一下期期数
		}, 5000);
	},
	cqnc: function(id) {
		clearInterval(animateID[id]);
		setTimeout(function() {
			ajaxRequst($(".nextIssue").text()); //请求后台加载数据传入一下期期数
		}, 5000);
	}
}

tools.bannerImg = function(obj) {
	var html1 = "";
	$(obj).each(function(i) {
		html1 += '<div class="swiper-slide"><a href="'+ this.link + '" target="_blank"><img src="'+ this.image + '"></a></div>'
	});
	var html = '<div class="device">' +
		'<div class="swiper-container">' +
		'<div class="swiper-wrapper">' +
		html1 +
		'</div>' +
		'</div>' +
		'<div class="pagination"></div>' +
		'</div>';
	$("#littleimg").empty();
	$("#littleimg").append(html);
	var bgcolor = ['#f2a764', '#db1f14', '#5555ff', '#724732', '#d9e5f1', '#747f83'];
	var mySwiper = new Swiper('.swiper-container', {
		pagination: '.pagination',
		loop: true,
		freeMode: true,
		grabCursor: true,
		paginationClickable: true,
		autoplay: 4500,
		effect: 'fade',
		//切换模式effect:slide/fade/cube/coverflow/flip
		fade: {
			crossFade: true,
		},
		autoplayDisableOnInteraction: false,
		onSlideChangeStart: function(swiper) {
			$(".device").css("background-color", "#fff");
		}
	});
	$('.arrow-left').on('click', function(e) {
		e.preventDefault();
		mySwiper.swipePrev();
	})
	$('.arrow-right').on('click', function(e) {
		e.preventDefault();
		mySwiper.swipeNext();
	})
}

tools.advertImg = function(obj) {
	console.log(obj);
	var html = "";
	$(obj).each(function(i) {
		switch(this.pic_type){
			case 'logo_up': 
				html = '<a class="logobox" href="'+ this.link + '" target="_blank"><img src="'+ this.image + '"></a>';
				break;
			case 'advert_up':
				html = '<a class="logobox guanggao1" href="'+ this.link + '" target="_blank"><img src="'+ this.image + '"></a>';
				break;
			case 'api_pic':
			case 'member_pic':
			case 'expert_pic':
				html = '<a href="'+ this.link + '" target="_blank"><img style="height:100%;width:100%;" src="'+ this.image + '"></a>';
				break;
			case 'home_down':
			case 'zx_detail':
			case 'zx_list':
			case 'zx_index':
			case 'home_up':
			case 'logo_down':
			default:
				html = '<a href="'+ this.link + '" target="_blank"><img src="'+ this.image + '"></a>';
		}
		var obj = $("#"+this.pic_type);
		if(obj != 'undefined' || obj != ''){
			$("#"+this.pic_type).empty();
			$("#"+this.pic_type).append(html);
		}
	});
}

tools.browserRedirect = function() {
	var sUserAgent = navigator.userAgent.toLowerCase();
	var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
	var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
	var bIsMidp = sUserAgent.match(/midp/i) == "midp";
	var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
	var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
	var bIsAndroid = sUserAgent.match(/android/i) == "android";
	if(sUserAgent.indexOf("android") > 0) {
		bIsAndroid = true;
	}
	var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
	var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
	if(bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
		var urlstr = window.location.href;
		var noAdvertiseWWW = "1680100.com";
		//noAdvertiseWWW = "192.168";//测试
		if(urlstr.indexOf(noAdvertiseWWW) != -1) {
			window.location.href = "http://m.1680100.com/";
		} else {
			window.location.href = "http://m.1680210.com/";
		}
	} else {
		window.location.href = "html/public/home.html?v=2017731625";
	}
}
//var flusher = null;
//var timer = null;
if(typeof(firstPage)=="undefined")firstPage=0;
tools.getLotteryNewestGid = function(id,timer){
	var waittime = 2000;
	if(firstPage == 1){
		var lotKey = id.replace('#','');
		var cuttype = $(id).find(".cutType").text();
		waittime = 5000;
	}else{
		lotKey = $("#lotKey").val();
	}
	$.ajax({
		url: publicUrl + "/home/api/getssc",
		type: "get",
		timeout: 60000,
		asasync: false,
		success: function(jsondata) {
			var data = null;
			if(typeof jsondata != "object") {
				data = JSON.parse(jsondata);
			} else {
				data = JSON.stringify(jsondata);
				data = JSON.parse(data);
			}
			nextIssue = $(id).find(".nextIssue").text();
			preIssue = $(id).find(".preDrawIssue").text();
			try{
				if(firstPage == 0 && nextIssue == preIssue && data.newest_gid < nextIssue){
					throw new Error("error");// 开奖时
				}else if(firstPage == 0 &&nextIssue > preIssue && data.newest_gid <= preIssue){
					throw new Error("error");// 期号间隔大于1
				}else if(firstPage == 1 && data.newest_gid < preIssue){
					throw new Error("error");// 开奖时
				}else{
					clearInterval(timer); //清除当前定时器
					if(firstPage == 0){
						ajaxRequst(nextIssue, id); //请求后台加载数据传入一下期期数
					}else{
						ajaxRequst("", id, cuttype, lotKey);
					}
				}
			}catch(e){
				setTimeout(function() {
					tools.getLotteryNewestGid(id,timer);
				}, waittime);
			}
		},
		error: function(data) {
			setTimeout(function() {
				tools.getLotteryNewestGid(id,timer);
			}, waittime);
			iferror = true;
		},
		complete: function(xmlobj, state) {
			iferror = false;
			xmlobj = null;
			if(!iferror) {
				if(state == "timeout") {
					setTimeout(function() {
						tools.getLotteryNewestGid(id,timer);
					}, waittime);
				}
			}
		}
	});
}

tools.countDown = function(timestr, serverTime, id) {
	//timestr：下期开奖时间
	//serverTime：服务器时间
	//id：倒计时显示区域
	var time = timestr.replace("-", "/");
	time = time.replace("-", "/");
	//var day_elem = $(id).find('.day');
	var hour_elem = $(id).find('.hour');
	var minute_elem = $(id).find('.minute');
	var second_elem = $(id).find('.second');
	var opentyle = $(id).find('.opentyle');
	var cuttime = $(id).find('.cuttime');
	var end_time = new Date(time); //月份是实际月份-1
	var sys_second = (end_time - serverTime*1000) / 1000;
	//	sys_second = sys_second - 1000;
	var isOpen = true;
	var lastTime = new Date;
	var timer = setInterval(function() {
		 var timerS = Math.abs(new Date - lastTime)/1000;
			lastTime = new Date;
			timerS = timerS.toString().split(".");
			if(timerS[0]>1){
				sys_second= sys_second-timerS[0];
			}
		//倒计时铃声
		if(isOpen) {
			isOpen = false;
			tools.playSound("begin");
		} else {
			tools.playSound(sys_second);
		}
		if(sys_second > 1) {
			sys_second -= 1;
			//var day = Math.floor((sys_second / 3600) / 24);
			var hour = Math.floor(sys_second / (60 * 60));
			var minute = Math.floor((sys_second / (60)) % 60);
			var second = Math.floor((sys_second) % 60);
			//day_elem && $(day_elem).text(day); //计算天
			$(hour_elem).text(hour < 10 ? "0" + hour : hour); //计算小时
			$(minute_elem).text(minute < 10 ? "0" + minute : minute); //计算分钟
			$(second_elem).text(second < 10 ? "0" + second : second); //计算秒杀
			//如果时间小于0则删除时间显示
			if(hour <= 0) {
				$(id).find(".hourtxt").hide();
				$(hour_elem).hide();
			} else {
				$(id).find(".hourtxt").show();
				$(hour_elem).show();
			}
			$(opentyle).hide(); //倒计时区域显示开奖中...
			$(cuttime).css({
				display: "inline-block"
			}); //倒计时区域隐藏...
		} else {
			var nextIssue = $(id).find(".nextIssue").text();
			$(id).find(".preDrawIssue").text(nextIssue);
			$(opentyle).show(); //倒计时区域显示开奖中...
			$(cuttime).hide(); //倒计时区域隐藏...
			clearInterval(timer); //清除当前定时器
			if(typeof(animateType) == "undefined"){
				var animateType = $(id).find(".animateType").text();
			}
			if(animateID[id] == undefined && typeof(animateType) != "undefined") {
                animate.sscAnimate(id)
				setTimeout(function() {
					tools.getLotteryNewestGid(id,timer);
				}, "5000");
			}
		}
	}, 1000);
	// 因为采集是异步的，分析完后需要更新到页面上
	var nextIssue = $(id).find(".nextIssue").text();
	var curIssue = $(id).find(".preDrawIssue").text();
	var interv = parseInt(nextIssue) - parseInt(curIssue);
	if(interv <= 1)return;
	if(sys_second > 10 && interv > 1 && typeof(boxid) != "undefined"){
		tools.getLotteryNewestGid(id,timer);
	}
}
tools.ifCheckedOnToday = function() {
	var flag = null;
	if(!$("#dateframe").length < 1) {
		var dayC = $(".listheadrl").find(".checked");
		var dateC = $("#dateframe").find(".date").val();
		if($(dayC).attr("id") == "today" && dateC == tools.getDate()) {
			flag = true;
		} else {
			flag = false;
		}
	} else {
		flag = true;
	}
	//如果为第二次开奖请求再执行返回true
	if(flag&&config.firstLoad){
		return true;
	}else{
		return false;
	}
}
tools.getDate = function() {
	//得到系统时间
	var current_time = "";
	var date = new Date();
	var year = date.getFullYear();
	var month = date.getMonth() + 1;
	var day = date.getDate();
	var hour = date.getHours();
	var minute = date.getMinutes();
	var second = date.getSeconds();
	current_time = year + "-" + month + "-" + day;
	return current_time;
}
tools.insertVideo = function() {
	//pk10动画初始化倒计时
	var hour = $("#pk10 .cuttime").find(".hour").text();
	var minute = $("#pk10 .cuttime").find(".minute").text();
	var second = $("#pk10 .cuttime").find(".second").text();
	var sedonds = hour * 3600 + minute * 60 + second * 1 - 1;
	if(sedonds == "-1") {
		sedonds = 0;
	}
	//执行倒计时
	$("iframe")[0].contentWindow.startcountdown(sedonds);
	var thisnum = "";
	var liobj = $("#pk10 #jnumber").find("li");
	$(liobj).each(function() {
		thisnum += $(this).text() + ",";
	});
	var numobj = null;
	if(thisnum.length < 11) {
		numobj = "5,6,3,4,8,7,9,10,2,1";
	} else {
		numobj = thisnum.substring(0, thisnum.length - 1);
	}
	$("iframe")[0].contentWindow.showcurrentresult(numobj);
	//添加drawCount
	$('.animate iframe').contents().find("#currentdrawid").text($("#pk10").find(".drawCount").text());
	$('.animate iframe').contents().find("#nextdrawtime").text($("#pk10").find(".preDrawIssue").text());
	$('.animate iframe').contents().find("#stat1_1").text($("#pk10").find(".sumFS").text());
	$('.animate iframe').contents().find("#stat1_2").text($("#pk10").find(".sumBigSamll").text());
	$('.animate iframe').contents().find("#stat1_3").text($("#pk10").find(".sumSingleDouble").text());
	var tdobj = $("#pk10 .longhu").find("td");
	$('.animate iframe').contents().find("#stat2_1").text($(tdobj).eq(0).text());
	$('.animate iframe').contents().find("#stat2_2").text($(tdobj).eq(1).text());
	$('.animate iframe').contents().find("#stat2_3").text($(tdobj).eq(2).text());
	$('.animate iframe').contents().find("#stat2_4").text($(tdobj).eq(3).text());
	$('.animate iframe').contents().find("#stat2_5").text($(tdobj).eq(4).text());
}
tools.testWWW = function() {
	//判断不同域名会去掉广告
	var urlstr = window.location.href;
	var noAdvertiseWWW = "1680100.com";
	//	noAdvertiseWWW = "192.168";//测试
	if(urlstr.indexOf(noAdvertiseWWW) != -1) {
		$(".advertisebox").hide(); //去掉广告
		var Nimghtml = "<img src='/statics/168/img/banner/neiye.png?v=2017731625'></img>";
		var Simghtml = "<img src='/statics/168/img/banner/shouye.png?v=2017731625'></img>";
		if($("#ifindex").val() != "index") { //不是主页所添加小图去掉广告
			$("#littleimg").empty().append(Nimghtml);
		} else { //是主页添加大图去掉广告
			$("#littleimg").empty().append(Simghtml);
		}
		//修改手机跳转到手机版本无广告
		$(".lasli").find(".phoneicon").attr("href", "http://m.1680100.com");
		$(".fix1200").find(".backold").hide();
	} else {
		if($("#ifindex").val() == "index") { //不是主页所添加小图去掉广告
			indexObj.loadBanner();
		}
		$(".advertisebox").show();
	}
}
tools.clearHC = function() {
	var version = config.version;

	//增加版本号
	var csshref = $("link").attr("href");
	$("link").each(function() {
		var href = $(this).attr("href");
		var date = new Date();
		href = href + "?v=" + date.getFullYear() + "" + date.getMonth() + 1 + "" + date.getDate();
		$(this).attr("href", href);
	});
}
tools.setPK10TB = function() {
	pk10jiuchuo = setInterval(function() {
		if($("#videobox").css("z-index") != -1) {
			var timestr = $('.animate iframe').contents().find(".countdownnum").text();
			if(timestr != "00:00") {
				tools.insertVideo();
				if(config.ifdebug) {
					console.log("纠错....");
				}
			} else {
				if(config.ifdebug) {
					console.log("开始开奖了....");
					console.log("停止纠错....");
				}
			}
		}
	}, 5000)
}
tools.operatorTime = function(timestr, serverTime) {
	//下期开奖时间：timestr 服务器时间：serverTime
	var time = timestr.replace("-", "/");
	var serverTime = serverTime.replace("-", "/");
	time = time.replace("-", "/");
	serverTime = serverTime.replace("-", "/");
	var end_time = new Date(time).getTime(); //月份是实际月份-1
	var sys_second = (end_time - new Date(serverTime).getTime()) / 1000;
	return sys_second;
}
tools.operatorTime2 = function(timestr, serverTime) {
    //下期开奖时间：timestr 服务器时间：serverTime
    var time = timestr.replace("-", "/");
    time = time.replace("-", "/");
    var end_time = new Date(time).getTime(); //月份是实际月份-1
    var sys_second = (end_time - serverTime*1000) / 1000;
    return sys_second;
}
//广西快乐十分开奖区特殊背景
tools.gxKaiBg = function(num, id) {
	for(var i = 0; i < num.length; i++) {
		if(num[i] == 1 || num[i] == 4 || num[i] == 7 || num[i] == 10 || num[i] == 13 || num[i] == 16 || num[i] == 19) {
			$(id).find(".gx_kajianhao").find("li").eq(i).addClass("numred");
		} else if(num[i] == 3 || num[i] == 6 || num[i] == 9 || num[i] == 12 || num[i] == 15 || num[i] == 18 || num[i] == 21) {
			$(id).find(".gx_kajianhao").find("li").eq(i).addClass("numgreen");
		}
	}

}

//PC蛋蛋幸运28开奖区最后一个号码添加特殊背景
tools.egxy28 = function(id) {
	$(id).find(".kajianhao ul").find("li:last-child").addClass("numred");
}

//给北京快乐8系列开奖号码区添加特殊号码背景色
tools.bjkl8BagColor = function(num, boxId) {
	var code = num.split(",");
	code.splice(code.length - 1, 1);
	for(var i = 0; i < code.length - 1; i++) {
		if(code[i] >= 41) {
			$(boxId).find(".kajianhao ul").find("li").eq(i).addClass("numWeightblue");
		}
	}
	$(boxId).find(".kajianhao ul").find("li:last-child").addClass("numOrange");
}
tools.setTimefun_k3 = function(){
	setTimeout(function() {
		if($("#drawTime").val() !== undefined) {
			if(!($("#videobox").css("z-index") == -1)) {
				var className = "";
				var preDrawCode = "";
				var numd ;
				var lilen = $("#cqSsc").find(".kajianhao li").length;
				var sumNum = 0;
				var drawIsStr = $("#cqSsc").find(".preDrawIssue").text();
				var drawIssue = parseInt(drawIsStr);
				var nowDraw = 0 + $(".drawCount").text();
				var hour = $("#timebox").find("hour").text();
				var minute = $("#timebox").find(".minute").text();
				var second = $("#timebox").find(".second").text();
				var seconds = hour * 3600 + minute * 60 + second * 1 - 2;
				if(seconds == "-1") {
					seconds = 0;
				}
				$("#cqSsc").find(".kajianhao").find("li").each(function(i){
					className = $(this).attr("class");
					numd = className.substring(className.length-1,className.length);
					if(i <= 2){
						$("iframe").contents().find("#codetop").find("li").eq(i).text(numd);
					}
					preDrawCode += numd;
					sumNum += parseInt(numd);
				});
				preDrawCode = [].slice.call(preDrawCode);
				$("iframe").contents().find(".nowDraw").text(nowDraw);
//				$("iframe")[0].contentWindow.k3v.startVideo(seconds);
				var data = {
					preDrawCode:preDrawCode,
					sumNum:sumNum,
					sumBigSmall:sumBigSmall,
					drawIssue:drawIssue+1,
					drawTime:drawTime,
					seconds:seconds
				}
				$("iframe")[0].contentWindow.k3v.stopVideo(data);
			}
		}
	}, 2000)
}
tools.setTimefun_ssc = function(jsondata){
    jsondata=JSON.parse(jsondata);
	setTimeout(function() {
		if($("#drawTime").val() !== undefined) {
			if(!($("#videobox").css("z-index") == -1)) {
				var sedonds = jsondata.next.awardTimeInterval;
				if(sedonds<=0) {
					sedonds = 0;
				}
				console.log(jsondata);
				var num=jsondata.current.awardNumbers.split(',');
				var data = {
					preDrawCode: num,
					id: "#numBig",
					counttime: sedonds,
					preDrawIssue: $(".preDrawIssue").text(),
					drawTime: $("#drawTime").val(),
					sumNum: $("#sumNum").val(),
					sumSingleDouble: $("#sumSingleDouble").val(),
					sumBigSmall: $("#sumBigSmall").val(),
					dragonTiger: $("#dragonTiger").val()
				}
				sscAnimateEnd(data);
			}
		}
	}, 1000)
}
tools.setTimefun_ssl = function(){
	setTimeout(function() {
		if($("#drawTime").val() !== undefined) {
			if(!($("#videobox").css("z-index") == -1)) {
				var hour = $("#timebox").find(".hour").text();
				var minute = $("#timebox").find(".minute").text();
				var second = $("#timebox").find(".second").text();
				var sedonds = hour * 3600 + minute * 60 + second * 1 - 1;
				if(sedonds == "-1") {
					sedonds = 0;
				}
				var numarr = $("#cqSsc").find(".kajianhao li").text();
				
			}
		}
	}, 1000)
}
var listColor = "";
(tools.initListen = function() {
  $("#jrsmhmtj").find("table").css("background", "#d4d4d4"), $(
	"#selectcolor"
  ).on("click", "span", function() {
	$(this)
	  .addClass("select")
	  .siblings()
	  .removeClass(), 1 != $(this).children().length && ((listColor = $(this).css("background-color")), $("#jrsmhmtj table tr:odd").find("td").css("background", listColor));
  });
}), (tools.resetListColor = function() {
  "" != listColor &&
	$("#jrsmhmtj table tr:odd").find("td").css("background", listColor);
});
function setTextColor(e) {
  (listColor = "#" + e), $("#jrsmhmtj table tr:odd")
	.find("td")
	.css("background", listColor);
}
