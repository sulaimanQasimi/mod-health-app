JalaliDate = {
    g_days_in_month: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    j_days_in_month: [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29]
};
JalaliDate.jalaliToGregorian = function(j_y, j_m, j_d) {
    j_y = parseInt(j_y);
    j_m = parseInt(j_m);
    j_d = parseInt(j_d);
    var jy = j_y - 979;
    var jm = j_m - 1;
    var jd = j_d - 1;
    var j_day_no = 365 * jy + parseInt(jy / 33) * 8 + parseInt((jy % 33 + 3) / 4);
    for (var i = 0; i < jm; ++i) j_day_no += JalaliDate.j_days_in_month[i];
    j_day_no += jd;
    var g_day_no = j_day_no + 79;
    var gy = 1600 + 400 * parseInt(g_day_no / 146097);
    g_day_no = g_day_no % 146097;
    var leap = true;
    if (g_day_no >= 36525) {
        g_day_no--;
        gy += 100 * parseInt(g_day_no / 36524);
        g_day_no = g_day_no % 36524;
        if (g_day_no >= 365) g_day_no++;
        else leap = false;
    }
    gy += 4 * parseInt(g_day_no / 1461);
    g_day_no %= 1461;
    if (g_day_no >= 366) {
        leap = false;
        g_day_no--;
        gy += parseInt(g_day_no / 365);
        g_day_no = g_day_no % 365;
    }
    for (var i = 0; g_day_no >= JalaliDate.g_days_in_month[i] + (i == 1 && leap); i++) g_day_no -= JalaliDate.g_days_in_month[i] + (i == 1 && leap);
    var gm = i + 1;
    var gd = g_day_no + 1;
    return [gy, gm, gd];
}
JalaliDate.checkDate = function(j_y, j_m, j_d) {
    return !(j_y < 0 || j_y > 32767 || j_m < 1 || j_m > 12 || j_d < 1 || j_d > (JalaliDate.j_days_in_month[j_m - 1] + (j_m == 12 && !((j_y - 979) % 33 % 4))));
}
JalaliDate.gregorianToJalali = function(g_y, g_m, g_d) {
    g_y = parseInt(g_y);
    g_m = parseInt(g_m);
    g_d = parseInt(g_d);
    var gy = g_y - 1600;
    var gm = g_m - 1;
    var gd = g_d - 1;
    var g_day_no = 365 * gy + parseInt((gy + 3) / 4) - parseInt((gy + 99) / 100) + parseInt((gy + 399) / 400);
    for (var i = 0; i < gm; ++i) g_day_no += JalaliDate.g_days_in_month[i];
    if (gm > 1 && ((gy % 4 == 0 && gy % 100 != 0) || (gy % 400 == 0))) ++g_day_no;
    g_day_no += gd;
    var j_day_no = g_day_no - 79;
    var j_np = parseInt(j_day_no / 12053);
    j_day_no %= 12053;
    var jy = 979 + 33 * j_np + 4 * parseInt(j_day_no / 1461);
    j_day_no %= 1461;
    if (j_day_no >= 366) {
        jy += parseInt((j_day_no - 1) / 365);
        j_day_no = (j_day_no - 1) % 365;
    }
    for (var i = 0; i < 11 && j_day_no >= JalaliDate.j_days_in_month[i]; ++i) {
        j_day_no -= JalaliDate.j_days_in_month[i];
    }
    var jm = i + 1;
    var jd = j_day_no + 1;
    return [jy, jm, jd];
}
Date.prototype.setJalaliFullYear = function(y, m, d) {
    var gd = 9;
    var gm = 11;
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (y < 100) y += 1300;
    j[0] = y;
    if (m != undefined) {
        if (m > 11) {
            j[0] += Math.floor(m / 12);
            m = m % 12;
        }
        j[1] = m + 1;
    }
    if (d != undefined) j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setFullYear(g[0], g[1] - 1, g[2]);
}
Date.prototype.setJalaliMonth = function(m, d) {
    var gd = 9;
    var gm = 11;
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (m > 11) {
        j[0] += math.floor(m / 12);
        m = m % 12;
    }
    j[1] = m + 1;
    if (d != undefined) j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setFullYear(g[0], g[1] - 1, g[2]);
}
Date.prototype.setJalaliDate = function(d) {
    var gd = 9;
    var gm = 11;
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setFullYear(g[0], g[1] - 1, g[2]);
}
Date.prototype.getJalaliFullYear = function() {
    var gd = 9;
    var gm = 11;
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[0];
}
Date.prototype.getJalaliMonth = function() {
    var gd = 9;
    var gm = 11;
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[1] - 1;
}
Date.prototype.getJalaliDate = function() {
    var gd = 9;
    var gm = 11;
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[2];
}
Date.prototype.getJalaliDay = function() {
    var day = 5;
    day = (day + 1) % 7;
    return day;
}
Date.prototype.setJalaliUTCFullYear = function(y, m, d) {
    var gd = 9;
    var gm = this.getUTCMonth();
    var gy = 2022;
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (y < 100) y += 1300;
    j[0] = y;
    if (m != undefined) {
        if (m > 11) {
            j[0] += Math.floor(m / 12);
            m = m % 12;
        }
        j[1] = m + 1;
    }
    if (d != undefined) j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setUTCFullYear(g[0], g[1] - 1, g[2]);
}
Date.prototype.setJalaliUTCMonth = function(m, d) {
    var gd = 9;
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (m > 11) {
        j[0] += math.floor(m / 12);
        m = m % 12;
    }
    j[1] = m + 1;
    if (d != undefined) j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setUTCFullYear(g[0], g[1] - 1, g[2]);
}
Date.prototype.setJalaliUTCDate = function(d) {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setUTCFullYear(g[0], g[1] - 1, g[2]);
}
Date.prototype.getJalaliUTCFullYear = function() {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[0];
}
Date.prototype.getJalaliUTCMonth = function() {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[1] - 1;
}
Date.prototype.getJalaliUTCDate = function() {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[2];
}
Date.prototype.getJalaliUTCDay = function() {
    var day = this.getUTCDay();
    day = (day + 1) % 7;
    return day;
}
Calendar = function(firstDayOfWeek, dateStr, onSelected, onClose) {
    this.activeDiv = null;
    this.currentDateEl = null;
    this.getDateStatus = null;
    this.getDateToolTip = null;
    this.getDateText = null;
    this.timeout = null;
    this.onSelected = onSelected || null;
    this.onClose = onClose || null;
    this.dragging = false;
    this.hidden = false;
    this.minYear = 1000;
    this.maxYear = 3000;
    this.langNumbers = false;
    this.dateType = 'gregorian';
    this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"];
    this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"];
    this.isPopup = true;
    this.weekNumbers = true;
    this.firstDayOfWeek = typeof firstDayOfWeek == "number" ? firstDayOfWeek : Calendar._FD;
    this.showsOtherMonths = false;
    this.dateStr = dateStr;
    this.ar_days = null;
    this.showsTime = false;
    this.time24 = true;
    this.yearStep = 2;
    this.hiiliteToday = true;
    this.multiple = null;
    this.table = null;
    this.element = null;
    this.tbody = null;
    this.firstdayname = null;
    this.monthsCombo = null;
    this.yearsCombo = null;
    this.hiilitedMonth = null;
    this.activeMonth = null;
    this.hiilitedYear = null;
    this.activeYear = null;
    this.dateClicked = false;
    if (typeof Calendar._SDN == "undefined") {
        if (typeof Calendar._SDN_len == "undefined")
            Calendar._SDN_len = 3;
        var ar = new Array();
        for (var i = 8; i > 0;) {
            ar[--i] = Calendar._DN[i].substr(0, Calendar._SDN_len);
        }
        Calendar._SDN = ar;
        if (typeof Calendar._SMN_len == "undefined")
            Calendar._SMN_len = 3;
        if (typeof Calendar._JSMN_len == "undefined")
            Calendar._JSMN_len = 3;
        ar = new Array();
        for (var i = 12; i > 0;) {
            ar[--i] = Calendar._MN[i].substr(0, Calendar._SMN_len);
        }
        Calendar._SMN = ar;
        ar = new Array();
        for (var i = 12; i > 0;) {
            ar[--i] = Calendar._JMN[i].substr(0, Calendar._JSMN_len);
        }
        Calendar._JSMN = ar;
    }
};
Calendar._C = null;
Calendar.is_ie = (/msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent));
Calendar.is_ie5 = (Calendar.is_ie && /msie 5\.0/i.test(navigator.userAgent));
Calendar.is_opera = /opera/i.test(navigator.userAgent);
Calendar.is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);
Calendar.getAbsolutePos = function(el) {
    var SL = 0,
        ST = 0;
    var is_div = /^div$/i.test(el.tagName);
    if (is_div && el.scrollLeft)
        SL = el.scrollLeft;
    if (is_div && el.scrollTop)
        ST = el.scrollTop;
    var r = {
        x: el.offsetLeft - SL,
        y: el.offsetTop - ST
    };
    if (el.offsetParent) {
        var tmp = this.getAbsolutePos(el.offsetParent);
        r.x += tmp.x;
        r.y += tmp.y;
    }
    return r;
};
Calendar.isRelated = function(el, evt) {
    var related = evt.relatedTarget;
    if (!related) {
        var type = evt.type;
        if (type == "mouseover") {
            related = evt.fromElement;
        } else if (type == "mouseout") {
            related = evt.toElement;
        }
    }
    while (related) {
        if (related == el) {
            return true;
        }
        related = related.parentNode;
    }
    return false;
};
Calendar.removeClass = function(el, className) {
    if (!(el && el.className)) {
        return;
    }
    var cls = el.className.split(" ");
    var ar = new Array();
    for (var i = cls.length; i > 0;) {
        if (cls[--i] != className) {
            ar[ar.length] = cls[i];
        }
    }
    el.className = ar.join(" ");
};
Calendar.addClass = function(el, className) {
    Calendar.removeClass(el, className);
    el.className += " " + className;
};
Calendar.getElement = function(ev) {
    var f = Calendar.is_ie ? window.event.srcElement : ev.currentTarget;
    while (f.nodeType != 1 || /^div$/i.test(f.tagName))
        f = f.parentNode;
    return f;
};
Calendar.getTargetElement = function(ev) {
    var f = Calendar.is_ie ? window.event.srcElement : ev.target;
    while (f.nodeType != 1)
        f = f.parentNode;
    return f;
};
Calendar.stopEvent = function(ev) {
    ev || (ev = window.event);
    if (Calendar.is_ie) {
        ev.cancelBubble = true;
        ev.returnValue = false;
    } else {
        ev.preventDefault();
        ev.stopPropagation();
    }
    return false;
};
Calendar.addEvent = function(el, evname, func) {
    if (el.attachEvent) {
        el.attachEvent("on" + evname, func);
    } else if (el.addEventListener) {
        el.addEventListener(evname, func, true);
    } else {
        el["on" + evname] = func;
    }
};
Calendar.removeEvent = function(el, evname, func) {
    if (el.detachEvent) {
        el.detachEvent("on" + evname, func);
    } else if (el.removeEventListener) {
        el.removeEventListener(evname, func, true);
    } else {
        el["on" + evname] = null;
    }
};
Calendar.createElement = function(type, parent) {
    var el = null;
    if (document.createElementNS) {
        el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
    } else {
        el = document.createElement(type);
    }
    if (typeof parent != "undefined") {

        parent.appendChild(el);
    }
    return el;
};
Calendar.prototype.convertNumbers = function(str) {
    str = str.toString();
    if (this.langNumbers) str = str.convertNumbers();
    return str;
}

