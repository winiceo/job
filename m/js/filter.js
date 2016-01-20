$(function(){
	searchCol.init();
})
function clickButtonLog(b) {
	var a = (!("configwebname" in window)) ? "" : configwebname;
	if (!fnExist("clickLog")) {
		return
	}
	if (typeof(b) == "object") {
		if (b.data("clickparam")) {
			clickLog(b.data("clickparam") + a)
		} else {
			clog("该节点没有data-clickparam属性，请检查！")
		}
	} else {
		if (typeof(b) == "string") {
			clickLog(b + a)
		} else {
			clog("异常参数类型")
		}
	}
}
function fnExist(fnName) {
	return fnName in this && typeof(eval(fnName)) == "function"
}
var m_selector = {
	jobsArr: "",
	lockWheel: function(a) {
		a = a || window.event;
		if (a.preventDefault) {
			a.preventDefault()
		}
		a.returnValue = false
	},
	disable_scroll: function() {
		var a = this;
		if (window.addEventListener) {
			window.addEventListener("touchmove", a.lockWheel, false)
		}
		window.onmousewheel = document.onmousewheel = a.lockWheel
	},
	enable_scroll: function() {
		var a = this;
		if (window.removeEventListener) {
			window.removeEventListener("touchmove", a.lockWheel, false)
		}
		window.onmousewheel = document.onmousewheel = null
	},
	salary: function(e) {
		var d = $("#" + e);
		var c = '<ul id="salary_level">',
			a = QS_wage;
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		d.find(".sl_list li").off().on("click", function() {
			storage.setJson("search", "salary", $(this).data("val"));
			storage.setJson("search", "salary_cn", $(this).text())
		});
		new iScroll(e)
	},
	education: function(e) {
		var d = $("#" + e);
		var c = '<ul id="education_level">',
			a = QS_education;
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		new iScroll(e)
	},
	experience: function(e) {
		var d = $("#" + e);
		var c = '<ul id="experience_level">',
			a = QS_experience;
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		new iScroll(e)
	},
	states: function(e) {
		var d = $("#" + e);
		var c = '<ul id="tag_level">',
			a = new Array("0,全部","1,合适","2,不合适","3,待定","4,未接通");
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		new iScroll(e)
	},
	applys: function(e) {
		var d = $("#" + e);
		var c = '<ul id="apply_level">',
			a = new Array("0,全部","1,委托投递","2,主动投递");
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		new iScroll(e)
	},
	looks: function(e) {
		var d = $("#" + e);
		var c = '<ul id="look_level">',
			a = new Array("0,全部","1,未查看","2,已查看");
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		new iScroll(e)
	},
	settrs: function(e) {
		var d = $("#" + e);
		var c = '<ul id="settr_level">',
			a = new Array("0,全部","3,三天内","7,一周内","30,一月内");
		if (!d) {
			clog("nodiv");
			return
		}
		$.each(a, function(index, val) {
			var aArr = val.split(",");
			c += '<li data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += "</ul>";
		this.set(d, c);
		new iScroll(e)
	},
	companyType: function(g, f) {
		var e = $("#" + g);
		var d = '<ul id="comType_level">',
			a = this.companyTypeArr;
		if (!e) {
			clog("nodiv");
			return
		}
		for (var b in a) {
			if (f && b == 0) {
				continue
			}
			if (a[b] == "其他") {
				d += '<li data-val="99">' + a[b] + "</li>"
			} else {
				d += '<li data-val="' + b + '">' + a[b] + "</li>"
			}
		}
		d += "</ul>";
		this.set(e, d);
		var c = new iScroll(g);
		return function() {
			c.refresh()
		}
	},
	workType: function(g, f) {
		var e = $("#" + g);
		var d = '<ul id="workType_level">',
			a = this.workTypeArr;
		if (!e) {
			clog("nodiv");
			return
		}
		for (var b in a) {
			if (f && b == 0) {
				continue
			}
			d += '<li data-val="' + b + '">' + a[b] + "</li>"
		}
		d += "</ul>";
		this.set(e, d);
		var c = new iScroll(g);
		return function() {
			c.refresh()
		}
	},
	workStatu: function(f) {
		var e = $("#" + f);
		var d = '<ul id="workStatu_level">',
			a = this.workStatuArr;
		if (!e) {
			clog("nodiv");
			return
		}
		for (var c in a) {
			d += '<li data-val="' + c + '">' + a[c] + "</li>"
		}
		d += "</ul>";
		this.set(e, d);
		var b = new iScroll(f);
		return function() {
			b.refresh()
		}
	},
	industry: function(a, p, e) {
		var n = $("#" + a),
			l = "",
			m = this;
		if (!n) {
			clog("nodiv");
			return
		}
		n.before('<div id="sValDiv" class="sValDiv"><div></div></div>');
		l += '<div id="ind_level1" class="sl_level1"><div><ul>';
		for (var d in indArr) {
			l += '<li data-key="' + indArr[d].level1[1] + '">' + indArr[d].level1[0] + "</li>"
		}
		l += '		</ul></div></div><div id="ind_level2" class="sl_level2"><div><ul>';
		for (var d in indArr) {
			for (var f in indArr[d].level2) {
				if (e && f == 0) {
					continue
				}
				l += '<li data-val="' + d + "," + indArr[d].level2[f][1] + '">' + indArr[d].level2[f][0] + "</li>"
			}
			break
		}
		l += "		</ul></div></div>";
		this.set(n, l);
		var o = m.getIndVal(indArr);
		if (o) {
			for (var h in o) {
				$("#sValDiv>div").append('<div data-val="' + o[h].ids + '">' + o[h].txt + '<em class="close"></em></div>')
			}
		}
		m.markMulti();
		$("#ind_level1 li").first().addClass("on");
		if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
			$(".sl_level1,.sl_level2").css("height", window.innerHeight - 45 - 108)
		} else {
			$(".sl_level1,.sl_level2").css("height", window.innerHeight - n.offset().top)
		}
		if (!p) {
			$("#ind_level2").delegate("li", "click", function() {
				if ($(this).text() == "全部") {
					storage.setJson("search", "industry_cn", $("#ind_level1").find(".on").text())
				} else {
					storage.setJson("search", "industry_cn", $(this).text())
				}
				storage.setJson("search", "industry", $(this).data("val"))
			})
		}
		$("#ind_level1 li").on("click", function() {
			$(this).parent().find("li").removeClass("on");
			$(this).addClass("on");
			var j = $(this).data("key"),
				q = "";
			for (var i in indArr[j].level2) {
				if (e && i == 0) {
					continue
				}
				q += '<li data-val="' + j + "," + indArr[j].level2[i][1] + '">' + indArr[j].level2[i][0] + "</li>"
			}
			$("#ind_level2 ul").empty().html(q);
			m.markMulti();
			b.refresh()
		});
		var c = new iScroll("ind_level1");
		var b = new iScroll("ind_level2");
		var g = new iScroll("sValDiv", {
			vScroll: false
		});
		return function() {
			c.refresh();
			b.refresh();
			g.refresh()
		}
	},
	markMulti: function() {
		if ($("#sValDiv>div>div").length) {
			$("#sValDiv>div>div").each(function() {
				var b = $(this);
				var a = b.data("val");
				a = a.toString();
				if (a.split(",").length == 2) {
					$(".sl_level2 li[data-val='" + b.data("val") + "']").addClass("on")
				} else {
					$(".sl_level1 li[data-val='" + b.data("val") + "']").addClass("on")
				}
			})
		}
	},
	getIndVal: function(e) {
		var g = $("#industry_dummy").val(),
			a = "",
			h = "",
			d = "",
			f = [];
		if (!g) {
			return false
		}
		g.toString();
		a = g.split("_");
		for (var c in a) {
			if (a[c]) {
				h = a[c].split(",")[0];
				d = a[c].split(",")[1];
				if (d) {
					for (var b in e[h].level2) {
						if (e[h].level2[b][1] == d) {
							f.push({
								txt: e[h].level2[b][0],
								ids: h + "," + d
							})
						}
					}
				} else {
					f.push({
						txt: e[h].level1[0],
						ids: h
					})
				}
			}
		}
		if (!f) {
			return false
		}
		return f
	},
	jobs: function(a, i, d) {
		var h = $("#" + a);
		var e = "",
			f = this;
		if (!h) {
			clog("nodiv");
			return
		}
		e += '<div id="loc_level1_' + a + '" class="sl_level1"><div><ul>';
		$.each(QS_jobs_parent, function(indexParent, valParent) {
			var i = valParent.split(",")[0],
				cArrParent = QS_jobs[i].split("|");
			$.each(cArrParent, function(index, val) {
				var cArr = val.split(",");
				e += '<li data-key="' + cArr[1] + '" data-val="' + i + ',' + cArr[0] + '">' + cArr[1] + "</li>";
			});
		});
		e += '		</ul></div></div><div id="loc_level2_' + a + '" class="sl_level2"><div><ul>';
		e += "		</ul></div></div>";
		this.set(h, e);
		/* 恢复选中 */
		if ($("#topclass").val()) {
			var restoreId = $("#topclass").val();
			var restoreArr = $("#loc_level1_" + a + " li");
			$.each(restoreArr, function(index, val) {
				if ($(this).data("val") == restoreId) {
					$(this).addClass('on');
				};
			});
			var gArr = QS_jobs[restoreId].split("|"),
				g = this.getFistJob(gArr, restoreId);
			$("#loc_level2_" + a + " ul").empty().html(g);
		} else {
			$("#loc_level1_" + a + " li").first().addClass("on");
			var gidp = $("#loc_level1_" + a + " li").first().data("val").split(",")[0],
				gid = $("#loc_level1_" + a + " li").first().data("val").split(",")[1],
				gArr = QS_jobs[gid].split("|"),
				g = this.getFistJob(gArr, gidp, gid);
			$("#loc_level2_" + a + " ul").empty().html(g);
		};
		if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
			$(".sl_level1,.sl_level2").css("height", window.innerHeight - 45 - 108)
		} else {
			$(".sl_level1,.sl_level2").css("height", window.innerHeight - h.offset().top)
		}
		$("#loc_level1_" + a + " li").on("click", function() {
			$(this).parent().find("li").removeClass("on");
			$(this).addClass("on");
			var mp = $(this).data("val").split(",")[0],
				m = $(this).data("val").split(",")[1],
				n = "",
				mArr = QS_jobs[m].split("|");
			$.each(mArr, function(indexlevel2, vallevel2) {
				var mArrLeval2 = vallevel2.split(",");
				n += '<li data-val="' + mp + "," + m + "," + mArrLeval2[0] + '">' + mArrLeval2[1] + "</li>";
			});
			$("#loc_level2_" + a + " ul").empty().html(n);
			j.refresh();
		});
		var b = new iScroll("loc_level1_" + a);
		var j = new iScroll("loc_level2_" + a);
		return b
	},
	markThirdMulti: function() {
		if ($("#sValDiv>div>div").length) {
			$("#sValDiv>div>div").each(function() {
				var b = $(this);
				var a = b.data("val");
				a = a.toString();
				if (a.split(",").length == 3) {
					$(".sl-th li[data-key='" + b.data("val") + "']").addClass("on")
				} else {
					if (a.split(",").length == 2) {
						$(".sl-se li[data-key='" + b.data("val") + "']").addClass("on")
					} else {
						$(".sl-fr li[data-key='" + b.data("val") + "']").addClass("on")
					}
				}
			})
		}
	},
	getJobsVal: function() {
		var g = $("#jobs_dummy").val(),
			a = "",
			h = "",
			d = "",
			b = "",
			f = [],
			e = this;
		if (!g) {
			return false
		}
		g.toString();
		a = g.split("_");
		for (var c in a) {
			if (a[c]) {
				h = a[c].split(",")[0];
				d = a[c].split(",")[1];
				b = a[c].split(",")[2];
				if (b) {
					f.push({
						txt: e.jobsArr[h][d][b].cn,
						ids: h + "," + d + "," + b
					})
				} else {
					if (d) {
						f.push({
							txt: e.jobsArr[h][d].cn,
							ids: h + "," + d
						})
					} else {
						f.push({
							txt: e.jobsArr[h].cn,
							ids: h
						})
					}
				}
			}
		}
		if (!f) {
			return false
		}
		return f
	},
	nearCityCatch: "",
	loadingNear: false,
	soCity: function(a, i, d) {
		var h = $("#" + a);
		var e = "",
			f = this;
		if (!h) {
			clog("nodiv");
			return
		}
		e += '<div id="loc_level1_' + a + '" class="sl_level1"><div><ul>';
		$.each(QS_city_parent, function(index, val) {
			 var cArr = val.split(",");
			e += '<li data-key="' + cArr[1] + '" data-val="' + cArr[0] + '">' + cArr[1] + "</li>"
		});
		e += '		</ul></div></div><div id="loc_level2_' + a + '" class="sl_level2"><div><ul>';
		e += "		</ul></div></div>";
		this.set(h, e);
		/* 恢复选中 */
		if ($("#district").val()) {
			var restoreId = $("#district").val();
			var restoreArr = $("#loc_level1_" + a + " li");
			$.each(restoreArr, function(index, val) {
				if ($(this).data("val") == restoreId) {
					$(this).addClass('on');
				};
			});
			var gArr = QS_city[restoreId].split("|"),
				g = this.getFistCity(gArr, restoreId);
			$("#loc_level2_" + a + " ul").empty().html(g);
		} else {
			$("#loc_level1_" + a + " li").first().addClass("on");
			var gid = $("#loc_level1_" + a + " li").first().data("val"),
				gArr = QS_city[gid].split("|"),
				g = this.getFistCity(gArr, gid);
			$("#loc_level2_" + a + " ul").empty().html(g);
		};
		if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
			$(".sl_level1,.sl_level2").css("height", window.innerHeight - 45 - 108)
		} else {
			$(".sl_level1,.sl_level2").css("height", window.innerHeight - h.offset().top)
		}
		$("#loc_level1_" + a + " li").on("click", function() {
			$(this).parent().find("li").removeClass("on");
			$(this).addClass("on");
			var m = $(this).data("val"),
				n = "",
				mArr = QS_city[m].split("|");
			$.each(mArr, function(indexlevel2, vallevel2) {
				var mArrLeval2 = vallevel2.split(",");
				n += '<li data-val="' + m + "," + mArrLeval2[0] + '">' + mArrLeval2[1] + "</li>";
			});
			$("#loc_level2_" + a + " ul").empty().html(n);
			j.refresh();
		});
		var b = new iScroll("loc_level1_" + a);
		var j = new iScroll("loc_level2_" + a);
		return b
	},
	getFistCity: function(arr, pid) {
		var b = "";
		$.each(arr, function(index, val) {
			var sArr = val.split(",");
			b += '<li data-val="' + pid + "," + sArr[0] + '">' + sArr[1] + "</li>";
		});
		return b;
	},
	getFistJob: function(arr, pid, sid) {
		var b = "";
		$.each(arr, function(index, val) {
			var sArr = val.split(",");
			b += '<li data-val="' + sid + "," + pid + "," + sArr[0] + '">' + sArr[1] + "</li>";
		});
		return b;
	},
	more: function(b) {
		var j = $("#" + b);
		var f = '<div id="more_level">';
		var g = this;
		var refTimeArr = new Array("0,不限时间","3,3天内","7,7天内","15,15天内","30,30天内");
		var sexArr = new Array("0,不限","1,男","2,女");
		var talentArr = new Array("0,不限","1,普通","2,高级");
		var photoArr = new Array("0,无","1,有");
		if (!j) {
			clog("nodiv");
			return
		}
		var h = "",
			a = "",
			c = "",
			k = "",
			d = "";
		h += '<li class="single_sl" data-type="education"  data-val="0">全部' + "</li>";
		$.each(QS_education, function(index, val) {
			var aArr = val.split(",");
			h += '<li class="single_sl" data-type="education"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		$.each(refTimeArr, function(index, val) {
			var aArr = val.split(",");
			a += '<li class="single_sl" data-type="settr"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		$.each(sexArr, function(index, val) {
			var aArr = val.split(",");
			c += '<li class="single_sl" data-type="sex"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		$.each(talentArr, function(index, val) {
			var aArr = val.split(",");
			k += '<li class="single_sl" data-type="talent"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		$.each(photoArr, function(index, val) {
			var aArr = val.split(",");
			d += '<li class="single_sl" data-type="photo"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		f += '<div><div class="more_child_div settr clear"><div class="more_child_title">刷新时间</div><ul>' + a + '</ul></div><div class="cline"></div><div class="more_child_div education clear"><div class="more_child_title">最高学历</div><ul>' + h + '</ul></div><div class="cline"></div><div class="more_child_div sex clear"><div class="more_child_title">性别要求</div><ul>' + c + '</ul></div><div class="cline"></div><div class="more_child_div talent clear"><div class="more_child_title">简历等级</div><ul>' + k + '</ul></div><div class="cline"></div><div class="more_child_div photo clear"><div class="more_child_title">照片简历</div><ul>' + d + '</ul></div><div class="cline"></div><div class="more_foot"></div></div>';
		f += "</div>";
		g.set(j, f, true);
		j.find(".more_child_div li").off("click").on("click", function() {
			if ($(this).hasClass("single_sl")) {
				if (!$(this).hasClass("on")) {
					$(this).parent().find("li").removeClass("on");
					$(this).addClass("on");
					g.showClear(j)
				} else {
					$(this).parent().find("li").removeClass("on");
					g.showClear(j)
				}
			} else {
				if ($(this).data("val") == 0) {
					if (!$(this).hasClass("on")) {
						$(this).parent().find("li").removeClass("on");
						$(this).addClass("on");
						g.showClear(j)
					} else {
						$(this).parent().find("li").removeClass("on");
						g.showClear(j)
					}
				} else {
					$(this).parent().find("li").first().removeClass("on");
					if (!$(this).hasClass("on")) {
						$(this).addClass("on");
						g.showClear(j)
					} else {
						$(this).removeClass("on");
						g.showClear(j)
					}
				}
			}
			return
		});
		j.parent().find(".clear_more").off("click").on("click", function() {
			var h = j.find("li.on");
			h.each(function() {
				var i = $(this).data("type");
				$("#h" + i).val('');
				$("#h" + i + "_cn").val('');
			});
			j.find("li").removeClass("on");
			$(this).removeClass("on");
		});
		j.parent().find(".more_ok").off("click").on("click", function() {
			g.save(j);
			$("#cleanSearch").remove();
		});
		g.setMore(j);
		g.showClear(j);
		new iScroll("more_level")
	},
	morejob: function(b) {
		var j = $("#" + b);
		var f = '<div id="more_level">';
		var g = this;
		var refTimeArr = new Array("0,不限时间","3,3天内","7,7天内","15,15天内","30,30天内");
		var sexArr = new Array("0,不限","1,男","2,女");
		var talentArr = new Array("0,不限","1,普通","2,高级");
		var photoArr = new Array("0,无","1,有");
		if (!j) {
			clog("nodiv");
			return
		}
		var h = "",
			a = "",
			c = "",
			k = "",
			d = "";
		h += '<li class="single_sl" data-type="education"  data-val="0">全部' + "</li>";
		$.each(QS_education, function(index, val) {
			var aArr = val.split(",");
			h += '<li class="single_sl" data-type="education"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		$.each(refTimeArr, function(index, val) {
			var aArr = val.split(",");
			a += '<li class="single_sl" data-type="settr"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += '<li class="single_sl" data-type="experience"  data-val="0">全部' + "</li>";
		$.each(QS_experience, function(index, val) {
			var aArr = val.split(",");
			c += '<li class="single_sl" data-type="experience"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		k += '<li class="single_sl" data-type="nature"  data-val="0">全部' + "</li>";
		$.each(QS_jobsnature, function(index, val) {
			var aArr = val.split(",");
			k += '<li class="single_sl" data-type="nature"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		d += '<li class="single_sl" data-type="scale"  data-val="0">全部' + "</li>";
		$.each(QS_scale, function(index, val) {
			var aArr = val.split(",");
			d += '<li class="single_sl" data-type="scale"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		f += '<div><div class="more_child_div settr clear"><div class="more_child_title">刷新时间</div><ul>' + a + '</ul></div><div class="cline"></div><div class="more_child_div education clear"><div class="more_child_title">学历要求</div><ul>' + h + '</ul></div><div class="cline"></div><div class="more_child_div experience clear"><div class="more_child_title">工作年限</div><ul>' + c + '</ul></div><div class="cline"></div><div class="more_child_div nature clear"><div class="more_child_title">工作性质</div><ul>' + k + '</ul></div><div class="cline"></div><div class="more_child_div scale clear"><div class="more_child_title">企业规模</div><ul>' + d + '</ul></div><div class="cline"></div><div class="more_foot"></div></div>';
		f += "</div>";
		g.set(j, f, true);
		j.find(".more_child_div li").off("click").on("click", function() {
			if ($(this).hasClass("single_sl")) {
				if (!$(this).hasClass("on")) {
					$(this).parent().find("li").removeClass("on");
					$(this).addClass("on");
					g.showClear(j)
				} else {
					$(this).parent().find("li").removeClass("on");
					g.showClear(j)
				}
			} else {
				if ($(this).data("val") == 0) {
					if (!$(this).hasClass("on")) {
						$(this).parent().find("li").removeClass("on");
						$(this).addClass("on");
						g.showClear(j)
					} else {
						$(this).parent().find("li").removeClass("on");
						g.showClear(j)
					}
				} else {
					$(this).parent().find("li").first().removeClass("on");
					if (!$(this).hasClass("on")) {
						$(this).addClass("on");
						g.showClear(j)
					} else {
						$(this).removeClass("on");
						g.showClear(j)
					}
				}
			}
			return
		});
		j.parent().find(".clear_more").off("click").on("click", function() {
			var h = j.find("li.on");
			h.each(function() {
				var i = $(this).data("type");
				$("#h" + i).val('');
				$("#h" + i + "_cn").val('');
			});
			j.find("li").removeClass("on");
			$(this).removeClass("on");
		});
		j.parent().find(".more_ok").off("click").on("click", function() {
			g.saveJob(j);
			$("#cleanSearch").remove();
		});
		g.setMoreJob(j);
		g.showClear(j);
		new iScroll("more_level")
	},
	morenearjob: function(b) {
		var j = $("#" + b);
		var f = '<div id="more_level">';
		var g = this;
		var refTimeArr = new Array("0,不限时间","3,3天内","7,7天内","15,15天内","30,30天内");
		var sexArr = new Array("0,不限","1,男","2,女");
		var talentArr = new Array("0,不限","1,普通","2,高级");
		var photoArr = new Array("0,无","1,有");
		if (!j) {
			clog("nodiv");
			return
		}
		var h = "",
			a = "",
			c = "",
			k = "",
			d = "";
		h += '<li class="single_sl" data-type="education"  data-val="0">全部' + "</li>";
		$.each(QS_education, function(index, val) {
			var aArr = val.split(",");
			h += '<li class="single_sl" data-type="education"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		$.each(refTimeArr, function(index, val) {
			var aArr = val.split(",");
			a += '<li class="single_sl" data-type="settr"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		c += '<li class="single_sl" data-type="experience"  data-val="0">全部' + "</li>";
		$.each(QS_experience, function(index, val) {
			var aArr = val.split(",");
			c += '<li class="single_sl" data-type="experience"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		k += '<li class="single_sl" data-type="nature"  data-val="0">全部' + "</li>";
		$.each(QS_jobsnature, function(index, val) {
			var aArr = val.split(",");
			k += '<li class="single_sl" data-type="nature"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		d += '<li class="single_sl" data-type="scale"  data-val="0">全部' + "</li>";
		$.each(QS_scale, function(index, val) {
			var aArr = val.split(",");
			d += '<li class="single_sl" data-type="scale"  data-val="' + aArr[0] + '">' + aArr[1] + "</li>";
		});
		f += '<div><div class="more_child_div settr clear"><div class="more_child_title">刷新时间</div><ul>' + a + '</ul></div><div class="cline"></div><div class="more_child_div education clear"><div class="more_child_title">学历要求</div><ul>' + h + '</ul></div><div class="cline"></div><div class="more_child_div experience clear"><div class="more_child_title">工作年限</div><ul>' + c + '</ul></div><div class="cline"></div><div class="more_child_div nature clear"><div class="more_child_title">工作性质</div><ul>' + k + '</ul></div><div class="cline"></div><div class="more_child_div scale clear"><div class="more_child_title">企业规模</div><ul>' + d + '</ul></div><div class="cline"></div><div class="more_foot"></div></div>';
		f += "</div>";
		g.set(j, f, true);
		j.find(".more_child_div li").off("click").on("click", function() {
			if ($(this).hasClass("single_sl")) {
				if (!$(this).hasClass("on")) {
					$(this).parent().find("li").removeClass("on");
					$(this).addClass("on");
					g.showClear(j)
				} else {
					$(this).parent().find("li").removeClass("on");
					g.showClear(j)
				}
			} else {
				if ($(this).data("val") == 0) {
					if (!$(this).hasClass("on")) {
						$(this).parent().find("li").removeClass("on");
						$(this).addClass("on");
						g.showClear(j)
					} else {
						$(this).parent().find("li").removeClass("on");
						g.showClear(j)
					}
				} else {
					$(this).parent().find("li").first().removeClass("on");
					if (!$(this).hasClass("on")) {
						$(this).addClass("on");
						g.showClear(j)
					} else {
						$(this).removeClass("on");
						g.showClear(j)
					}
				}
			}
			return
		});
		j.parent().find(".clear_more").off("click").on("click", function() {
			var h = j.find("li.on");
			h.each(function() {
				var i = $(this).data("type");
				$("#h" + i).val('');
				$("#h" + i + "_cn").val('');
			});
			j.find("li").removeClass("on");
			$(this).removeClass("on");
		});
		j.parent().find(".more_ok").off("click").on("click", function() {
			g.saveNearJob(j);
			$("#cleanSearch").remove();
		});
		g.setMoreJob(j);
		g.showClear(j);
		new iScroll("more_level")
	},
	setMore: function(e) {
		var a = [
			["settr"],
			["education"],
			["sex"],
			["talent"],
			["photo"]
		];
		for (var b in a) {
			e.find(".more_child_div." + a[b][0] + " li[data-val='" + $("#h" + a[b][0]).val() + "']").addClass("on");
		}
	},
	setMoreJob: function(e) {
		var a = [
			["settr"],
			["education"],
			["experience"],
			["nature"],
			["scale"]
		];
		for (var b in a) {
			e.find(".more_child_div." + a[b][0] + " li[data-val='" + $("#h" + a[b][0]).val() + "']").addClass("on");
		}
	},
	showClear: function(a) {
		if (!a.find("li").hasClass("on")) {
			a.parent().find(".clear_more").removeClass("on")
		} else {
			a.parent().find(".clear_more").addClass("on")
		}
	},
	save: function(f) {
		var h = f.find("li.on");
		var a = [
			["settr"],
			["education"],
			["sex"],
			["talent"],
			["photo"]
		];
		if (h.length == 0) {
			searchCol.makeUrl("resume_list");
		} else {
			h.each(function() {
				var i = $(this).data("type"),
					k = $(this).data("val"),
					j = $(this).text();
				$("#h" + i).val(k);
				$("#h" + i + "_cn").val(j);
			});
			searchCol.makeUrl("resume_list");
		}
		$(".more_main").css("-webkit-transform", "translateX(100%)");
		$(".select_bar_more").removeClass("on");
	},
	saveJob: function(f) {
		var h = f.find("li.on");
		var a = [
			["settr"],
			["education"],
			["experience"],
			["nature"],
			["scale"]
		];
		if (h.length == 0) {
			searchCol.makeUrl("job_list");
		} else {
			h.each(function() {
				var i = $(this).data("type"),
					k = $(this).data("val"),
					j = $(this).text();
				$("#h" + i).val(k);
				$("#h" + i + "_cn").val(j);
			});
			searchCol.makeUrl("job_list");
		}
		$(".more_main").css("-webkit-transform", "translateX(100%)");
		$(".select_bar_more").removeClass("on");
	},
	saveNearJob: function(f) {
		var h = f.find("li.on");
		var a = [
			["settr"],
			["education"],
			["experience"],
			["nature"],
			["scale"]
		];
		if (h.length == 0) {
			searchCol.makeUrl("job_list");
		} else {
			h.each(function() {
				var i = $(this).data("type"),
					k = $(this).data("val"),
					j = $(this).text();
				$("#h" + i).val(k);
				$("#h" + i + "_cn").val(j);
			});
			searchCol.makeUrl("jobnear_list");
		}
		$(".more_main").css("-webkit-transform", "translateX(100%)");
		$(".select_bar_more").removeClass("on");
	},
	set: function(b, a, c) {
		if (b.find(".sl_list>div").length) {
			b.find(".sl_list>div").empty().html(a)
		} else {
			if (b.find(".sl-main>div").length) {
				b.find(".sl-main>div").empty().html(a)
			} else {
				b.empty().html(a)
			}
		}
		if (c) {
			$("#more_level").css("height", window.innerHeight - 50)
		} else {
			if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
				b.css("height", window.innerHeight - 45 - 108)
			} else {
				b.css("height", window.innerHeight - b.offset().top)
			}
		}
	},
	rtrim: function(a) {
		return a.substring(0, a.length - 1)
	}
};
var searchCol = {
	init : function(){
		var $this = this;
		$(window).scroll(function(){
			if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
		        $(".select_bar").css({position:"fixed",top:0});
		    }else{
		        $(".select_bar").css("position","relative");
		    }
		})
		$(".select_bar .lock").on("click",function(){
			m_selector.enable_scroll()
			$this.close()
		})
		$(".select_bar li").on("click",function(e){
			if($(this).hasClass("on") == true){
				m_selector.enable_scroll()
				$this.close();
				return;
			}
			m_selector.disable_scroll()
			$(".select_bar li").removeClass("on");
			$this.lockWin("on");
			$(this).addClass("on");
			var goDiv = $("#"+$(this).data("go")+"List");
			$(".select_bar_div").addClass("on");
			$(".select_bar_div>div").hide();
			$(".more_main").css('-webkit-transform', 'translateX(100%)');
			$("html").css("overflow","hidden");
			switch($(this).data("go")){
				case "salary":
					goDiv.show();
					m_selector.salary($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%")
					if(window.location.href.indexOf("earnMore=")!=-1){
						clickButtonLog("from=chr_home_salary")
					}else{
						clickButtonLog("from=chr_list_changesalary")
					}
					goDiv.find("li").on("click",function(){
						$this.lockWin("off");
						$(this).off("click");
						$("#cleanSearch").remove();
						if(window.location.href.indexOf("earnMore=")!=-1){
							clickButtonLog("from=chr_home_salarydetail")
							search.doit(true,getQueryStringRegExp("earnMore"));
						}else{
							clickButtonLog("from=chr_list_salarydetail");
							search.doit(true);
						}
					})
					break;
				case "education":
					goDiv.show();
					m_selector.education($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find("li").on("click",function(){
						$("#heducation").val($(this).data('val'));
						$("#heducation_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("interview_list");
					})
					break;
				case "experience":
					goDiv.show();
					m_selector.experience($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","25%");
					goDiv.find("li").on("click",function(){
						$("#hexperience").val($(this).data('val'));
						$("#hexperience_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("interview_list");
					})
					break;
				case "jobcollect":
					goDiv.show();
					m_selector.jobs($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","50%")
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#hcategory").val($(this).data('val').split(",")[1]);
						$("#hsubclass").val($(this).data('val').split(",")[2]);
						$("#hcategory_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("interview_list");
					})
					break;
				case "educationmt":
					goDiv.show();
					m_selector.education($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find("li").on("click",function(){
						$("#heducation").val($(this).data('val'));
						$("#heducation_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("my_attention");
					})
					break;
				case "experiencemt":
					goDiv.show();
					m_selector.experience($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","25%");
					goDiv.find("li").on("click",function(){
						$("#hexperience").val($(this).data('val'));
						$("#hexperience_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("my_attention");
					})
					break;
				case "jobmyattention":
					goDiv.show();
					m_selector.jobs($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","50%")
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#hcategory").val($(this).data('val').split(",")[1]);
						$("#hsubclass").val($(this).data('val').split(",")[2]);
						$("#hcategory_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("my_attention");
					})
					break;
				case "state":
					goDiv.show();
					m_selector.states($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find("li").on("click",function(){
						$("#hstate").val($(this).data('val'));
						$("#hstate_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("apply_jobs");
					})
					break;
				case "statedownload":
					goDiv.show();
					m_selector.states($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find("li").on("click",function(){
						$("#hstate").val($(this).data('val'));
						$("#hstate_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("down_resume_list");
					})
					break;
				case "apply":
					goDiv.show();
					m_selector.applys($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#his_apply").val($(this).data('val'));
						$("#his_apply_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("apply_jobs");
					})
					break;
				case "look":
					goDiv.show();
					m_selector.looks($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","75%");
					goDiv.find("li").on("click",function(){
						$("#hlook").val($(this).data('val'));
						$("#hlook_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("apply_jobs");
					})
					break;
				case "lookinterview":
					goDiv.show();
					m_selector.looks($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","33%");
					goDiv.find("li").on("click",function(){
						$("#hlook").val($(this).data('val'));
						$("#hlook_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("interview_list");
					})
					break;
				case "job":
					goDiv.show();
					$this.markLine($(".select_bar"),"show","25%");
					goDiv.find("li").on("click",function(){
						$("#hjobsid").val($(this).data('val'));
						$("#hjobname").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("apply_jobs");
					})
					break;
				case "jobinterview":
					goDiv.show();
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find("li").on("click",function(){
						$("#hjobsid").val($(this).data('val'));
						$("#hjobname").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("interview_list");
					})
					break;
				case "jobview":
					goDiv.show();
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find("li").on("click",function(){
						$("#hjobsid").val($(this).data('val'));
						$("#hjobname").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("view_jobs_log");
					})
					break;
				case "settrinterview":
					goDiv.show();
					m_selector.settrs($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","66%");
					goDiv.find("li").on("click",function(){
						$("#hsettr").val($(this).data('val'));
						$("#hsettr_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("interview_list");
					})
					break;
				case "settrdownload":
					goDiv.show();
					m_selector.settrs($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hsettr").val($(this).data('val'));
						$("#hsettr_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("down_resume_list");
					})
					break;
				case "settrview":
					goDiv.show();
					m_selector.settrs($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hsettr").val($(this).data('val'));
						$("#hsettr_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("view_jobs_log");
					})
					break;
				case "settrcollect":
					goDiv.show();
					m_selector.settrs($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hsettr").val($(this).data('val'));
						$("#hsettr_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("favorites_list");
					})
					break;
				case "settrcollect":
					goDiv.show();
					m_selector.settrs($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hsettr").val($(this).data('val'));
						$("#hsettr_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("favorites_list");
					})
					break;
				case "settrmyattention":
					goDiv.show();
					m_selector.settrs($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hsettr").val($(this).data('val'));
						$("#hsettr_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("my_attention");
					})
					break;
				case "city":
					goDiv.show();
					m_selector.soCity($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","0%")
					if(window.location.href.indexOf("earnMore=")!=-1){
						clickButtonLog("from=chr_home_city");
					}else{
						clickButtonLog("from=chr_list_changecity")
					}
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$this.lockWin("off");
						$("#cleanSearch").remove();
						if(window.location.href.indexOf("earnMore=")!=-1){
							clickButtonLog("from=chr_home_citydetail")
							search.doit(true,getQueryStringRegExp("earnMore"));
						}else{
							clickButtonLog("from=chr_list_citydetail");
							search.doit(true);
						}
					})
					break;
				case "cityresume":
					goDiv.show();
					m_selector.soCity($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#hdistrict").val($(this).data('val').split(",")[0]);
						$("#hsdistrict").val($(this).data('val').split(",")[1]);
						$("#hdistrict_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("resume_list");
					})
					break;
				case "jobresume":
					goDiv.show();
					m_selector.jobs($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","25%");
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#htopclass").val($(this).data('val').split(",")[0]);
						$("#hcategory").val($(this).data('val').split(",")[1]);
						$("#hsubclass").val($(this).data('val').split(",")[2]);
						$("#hcategory_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("resume_list");
					})
					break;
				case "cityjob":
					goDiv.show();
					m_selector.soCity($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#hdistrict").val($(this).data('val').split(",")[0]);
						$("#hsdistrict").val($(this).data('val').split(",")[1]);
						$("#hdistrict_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("job_list");
					})
					break;
				case "citynearjob":
					goDiv.show();
					m_selector.soCity($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","0%");
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#hdistrict").val($(this).data('val').split(",")[0]);
						$("#hsdistrict").val($(this).data('val').split(",")[1]);
						$("#hdistrict_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("jobnear_list");
					})
					break;
				case "jobjob":
					goDiv.show();
					m_selector.jobs($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","25%");
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#htopclass").val($(this).data('val').split(",")[0]);
						$("#hcategory").val($(this).data('val').split(",")[1]);
						$("#hsubclass").val($(this).data('val').split(",")[2]);
						$("#hcategory_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("job_list");
					})
					break;
				case "jobnearjob":
					goDiv.show();
					m_selector.jobs($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","25%");
					goDiv.find(".sl_level2").delegate("li:not(.fail)","click",function(){
						$("#htopclass").val($(this).data('val').split(",")[0]);
						$("#hcategory").val($(this).data('val').split(",")[1]);
						$("#hsubclass").val($(this).data('val').split(",")[2]);
						$("#hcategory_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("jobnear_list");
					})
					break;
				case "wagejob":
					goDiv.show();
					m_selector.salary($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hwage").val($(this).data('val'));
						$("#hwage_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("job_list");
					})
					break;
				case "wagenearjob":
					goDiv.show();
					m_selector.salary($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hwage").val($(this).data('val'));
						$("#hwage_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("jobnear_list");
					})
					break;
				case "experienceresume":
					goDiv.show();
					m_selector.experience($(this).data("go")+"List",true);
					$this.markLine($(".select_bar"),"show","50%");
					goDiv.find("li").on("click",function(){
						$("#hexperience").val($(this).data('val'));
						$("#hexperience_cn").val($(this).text());
						$this.lockWin("off");
						$(this).off("click");
						$this.makeUrl("resume_list");
					})
					break;
				case "more":
					$(".select_bar_div").removeClass("on");
					$this.lockWin("off");
					$(".select_bar_more").addClass("on");
					m_selector.more($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","75%");
					$(".more_main").css('-webkit-transform', 'translateX(0%)');
					$(".more_main .close").off("click").on("click",function(){
						$(".more_main").css('-webkit-transform', 'translateX(100%)');
						$(".select_bar_more").removeClass("on");
						$this.markLine($(".select_bar"),"hide");
						$this.close();
						m_selector.enable_scroll()
					})
					$(".more_main .title").off("click");
					break;
				case "morejob":
					$(".select_bar_div").removeClass("on");
					$this.lockWin("off");
					$(".select_bar_more").addClass("on");
					m_selector.morejob($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","75%");
					$(".more_main").css('-webkit-transform', 'translateX(0%)');
					$(".more_main .close").off("click").on("click",function(){
						$(".more_main").css('-webkit-transform', 'translateX(100%)');
						$(".select_bar_more").removeClass("on");
						$this.markLine($(".select_bar"),"hide");
						$this.close();
						m_selector.enable_scroll()
					})
					$(".more_main .title").off("click");
					break;
				case "morenearjob":
					$(".select_bar_div").removeClass("on");
					$this.lockWin("off");
					$(".select_bar_more").addClass("on");
					m_selector.morenearjob($(this).data("go")+"List");
					$this.markLine($(".select_bar"),"show","75%");
					$(".more_main").css('-webkit-transform', 'translateX(0%)');
					$(".more_main .close").off("click").on("click",function(){
						$(".more_main").css('-webkit-transform', 'translateX(100%)');
						$(".select_bar_more").removeClass("on");
						$this.markLine($(".select_bar"),"hide");
						$this.close();
						m_selector.enable_scroll()
					})
					$(".more_main .title").off("click");
					break;
				default:
			 		break;
			}
			e.preventDefault();
		})
		$(".w-icon-searchjobs").on('click', function(event) {
			$this.makeUrl("job_list");
		});
		$(".w-icon-searchresumes").on('click', function(event) {
			$this.makeUrl("resume_list");
		});
	},
	makeUrl : function(type){
		var	state = $("#hstate").val(),
			state_cn = $("#hstate_cn").val(),
			jobsid = $("#hjobsid").val(),
			jobname = $("#hjobname").val(),
			is_apply = $("#his_apply").val(),
			is_apply_cn = $("#his_apply_cn").val(),
			look = $("#hlook").val(),
			look_cn = $("#hlook_cn").val(),
			settr = $("#hsettr").val(),
			settr_cn = $("#hsettr_cn").val(),
			education = $("#heducation").val(),
			education_cn = $("#heducation_cn").val(),
			experience = $("#hexperience").val(),
			experience_cn = $("#hexperience_cn").val(),
			topclass = $("#htopclass").val(),
			category = $("#hcategory").val(),
			subclass = $("#hsubclass").val(),
			category_cn = $("#hcategory_cn").val(),
			district = $("#hdistrict").val(),
			sdistrict = $("#hsdistrict").val(),
			district_cn = $("#hdistrict_cn").val(),
			sex = $("#hsex").val(),
			sex_cn = $("#hsex_cn").val(),
			talent = $("#htalent").val(),
			talent_cn = $("#htalent_cn").val(),
			photo = $("#hphoto").val(),
			photo_cn = $("#hphoto_cn").val(),
			wage = $("#hwage").val(),
			wage_cn = $("#hwage_cn").val(),
			nature = $("#hnature").val(),
			nature_cn = $("#hnature_cn").val(),
			scale = $("#hscale").val(),
			scale_cn = $("#hscale_cn").val(),
			key = $("#key").val(),
			url = "",
			lng = $("#hlng").val(),
			lat = $("#hlat").val();
		switch(type){
				case "apply_jobs":
					url = "?state=" + state + "&state_cn=" + state_cn + "&jobsid=" + jobsid + "&jobname=" + jobname + "&is_apply=" + is_apply + "&is_apply_cn=" + is_apply_cn + "&look=" + look + "&look_cn=" + look_cn + "";
					break;
				case "interview_list":
					url = "?jobsid=" + jobsid + "&jobname=" + jobname + "&look=" + look + "&look_cn=" + look_cn + "&settr=" + settr + "&settr_cn=" + settr_cn + "";
					break;
				case "down_resume_list":
					url = "?state=" + state + "&state_cn=" + state_cn + "&settr=" + settr + "&settr_cn=" + settr_cn + "";
					break;
				case "view_jobs_log":
					url = "?jobsid=" + jobsid + "&jobname=" + jobname + "&settr=" + settr + "&settr_cn=" + settr_cn + "";
					break;
				case "favorites_list":
					url = "?education=" + education + "&education_cn=" + education_cn + "&experience=" + experience + "&experience_cn=" + experience_cn + "&category=" + category + "&subclass=" + subclass + "&category_cn=" + category_cn + "&settr=" + settr + "&settr_cn=" + settr_cn + "";
					break;
				case "my_attention":
					url = "?education=" + education + "&education_cn=" + education_cn + "&experience=" + experience + "&experience_cn=" + experience_cn + "&category=" + category + "&subclass=" + subclass + "&category_cn=" + category_cn + "&settr=" + settr + "&settr_cn=" + settr_cn + "";
					break;
				case "resume_list":
					url = "?key=" + key + "&district=" + district + "&sdistrict=" + sdistrict + "&experience=" + experience + "&education=" + education + "&topclass=" + topclass + "&category=" + category + "&subclass=" + subclass + "&settr=" + settr + "&sex=" + sex + "&talent=" + talent + "&photo=" + photo + "&district_cn=" + district_cn + "&category_cn=" + category_cn + "&experience_cn=" + experience_cn + "&settr_cn=" + settr_cn + "&education_cn=" + education_cn + "&sex_cn=" + sex_cn + "&talent_cn=" + talent_cn + "&photo_cn=" + photo_cn + "";
					break;
				case "job_list":
					url = "?key=" + key + "&district=" + district + "&sdistrict=" + sdistrict + "&experience=" + experience + "&education=" + education + "&topclass=" + topclass + "&category=" + category + "&subclass=" + subclass + "&settr=" + settr + "&wage=" + wage + "&nature=" + nature + "&scale=" + scale + "&district_cn=" + district_cn + "&category_cn=" + category_cn + "&experience_cn=" + experience_cn + "&settr_cn=" + settr_cn + "&education_cn=" + education_cn + "&wage_cn=" + wage_cn + "&nature_cn=" + nature_cn + "&scale_cn=" + scale_cn + "";
					break;
				case "index_list":
					url = "jobs-list.php?key=" + key + "";
					break;
				case "jobnear_list":
					url = "?key=" + key + "&district=" + district + "&sdistrict=" + sdistrict + "&experience=" + experience + "&education=" + education + "&topclass=" + topclass + "&category=" + category + "&subclass=" + subclass + "&settr=" + settr + "&wage=" + wage + "&nature=" + nature + "&scale=" + scale + "&district_cn=" + district_cn + "&category_cn=" + category_cn + "&experience_cn=" + experience_cn + "&settr_cn=" + settr_cn + "&education_cn=" + education_cn + "&wage_cn=" + wage_cn + "&nature_cn=" + nature_cn + "&scale_cn=" + scale_cn + "&lng=" + lng + "&lat=" + lat + "";
					break;
				default:
			 		break;
			}
		window.location.href = url;
	},
	lockWin : function(type){
		var lockDiv = $(".select_bar .lock");
		switch(type){
			case "on":
				lockDiv.css("height",window.innerHeight*2);
				if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
				    $(".select_bar_div").css("height",window.innerHeight - (108 + 44));
				}else{
				    $(".select_bar_div").css("height",window.innerHeight - ($(".select_bar").offset().top + 44));
				}
				$(".select_bar_div").css("top",$(".select_bar").height())
				$(".select_bar_more").css("height",window.innerHeight);
				lockDiv.show();
				break;
			default:
				$(".select_bar_div,.select_bar_more,.select_bar .col").removeClass("on");
				lockDiv.hide();
				break;
		}
	},
	markLine : function($this,type,pos){
		switch(type){
			case "show":
				$this.find(".mark_bottom").addClass("on").find(".mark_line").css("left",pos);
				break;
			default:
				$this.find(".mark_bottom").removeClass("on");
				break;
		}
	},
	close :function(){
		this.lockWin("off");
		this.markLine($(".select_bar"),"hide")
		$("html").css("overflow","scroll");
		$(".select_bar li,.select_bar_div,.select_bar_more").removeClass("on");
	}
}