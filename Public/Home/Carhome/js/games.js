var img_path = img_path || "/pk10/",
    zy = zy || {},
    socket, ifa_height = ifa_height || 650;
zy.kb0init = function() {
    var a = '<input placeholder="' + (lang_cfg.tips || '车道/车号/金额') + '" type="text" id=\'Message\'/>';
    a += '<img src="' + site_url + '/Public/images/kb.png" class="keybord">';
    a += '<span class="txtbet">' + lang_cfg.quick + '</span><span class="sendemaill">发 送</span>';
    $(".keybord_box").html(a), $('.getkeybord').hide()
};
$(document).ready(function(){
    $("#Message").focus(function(){
        setTimeout(function(){
            $(".foot-item").css("position","fixed");
            $(".foot-item").css("top","560px")
            document.body.scrollTop = document.body.scrollHeight;
        },300);
    });
    $("#Message").blur(function(){
        $(".foot-item").css("position","fixed");
        $(".foot-item").css("top","");
    });
});
zy.kb0end = function() {
    $(document).on("click", "i.kclose", function() {
        $('.keybord').addClass("gray");
        $(".keybord_div").hide();
        $("#Message").attr("readonly", false)
    });
    $(".keybord").on('touchstart', function() {
        $(this).toggleClass("gray");
        if ($(this).hasClass('gray')) {
            $(".keybord_div").hide();
            $("#Message").attr("readonly", false)
        } else {
            $(".txtbet").removeClass("on");
            $(".txtbet_div").hide();
            $(".keybord_div").show();
            $("#Message").attr("readonly", true)
        }
    });
    $(".txtbet").on('touchstart', function() {
        $(this).toggleClass("on");
        if ($(this).hasClass('on')) {
            $(".keybord_div").hide();
            $(".keybord").addClass('gray');
            $(".txtbet_div").show()
        } else {
            $(".txtbet").removeClass("on");
            $(".txtbet_div").hide()
        }
    });
    $(".keybord_div em").on('touchstart', function() {
        $(this).addClass("on").siblings().removeClass('on');
        if ($(this).hasClass("c2")) return;
        var val = $("#Message").val(),
            vkey = $(this).html();
        if (vkey == "清") return $("#Message").val('');
        if (vkey == "←" || vkey == "删") return $("#Message").val(val.substr(0, val.length - 1));
        if (vkey == "×") {
            $('.keybord').addClass("gray"), $(".keybord_div").hide(), $("#Message").attr("readonly", false);
            return
        }
        if (!in_array(vkey, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0]) && val == vkey) return $("#Message").val('');
        if (!in_array(vkey, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0, '.', '/', '-']) && val.indexOf(vkey) != -1) return;
        $("#Message").val(val + vkey)
    });
    $(".keybord_div em").on('touchend', function() {
        $(this).removeClass("on")
    })
};
zy.show_v_kb2 = function() {
    zy.kb1init();
    var kbs = (typeof lang_cfg.keytop == 'object') ? lang_cfg.keytop : lang_cfg.keytop.split(''),
        s = '';
    for (var i = 0; i < kbs.length; i++) {
        if (chk_ks(kbs[i])) s += '<div class="swiper-slide">' + kbs[i] + '</div>'
    }
    $('.kb1top').html(s), kbs = (typeof lang_cfg.keytop == 'object') ? lang_cfg.keyleft : lang_cfg.keyleft.split(''), s = '';
    for (var i = 0; i < kbs.length; i++) {
        if (chk_ks(kbs[i])) s += '<li class="swiper-slide">' + kbs[i] + '</li>'
    }
    $('.kb1left').html(s);
    zy.kb1end()
};
zy.kb1init = function() {
    var s = '<div class="foot-item"><div class="foot-box">';
    s += '<i class="icon-keybord off"><img src="' + site_url + '/Public/images/keybord1.png"/></i>';
    s += '<input type="text" placeholder="' + (lang_cfg.tips || '车道/车号/金额') + '" id=\'Message\' class="text" />';
    s += '<div class="hand-btn-group"><span class=squikc>' + lang_cfg.quick + '</span><span class="kbsend">发送</span></div></div>';
    s += '<div class="foot-keybord" style="display: none;">';
    s += '<div class="playtype"><div class="swiper-container"><div class="swiper-wrapper kb1top"></div>';
    s += '<div class="swiper-pagination" style="display: none;"></div></div><i class="iconfont closer">&#xe7fa;</i></div>';
    s += '<div class="play-b"><div class="b-left swiper-container-l"><ul class="swiper-wrapper kb1left"></ul><div class="swiper-scrollbar"></div></div>';
    s += '<div class="b-center kb1num">';
    s += '<span><em>1</em></span><span><em>2</em></span><span><em>3</em></span>';
    s += '<span><em>4</em></span><span><em>5</em></span><span><em>6</em></span>';
    s += '<span><em>7</em></span><span><em>8</em></span><span><em>9</em></span>';
    s += '<span><em class="other">/</em></span>';
    s += '<span><em>0</em></span>';
    s += '<span><em class="other">.</em></span></div>';
    s += '<div class="b-right kb1com"><span><em data-val="clear"><i class="iconfont">&#xe6ca;</i></em></span>';
    s += '<span><em>查</em></span>';
    s += '<span><em>回</em></span>';
    s += '<span class="kbsend"><em>' + lang_cfg.send + '</em></span></div>';
    s += '</div></div>';
    $('body').append(s);
    $('.rightdiv').css('margin-top', '2%')
};
zy.kb1end = function() {
    new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        slidesPerView: 'auto',
        spaceBetween: 0,
        observer: true,
        observeParents: true
    });
    new Swiper('.swiper-container-l', {
        slidesPerView: 'auto',
        paginationClickable: true,
        spaceBetween: 0,
        direction: 'vertical',
        observer: true,
        observeParents: true
    });
    var tmove = true,
        i, kbs = [];
    kbs1 = (typeof lang_cfg.keytop == 'object') ? lang_cfg.keytop : lang_cfg.keytop.split(''), kbs2 = (typeof lang_cfg.keyleft == 'object') ? lang_cfg.keyleft : lang_cfg.keyleft.split('');
    for (i in kbs1) kbs.push(kbs1[i]);
    for (i in kbs2) kbs.push(kbs2[i]);
    kbs.push('查'), kbs.push('回');
    kbs1.push("大单"), kbs1.push("大双"), kbs1.push("小单"), kbs1.push("小双");
    kbs1.push("单大"), kbs1.push("双大"), kbs1.push("单小"), kbs1.push("双小");
    $(".foot-item span,.foot-item .swiper-wrapper>div,.foot-item .swiper-wrapper>li").on("click", function() {
        var a = $(this);
        tmove = false;
        a.hasClass('skip') ? '' : a.addClass("on").siblings().not('skip').removeClass('on')
    }).on('click', function() {
        var a = $(this),
            v = a.text(),
            val = $("#Message").val();
        $(this).hasClass('skip') ? "" : $(this).removeClass("on");
        if (tmove) return;
        if (a.hasClass('skip')) return;
        if (v == lang_cfg.send) {
            return $('html, body').animate({
                scrollTop: $(".leftdiv").offset().top
            }, 200)
        }
        if (v == lang_cfg.quick) return $(".foot-item").slideUp(200, function() {
            $('.txtbet_div').show();
            $(".infuse").slideDown(200);
            zy.go2top($('.leftdiv'))
        });
        if (v == "") {
            return $("#Message").val(val.substr(0, val.length - 1))
        }
        var vn = in_array(v, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0]),
            vm = in_array(v, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0, '.', '/', '-']);
        if (!vn && val == v) return $("#Message").val('');
        if (!vm && val.indexOf(v) != -1) return;
        if (!vm && in_array(val, kbs) && in_array(val, ['大', '小']) && in_array(v, ['单', ['双']])) return $("#Message").val(val + v);
        if (val && !in_array(v, ['-']) && in_array(v, kbs1)) {
            for (i in kbs1) {
                if (val.indexOf(kbs1[i]) > -1 && (!in_array(kbs1[i], ['大', '小', '单', '双']) || in_array(kbs1[i], ['大', '小']) && in_array(v, ['大', '小']) || in_array(kbs1[i], ['单', '双']) && in_array(v, ['单', '双']))) return $("#Message").val(val.replace(kbs1[i], v))
            }
        };
        if (val && !in_array(v, ['-']) && in_array(v, kbs2)) {
            for (i in kbs2) {
                if (val.indexOf(kbs2[i]) > -1) return $("#Message").val(val.replace(kbs2[i], v))
            }
        };
        $("#Message").val(val + v)
    }).on('touchmove', function() {
        tmove = true
    });
    $('.getkeybord,i.closebt').click(function() {
        $(".infuse").slideUp(200, function() {
            $(".txtbet_div").hide();
            $(".foot-item").slideDown(200)
        })
    });
    $(".icon-keybord").click(function() {
        $(this).toggleClass("off");
        if ($(this).hasClass("off")) {
            $(this).next().attr('readonly', false), $(".foot-keybord").slideUp(300)
        } else {
            $(this).next().attr('readonly', true), $(".foot-keybord").slideDown(300)
        }
    });
    $(".playtype .closer").click(function() {
        $(".icon-keybord").addClass("off").next().attr('readonly', false), $(".foot-keybord").slideToggle(300)
    })
};
zy.kb1show = function(v) {
    return udata.kt == 1 ? v == 0 ? $(".foot-item").slideUp(200) : ($("#txtbet_div").length == 0 || $("#txtbet_div").is(":hidden")) ? $(".foot-item").slideDown(200) : !0 : !0
};
zy.go2top = function(a) {
    $('html, body').animate({
        scrollTop: typeof a == 'number' ? a : $(a).offset().top
    }, 300)
};
$(function() {
    udata ? '' : location.href = 'https://xw.qq.com/';
    $('<i class="totop iconfont">&#xe6fb;</i>').appendTo($("body"));
    show_v_kb();
    $(".mlogo").attr('src', udata.headimg);
    $(document).on("touchstart", 'i.totop', function() {
        zy.go2top(0);
        $(this).hide()
    });
    $(window).scroll(function() {
        $(window).scrollTop() > $(window).height() * 0.3 ? $('i.totop').fadeIn() : $('i.totop').fadeOut()
    });
    $(document).on("click", "i.close", function() {
        var a = $(this).data();
        a.id ? $(a.id).hide() : $(this).parents().hide()
    });
    $("img").on("error", function() {
        $(this).unbind("error").attr("src", "/Public/images/logo.jpg")
    });
    // $("#ifarms").attr("src", lottery_uri).css("height", ifa_height);
    $(".leftdiv li,.nav_banner .lottery li").click(function() {
        var o = $(this),
            d = o.data();
        if (d.uri) {
            location.href = d.uri;
            return
        }
        switch (d.id) {
            case "home":
                location.href = "#menu";
                $("#ss_menu").show().find("ul").show();
                $(".ss_nav").css("margin-top", ($(window).height() - $(".ss_nav").height()) / 2 * .8);
                break;
            case "lottery":
                location.href = "#menu";
                $("#ss_menu").find("ul.menu").hide();
                $("#ss_menu").show().find('ul.lottery').show();
                $(".ss_nav").css("margin-top", ($(window).height() - $(".ss_nav").height()) / 2 * .8);
                break;
            case "reload":
                location.href = get_now_url();
                break;
            case "reload2":
                document.ifarms.location.reload();
                zy.go2top(0);
                break;
            case "minmax":
                if ($("#ifarms").is(":visible")) {
                    $("#ifarms").toggleClass("minmax");
                    $(this).find('span').text($("#ifarms").hasClass("minmax") ? "大窗" : "小窗");
                    zy.go2top(0);
                    return
                }
                if ($(".neirong").is(":visible")) {
                    $(".neirong").toggleClass("minmax");
                    $(this).find('span').text($(".neirong").hasClass("minmax") ? "大窗" : "小窗");
                    zy.go2top(0)
                }
                break;
            case "wenzi":
                $(".neirong").show();
                $(".changlong").hide();
                $('.min_max').find('span').text($('.neirong').hasClass("minmax") ? "大窗" : "小窗");
                $("#ifarms").hide();
                zy.go2top(0);
                break;
            case "donghua":
                $(".neirong").hide();
                $(".changlong").hide();
                $("#ifarms").show();
                $('.min_max').find('span').text($("#ifarms").hasClass("minmax") ? "大窗" : "小窗");
                zy.go2top(0);
                break;
            case "changlong":
                $(".neirong").hide();
                $(".changlong").show();
                $("#ifarms").hide();
                zy.go2top(0);
                break;
            case "logout":
                layer.open({
                    content: '确定要退出吗？',
                    btn: ['确定', '关闭'],
                    yes: function(index) {
                        layer.close(index), $.get('/member/logout').then(function(res) {
                            location.href = '/toutiao/'
                        })
                    }
                });
                break;
            case "":
            case undefined:
                break;
            default:
                var e = $('#frameRIGHTH').find("." + d.id);
                if (e) {
                    var f = e.data();
                    $(".rbox").hide(), e.removeClass('off').show();
                    if (f.uri && f.load != "1") {
                        $.get(f.uri).then(function(res) {
                            if ("string" == typeof res) {
                                $(e).html(res);
                                e.data("load", f.load == "0" ? 1 : 2)
                            }
                        })
                    }
                }
                if (d.id == "kefu") {
                    $(".skefu").find('em').html(0).hide()
                }
                if (in_array(d.id, ['tzlog', 'kefu', 'guize', 'czhelp'])) {
                    zy.go2top($('.leftdiv').offset().top);
                    zy.kb1show(0)
                }
                if (d.id == 'touzu') {
                    zy.go2top(0);
                    zy.kb1show(1)
                }
                break
        }
    })
});
var get_now_time = function() {
    return new Date().toLocaleTimeString()
};
window.onhashchange = function() {
    if (location.href.indexOf("#menu") == -1) {
        $("#ss_menu").hide()
    }
};
var get_now_url = function(skip, puid) {
    url = window.location.pathname;
    var skips = ",isappinstalled,from,time," + (skip ? skip + ',' : '');
    var schurl = window.location.search;
    var qeury = "";
    if (puid > 0) qeury = "puid=" + puid;
    if (schurl) {
        schurl = schurl.substr(1).split("&");
        for (var i = 0; i < schurl.length; i++) {
            t = schurl[i].split("=");
            if (skips.indexOf(',' + t[0].toLowerCase() + ',') == -1 && t[1]) {
                qeury += (qeury == "" ? "" : "&") + t[0] + "=" + t[1]
            }
        }
    }
    qeury += (qeury == "" ? "" : "&") + "time=" + new Date().getTime();
    if (qeury != "") qeury = "?" + qeury;
    return url + qeury
};
zy.tips = function(msg, time) {
    var zytips = $(".zytips");
    if (zytips.length == 0) {
        zytips = $('<div class="zytips"></div>').appendTo("body")
    }
    zytips.html("<div>" + msg + "</div>");
    $(".zytips").fadeOut(200);
    zytips.fadeIn();
    time = time || 2;
    var tt = window.setTimeout(function() {
        $(".zytips").fadeOut(200)
    }, time * 1000)
};
var in_array = function(sVal, aVal) {
    for (s = 0; s < aVal.length; s++) {
        tVal = aVal[s].toString();
        if (tVal == sVal) return true
    }
    return false
};

