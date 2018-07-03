function show_lottery_data(str) {
    if (!str) return;
    $("#tables").html('<tr><th class=th_1>' + lang_cfg.qihao + '</th><th class="th_2 no">' + lang_cfg.no + '<br><i>1</i><i>2</i><i>3</i><i>4</i><i>5</i><i>6</i><i>7</i><i>8</i><i>9</i><i>10</i></th><th class="th_3 lh">' + lang_cfg.lh + '<br><i>1</i><i>2</i><i>3</i><i>4</i><i>5</i></th><th class=th_4>' + lang_cfg.tm + '</th></tr>');
    for (obj in str) {
        var f = str[obj].numbers.split(',');
        var lh = str[obj].lh.split('');
        var tm = str[obj].tm.split(',');
        var html = "<tr><td class='qihao'>" + str[obj].issue + "</td>";
        html += "<td class='no'>";
        for (of in f) {
            html += "<img src='" + site_url + img_path + "s" + (f[of] * 1) + ".png'>"
        }
        html += "</td><td class='lh'>";
        for (of in lh) {
            if (lh[of] == "龙" || lh[of] == '龍') {
                html += "<img src='" + site_url + img_path + "lh1.png'>"
            } else {
                html += "<img src='" + site_url + img_path + "lh2.png'>"
            }
        }
        html += "</td><td class='tm'>";
        html += "<img src='" + site_url + img_path + "qd" + tm[0] + ".png'>";
        html += "<img src='" + site_url + img_path + "dx" + (tm[1] == "大" ? 1 : 2) + ".png'>";
        if (tm[2] == "双" || tm[2] == "雙") {
            html += "<img src='" + site_url + img_path + "ds1.png'>"
        } else {
            html += "<img src='" + site_url + img_path + "ds2.png'>"
        }
        if (tm[3] == "庄" || tm[3] == "莊") {
            html += "<img src='" + site_url + img_path + "zx1.png'>"
        } else {
            html += "<img src='" + site_url + img_path + "zx2.png'>"
        }
        html += "<img src='" + site_url + img_path + "qd" + tm[4] + ".png'>";
        html += "</td></tr>";
        $("#tables").append(html)
    }
}
function show_lottery_cl(str) {
    if (!str) return;
    var head = '<tr><th class=th_cl_0>' + lang_cfg.pmin + '</td><th class=th_cl_1>' + lang_cfg.cdao + '</th><th class=th_cl_2>' + lang_cfg.jguo + '</th><th class=th_cl_3>' + lang_cfg.lqi + '</th></tr>';
    $("#changlong1").html(head);
    $("#changlong2").html(head);
    $("#changlong3").html(head);
    var i = 0;
    for (i in str) {
        var html = "<tr>";
        html += "<td class=cl_no>" + (i * 1 + 1) + "</td>";
        html += "<td class=cl_img><img src='" + site_url + img_path + "s" + (str[i].line) + ".png'></td>";
        switch (str[i].val) {
            case "大":
                html += "<td class=cl_img><img src='" + site_url + img_path + "dx1.png'></td>";
                break;
            case "小":
                html += "<td class=cl_img><img src='" + site_url + img_path + "dx2.png'></td>";
                break;
            case "双":
                html += "<td class=cl_img><img src='" + site_url + img_path + "ds1.png'></td>";
                break;
            case "单":
                html += "<td class=cl_img><img src='" + site_url + img_path + "ds2.png'></td>";
                break;
            case "龙":
                html += "<td class=cl_img><img src='" + site_url + img_path + "lh1.png'></td>";
                break;
            case "虎":
                html += "<td class=cl_img><img src='" + site_url + img_path + "lh2.png'></td>";
                break;
            case "庄":
                html += "<td class=cl_img><img src='" + site_url + img_path + "zx1.png'></td>";
                break;
            case "闲":
                html += "<td class=cl_img><img src='" + site_url + img_path + "zx2.png'></td>";
                break;
            default:
                html += "<td class='cl_txt'>" + str[i].val + "</td>";
                break
        }
        html += "<td class='cl_txt'>" + str[i].num + "<font>期</font></td>";
        html += "</tr>";
        if (i < 10) {
            $("#changlong1").append(html)
        } else if (i < 20) {
            $("#changlong2").append(html)
        } else {
            $("#changlong3").append(html)
        }
    }
}
function show_v_kb() {
    if (udata.status != 0) return;
    if (udata.kt == 1) return zy.show_v_kb2();
    zy.kb0init();
    var html = '',
        kn = 0,
        kbs = lang_cfg.keybord.split('');
    html = "<em>1</em><em>2</em><em>3</em><em>4</em>";
    for (var i = 0; i < kbs.length; i++) {
        if (chk_ks(kbs[i])) {
            kn++, html += "<em>" + kbs[i] + "</em>";
            if (kn == 4) html += "<em>5</em><em>6</em><em>7</em><em>8</em>";
            if (kn == 8) html += "<em>9</em><em>0</em>"
        }
    }
    html += "<em class='c2'>" + lang_cfg.send + "</em>";
    html += "<em class='c'>清</em>";
    html += "<em class='c'>←</em>";
    html += "<i class='kclose iconfont'>&#xe60d;</i>";
    $("#keybord_div").html(html).show(), zy.kb0end()
};

function chk_ks(k) {
    switch (k) {
        case "大":
        case "小":
        case "单":
        case "双":
            return in_array("1", tz_types);
            break;
        case "龙":
        case "虎":
            return in_array("4", tz_types);
            break;
        case "庄":
        case "闲":
            return in_array("5", tz_types);
            break;
        case "组":
        case "-":
            return in_array("6", tz_types);
            break;
        case "和":
        case "特":
        case ".":
            return in_array("7", tz_types) || in_array("8", tz_types);
            break;
        case "A":
        case "B":
        case "C":
            return in_array("9", tz_types);
            break;
        case "非":
            return in_array("10", tz_types);
            break;
        default:
            return !0
    }
}