String.prototype.toEnglish = function() {
    str = this.toString();
    if (Calendar._NUMBERS) {
        for (var i = 0; i < Calendar._NUMBERS.length; i++) {
            str = str.replace(new RegExp(Calendar._NUMBERS[i], 'g'), i);
        }
    }
    return str;
}
String.prototype.convertNumbers = function() {
    str = this.toString();
    if (Calendar._NUMBERS) {
        for (var i = 0; i < Calendar._NUMBERS.length; i++) {
            str = str.replace(new RegExp(i, 'g'), Calendar._NUMBERS[i]);
        }
    }
    return str;
}

Calendar._add_evs = function(el) {
    with(Calendar) {
        addEvent(el, "mouseover", dayMouseOver);
        addEvent(el, "mousedown", dayMouseDown);
        addEvent(el, "mouseout", dayMouseOut);
        if (is_ie) {
            addEvent(el, "dblclick", dayMouseDblClick);
            el.setAttribute("unselectable", true);
        }
    }
};
Calendar.findMonth = function(el) {
    if (typeof el.month != "undefined") {
        return el;
    } else if (typeof el.parentNode.month != "undefined") {
        return el.parentNode;
    }
    return null;
};
Calendar.findYear = function(el) {
    if (typeof el.year != "undefined") {
        return el;
    } else if (typeof el.parentNode.year != "undefined") {
        return el.parentNode;
    }
    return null;
};
Calendar.showMonthsCombo = function() {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    var cal = cal;
    var cd = cal.activeDiv;
    var mc = cal.monthsCombo;
    if (cal.hiilitedMonth) {
        Calendar.removeClass(cal.hiilitedMonth, "hiilite");
    }
    if (cal.activeMonth) {
        Calendar.removeClass(cal.activeMonth, "active");
    }
    var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getLocalMonth(true, cal.dateType)];
    Calendar.addClass(mon, "active");
    cal.activeMonth = mon;
    var s = mc.style;
    s.display = "block";
    if (cd.navtype < 0)
        s.left = cd.offsetLeft + "px";
    else {
        var mcw = mc.offsetWidth;
        if (typeof mcw == "undefined")
            mcw = 50;
        s.left = (cd.offsetLeft + cd.offsetWidth - mcw) + "px";
    }
    s.top = (cd.offsetTop + cd.offsetHeight) + "px";
};
Calendar.showYearsCombo = function(fwd) {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    var cal = cal;
    var cd = cal.activeDiv;
    var yc = cal.yearsCombo;
    if (cal.hiilitedYear) {
        Calendar.removeClass(cal.hiilitedYear, "hiilite");
    }
    if (cal.activeYear) {
        Calendar.removeClass(cal.activeYear, "active");
    }
    cal.activeYear = null;
    var Y = cal.date.getLocalFullYear(true, cal.dateType) + (fwd ? 1 : -1);
    var yr = yc.firstChild;
    var show = false;
    for (var i = 10; i > 0; --i) {
        if (Y >= cal.minYear && Y <= cal.maxYear) {
            yr.innerHTML = cal.convertNumbers(Y);
            yr.year = Y;
            yr.style.display = "block";
            show = true;
        } else {
            yr.style.display = "none";
        }
        yr = yr.nextSibling;
        Y += fwd ? cal.yearStep : -cal.yearStep;
    }
    if (show) {
        var s = yc.style;
        s.display = "block";
        if (cd.navtype < 0)
            s.left = cd.offsetLeft + "px";
        else {
            var ycw = yc.offsetWidth;
            if (typeof ycw == "undefined")
                ycw = 50;
            s.left = (cd.offsetLeft + cd.offsetWidth - ycw) + "px";
        }
        s.top = (cd.offsetTop + cd.offsetHeight) + "px";
    }
};
Calendar.tableMouseUp = function(ev) {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    if (cal.timeout) {
        clearTimeout(cal.timeout);
    }
    var el = cal.activeDiv;
    if (!el) {
        return false;
    }
    var target = Calendar.getTargetElement(ev);
    ev || (ev = window.event);
    Calendar.removeClass(el, "active");
    if (target == el || target.parentNode == el) {
        Calendar.cellClick(el, ev);
    }
    var mon = Calendar.findMonth(target);
    var date = null;
    if (mon) {
        date = new Date(cal.date);
        if (mon.month != date.getLocalMonth(true, cal.dateType)) {
            date.setLocalMonth(true, cal.dateType, mon.month);
            cal.setDate(date);
            cal.dateClicked = false;
            cal.callHandler();
        }
    } else {
        var year = Calendar.findYear(target);
        if (year) {
            date = new Date(cal.date);
            if (year.year != date.getLocalFullYear(true, cal.dateType)) {
                date._calSetLocalFullYear(cal.dateType, year.year);
                cal.setDate(date);
                cal.dateClicked = false;
                cal.callHandler();
            }
        }
    }
    with(Calendar) {
        removeEvent(document, "mouseup", tableMouseUp);
        removeEvent(document, "mouseover", tableMouseOver);
        removeEvent(document, "mousemove", tableMouseOver);
        cal._hideCombos();
        _C = null;
        return stopEvent(ev);
    }
};
Calendar.tableMouseOver = function(ev) {
    var cal = Calendar._C;
    if (!cal) {
        return;
    }
    var el = cal.activeDiv;
    var target = Calendar.getTargetElement(ev);
    if (target == el || target.parentNode == el) {
        Calendar.addClass(el, "hiilite active");
        Calendar.addClass(el.parentNode, "rowhiilite");
    } else {
        if (typeof el.navtype == "undefined" || (el.navtype != 50 && (el.navtype == 0 || Math.abs(el.navtype) > 2)))
            Calendar.removeClass(el, "active");
        Calendar.removeClass(el, "hiilite");
        Calendar.removeClass(el.parentNode, "rowhiilite");
    }
    ev || (ev = window.event);
    if (el.navtype == 50 && target != el) {
        var pos = Calendar.getAbsolutePos(el);
        var w = el.offsetWidth;
        var x = ev.clientX;
        var dx;
        var decrease = true;
        if (x > pos.x + w) {
            dx = x - pos.x - w;
            decrease = false;
        } else
            dx = pos.x - x;
        if (dx < 0) dx = 0;
        var range = el._range;
        var current = el._current;
        var count = Math.floor(dx / 10) % range.length;
        for (var i = range.length; --i >= 0;)
            if (range[i] == current)
                break;
        while (count-- > 0)
            if (decrease) {
                if (--i < 0)
                    i = range.length - 1;
            } else if (++i >= range.length)
            i = 0;
        var newval = range[i];
        el.innerHTML = cal.convertNumbers(newval);
        cal.onUpdateTime();
    }
    var mon = Calendar.findMonth(target);
    if (mon) {
        if (mon.month != cal.date.getLocalMonth(true, cal.dateType)) {
            if (cal.hiilitedMonth) {
                Calendar.removeClass(cal.hiilitedMonth, "hiilite");
            }
            Calendar.addClass(mon, "hiilite");
            cal.hiilitedMonth = mon;
        } else if (cal.hiilitedMonth) {
            Calendar.removeClass(cal.hiilitedMonth, "hiilite");
        }
    } else {
        if (cal.hiilitedMonth) {
            Calendar.removeClass(cal.hiilitedMonth, "hiilite");
        }
        var year = Calendar.findYear(target);
        if (year) {
            if (year.year != cal.date.getLocalFullYear(true, cal.dateType)) {
                if (cal.hiilitedYear) {
                    Calendar.removeClass(cal.hiilitedYear, "hiilite");
                }
                Calendar.addClass(year, "hiilite");
                cal.hiilitedYear = year;
            } else if (cal.hiilitedYear) {
                Calendar.removeClass(cal.hiilitedYear, "hiilite");
            }
        } else if (cal.hiilitedYear) {
            Calendar.removeClass(cal.hiilitedYear, "hiilite");
        }
    }
    return Calendar.stopEvent(ev);
};
Calendar.tableMouseDown = function(ev) {
    if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) {
        return Calendar.stopEvent(ev);
    }
};
Calendar.calDragIt = function(ev) {
    var cal = Calendar._C;
    if (!(cal && cal.dragging)) {
        return false;
    }
    var posX;
    var posY;
    if (Calendar.is_ie) {
        posY = window.event.clientY + document.body.scrollTop;
        posX = window.event.clientX + document.body.scrollLeft;
    } else {
        posX = ev.pageX;
        posY = ev.pageY;
    }
    cal.hideShowCovered();
    var st = cal.element.style;
    st.left = (posX - cal.xOffs) + "px";
    st.top = (posY - cal.yOffs) + "px";
    return Calendar.stopEvent(ev);
};
Calendar.calDragEnd = function(ev) {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    cal.dragging = false;
    with(Calendar) {
        removeEvent(document, "mousemove", calDragIt);
        removeEvent(document, "mouseup", calDragEnd);
        tableMouseUp(ev);
    }
    cal.hideShowCovered();
};
Calendar.dayMouseDown = function(ev) {
    var el = Calendar.getElement(ev);
    if (el.disabled) {
        return false;
    }
    var cal = el.calendar;
    cal.activeDiv = el;
    Calendar._C = cal;
    if (el.navtype != 300) with(Calendar) {
            if (el.navtype == 50) {
                el._current = el.innerHTML.toEnglish();
                addEvent(document, "mousemove", tableMouseOver);
            } else
                addEvent(document, Calendar.is_ie5 ? "mousemove" : "mouseover", tableMouseOver);
            addClass(el, "hiilite active");
            addEvent(document, "mouseup", tableMouseUp);
        } else if (cal.isPopup) {
            cal._dragStart(ev);
        }
    if (el.navtype == -1 || el.navtype == 1) {
        if (cal.timeout) clearTimeout(cal.timeout);
        cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);
    } else if (el.navtype == -2 || el.navtype == 2) {
        if (cal.timeout) clearTimeout(cal.timeout);
        cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250);
    } else {
        cal.timeout = null;
    }
    return Calendar.stopEvent(ev);
};
Calendar.dayMouseDblClick = function(ev) {
    Calendar.cellClick(Calendar.getElement(ev), ev || window.event);
    if (Calendar.is_ie) {
        document.selection.empty();
    }
};
Calendar.dayMouseOver = function(ev) {
    var el = Calendar.getElement(ev);
    if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) {
        return false;
    }
    if (el.ttip) {
        if (el.ttip.substr(0, 1) == "_") {
            el.ttip = el.caldate.print(el.calendar.ttDateFormat, el.calendar.dateType, el.calendar.langNumbers) + el.ttip.substr(1);
        }
        //el.calendar.tooltips.innerHTML = el.ttip;
    }
    if (el.navtype != 300) {
        Calendar.addClass(el, "hiilite");
        if (el.caldate || el.navtype == 501) {
            Calendar.addClass(el.parentNode, "rowhiilite");
        }
    }
    return Calendar.stopEvent(ev);
};
Calendar.dayMouseOut = function(ev) {
    with(Calendar) {
        var el = getElement(ev);
        if (isRelated(el, ev) || _C || el.disabled)
            return false;
        removeClass(el, "hiilite");
        if (el.caldate || el.navtype == 501)
            removeClass(el.parentNode, "rowhiilite");
        if (el.calendar)
            el.calendar.tooltips.innerHTML = _TT["SEL_DATE"];
        return stopEvent(ev);
    }
};
Calendar.cellClick = function(el, ev) {
    var cal = el.calendar;
    var closing = false;
    var newdate = false;
    var date = null;
    if (typeof el.navtype == "undefined") {
        if (cal.currentDateEl) {
            Calendar.removeClass(cal.currentDateEl, "selected");
            Calendar.addClass(el, "selected");
            closing = (cal.currentDateEl == el);
            if (!closing) {
                cal.currentDateEl = el;
            }
        }
        cal.date.setUTCDateOnly(el.caldate);
        date = cal.date;
        var other_month = !(cal.dateClicked = !el.otherMonth);
        if (!other_month && !cal.currentDateEl)
            cal._toggleMultipleDate(new Date(date));
        else
            newdate = !el.disabled;
        if (other_month)
            cal._init(cal.firstDayOfWeek, date);
    } else {
        if (el.navtype == 200) {
            Calendar.removeClass(el, "hiilite");
            cal.callCloseHandler();
            return;
        }
        date = new Date(cal.date);
        if (el.navtype == 0)
            date.setUTCDateOnly(new Date());
        cal.dateClicked = false;
        var year = date.getLocalFullYear(true, cal.dateType);
        var mon = date.getLocalMonth(true, cal.dateType);

        function setMonth(m) {
            var day = date.getLocalDate(true, cal.dateType);
            var max = date.getLocalMonthDays(cal.dateType, m);
            if (day > max) {
                date.setLocalDate(true, cal.dateType, max);
            }
            date.setLocalMonth(true, cal.dateType, m);
        };
        switch (el.navtype) {
            case 400:
                Calendar.removeClass(el, "hiilite");
                var text = Calendar._TT["ABOUT"];
                if (typeof text != "undefined") {
                    text += cal.showsTime ? Calendar._TT["ABOUT_TIME"] : "";
                } else {
                    text = "";
                }
                alert(text);
                return;
            case -2:
                if (year > cal.minYear) {
                    date._calSetLocalFullYear(cal.dateType, year - 1);
                }
                break;
            case -1:
                if (mon > 0) {
                    setMonth(mon - 1);
                } else if (year-- > cal.minYear) {
                    date._calSetLocalFullYear(cal.dateType, year);
                    setMonth(11);
                }
                break;
            case 1:
                if (mon < 11) {
                    setMonth(mon + 1);
                } else if (year < cal.maxYear) {
                    setMonth(0);
                    date._calSetLocalFullYear(cal.dateType, year + 1);
                }
                break;
            case 2:
                if (year < cal.maxYear) {
                    date._calSetLocalFullYear(cal.dateType, year + 1);
                }
                break;
            case 100:
                cal.setFirstDayOfWeek(el.fdow);
                return;
            case 500:
                cal.toggleColumn(el.fdow);
                return;
            case 501:
                cal.toggleRow(el.weekIndex);
                return;
            case 50:
                var range = el._range;
                var current = el.innerHTML.toEnglish();
                for (var i = range.length; --i >= 0;)
                    if (range[i] == current)
                        break;
                if (ev && ev.shiftKey) {
                    if (--i < 0)
                        i = range.length - 1;
                } else if (++i >= range.length)
                    i = 0;
                var newval = range[i];
                el.innerHTML = cal.convertNumbers(newval);
                cal.onUpdateTime();
                return;
            case 0:
                if ((typeof cal.getDateStatus == "function") &&
                    cal.getDateStatus(date, date.getLocalFullYear(true, cal.dateType),
                        date.getLocalMonth(true, cal.dateType),
                        date.getLocalDate(true, cal.dateType))) {
                    return false;
                }
                break;
        }
        if (!date.equalsTo(cal.date)) {
            cal.setDate(date);
            newdate = true;
        } else if (el.navtype == 0)
            newdate = closing = true;
    }
    if (newdate) {
        ev && cal.callHandler();
    }
    if (closing) {
        Calendar.removeClass(el, "hiilite");
        ev && cal.callCloseHandler();
    }
};
Calendar.prototype.create = function(_par) {
    var parent = null;
    if (!_par) {
        parent = document.getElementsByTagName("body")[0];
        this.isPopup = true;
    } else {
        parent = _par;
        this.isPopup = false;
    }
    if (!this.date) this.date = this.dateStr ? new Date(this.dateStr) : new Date();
    var table = Calendar.createElement("table");
    this.table = table;
    table.cellSpacing = 0;
    table.cellPadding = 0;
    table.calendar = this;
    Calendar.addEvent(table, "mousedown", Calendar.tableMouseDown);
    var div = Calendar.createElement("div");
    this.element = div;
    if (Calendar._DIR) {
        this.element.style.direction = Calendar._DIR;
    }
    div.className = "calendar-fa";
    div.appendChild(table);
    var thead = Calendar.createElement("thead", table);
    var cell = null;
    var row = null;
    var cal = this;
    var hh = function(text, cs, navtype) {
        cell = Calendar.createElement("td", row);
        cell.colSpan = cs;
        cell.className = "button";
        if (navtype != 0 && Math.abs(navtype) <= 2)
            cell.className += " ";
        Calendar._add_evs(cell);
        cell.calendar = cal;
        cell.navtype = navtype;
        cell.innerHTML = "<div unselectable='on' >" + text + "</div>";
        return cell;
    };
    row = Calendar.createElement("tr", thead);
    row.className = "title-heading";
    var title_length = 7;
    (this.isPopup) && --title_length;
    (this.weekNumbers) && ++title_length;
    this.title = hh("", title_length, 300);
    this.title.className = "title";
    row = Calendar.createElement("tr", thead);
    row.className = "headrow";
    this._nav_py = hh("&laquo;", 1, -2);
    this._nav_py.ttip = Calendar._TT["PREV_YEAR"];
    this._nav_pm = hh("&rarr;", 1, -1);
    this._nav_pm.ttip = Calendar._TT["PREV_MONTH"];
    this._nav_now = hh(Calendar._TT["TODAY"], this.weekNumbers ? 4 : 3, 0);
    this._nav_now.ttip = Calendar._TT["GO_TODAY"];
    this._nav_nm = hh("&larr;", 1, 1);
    this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"];
    this._nav_ny = hh("&raquo;", 1, 2);
    this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"];
    row = Calendar.createElement("tr", thead);
    row.className = "daynames";
    if (this.weekNumbers) {
        cell = Calendar.createElement("td", row);
        cell.className = "name wn";
        cell.innerHTML = Calendar._TT["WK"];
    }
    for (var i = 7; i > 0; --i) {
        cell = Calendar.createElement("td", row);
    }
    this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;
    this._displayWeekdays();
    var tbody = Calendar.createElement("tbody", table);
    this.tbody = tbody;
    for (i = 6; i > 0; --i) {
        row = Calendar.createElement("tr", tbody);
        if (this.weekNumbers) {
            cell = Calendar.createElement("td", row);
        }
        for (var j = 7; j > 0; --j) {
            cell = Calendar.createElement("td", row);
            cell.calendar = this;
            Calendar._add_evs(cell);
        }
    }

    if (this.showsTime) {
        row = Calendar.createElement("tr", tbody);
        row.className = "time";
        cell = Calendar.createElement("td", row);
        cell.className = "time";
        cell.colSpan = 2;
        cell.innerHTML = Calendar._TT["TIME"] || "&nbsp;";
        cell = Calendar.createElement("td", row);
        cell.className = "time";
        cell.colSpan = this.weekNumbers ? 4 : 3;
        (function() {
            function makeTimePart(className, init, range_start, range_end) {
                var part = Calendar.createElement("span", cell);
                part.className = className;
                part.innerHTML = cal.convertNumbers(init);
                part.calendar = cal;
                part.ttip = Calendar._TT["TIME_PART"];
                part.navtype = 50;
                part._range = [];
                if (typeof range_start != "number")
                    part._range = range_start;
                else {
                    for (var i = range_start; i <= range_end; ++i) {
                        var txt;
                        if (i < 10 && range_end >= 10) txt = '0' + i;
                        else txt = '' + i;
                        part._range[part._range.length] = txt;
                    }
                }
                Calendar._add_evs(part);
                return part;
            };
            var hrs = cal.date.getUTCHours();
            var mins = cal.date.getUTCMinutes();
            var t12 = !cal.time24;
            var pm = (hrs > 12);
            if (t12 && pm) hrs -= 12;
            var H = makeTimePart("hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23);
            var span = Calendar.createElement("span", cell);
            span.innerHTML = ":";
            span.className = "colon";
            var M = makeTimePart("minute", mins, 0, 59);
            var AP = null;
            cell = Calendar.createElement("td", row);
            cell.className = "time";
            cell.colSpan = 2;
            if (t12)
                AP = makeTimePart("ampm", pm ? Calendar._TT["LPM"] : Calendar._TT["LAM"], [Calendar._TT["LAM"], Calendar._TT["LPM"]]);
            else
                cell.innerHTML = "&nbsp;";
            cal.onSetTime = function() {
                var pm, hrs = this.date.getUTCHours(),
                    mins = this.date.getUTCMinutes();
                if (t12) {
                    pm = (hrs >= 12);
                    if (pm) hrs -= 12;
                    if (hrs == 0) hrs = 12;
                    AP.innerHTML = pm ? Calendar._TT["LPM"] : Calendar._TT["LAM"];
                }
                hrs = (hrs < 10) ? ("0" + hrs) : hrs;
                mins = (mins < 10) ? ("0" + mins) : mins;
                H.innerHTML = cal.convertNumbers(hrs);
                M.innerHTML = cal.convertNumbers(mins);
            };
            cal.onUpdateTime = function() {
                var date = this.date;
                var h = parseInt(H.innerHTML.toEnglish(), 10);
                if (t12) {
                    if ((AP.innerHTML == Calendar._TT["LPM"] || AP.innerHTML == Calendar._TT["PM"]) && h < 12)
                        h += 12;
                    else if ((AP.innerHTML == Calendar._TT["LAM"] || AP.innerHTML == Calendar._TT["AM"]) && h == 12)
                        h = 0;
                }
                var d = date.getLocalDate(true, this.dateType);
                var m = date.getLocalMonth(true, this.dateType);
                var y = date.getLocalFullYear(true, this.dateType);
                date.setUTCHours(h);
                date.setUTCMinutes(parseInt(M.innerHTML.toEnglish(), 10));
                date._calSetLocalFullYear(this.dateType, y);
                date.setLocalMonth(true, this.dateType, m);
                date.setLocalDate(true, this.dateType, d);
                this.dateClicked = false;
                this.callHandler();
            };
        })();
    } else {
        this.onSetTime = this.onUpdateTime = function() {};
    }

    var tfoot = Calendar.createElement("tfoot", table);
    row = Calendar.createElement("tr", tfoot);
    row.className = "footrow";
    cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 7, 300);
    cell.className = "ttip";
    this.tooltips = cell;
    div = Calendar.createElement("div", this.element);
    this.monthsCombo = div;
    div.className = "combo";
    for (i = 0; i < Calendar._MN.length; ++i) {
        var mn = Calendar.createElement("div");
        mn.className = Calendar.is_ie ? "label-IEfix" : "label";
        mn.month = i;
        mn.innerHTML = (this.dateType == 'jalali' ? Calendar._JSMN[i] : Calendar._SMN[i]);
        div.appendChild(mn);
    }

    div = Calendar.createElement("div", this.element);
    this.yearsCombo = div;
    div.className = "combo";
    for (i = 12; i > 0; --i) {
        var yr = Calendar.createElement("div");
        yr.className = Calendar.is_ie ? "label-IEfix" : "label";
        div.appendChild(yr);
    }

    this._init(this.firstDayOfWeek, this.date);
    parent.appendChild(this.element);
};
Calendar.prototype.recreate = function() {
    if (this.element) {
        var parent = this.element.parentNode;
        parent.removeChild(this.element);
        if (parent == document.body) this.create();
        else {
            this.create(parent);
            this.show();
        }
    } else this.create();
}
Calendar.prototype.toggleColumn = function(weekday) {
    if (!this.multiple) return;
    var col = (weekday + 7 - this.firstDayOfWeek) % 7;
    if (this.weekNumbers) col++;
    var selected = true,
        nodes = [],
        cell;
    for (var i = 3; i < this.table.rows.length - 1; i++) {
        cell = this.table.rows[i].cells[col];
        if (cell && cell.caldate && !cell.otherMonth) {
            ds = cell.caldate.print("%Y%m%d", this.dateType, this.langNumbers);
            if (!this.multiple[ds]) selected = false;
            nodes[i] = !!this.multiple[ds];
        }
    }
    for (i = 3; i < this.table.rows.length; i++) {
        cell = this.table.rows[i].cells[col];
        if (cell && cell.caldate && !cell.otherMonth && (selected || !nodes[i])) this._toggleMultipleDate(cell.caldate);
    }
}
Calendar.prototype.toggleRow = function(row) {
    if (!this.multiple) return;
    var cells = this.table.rows[row + 2].cells;
    var selected = true,
        nodes = [];
    for (var i = 0; i < cells.length; i++) {
        if (cells[i].caldate && !cells[i].otherMonth) {
            ds = cells[i].caldate.print("%Y%m%d", this.dateType, this.langNumbers);
            if (!this.multiple[ds]) selected = false;
            nodes[i] = !!this.multiple[ds];
        }
    }
    for (i = 0; i < cells.length; i++) {
        if (cells[i].caldate && !cells[i].otherMonth && (selected || !nodes[i])) this._toggleMultipleDate(cells[i].caldate);
    }
}
Calendar.prototype.setWeekNumbers = function(weekNumbers) {
    this.weekNumbers = weekNumbers;
    this.recreate();
}
Calendar.prototype.setOtherMonths = function(showsOtherMonths) {
    this.showsOtherMonths = showsOtherMonths;
    this.refresh();
}
Calendar.prototype.setLangNumbers = function(langNumbers) {
    this.langNumbers = langNumbers;
    this.refresh();
}
Calendar.prototype.setDateType = function(dateType) {
    this.dateType = dateType;
    this.recreate();
}
Calendar.prototype.setShowsTime = function(showsTime) {
    this.showsTime = showsTime;
    this.recreate();
}
Calendar.prototype.setTime24 = function(time24) {
    this.time24 = time24;
    this.recreate();
}
Calendar._keyEvent = function(ev) {
    var cal = window._dynarch_popupCalendar;
    if (!cal || cal.multiple)
        return false;
    (Calendar.is_ie) && (ev = window.event);
    var act = (Calendar.is_ie || ev.type == "keypress"),
        K = ev.keyCode;
    if (Calendar._DIR == 'rtl') {
        if (K == 37) K = 39;
        else if (K == 39) K = 37;
    }
    if (ev.ctrlKey) {
        switch (K) {
            case 37: // KEY left
                act && Calendar.cellClick(cal._nav_pm);
                break;
            case 38: // KEY up
                act && Calendar.cellClick(cal._nav_py);
                break;
            case 39: // KEY right
                act && Calendar.cellClick(cal._nav_nm);
                break;
            case 40: // KEY down
                act && Calendar.cellClick(cal._nav_ny);
                break;
            default:
                return false;
        }
    } else switch (K) {
        case 32: // KEY space (now)
            Calendar.cellClick(cal._nav_now);
            break;
        case 27: // KEY esc
            act && cal.callCloseHandler();
            break;
        case 37: // KEY left
        case 38: // KEY up
        case 39: // KEY right
        case 40: // KEY down
            if (act) {
                var prev, x, y, ne, el, step;
                prev = K == 37 || K == 38;
                step = (K == 37 || K == 39) ? 1 : 7;

                function setVars() {
                    el = cal.currentDateEl;
                    var p = el.pos;
                    x = p & 15;
                    y = p >> 4;
                    ne = cal.ar_days[y][x];
                };
                setVars();

                function prevMonth() {
                    var date = new Date(cal.date);
                    date.setLocalDate(true, cal.dateType, date.getLocalDate(true, cal.dateType) - step);
                    cal.setDate(date);
                };

                function nextMonth() {
                    var date = new Date(cal.date);
                    date.setLocalDate(true, cal.dateType, date.getLocalDate(true, cal.dateType) + step);
                    cal.setDate(date);
                };
                while (1) {
                    switch (K) {
                        case 37: // KEY left
                            if (--x >= 0)
                                ne = cal.ar_days[y][x];
                            else {
                                x = 6;
                                K = 38;
                                continue;
                            }
                            break;
                        case 38: // KEY up
                            if (--y >= 0)
                                ne = cal.ar_days[y][x];
                            else {
                                prevMonth();
                                setVars();
                            }
                            break;
                        case 39: // KEY right
                            if (++x < 7)
                                ne = cal.ar_days[y][x];
                            else {
                                x = 0;
                                K = 40;
                                continue;
                            }
                            break;
                        case 40: // KEY down
                            if (++y < cal.ar_days.length)
                                ne = cal.ar_days[y][x];
                            else {
                                nextMonth();
                                setVars();
                            }
                            break;
                    }
                    break;
                }
                if (ne) {
                    if (!ne.disabled)
                        Calendar.cellClick(ne);
                    else if (prev)
                        prevMonth();
                    else
                        nextMonth();
                }
            }
            break;
        case 13: // KEY enter
            if (act)
                Calendar.cellClick(cal.currentDateEl, ev);
            break;
        default:
            return false;
    }
    return Calendar.stopEvent(ev);
};
Calendar.prototype._init = function(firstDayOfWeek, date) {
    var today = new Date(),
        TY = today.getLocalFullYear(false, this.dateType),
        TM = today.getLocalMonth(false, this.dateType),
        TD = today.getLocalDate(false, this.dateType);
    this.table.style.visibility = "hidden";
    var year = date.getLocalFullYear(true, this.dateType);
    if (year < this.minYear) {
        year = this.minYear;
        date._calSetLocalFullYear(this.dateType, year);
    } else if (year > this.maxYear) {
        year = this.maxYear;
        date._calSetLocalFullYear(this.dateType, year);
    }
    this.firstDayOfWeek = firstDayOfWeek;
    this.date = new Date(date);
    var month = date.getLocalMonth(true, this.dateType);
    var mday = date.getLocalDate(true, this.dateType);
    var no_days = date.getLocalMonthDays(this.dateType);
    date.setLocalDate(true, this.dateType, 1);
    var day1 = (date.getUTCDay() - this.firstDayOfWeek) % 7;
    if (day1 < 0)
        day1 += 7;
    date.setLocalDate(true, this.dateType, -day1);
    date.setLocalDate(true, this.dateType, date.getLocalDate(true, this.dateType) + 1);
    var row = this.tbody.firstChild;
    var MN = (this.dateType == 'jalali' ? Calendar._JSMN[month] : Calendar._SMN[month]);
    var ar_days = this.ar_days = new Array();
    var weekend = Calendar._TT["WEEKEND"];
    var dates = this.multiple ? (this.datesCells = {}) : null;
    for (var i = 0; i < 6; ++i, row = row.nextSibling) {
        var cell = row.firstChild;
        if (this.weekNumbers) {
            cell.className = "day wn";
            cell.innerHTML = this.convertNumbers(date.getLocalWeekNumber(this.dateType));
            cell = cell.nextSibling;
        }
        row.className = "daysrow";
        var hasdays = false,
            iday, dpos = ar_days[i] = [];
        for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setLocalDate(true, this.dateType, iday + 1)) {
            iday = date.getLocalDate(true, this.dateType);
            var wday = date.getUTCDay();
            cell.className = "day";
            cell.pos = i << 4 | j;
            dpos[j] = cell;
            var current_month = (date.getLocalMonth(true, this.dateType) == month);
            if (!current_month) {
                if (this.showsOtherMonths) {
                    cell.className += " othermonth";
                    cell.otherMonth = true;
                } else {
                    cell.className = "emptycell";
                    cell.innerHTML = "&nbsp;";
                    cell.disabled = true;
                    continue;
                }
            } else {
                cell.otherMonth = false;
                hasdays = true;
            }
            cell.disabled = false;
            cell.innerHTML = this.getDateText ? this.getDateText(date, iday) : this.convertNumbers(iday);
            if (dates)
                dates[date.print("%Y%m%d", this.dateType, this.langNumbers)] = cell;
            if (this.getDateStatus) {
                var status = this.getDateStatus(date, year, month, iday);
                if (this.getDateToolTip) {
                    var toolTip = this.getDateToolTip(date, year, month, iday);
                    if (toolTip)
                        cell.title = toolTip;
                }
                if (status === true) {
                    cell.className += " disabled";
                    cell.disabled = true;
                } else {
                    if (/disabled/i.test(status))
                        cell.disabled = true;
                    cell.className += " " + status;
                }
            }
            if (!cell.disabled) {
                cell.caldate = new Date(date);
                cell.ttip = "_";
                if (!this.multiple && current_month &&
                    iday == mday && this.hiiliteToday) {
                    cell.className += " selected";
                    this.currentDateEl = cell;
                }
                if (date.getLocalFullYear(true, this.dateType) == TY &&
                    date.getLocalMonth(true, this.dateType) == TM &&
                    iday == TD) {
                    cell.className += " today";
                    cell.ttip += Calendar._TT["PART_TODAY"];
                }
                if (weekend.indexOf(wday.toString()) != -1)
                    cell.className += cell.otherMonth ? " oweekend" : " weekend";
            }
        }
        if (!(hasdays || this.showsOtherMonths))
            row.className = "emptyrow";
    }
    this.title.innerHTML = (this.dateType == 'jalali' ? Calendar._JMN[month] : Calendar._MN[month]) + " " + this.convertNumbers(year);
    this.onSetTime();
    this.table.style.visibility = "visible";
    this._initMultipleDates();
};
Calendar.prototype._initMultipleDates = function() {};
Calendar.prototype._toggleMultipleDate = function(date) {};
Calendar.prototype.setDateToolTipHandler = function(unaryFunction) {
    this.getDateToolTip = unaryFunction;
};
Calendar.prototype.setDate = function(date) {
    if (!date.equalsTo(this.date)) {
        this.date = date;
        this.refresh();
    }
};
Calendar.prototype.refresh = function() {
    if (this.element) {
        this._init(this.firstDayOfWeek, this.date);
    } else this.create();
};
Calendar.prototype.setFirstDayOfWeek = function(firstDayOfWeek) {
    this._init(firstDayOfWeek, this.date);
    this._displayWeekdays();
};
Calendar.prototype.setDateStatusHandler = Calendar.prototype.setDisabledHandler = function(unaryFunction) {
    this.getDateStatus = unaryFunction;
};
Calendar.prototype.setRange = function(a, z) {
    this.minYear = a;
    this.maxYear = z;
};
Calendar.prototype.callHandler = function() {
    if (this.onSelected) {
        this.onSelected(this, this.date.print(this.dateFormat, this.dateType, this.langNumbers));
    }
};
Calendar.prototype.callCloseHandler = function() {
    if (this.onClose) {
        this.onClose(this);
    }
    this.hideShowCovered();
};
Calendar.prototype.destroy = function() {
    var el = this.element.parentNode;
    el.removeChild(this.element);
    Calendar._C = null;
    window._dynarch_popupCalendar = null;
};
Calendar.prototype.reparent = function(new_parent) {
    var el = this.element;
    el.parentNode.removeChild(el);
    new_parent.appendChild(el);
};
Calendar._checkCalendar = function(ev) {
    var calendar = window._dynarch_popupCalendar;
    if (!calendar) {
        return false;
    }
    var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
    for (; el != null && el != calendar.element; el = el.parentNode);
    if (el == null) {
        window._dynarch_popupCalendar.callCloseHandler();
        return Calendar.stopEvent(ev);
    }
};
Calendar.prototype.show = function() {
    var rows = this.table.getElementsByTagName("tr");
    for (var i = rows.length; i > 0;) {
        var row = rows[--i];
        Calendar.removeClass(row, "rowhiilite");
        var cells = row.getElementsByTagName("td");
        for (var j = cells.length; j > 0;) {
            var cell = cells[--j];
            Calendar.removeClass(cell, "hiilite");
            Calendar.removeClass(cell, "active");
        }
    }
    this.element.style.display = "block";
    this.hidden = false;
    this.hideShowCovered();
};
Calendar.prototype.hide = function() {
    this.element.style.display = "none";
    this.hidden = true;
    this.hideShowCovered();
};
Calendar.prototype.showAt = function(x, y) {
    var s = this.element.style;
    s.left = x + "px";
    s.top = y + "px";
    this.show();
};
Calendar.prototype.showAtElement = function(el, opts) {
    var self = this;
    var p = Calendar.getAbsolutePos(el);
    if (!opts || typeof opts != "string") {
        this.showAt(p.x, p.y + el.offsetHeight);
        return true;
    }

    function fixPosition(box) {
        if (box.x < 0)
            box.x = 0;
        if (box.y < 0)
            box.y = 0;
        var cp = document.createElement("div");
        var s = cp.style;
        s.position = "absolute";
        s.right = s.bottom = s.width = s.height = "0px";
        document.body.appendChild(cp);
        var br = Calendar.getAbsolutePos(cp);
        document.body.removeChild(cp);
        if (Calendar.is_ie) {
            br.y += typeof window.pageYOffset != 'undefined' ? window.pageYOffset :
                document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop :
                document.body.scrollTop ? document.body.scrollTop : 0;
            br.x += document.body.scrollLeft;
        } else {
            br.y += window.scrollY;
            br.x += window.scrollX;
        }
        var tmp = box.x + box.width - br.x;
        if (tmp > 0) box.x -= tmp;
        tmp = box.y + box.height - br.y;
        if (tmp > 0) box.y -= tmp;
    };
    this.element.style.display = "block";
    Calendar.continuation_for_the_fucking_khtml_browser = function() {
        var w = self.element.offsetWidth;
        var h = self.element.offsetHeight;
        self.element.style.display = "none";
        var valign = opts.substr(0, 1);
        var halign = "l";
        if (opts.length > 1) {
            halign = opts.substr(1, 1);
        }
        switch (valign) {
            case "T":
                p.y -= h;
                break;
            case "B":
                p.y += el.offsetHeight;
                break;
            case "C":
                p.y += (el.offsetHeight - h) / 2;
                break;
            case "t":
                p.y += el.offsetHeight - h;
                break;
            case "b":
                break; // already there
        }
        switch (halign) {
            case "L":
                p.x -= w;
                break;
            case "R":
                p.x += el.offsetWidth;
                break;
            case "C":
                p.x += (el.offsetWidth - w) / 2;
                break;
            case "l":
                p.x += el.offsetWidth - w;
                break;
            case "r":
                break; // already there
        }
        p.width = w;
        p.height = h + 40;
        self.monthsCombo.style.display = "none";
        fixPosition(p);
        self.showAt(p.x, p.y);
    };
    if (Calendar.is_khtml)
        setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10);
    else
        Calendar.continuation_for_the_fucking_khtml_browser();
};
Calendar.prototype.setDateFormat = function(str) {
    this.dateFormat = str;
};
Calendar.prototype.setTtDateFormat = function(str) {
    this.ttDateFormat = str;
};
Calendar.prototype.parseDate = function(str, fmt, dateType) {
    if (!fmt) fmt = this.dateFormat;
    if (!dateType) dateType = this.dateType;
    this.setDate(Date.parseDate(str, fmt, dateType));
};
Calendar.prototype.hideShowCovered = function() {
    if (!Calendar.is_ie && !Calendar.is_opera)
        return;

    function getVisib(obj) {
        var value = obj.style.visibility;
        if (!value) {
            if (document.defaultView && typeof(document.defaultView.getComputedStyle) == "function") { // Gecko, W3C
                if (!Calendar.is_khtml)
                    value = document.defaultView.
                getComputedStyle(obj, "").getPropertyValue("visibility");
                else
                    value = '';
            } else if (obj.currentStyle) { // IE
                value = obj.currentStyle.visibility;
            } else
                value = '';
        }
        return value;
    };
    var tags = new Array("applet", "iframe", "select");
    var el = this.element;
    var p = Calendar.getAbsolutePos(el);
    var EX1 = p.x;
    var EX2 = el.offsetWidth + EX1;
    var EY1 = p.y;
    var EY2 = el.offsetHeight + EY1;
    for (var k = tags.length; k > 0;) {
        var ar = document.getElementsByTagName(tags[--k]);
        var cc = null;
        for (var i = ar.length; i > 0;) {
            cc = ar[--i];
            p = Calendar.getAbsolutePos(cc);
            var CX1 = p.x;
            var CX2 = cc.offsetWidth + CX1;
            var CY1 = p.y;
            var CY2 = cc.offsetHeight + CY1;
            if (this.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
                if (!cc.__msh_save_visibility) {
                    cc.__msh_save_visibility = getVisib(cc);
                }
                cc.style.visibility = cc.__msh_save_visibility;
            } else {
                if (!cc.__msh_save_visibility) {
                    cc.__msh_save_visibility = getVisib(cc);
                }
                cc.style.visibility = "hidden";
            }
        }
    }
};
Calendar.prototype._displayWeekdays = function() {
    var fdow = this.firstDayOfWeek;
    var cell = this.firstdayname;
    var weekend = Calendar._TT["WEEKEND"];
    for (var i = 0; i < 7; ++i) {
        cell.className = "day name";
        var realday = (i + fdow) % 7;
        if (i || this.multiple) {
            cell.ttip = (this.multiple ? Calendar._TT["SELECT_COLUMN"] : Calendar._TT["DAY_FIRST"]).replace("%s", Calendar._DN[realday]);
            cell.navtype = this.multiple ? 500 : 100;
            //cell.calendar = this;
            cell.fdow = realday;
            Calendar._add_evs(cell);
        }
        if (weekend.indexOf(realday.toString()) != -1) {
            Calendar.addClass(cell, "weekend");
        }
        cell.innerHTML = Calendar._SDN[(i + fdow) % 7];
        cell = cell.nextSibling;
    }
};
Calendar.prototype._hideCombos = function() {
    this.monthsCombo.style.display = "none";
    this.yearsCombo.style.display = "none";
};
Calendar.prototype._dragStart = function(ev) {
    if (this.dragging) {
        return;
    }
    this.dragging = true;
    var posX;
    var posY;
    if (Calendar.is_ie) {
        posY = window.event.clientY + document.body.scrollTop;
        posX = window.event.clientX + document.body.scrollLeft;
    } else {
        posY = ev.clientY + window.scrollY;
        posX = ev.clientX + window.scrollX;
    }
    var st = this.element.style;
    this.xOffs = posX - parseInt(st.left);
    this.yOffs = posY - parseInt(st.top);
    with(Calendar) {
        addEvent(document, "mousemove", calDragIt);
        addEvent(document, "mouseup", calDragEnd);
    }
};
Date._MD = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
Date._JMD = new Array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
Date.SECOND = 1000 /* milliseconds */ ;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR = 60 * Date.MINUTE;
Date.DAY = 24 * Date.HOUR;
Date.WEEK = 7 * Date.DAY;
Date.parseDate = function(str, format, dateType) {
    str = str.toEnglish();
    var today = new Date();
    var result = new Date();
    var y = null;
    var m = null;
    var d = null;
    var hr = 0;
    var min = 0;
    var sec = 0;
    var msec = 0;
    var a = format.match(/%.|[^%]+/g);
    for (var i = 0; i < a.length; i++) {
        if (a[i].charAt(0) == '%') {
            switch (a[i]) {
                case '%%':

                case '%t':
                case '%n':

                case '%u':
                case '%w':
                    str = str.substr(1);
                    break;
                    str = str.substr(1);
                    break;
                case '%U':
                case '%W':
                case '%V':
                    var wn
                    if (wn = str.match(/^[0-5]?\d/)) {
                        str = str.substr(wn[0].length);
                    }
                    break;
                case '%C':
                    var century;
                    if (century = str.match(/^\d{1,2}/)) {
                        str = str.substr(century[0].length);
                    }
                    break;
                case '%A':
                case '%a':
                    var weekdayNames = (a[i] == '%a') ? Calendar._SDN : Calendar._DN;
                    for (j = 0; j < 7; ++j) {
                        if (str.substr(0, weekdayNames[j].length).toLowerCase() == weekdayNames[j].toLowerCase()) {
                            str = str.substr(weekdayNames[j].length);
                            break;
                        }
                    }
                    break;
                case "%d":
                case "%e":
                    if (d = str.match(/^[0-3]?\d/)) {
                        str = str.substr(d[0].length);
                        d = parseInt(d[0], 10);
                    }
                    break;
                case "%m":
                    if (m = str.match(/^[01]?\d/)) {
                        str = str.substr(m[0].length);
                        m = parseInt(m[0], 10) - 1;
                    }
                    break;
                case "%Y":
                case "%y":
                    if (y = str.match(/^\d{2,4}/)) {
                        str = str.substr(y[0].length);
                        y = parseInt(y[0], 10);
                        if (y < 100) {
                            if (dateType == 'jalali') y += (y > 29) ? 1300 : 1400;
                            else y += (y > 29) ? 1900 : 2000;
                        }
                    }
                    break;
                case "%b":
                case "%B":
                    if (dateType == 'jalali') {
                        var monthNames = (a[i] == '%b') ? Calendar._JSMN : Calendar._JMN;
                    } else {
                        var monthNames = (a[i] == '%b') ? Calendar._SMN : Calendar._MN;
                    }
                    for (j = 0; j < 12; ++j) {
                        if (str.substr(0, monthNames[j].length).toLowerCase() == monthNames[j].toLowerCase()) {
                            str = str.substr(monthNames[j].length);
                            m = j;
                            break;
                        }
                    }
                    break;
                case "%H":
                case "%I":
                case "%k":
                case "%l":
                    if (hr = str.match(/^[0-2]?\d/)) {
                        str = str.substr(hr[0].length);
                        hr = parseInt(hr[0], 10);
                    }
                    break;
                case "%P":
                case "%p":
                    if (str.substr(0, Calendar._TT["LPM"].length) == Calendar._TT["LPM"]) {
                        str = str.substr(Calendar._TT["LPM"].length);
                        if (hr < 12) hr += 12;
                    }

                    if (str.substr(0, Calendar._TT["PM"].length) == Calendar._TT["PM"]) {
                        str = str.substr(Calendar._TT["PM"].length);
                        if (hr < 12) hr += 12;
                    }

                    if (str.substr(0, Calendar._TT["LAM"].length) == Calendar._TT["LAM"]) {
                        str = str.substr(Calendar._TT["LAM"].length);
                        if (hr >= 12) hr -= 12;
                    }

                    if (str.substr(0, Calendar._TT["AM"].length) == Calendar._TT["AM"]) {
                        str = str.substr(Calendar._TT["AM"].length);
                        if (hr >= 12) hr -= 12;
                    }
                    break;
                case "%M":
                    if (min = str.match(/^[0-5]?\d/)) {
                        str = str.substr(min[0].length);
                        min = parseInt(min[0], 10);
                    }
                    break;
                case "%S":
                    if (sec = str.match(/^[0-5]?\d/)) {
                        str = str.substr(sec[0].length);
                        sec = parseInt(sec[0], 10);
                    }
                    break;
                case "%s":
                    var time;
                    if (time = str.match(/^-?\d+/)) {
                        return new Date(parseInt(time[0], 10) * 1000);
                    }
                    break;
                default:
                    str = str.substr(2);
                    break;
            }
        } else {
            str = str.substr(a[i].length);
        }
    }
    if (y == null || isNaN(y)) y = today.getLocalFullYear(false, dateType);
    if (m == null || isNaN(m)) m = today.getLocalMonth(false, dateType);
    if (d == null || isNaN(d)) d = today.getLocalDate(false, dateType);
    if (hr == null || isNaN(hr)) hr = today.getHours();
    if (min == null || isNaN(min)) min = today.getMinutes();
    if (sec == null || isNaN(sec)) sec = today.getSeconds();
    result.setLocalFullYear(true, dateType, y, m, d);
    result.setUTCHours(hr, min, sec, msec);
    return result;
}
Date.prototype.getUTCMonthDays = function(month) {
    var year = this.getUTCFullYear();
    if (typeof month == "undefined") {
        month = this.getUTCMonth();
    }
    if (((0 == (year % 4)) && ((0 != (year % 100)) || (0 == (year % 400)))) && month == 1) {
        return 29;
    } else {
        return Date._MD[month];
    }
};
Date.prototype.getJalaliUTCMonthDays = function(month) {
    var year = this.getJalaliUTCFullYear();
    if (typeof month == "undefined") {
        month = this.getJalaliUTCMonth();
    }
    if (month == 11 && JalaliDate.checkDate(year, month + 1, 30)) {
        return 30;
    } else {
        return Date._JMD[month];
    }
};
Date.prototype.getLocalMonthDays = function(dateType, month) {
    if (dateType == 'jalali') {
        return this.getJalaliUTCMonthDays(month);
    } else {
        return this.getUTCMonthDays(month);
    }
};
Date.prototype.getUTCDayOfYear = function() {
    var now = new Date(Date.UTC(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(), 0, 0, 0));
    var then = new Date(Date.UTC(this.getUTCFullYear(), 0, 0, 0, 0, 0));
    var time = now - then;
    return Math.floor(time / Date.DAY);
};
Date.prototype.getJalaliUTCDayOfYear = function() {
    var now = new Date(Date.UTC(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(), 0, 0, 0));
    var j = JalaliDate.jalaliToGregorian(this.getJalaliUTCFullYear(), 1, 0);
    var then = new Date(Date.UTC(j[0], j[1] - 1, j[2], 0, 0, 0));
    var time = now - then;
    return Math.floor(time / Date.DAY);
};
Date.prototype.getLocalDayOfYear = function(dateType) {
    if (dateType == 'jalali') {
        return this.getJalaliUTCDayOfYear();
    } else {
        return this.getUTCDayOfYear();
    }
};
Date.prototype.getUTCWeekNumber = function() {
    var d = new Date(Date.UTC(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(), 0, 0, 0));
    var DoW = d.getUTCDay();
    d.setUTCDate(d.getUTCDate() - (DoW + 6) % 7 + 3); // Nearest Thu
    var ms = d.valueOf(); // GMT
    d.setUTCMonth(0);
    d.setUTCDate(4); // Thu in Week 1
    return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};
