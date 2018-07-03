var page = 1,
    isLoading = false,
    hasMore = true,
    listId = "#datalist",
    moreId = ".loading",
    loaderr = 0,
    zy = zy || {};
moreURL = document.location.href;
zy.is_weixin = function() {
    return navigator.userAgent.toLowerCase().match(/MicroMessenger/i) == "micromessenger" ? true : false
};
zy.tips = function(msg, time) {
    var zytips = $(".zytips");
    if (zytips.length == 0) {
        zytips = $('<div class="zytips"></div>').appendTo("body")
    };
    zytips.html("<div>" + msg + "</div>");
    $(".zytips").fadeOut('fast');
    zytips.fadeIn();
    time = time || 2;
    var tt = window.setTimeout(function() {
        $(".zytips").fadeOut('fast')
    }, time * 1000)
};
zy.msg = function(msg, time) {
    return layer.open({
        content: msg,
        skin: 'msg',
        time: time || 2
    })
};
zy.alert = function(msg, btn) {
    return layer.open({
        content: msg,
        btn: btn || '我知道了'
    })
};
zy.notice = function(msg, title) {
    return layer.open({
        title: [title || '系统提示', 'background-color: #8DCE16;color:#fff;'],
        content: msg
    })
};
zy.setcc = function(name, value, day) {
    var Days = day || 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString()
};
zy.getcc = function(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg)) return unescape(arr[2]);
    else return null
};
zy.delcc = function(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = zy.getcc(name);
    if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString()
};
zy.sbind = function() {
    if (zy.getcc("sbingcc") == "1") return;
    zy.setcc('sbingcc', 1, 1);
    return layer.open({
        content: '您未设置登陆帐户！<br>绑定后微信无法打开<br>可用手机浏览器登陆。<br>请您先去设置帐户？',
        btn: ['现在设置', '以后再说'],
        yes: function(index) {
            layer.close(index);
            location.href = '/member/account.html'
        }
    })
};

function load_index() {
    $.get("/index/ajax").then(function(res) {
        $(".main").html(res)
    })
};

function ajax_load_page(obj, uri) {
    $.get(uri).then(function(res) {
        $(obj).html(res)
    })
};

function load_slide_menu() {
    $.get("/index/menu").then(function(res) {
        $("#ss_menu").html(res)
    })
};

