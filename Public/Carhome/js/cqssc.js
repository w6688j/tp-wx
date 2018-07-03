function show_v_kb() {
    if (udata.status != 0) return;
    if (udata.kt == 1) return zy.show_v_kb2();
    zy.kb0init();
    var html = '',
        kn = 0,
        css = '',
        kbs = lang_cfg.keybord.split('');
    html = "<em>1</em><em>2</em><em>3</em><em>4</em>";
    for (var i = 0; i < kbs.length; i++) {
        if (chk_ks(kbs[i])) {
            kn++, css = '';
            if (kbs[i] == "豹") kbs[i] = "豹子", css = ' class=k2';
            if (kbs[i] == "顺") kbs[i] = "顺子", css = ' class=k2';
            if (kbs[i] == "半") kbs[i] = "半顺", css = ' class=k2';
            if (kbs[i] == "对") kbs[i] = "对子", css = ' class=k2';
            if (kbs[i] == "杂") kbs[i] = "杂六", css = ' class=k2';
            html += "<em" + css + ">" + kbs[i] + "</em>";
            if (kn == 4) html += "<em>5</em><em>6</em><em>7</em><em>8</em>";
            if (kn == 8) html += "<em>9</em><em>0</em>"
        }
    }
    html += "<em class='c2'>" + lang_cfg.send + "</em>";
    html += "<em class='c'>清</em>";
    html += "<em class='c'>←</em>";
    html += "<i class='kclose iconfont'>&#xe60d;</i>";
    $("#keybord_div").html(html).show();
    zy.kb0end()
}
function chk_ks(k) {
    switch (k) {
        case "大":
        case "小":
        case "单":
        case "双":
            return in_array("1", tz_types);
            break;
        case "前":
        case "中":
        case "后":
        case "豹":
        case "顺":
        case "对":
        case "半":
        case "杂":
            return in_array("3", tz_types);
            break;
        case "龙":
        case "虎":
        case "和":
            return in_array("4", tz_types);
            break;
        case "特":
            return in_array("5", tz_types) || in_array("5", tz_types);
            break;
        case "A":
        case "B":
        case "C":
            return in_array("6", tz_types);
            break;
        default:
            return !0
    }
}