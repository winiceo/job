 /*
 * 74cms 查工资
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/ 
$(function(){
    editCv.initMb();
    var percent = 100/6,
		numPoint = 0;
	$(".tableSalary td").css("width", percent + "%");
	$('.skillbar').each(function(){
		numPoint = $(this).data('wage')/2000;
		$(this).data('percent',numPoint*percent+"%");
		$(this).find('.skillbar-bar').animate({
			width:$(this).data('percent')
		},3000);
	});
	setTimeout(function () {
        $(".skill-bar-percent").css({
			"left":(numPoint*percent+1)+"%"
		}).show();
    }, 3000);
})
var editCv = {
	initMb : function(){
        var curr = new Date().getFullYear(),
            cumm = new Date().getMonth(),
            that = this,
            opt = {
            'swdate': {
                theme: 'mct light',
                preset: 'date',
                dateOrder: 'yym月',
                display: 'bottom',
                headerText: false,
                startYear: curr - 50,
                endYear: curr,
                dateFormat: 'yy.m',
            },
            'ewdate': {
                theme: 'mct light',
                preset: 'date',
                dateOrder: 'yym月',
                display: 'bottom',
                headerText: false,
                startYear: curr - 50,
                endYear: curr,
                dateFormat: 'yy.m',
            },
            'birth': {
                theme: 'mct light',
                preset: 'date',
                dateOrder: 'yym月',
                display: 'bottom',
                headerText: false,
                defaultValue: new Date(new Date().setFullYear(curr-23)),
                maxDate: new Date(),
                minDate: new Date(new Date().setFullYear(1945)),
                dateFormat: 'yy.m',
            },
            'year': {
                theme: 'mct light',
                preset: 'date',
                dateOrder: 'yym月',
                display: 'bottom',
                startYear: curr - 50,
                endYear: curr,
                buttons: false,
                dateFormat: 'yy.m',
            },
            'yearStart': {
                theme: 'mct light',
                preset: 'date',
                dateOrder: 'yym月',
                display: 'bottom',
                defaultValue: new Date(new Date().setFullYear(curr-2)),
                maxDate: new Date(),
                minDate: new Date(new Date().setFullYear(curr - 60)),
                buttons: false,
                dateFormat: 'yy.m',
            },
            'yearEnd': {
                theme: 'mct light',
                preset: 'date',
                dateOrder: 'yym月',
                display: 'bottom',
                defaultValue: new Date(new Date().setFullYear(curr)),
                maxDate: new Date(),
                minDate: new Date(new Date().setFullYear(curr - 60)),
                buttons: false,
                dateFormat: 'yy.m',
            },
            'select': {
                theme: 'mct light',
                preset: 'select',
                rows: 7,
                inputClass: 'hide',
                display: 'bottom',
            }
        }
        $(".mbs").each(function(e,i){
            var type = $(this).data("type");
            var head = ($(this).data("text"))? $(this).data("text"):"";
            var thisid = ($(this).attr("id"))? $(this).attr("id"):"";
            switch(type){
                case "select":
                    $(this).scroller('destroy').scroller($.extend(opt['select'], {
                        buttons: false,
                        headerText: "选择学历",
                        onValueTap: function (item, inst) {
                            var text = item.find('.dw-i').text();
                            if(text){
                                $('#degreeTxt').text(text)
                                var majorDom = $("input[name$='major']");
                                if(text == "高中" ||text == "初中"){
                                    majorDom.attr("readonly","readonly").val("");
                                    majorDom.addClass("empty")
                                    majorDom.hide();
                                    majorDom.parent().prev().css("color",'#cfcfcf');
                                }else{
                                    majorDom.removeAttr("readonly").show()
                                    majorDom.removeClass("empty")
                                    majorDom.show();
                                    majorDom.parent().prev().removeAttr('style');
                                }
                                $('#degree').mobiscroll('select')
                            }
                        },
                        onSelect:function(v,i){
                            setTimeout(function(){
                                that.ifPractice()
                            },300)
                        }
                    }))
                    break;
                case "year":
                    that.formatDate($(this).next().val(),$(this))
                    if(head){
                        var self = $(this);
                        if(thisid == "beginworkTxt"){
                            $(this).scroller('destroy').scroller($.extend(opt['year'], {
                                headerText: head,
                                buttons: ["cancel","set",{text:"暂无工作经验",handler:function(e,i){
                                    $('#beginworkTxt').text("暂无工作经验");
                                    $('#beginwork_dummy').val(0);
                                    self.mobiscroll('cancel')
                                }}],
                                onSelect: function (v, i) {
                                    $('#beginworkTxt').text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                                    $('#beginworkTxt').next().val(v);
                                }
                            }))
                        }else if(thisid == "graduateTxt"){
                            $(this).scroller('destroy').scroller($.extend(opt['yearEnd'], {
                                headerText: head,
                                onSelect: function (v, inst) {
                                    $('#graduate_dummy').val(inst.values[0]);
                                },
                                onValueTap: function (item, inst) {
                                    var text = item.find('.dw-i').text();
                                    if(text){
                                        $('#graduateTxt').text(text);
                                        $('#graduateTxt').mobiscroll('select');
                                    }
                                }
                            }))
                        }
                    }else{

                    }
                    break;
                case "yearStart":
                    var sTxt = $(this);
                    that.formatDate($(this).next().val(),$(this))
                    sTxt.scroller('destroy').scroller($.extend(opt['yearStart'], {
                        headerText: head,
                        buttons: ["cancel","set"],
                        onSelect: function (v, i) {
                            sTxt.next().val(v);
                            sTxt.text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                        }
                    }))
                    break;
                case "yearEnd":
                    var eTxt = $(this);
                    that.formatDate($(this).next().val(),$(this))
                    eTxt.scroller('destroy').scroller($.extend(opt['yearEnd'], {
                        headerText: head,
                        buttons: ["cancel","set",{text:"至今",handler:function(e,i){
                            eTxt.next().val(-1);
                            eTxt.text("至今");
                            that.ifPractice()
                            eTxt.scroller("cancel")
                        }}],
                        onSelect: function (v, i) {
                            eTxt.next().val(v);
                            eTxt.text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                            that.ifPractice()
                        }
                    }))
                    break;
                case "date":
                    that.formatDate($(this).next().val(),$(this))
                    if(head){
                        var thisdom = $(this);
                        $(this).scroller('destroy').scroller($.extend(opt['ewdate'], {
                            buttons: ["cancel","set",{text:"至今",handler:function(e,i){
                                $('#endWork_dummy').val(-1);
                                $('#endWorkTxt').text("至今");
                                thisdom.scroller("cancel")
                            }}],
                            onSelect: function (v, i) {
                                $('#endWork_dummy').val(i.val);
                                $('#endWorkTxt').text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                            }
                        }))
                    }else{
                        $(this).scroller('destroy').scroller($.extend(opt['swdate'], {
                            buttons: ["cancel","set"],
                            onSelect: function (v, i) {
                                $('#startWork_dummy').val(i.val);
                                $('#startWorkTxt').text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                            }
                        }))
                    }
                    break;
                case "birthday" :
                    that.formatDate($(this).next().val(),$(this))
                    $(this).scroller('destroy').scroller($.extend(opt['birth'], {
                        headerText: head,
                        buttons: ["cancel","set"],
                        onSelect: function (v, i) {
                            $('#birthdate').val(i.val);
                            $('#brithdayTxt').text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                        }
                    }))
                    break;
                case "validity" :
                    that.formatDate($(this).next().val(),$(this))
                    $(this).scroller('destroy').scroller($.extend(opt['birth'], {
                        headerText: head,
                        buttons: ["cancel","set"],
                        onSelect: function (v, i) {
                            $('#validity').val(i.val);
                            $('#validityTxt').text(i.values[0]+"年"+(parseInt(i.values[1])+1)+"月");
                        }
                    }))
                    break;
                default:
                    break;
            }
        })
    },
    formatDate:function(date,$this){
        var dArr = date.split("."),
            str = "";
        if(!dArr[0]) return false;
        dArr[0] += "";
        switch(dArr[0]){
            case "0":
                str += "暂无工作经验"
                break;
            case "-1":
                str += "至今"
                break;
            default:
                str += dArr[0]+"年";
                if(dArr[1]) str += dArr[1]+"月";
                break;
        }
        if($this && $this.length){
            $this.text(str)
        }
        return str;
    }
}
$(document).ready(function() {
	/* 点击div跳转 */
	$('.thisurl').click( function () {window.location.href =  $(this).attr('url');});

	/* 定义大背景 */
 	var lockWin = {
		on: function(a) {
			switch (a) {
				case 1:
					if ($(".hide_win").length < 1) {
						$("body").append('<div class="hide_win"></div>')
					}
					$(".hide_win").show();
					break;
				default:
					if ($(".lock_win").length < 1) {
						$("body").append('<div class="lock_win"></div>')
					}
					$(".lock_win").show();
					break
			}
		},
		off: function(a) {
			switch (a) {
				case 1:
					$(".hide_win").hide();
					break;
				default:
					$(".lock_win").hide();
					break
			}
		}
	};

 	var m_selector = {
	jobsArr: "",
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
		if ($("#category_cn").val()) {
			var restoreId = 0,
				restoreVal = $("#category_cn").val(),
				restoreArr = $("#loc_level1_" + a + " li");
			$.each(QS_jobs_parent, function(index, val) {
				if (val) {
					var oneId = val.split(",")[0],
						oneValArr = QS_jobs[oneId].split("|");
					$.each(oneValArr, function(indextwo, valtwo) {
						if (valtwo) {
							var twoId = valtwo.split(",")[0];
							if (QS_jobs[twoId]) {
								var twoValArr = QS_jobs[twoId].split("|");
								$.each(twoValArr, function(indexthree, valthree) {
									if (valthree) {
										var threeVal = valthree.split(",")[1];
										if (threeVal == restoreVal) {
											restoreId = twoId;
										};
									};
								});
							};
						};
					});
				};
			});
			$.each(restoreArr, function(index, val) {
				if ($(this).data("val").split(",")[1] == restoreId) {
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
		if (!i) {
			$("#loc_level2_" + a).delegate("li:not(.fail)", "click", function() {
				storage.setJson("search", "soCity", $(this).data("val"));
				if ($(this).hasClass("locCity")) {
					storage.setJson("search", "soCity_cn", $(this).data("txt"))
				} else {
					storage.setJson("search", "soCity_cn", $(this).text())
				}
			});
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
		if ($("#district_cn").val()) {
			var restoreId = 0,
				restoreVal = $("#district_cn").val(),
				restoreArr = $("#loc_level1_" + a + " li");
			$.each(QS_city_parent, function(index, val) {
				if (val) {
					var oneId = val.split(",")[0],
						oneValArr = QS_city[oneId].split("|");
					$.each(oneValArr, function(indextwo, valtwo) {
						if (valtwo) {
							var twoVal = valtwo.split(",")[1];
							if (twoVal == restoreVal) {
								restoreId = oneId;
							};
						};
					});
				};
			});
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
		if (!i) {
			$("#loc_level2_" + a).delegate("li:not(.fail)", "click", function() {
				storage.setJson("search", "soCity", $(this).data("val"));
				if ($(this).hasClass("locCity")) {
					storage.setJson("search", "soCity_cn", $(this).data("txt"))
				} else {
					storage.setJson("search", "soCity_cn", $(this).text())
				}
			});
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
    majors: function(a, i, d) {
        var h = $("#" + a);
        var e = "",
            f = this;
        if (!h) {
            clog("nodiv");
            return
        }
        e += '<div id="loc_level1_' + a + '" class="sl_level1"><div><ul>';
        $.each(QS_major_parent, function(index, val) {
             var cArr = val.split(",");
            e += '<li data-key="' + cArr[1] + '" data-val="' + cArr[0] + '">' + cArr[1] + "</li>"
        });
        e += '      </ul></div></div><div id="loc_level2_' + a + '" class="sl_level2"><div><ul>';
        e += "      </ul></div></div>";
        this.set(h, e);
        /* 恢复选中 */
        if ($("#major_cn").val()) {
            var restoreArr = $("#loc_level1_" + a + " li"),
                restoreId = this.getMajorRestoreId(a);
            $.each(restoreArr, function(index, val) {
                if ($(this).data("val") == restoreId) {
                    $(this).addClass('on');
                };
            });
            var gArr = QS_major[restoreId].split("|"),
                g = this.getFistCity(gArr, restoreId);
            $("#loc_level2_" + a + " ul").empty().html(g);
        } else {
            $("#loc_level1_" + a + " li").first().addClass("on");
            var gid = this.getMajorRestoreId(a),
                gArr = QS_major[gid].split("|"),
                g = this.getFistCity(gArr, gid);
            $("#loc_level2_" + a + " ul").empty().html(g);
        };
        if (document.documentElement.scrollTop + document.body.scrollTop > 108) {
            $(".sl_level1,.sl_level2").css("height", window.innerHeight - 45 - 108)
        } else {
            $(".sl_level1,.sl_level2").css("height", window.innerHeight - h.offset().top)
        }
        if (!i) {
            $("#loc_level2_" + a).delegate("li:not(.fail)", "click", function() {
                storage.setJson("search", "soCity", $(this).data("val"));
                if ($(this).hasClass("locCity")) {
                    storage.setJson("search", "soCity_cn", $(this).data("txt"))
                } else {
                    storage.setJson("search", "soCity_cn", $(this).text())
                }
            });
        }
        $("#loc_level1_" + a + " li").on("click", function() {
            $(this).parent().find("li").removeClass("on");
            $(this).addClass("on");
            var m = $(this).data("val"),
                n = "",
                mArr = QS_major[m].split("|");
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
    getMajorRestoreId: function(a) {
        var restoreCn = $("#major_cn").val(),
            restoreArr = $("#loc_level1_" + a + " li"),
            restoreId = '';
        $.each(QS_major_parent, function(index, val) {
            var confirmLevel1Arr = QS_major[index+1].split("|");
            $.each(confirmLevel1Arr, function(indexLenvl1, valLenvl1) {
                var confirmLevel1Cn = valLenvl1.split(",")[1];
                if (restoreCn === confirmLevel1Cn) {
                    restoreId = index + 1;
                };
            });
        });
        return restoreId;
    },
	set: function(b, a, c) {
		if (b.find(".sl_list>div").length) {
			b.find(".sl_list>div").empty().html(a)
		} else {
			if (b.find(".sl-main>div").length) {
				b.find(".sl-main>div").empty().html(a)
			} else {
				b.empty().html(a);
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
	}
};
 	/* 期望地区 */
 	$("#cityTxt").on("click",function(){
 		var elem = $(".select_bar_div"),
            that = this;
        elem.show().css("height",300)
        lockWin.on();
        if($("#loc_level1_cityList").length){
            elem.show().addClass("on");
        }else{
            elem.empty();
            elem.append('<div class="select_bar_head">城市</div><div id="cityList" class="sl_list"><div><ul></ul></div></div>');
            elem.addClass("on");
            var select_city = m_selector.soCity($(this).data("go")+"List",true,true)
            setTimeout(function(){
                $(".sl_level1,.sl_level2").css("height",255)
                select_city.refresh();
            },500)
        }
        elem.find(".sl_level2").off("click.checkcity").delegate("li:not(.fail)","click.checkcity",function(){
            if($(this).hasClass('locCity')){
                $("#cityTxt").text($(this).data("txt"));
            }else{
                $("#cityTxt").text($(this).text());
            }
            $("#district_cn").val($(this).text());
            hideBar(elem);
        })
        $(".lock_win").on("click",function(){
            hideBar(elem);
        })
    });

 	/* 期望职位 */
 	$("#jobTxt").on("click",function(){
 		var elem = $(".select_bar_div"),
            that = this;
        elem.show().css("height",300)
        lockWin.on();
        if($("#loc_level1_cityList").length){
            elem.show().addClass("on");
        }else{
            elem.empty();
            elem.append('<div class="select_bar_head">职位</div><div id="jobList" class="sl_list"><div><ul></ul></div></div>');
            elem.addClass("on");
            var select_city = m_selector.jobs($(this).data("go")+"List",true,true)
            setTimeout(function(){
                $(".sl_level1,.sl_level2").css("height",255)
                select_city.refresh();
            },500)
        }
        elem.find(".sl_level2").off("click.checkcity").delegate("li:not(.fail)","click.checkcity",function(){
            if($(this).hasClass('locCity')){
                $("#jobTxt").text($(this).data("txt"));
            }else{
                $("#jobTxt").text($(this).text());
            }
            $("#category_cn").val($(this).text());
            hideBar(elem);
        })
        $(".lock_win").on("click",function(){
            hideBar(elem);
        })
    });

 	/* 选中之后隐藏大背景 */
    function hideBar(elem) {
        lockWin.off();
        elem.removeClass("on");
        elem.empty();
    }

    function clog(a) {
		console.log(a)
	}

	function checkVals(val){
        var mark = true;
        $(".sValDiv>div>div").each(function(){
            if($(this).data("val") == val){
                mark = false;
                return;
            }
        })
        return mark;
    }

    function setVal($this,fun,length,lenFail){
        var addFlag = true,
            editDom = "",
            txt = ($this.data("key"))?$this.data("key"):$this.data("val");
        $("#sValDiv>div>div").each(function(){
            var a = $(this).data("val");
            var b = txt;
            a = a.toString();
            b = b.toString();
            var aArr = a.split(",");
            var bArr = b.split(",");
            if(a == b){
                return false;
            }
            if(aArr.length == 1 && bArr.length == 2){
                if(aArr[0] == bArr[0]){
                    editDom = $(this);
                    addFlag = false;
                }
            }
            if(aArr.length == 2 && bArr.length == 3){
                if(aArr[0] == bArr[0] && aArr[1] == bArr[1]){
                    editDom = $(this);
                    addFlag = false;
                }
            }
        })
        if(addFlag){
            if($("#sValDiv>div>div").length>=length){
                return lenFail()
            }
            $("#sValDiv>div").append('<div data-val="'+txt+'">'+$this.text()+'<em class="close"></em></div>')
            fun();
        }else{
            editDom.data("val",txt)
            editDom.html($this.text()+'<em class="close"></em>')
            fun();
        }
    }
 	/* 展开收起选填项 */
 	// $(".notRformChild").hide();
 	$("#showNotrequired").on('click', function(event) {
 		$(".notRformChild").toggle();
 	});
});

/* 获取弹出框里的内容 */
function getOptionsContent(dataSource, title) {
	var content = '<div class="formDiv formDivlayer">';
	if (title.length > 0) {
		content += '<div class="formChild"><div class="lt">' + title + '</div></div>';
	};
	$.each(dataSource, function(index, val) {
		var dataSourceContentArr = val.split(","), id = dataSourceContentArr[0], text = dataSourceContentArr[1];
		content += '<div class="formChild option" data-val="' + id + '" data-content="' + text + '"><div class="lt">' + text + '</div></div>';
	});
	content += '</div>';
	return content;
}

/* 弹出框选择列表点击绑定事件 */
function optionsClick(optionId, optionCn, showcontentId) {
	$(".formDivlayer .option").on('click', function(event) {
		$(optionId).val($(this).data('val'));
		$(optionCn).val($(this).data('content'));
		$(showcontentId).text($(this).data('content'));
 		layer.closeAll();
 	});
}