function kefumsg(picts, nickname, strs, time) {
    $(".kfcs").prepend("<div class=saidright><img  src=" + picts + " /><div class=tousaidl><span class=tousaid2 >" + time + "</span>&nbsp;&nbsp;<span class=tousaid1>" + nickname + "</span></div><div class=ts> <b></b><span class=neirongsaidl>" + strs + "</span></div></div>")
};

function kefume(picts, nickname, strs, time) {
    $(".kfcs").prepend("<div class=saidleft><img  src=" + picts + " /><div class=tousaid><span class=tousaid1>" + nickname + "</span>&nbsp;&nbsp;<span class=tousaid2>" + time + "</span></div><div class=tsf><b></b><span class=neirongsaid>" + strs + "</span></div></div>")
};

function addxitong(picts, nickname, strs, time) {
    $(".rightdiv").prepend("<div class=saidright><img  src=" + picts + " /><div class=tousaidl><span class=tousaid2 >" + time + "</span>&nbsp;&nbsp;<span class=tousaid1>" + nickname + "</span></div><div class=ts> <b></b><span class=neirongsaidl>" + strs + "</span></div></div>")
};

function addxitong1(picts, nickname, strs, time) {
    $(".rightdiv").prepend("<div class=saidright><img  src=" + picts + " /><div class=tousaidl><span class=tousaid2 >" + time + "</span>&nbsp;&nbsp;<span class=tousaid1>" + nickname + "</span></div><div class=ts> <b style='border-color:transparent  transparent transparent #FFBBBB;'></b><span class=neirongsaidl style='background-color: #FFBBBB;' >" + strs + "</span></div></div>")
};

function addxitong3(picts, nickname, strs, time) {
    $(".rightdiv").prepend("<div class=saidright><img  src=" + picts + " /><div class=tousaidl><span class=tousaid2 >" + time + "</span>&nbsp;&nbsp;<span class=tousaid1>" + nickname + "</span></div><div class='ts' style='min-width: 60%;'> <b style='border-color:transparent  transparent transparent #98E165;'></b><span class=neirongsaidl style='background-color:#98E165;max-width: 100%'>" + strs + "</span></div></div>")
};

function addtouzhu(picts, nickname, strs, time) {
    $(".rightdiv").prepend("<div class=saidleft><img  src=" + picts + " /><div class=tousaid><span class=tousaid1>" + nickname + "</span>&nbsp;&nbsp;<span class=tousaid2>" + time + "</span></div><div class=tsf><b></b><span class=neirongsaid>" + strs + "</span></div></div>")
};