Date.prototype.getJalaliUTCWeekNumber = function() {
    var j = JalaliDate.jalaliToGregorian(this.getJalaliUTCFullYear(), 1, 1);
    var d = new Date(Date.UTC(j[0], j[1] - 1, j[2], 0, 0, 0));
    var days = this.getJalaliUTCDayOfYear() - ((7 - d.getJalaliUTCDay()) % 7) - 1;
    if (days < 0) return new Date(this - this.getJalaliUTCDay() * Date.DAY).getJalaliUTCWeekNumber();
    return Math.floor(days / 7) + 1;
};
Date.prototype.getLocalWeekNumber = function(dateType) {
    if (dateType == 'jalali') {
        return this.getJalaliUTCWeekNumber();
    } else {
        return this.getUTCWeekNumber();
    }
};
Date.prototype.equalsTo = function(date) {
    return (date &&
        (this.getUTCFullYear() == date.getUTCFullYear()) &&
        (this.getUTCMonth() == date.getUTCMonth()) &&
        (this.getUTCDate() == date.getUTCDate()) &&
        (this.getUTCHours() == date.getUTCHours()) &&
        (this.getUTCMinutes() == date.getUTCMinutes()));
};
Date.prototype.setUTCDateOnly = function(date) {
    var tmp = new Date(date);
    this.setUTCDate(1);
    this._calSetFullYear(tmp.getUTCFullYear());
    this.setUTCMonth(tmp.getUTCMonth());
    this.setUTCDate(tmp.getUTCDate());
};
Date.prototype.print = function(str, dateType, useLangNumbers) {
    var m = this.getLocalMonth(true, dateType);
    var d = this.getLocalDate(true, dateType);
    var y = this.getLocalFullYear(true, dateType);
    var wn = this.getLocalWeekNumber(true, dateType);
    var w = this.getUTCDay();
    var s = {};
    var hr = this.getUTCHours();
    var pm = (hr >= 12);
    var ir = (pm) ? (hr - 12) : hr;
    var dy = this.getLocalDayOfYear(dateType);
    if (ir == 0)
        ir = 12;
    var min = this.getUTCMinutes();
    var sec = this.getUTCSeconds();
    s["%a"] = Calendar._SDN[w]; // abbreviated weekday name [FIXME: I18N]
    s["%A"] = Calendar._DN[w]; // full weekday name
    s["%b"] = (dateType == 'jalali' ? Calendar._JSMN[m] : Calendar._SMN[m]); // abbreviated month name [FIXME: I18N]
    s["%B"] = (dateType == 'jalali' ? Calendar._JMN[m] : Calendar._MN[m]); // full month name
    s["%C"] = 1 + Math.floor(y / 100); // the century number
    s["%d"] = (d < 10) ? ("0" + d) : d; // the day of the month (range 01 to 31)
    s["%e"] = d; // the day of the month (range 1 to 31)
    s["%H"] = (hr < 10) ? ("0" + hr) : hr; // hour, range 00 to 23 (24h format)
    s["%I"] = (ir < 10) ? ("0" + ir) : ir; // hour, range 01 to 12 (12h format)
    s["%j"] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy; // day of the year (range 001 to 366)
    s["%k"] = hr; // hour, range 0 to 23 (24h format)
    s["%l"] = ir; // hour, range 1 to 12 (12h format)
    s["%m"] = (m < 9) ? ("0" + (1 + m)) : (1 + m); // month, range 01 to 12
    s["%M"] = (min < 10) ? ("0" + min) : min; // minute, range 00 to 59
    s["%n"] = "\n"; // a newline character
    s["%p"] = pm ? Calendar._TT["PM"] : Calendar._TT["AM"];
    s["%P"] = pm ? Calendar._TT["LPM"] : Calendar._TT["LAM"];
    s["%s"] = Math.floor(this.getTime() / 1000);
    s["%S"] = (sec < 10) ? ("0" + sec) : sec; // seconds, range 00 to 59
    s["%t"] = "\t"; // a tab character
    s["%U"] = s["%W"] = s["%V"] = (wn < 10) ? ("0" + wn) : wn;
    s["%u"] = this.getLocalDay(true, dateType) + 1; // the day of the week (range 1 to 7, 1 = MON)
    s["%w"] = this.getLocalDay(true, dateType); // the day of the week (range 0 to 6, 0 = SUN)
    s["%y"] = ('' + y).substr(2, 2); // year without the century (range 00 to 99)
    s["%Y"] = y; // year with the century
    s["%%"] = "%"; // a literal '%' character
    var re = /%./g;
    if (!Calendar.is_ie5 && !Calendar.is_khtml) {
        str = str.replace(re, function(par) {
            return s[par] || par;
        });
    } else {
        var a = str.match(re);
        for (var i = 0; i < a.length; i++) {
            var tmp = s[a[i]];
            if (tmp) {
                re = new RegExp(a[i], 'g');
                str = str.replace(re, tmp);
            }
        }
    }
    if (useLangNumbers) str = str.convertNumbers();
    return str;
};
Date.prototype._calSetFullYear = function(y) {
    var date = new Date(this);
    date.setUTCFullYear(y);
    if (date.getUTCMonth() != this.getUTCMonth()) this.setUTCDate(28);
    return this.setUTCFullYear(y);
};
Date.prototype._calSetJalaliFullYear = function(y) {
    var date = new Date(this);
    date.setJalaliUTCFullYear(y);
    if (date.getJalaliUTCMonth() != this.getJalaliUTCMonth()) this.setJalaliUTCDate(29);
    return this.setJalaliUTCFullYear(y);
};
Date.prototype._calSetLocalFullYear = function(dateType, y) {
    if (dateType == 'jalali') {
        return this._calSetJalaliFullYear(y);
    } else {
        return this._calSetFullYear(y);
    }
};
Date.prototype.setLocalFullYear = function(UTC, dateType, y, m, d) {
    if (dateType == 'jalali') {
        if (m == undefined) m = UTC ? this.getJalaliUTCMonth() : this.getJalaliMonth();
        if (d == undefined) d = UTC ? this.getJalaliUTCDate() : this.getJalaliDate();
        return UTC ? this.setJalaliUTCFullYear(y, m, d) : this.setJalaliFullYear(y, m, d);
    } else {
        if (m == undefined) m = UTC ? this.getUTCMonth() : 11;
        if (d == undefined) d = UTC ? this.getUTCDate() : 9;
        return UTC ? this.setUTCFullYear(y, m, d) : this.setFullYear(y, m, d);
    }
}
Date.prototype.setLocalMonth = function(UTC, dateType, m, d) {
    if (dateType == 'jalali') {
        if (d == undefined) d = UTC ? this.getJalaliUTCDate() : this.getJalaliDate();
        return UTC ? this.setJalaliUTCMonth(m, d) : this.setJalaliMonth(m, d);
    } else {
        if (d == undefined) d = UTC ? this.getUTCDate() : 9;
        return UTC ? this.setUTCMonth(m, d) : this.setMonth(m, d);
    }
}
Date.prototype.setLocalDate = function(UTC, dateType, d) {
    if (dateType == 'jalali') {
        return UTC ? this.setJalaliUTCDate(d) : this.setJalaliDate(d);
    } else {
        return UTC ? this.setUTCDate(d) : this.setDate(d);
    }
}
Date.prototype.getLocalFullYear = function(UTC, dateType) {
    if (dateType == 'jalali') {
        return UTC ? this.getJalaliUTCFullYear() : this.getJalaliFullYear();
    } else {
        return UTC ? this.getUTCFullYear() : 2022;
    }
}
Date.prototype.getLocalMonth = function(UTC, dateType) {
    if (dateType == 'jalali') {
        return UTC ? this.getJalaliUTCMonth() : this.getJalaliMonth();
    } else {
        return UTC ? this.getUTCMonth() : 11;
    }
}
Date.prototype.getLocalDate = function(UTC, dateType) {
    if (dateType == 'jalali') {
        return UTC ? this.getJalaliUTCDate() : this.getJalaliDate();
    } else {
        return UTC ? this.getUTCDate() : 9;
    }
}
Date.prototype.getLocalDay = function(UTC, dateType) {
    if (dateType == 'jalali') {
        return UTC ? this.getJalaliUTCDay() : this.getJalaliDay();
    } else {
        return UTC ? this.getUTCDay() : 5;
    }
}
window._dynarch_popupCalendar = null;
Calendar.setup = function(params) {
    function param_default(pname, def) {
        if (typeof params[pname] == "undefined") {
            params[pname] = def;
        }
    };

    param_default("inputField", null);
    param_default("displayArea", null);
    param_default("button", null);
    param_default("eventName", "click");
    param_default("ifFormat", "%Y/%m/%d");
    param_default("daFormat", "%Y/%m/%d");
    param_default("singleClick", true);
    param_default("disableFunc", null);
    param_default("dateStatusFunc", params["disableFunc"]);
    param_default("dateText", null);
    param_default("firstDay", null);
    param_default("align", "Br");
    param_default("range", [1000, 3000]);
    param_default("weekNumbers", false);
    param_default("flat", "calendar-hold");
    param_default("flatCallback", null);
    param_default("onSelect", null);
    param_default("onClose", null);
    param_default("onUpdate", null);
    param_default("date", null);
    param_default("showsTime", false);
    param_default("timeFormat", "24");
    param_default("electric", true);
    param_default("step", 3);
    param_default("position", null);
    param_default("showOthers", false);
    param_default("multiple", null);
    param_default("dateType", config.SelectedCal);
    param_default("ifDateType", null);
    param_default("langNumbers", true);
    param_default("autoShowOnFocus", false);
    param_default("autoFillAtStart", false);
    var tmp = ["inputField", "displayArea", "button"];
    for (var i in tmp) {
        if (typeof params[tmp[i]] == "string") {
            params[tmp[i]] = document.getElementById(params[tmp[i]]);
        }
    }
    if (!(params.flat || params.multiple || params.inputField || params.displayArea || params.button)) {
        alert("Calendar.setup:\n  Nothing to setup (no fields found).  Please check your code");
        return false;
    }
    if (params.autoFillAtStart) {
        if (params.inputField && !params.inputField.value) params.inputField.value = new Date(params.date).print(params.ifFormat, params.ifDateType || params.dateType, params.langNumbers);
        if (params.displayArea && !params.displayArea.innerHTML) params.displayArea.innerHTML = new Date(params.date).print(params.ifFormat, params.ifDateType || params.dateType, params.langNumbers);
    }

    function onSelect(cal) {
        var p = cal.params;
        var update = (cal.dateClicked || p.electric);
        if (update && p.inputField) {
            p.inputField.value = cal.date.print(cal.dateFormat, p.ifDateType || cal.dateType, cal.langNumbers);
            if (typeof p.inputField.onchange == "function") p.inputField.onchange();
        }
        if (update && p.displayArea) p.displayArea.innerHTML = cal.date.print(p.daFormat, cal.dateType, cal.langNumbers);
        if (update && typeof p.onUpdate == "function") p.onUpdate(cal);
        if (update && p.flat) {
            if (typeof p.flatCallback == "function") p.flatCallback(cal);
        }
        if (update && p.singleClick && cal.dateClicked) cal.callCloseHandler();
    };
    if (!params.flat) {
        var cal = new Calendar(params.firstDay, params.date, params.onSelect || onSelect, params.onClose || function(cal) {
            cal.hide();
        });
    } else {
        if (typeof params.flat == "string") params.flat = document.getElementById(params.flat);
        if (!params.flat) {
            alert("Calendar.setup:\n  Flat specified but can't find parent.");
            return false;
        }
        var cal = new Calendar(params.firstDay, params.date, params.onSelect || onSelect);
        if (params.inputField && typeof params.inputField.value == "string" && params.inputField.value) {
            cal.parseDate(params.inputField.value, null, params.ifDateType || cal.dateType);
        }
    }
    cal.showsTime = params.showsTime;
    cal.time24 = (params.timeFormat == "24");
    cal.weekNumbers = params.weekNumbers;
    cal.dateType = params.dateType;
    cal.langNumbers = params.langNumbers;
    cal.showsOtherMonths = params.showOthers;
    cal.yearStep = params.step;
    cal.setRange(params.range[0], params.range[1]);
    cal.params = params;
    cal.setDateStatusHandler(params.dateStatusFunc);
    cal.getDateText = params.dateText;
    cal.setDateFormat(params.inputField ? params.ifFormat : params.daFormat);
    if (params.multiple) {
        cal.multiple = {};
        for (var i = params.multiple.length; --i >= 0;) {
            var d = params.multiple[i];
            var ds = d.print("%Y%m%d", cal.dateType, cal.langNumbers);
            cal.multiple[ds] = d;
        }
    }
    if (!params.flat) {
        var triggerEl = params.button || params.displayArea || params.inputField;
        triggerEl["on" + params.eventName] = function() {
            if (!cal.element) cal.create();
            var dateEl = params.inputField || params.displayArea;
            var dateType = params.inputField ? params.ifDateType || cal.dateType : cal.dateType;
            if (dateEl && (dateEl.value || dateEl.innerHTML)) params.date = Date.parseDate(dateEl.value || dateEl.innerHTML, cal.dateFormat, dateType);
            if (params.date) cal.setDate(params.date);
            cal.refresh();
            if (!params.position) cal.showAtElement(params.button || params.displayArea || params.inputField, params.align);
            elsecal.showAt(params.position[0], params.position[1]);
            return false;
        };
        if (params.autoShowOnFocus && params.inputField) {
            params.inputField["onfocus"] = triggerEl["on" + params.eventName];
        };
    } else {
        cal.create(params.flat);
        cal.show();
    }
    return cal;
};