function load_more(url, data) {
    var query_arg = data;
    if (isLoading || !hasMore) return;
    isLoading = true;
    if (data.p == 1) {
        $(listId).html("")
    }
    $(moreId).show();
    $.ajax({
        url: url,
        dataType: "json",
        data: data,
        type: "POST",
        error: function() {
            isLoading = false;
            loaderr++;
            if (loaderr > 3) {
                layer.open({
                    content: "Sorry!加载失败",
                    skin: 'msg',
                    time: 2
                })
            } else {
                load_more(url, data)
            }
        },
        success: function(res) {
            if (res.status) {
                page++, loaderr = 0;
                if (res.map.total == 0) {
                    $(listId).html(res.content)
                } else {
                    if (res.map.page == 1) {
                        $(listId).html(res.content)
                    } else {
                        $(res.content).appendTo($(listId))
                    }
                }
                if (res.map.page >= res.map.totalpage) {
                    hasMore = false
                } else {
                    hasMore = true
                }
                $(moreId).hide();
                isLoading = false
            }
        }
    })
};
$(function() {
    $('body').append('<section id="ajax_container" class="win-show score-view-box"></section>');
    $(".table-tongji").find("td").each(function() {
        var num = $(this).text() * 1;
        if (num < 0) $(this).addClass("red")
    });
    $(".building").click(function() {
        zy.tips("该功能开发中…")
    });
    $(document).on('click', '.show_load', function() {
        // layer.open({
        //     type: 2,
        //     content: '加载中'
        // })
    });
    var bet_money = zy.getcc("bet_money");
    if (bet_money > 0) {
        $(".bet_money").val(bet_money)
    }
});
$(document).on("click", ".ajax_load", function() {
    var a = $(this).data();
    location.href = "#win";
    $.get(a.uri).then(function(res) {
        layer.closeAll();
        layer.open({
            type: 1,
            content: res,
            anim: a.anim || 'up',
            style: 'position:fixed;' + (a.css || 'bottom: 0;') + 'left:1%;width:98%; max-height:100%; min-height:40%; border: none;border-top-left-radius: 10px; border-top-right-radius: 10px; -webkit-animation-duration: .5s; animation-duration: .5s;'
        })
    })
});
$(document).on('click', '.ajax_sw', function() {
    var o = $(this),
        a = o.data();
    layer.open({
        content: a.sw == 1 ? '确定要退出试玩吗？' : '试玩模式不作任何记录！<br><br>是否确认进入试玩模式?',
        style: "font-size:1.5rem;color:red",
        btn: ['确定', '取消'],
        yes: function(index) {
            a.remark = $(".charge_box select").val();
            $.ajax({
                type: "post",
                url: "/member/ajaxsw/",
                data: {
                    sw: 1 - a.sw
                },
                success: function(res) {
                    layer.closeAll();
                    if (a.reload == '1') {
                        location.reload()
                    } else {
                        o.data('sw', res.info.sw);
                        if (res.info.sw == 1) {
                            $(o).html('<i class="iconfont">&#xe665;</i> 退出试玩').parent().addClass('on');
                            $('.per-info .pic').addClass('on');
                            $('.ble').html('试玩余点:' + res.info.sw_balance)
                        } else {
                            $(o).html('<i class="iconfont">&#xe665;</i> 试玩').parent().removeClass('on');
                            $('.per-info .pic').removeClass('on');
                            $('.ble').html('余点:' + res.info.balance)
                        }
                    }
                },
            })
        }
    })
});
$(document).on("click", ".ajax_charge .ftype a", function() {
    $(this).addClass("on").siblings().removeClass('on');
    if ($(this).data("type") == 'tixian') {
        var that = $(this).parents().find("input[name='money']");
        if ($(that).val() * 1 > $(that).data("max") * 1) $(that).val($(that).data("max"))
    }
});
$(document).on("click", 'show_load', function() {
    layer.open({
        type: 2,
        content: '加载中'
    })
});
$(document).on("keyup", ".ajax_charge input[name='money']", function() {
    var a = $(this).parents('.ajax_charge').find(".ftype a.on").data();
    if (a.type == 'tixian') {
        if ($(this).val() * 1 > $(this).data("max") * 1) $(this).val($(this).data("max"))
    }
});
$(document).on("click", '.game-list li.disabled a', function() {
    layer.open({
        content: '暂未开通',
        skin: 'msg',
        time: 2
    })
});
$(document).on("click", ".ajax_charge .submit", function() {
    var p = $(this).parents('.ajax_charge'),
        t = $(p).find(".ftype a.on").data('type'),
        m = $(p).find("input[name='money']").val();

    if(t=="charge"){
        var zhifu='weixin';
        layer.open({
            content: '请选择支付方式？',
            btn: ['支付宝', '微信'],
            yes: function(index) {
                layer.close(index);
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: "/Home/Select/ajax_charge",
                    data: {
                        type: t,
                        money: m,
                        'zhifufanshi':"zhifubao"
                    },
                    success: function(res) {
                        zy.msg(res.info);
                        if(res.status == 0){
                            setTimeout(function(){
                                location.href = '/Home/Select/toprecord.html';
                            },2000);
                        }else {
                            setTimeout(function(){
                                location.href = res.url;
                            },2000);
                        }
                        // location.reload()
                    },
                    error: function() {}
                })
            },
            no: function(index) {
                layer.close(index);
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: "/Home/Select/ajax_charge",
                    data: {
                        type: t,
                        money: m,
                        'zhifufanshi':zhifu
                    },
                    success: function(res) {
                        zy.msg(res.info);
                        if(res.status == 0){
                            setTimeout(function(){
                                location.href = '/Home/Select/toprecord.html';
                            },2000);
                        }else {
                            setTimeout(function(){
                                location.href = res.url;
                            },2000);
                        }
                        // location.reload()
                    },
                    error: function() {}
                })
            }
        })
    }else {
        layer.open({
            content: '确定要提交吗？',
            btn: ['确定', '取消'],
            yes: function(index) {
                layer.close(index);
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: "/Home/Select/ajax_charge",
                    data: {
                        type: t,
                        money: m,
                    },
                    success: function(res) {
                        zy.msg(res.info);
                        if(res.status == 0){
                            setTimeout(function(){
                                location.href = '/Home/Select/toprecord.html';
                            },2000);
                        }else {
                            setTimeout(function(){
                                location.href = res.url;
                            },2000);
                        }
                        // location.reload()
                    },
                    error: function() {}
                })
            }
        })
    }

});
$(document).on("click", ".ajax_form_submit", function() {
    var a = $(this).data();
    layer.open({
        content: a.msg || '确定要提交吗？',
        btn: ['确定', '取消'],
        yes: function(index) {
            layer.close(index);
            $.ajax({
                type: "post",
                url: a.uri,
                data: $(a.form).serialize(),
                success: function(res) {
                    layer.closeAll();
                    layer.open({
                        content: res.info,
                        skin: 'msg',
                        time: 2
                    })
                },
                error: function() {
                    layer.open({
                        content: '网络连接失败',
                        skin: 'msg',
                        time: 2
                    })
                }
            })
        }
    })
});
$(document).on("click", ".agent_config .submit", function() {
    var that = $(this),
        ratio = $(that).parents('.agent_config').find('.ratio').val() * 1.0;
    if (ratio < 0 || ratio > 100) {
        return layer.open({
            content: '数字错误，请设置0~100',
            skin: 'msg',
            time: 2
        })
    }
    layer.open({
        content: '确定要保存吗？',
        btn: ['确定', '取消'],
        yes: function(index) {
            layer.close(index);
            $.ajax({
                type: "post",
                url: $(that).data('uri'),
                data: {
                    ratio: ratio
                },
                success: function(res) {
                    layer.open({
                        content: res.info,
                        skin: 'msg',
                        time: 2
                    });
                    location.reload()
                }
            })
        }
    })
});
$(document).on("click", ".close_layer", function() {
    layer.closeAll()
});
$(document).on("click", ".ajax_uri", function() {
    var uri = $(this).data('uri');
    if (!uri) return;
    location.href = uri
});
$(document).on("click", ".ajax_page", function() {
    var uri = $(this).data('uri');
    if (!uri) return;
    data = $(this).data();
    delete data.uri;
    $.ajax({
        type: "get",
        url: uri,
        data: data,
        success: function(html) {
            $("#ajax_container").html(html).addClass("on");
            location.href = "#ajax_page"
        },
        error: function() {}
    })
});
$(document).on("click", "#ajax_container .back", function() {
    $("#ajax_container").removeClass("on");
    location.href = "#"
});
window.onhashchange = function() {
    if (location.href.indexOf("#win") == -1) {
        layer.closeAll()
    };
    if (location.href.indexOf("#menu") == -1) {
        $("#ss_menu").hide()
    };
    if (location.href.indexOf("#ajax_page") == -1) {
        $("#ajax_container,.win-show").removeClass("on")
    };
    if (location.href.indexOf("#confirm") == -1) {
        $("#touzhu").removeClass("on")
    }
};