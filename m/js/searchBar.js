var searchBar = {
	keywordtimer: "",
	subDiv: "",
	search_div: '<div id="search_div" class="s"><div class="search in_div"><div class="rback"></div><div class="close"></div><div class="s_ipt"><input class="tl" type="search" data-replaceholder="请输入关键词" placeholder="请输入关键词"><div class="del"></div></div><div class="sou icon icon-search" data-clickparam="from=chr_other_search_search"></div></div><div id="subDiv" style="overflow: hidden; height: 518px;"><div style="transition-property: -webkit-transform; transform-origin: 0px 0px 0px; transform: translate(0px, 0px) translateZ(0px);"><div id="s_list"><ul></ul><div id="cleanhistory">清空历史</div></div><div class="cline"></div><div id="s_hot" class="hotword"><a href="#"><div class="hot_t"><span>热门</span></div></a><ul></ul></div></div></div></div>',
	mod: document.createElement("modernizr").style,
	_testPrefix: function() {
		var a = ["Webkit", "Moz", "O", "ms"],
			c, b = this;
		for (c in a) {
			if (this._testProps([a[c] + "Transform"])) {
				return "-" + a[c].toLowerCase() + "-"
			}
		}
		return ""
	},
	_testProps: function(b) {
		var a;
		for (a in b) {
			if (this.mod[b[a]] !== undefined) {
				return true
			}
		}
		return false
	},
	init: function() {
		var c = this,
			a = this._testPrefix();
		$(".main").hide();
		$("body").scrollTop(0);
		$("body").append(this.search_div).find("#subDiv").css("height", window.innerHeight - 50);
		var b = $("#search_div").show();
		$(".selectDiv").css(a + "transform", "translateX(100%)");
		$("#search_div .close").off().on("click", function() {
			$("#search_div").removeClass("s");
			$(".main").show();
			m_selectorbar.enable_scroll()
		});
		m_selectorbar.disable_scroll();
		if ($("#hotKeyWord").length != 0 && $("#hotKeyWord").val() != "") {
			$("#search_div input").val($("#hotKeyWord").val());
		} else {
			/*if (storage.getJson("search", "keyword")) {
				$("#search_div input").val(storage.getJson("search", "keyword"));
				$("#super_search_div input").val(storage.getJson("search", "keyword"))
			}*/
		}
		$("#search_div input").off().on("keyup", function() {
			clearTimeout(c.keywordtimer);
			if ($(this).val() != "") {
				$(this).parent().parent().addClass("hword");
				$(this).next(".del").show().on("click", function() {
					$(this).prev().val("").parent().parent().removeClass("hword");
					$(this).hide().off("click")
				});
				var e = $(this).val();
			} else {
				$(this).parent().parent().removeClass("hword");
				$(this).next(".del").hide().off("click");
				c.getHistory($("#s_list"))
			}
		}).on("blur", function() {
			$("#subDiv").css("height", window.innerHeight - 50)
		}).on("focus", function() {
			$("#search_div").scrollTop();
			$("#subDiv").css("height", window.innerHeight - 50)
		});
		$(".selectDiv .return").off("click").on("click", function() {
			$(".selectDiv").css(a + "transform", "translateX(100%)");
		});
		$("#cleanhistory").off("click").on("click", function() {
			$(this).hide();
			$("#s_list").hide().next().hide();
			cookie("searchHistory",null);
		});
		$("#search_div .sou").off("click").on("click", function() {
			searchGo($(this).parent().find("input").val());
		});
		c.getHistory($("#s_list"));
		c.getHot($("#s_hot"));
		this.subDiv = new iScroll("subDiv");
		window.onresize = function() {
			$("#subDiv").height(window.innerHeight - 50);
		}
	},
	show: function() {
		$(".main").hide();
		$("#search_div").addClass("s").show(), $("#super_search_div").show()
	},
	getHot: function(d) {
		var c = "";
		var a = $("#hiddenHotWords li");
		if (a.length == 0) {
			return false
		}
		c = $("#hiddenHotWords").html();
		d.find("ul").empty().html(c);
		d.find("li").off().on("click", function() {
			var e = "",
				key = $(this).text();
			console.log(cookie("searchHistory"));
			searchGo(key);
		})
	},
	getHistory: function(d) {
		var b = "", hlength = 0;
		var searchHistoryArr = new Array();
		if (cookie("searchHistory")) {
			searchHistoryArr = cookie("searchHistory").split(",");
		};
		if (searchHistoryArr.length == 0) {
			d.hide();
			return false
		}
		$.each(searchHistoryArr, function(index, val) {
			hlength += 1;
			b += '<li class="history_go" data-self="' + val + '">' + val + '<div class="close"></div></li>';
		});
		if (hlength > 0) {
			d.find("ul").empty().html(b);
			$("#cleanhistory").show();
			$(".history_go").off().on("click", function() {
				searchGo($(this).data("self"));
			});
			$(".history_go .close").off().on("click", function() {
				var searchHistoryArr = cookie("searchHistory").split(","),
					val = $(this).parent().data("self"),
					index = $.inArray(val,searchHistoryArr);
				if (index >= 0) {
					searchHistoryArr.splice(index,1);
				};
				$(this).parent().remove();
			});
		} else {
			d.find("ul").empty();
			$("#cleanhistory").hide()
		}
	}
};
function searchGo(key) {
	if (key.length > 0) {
		var searchHistoryArr = new Array();
		if (cookie("searchHistory")) {
			searchHistoryArr = cookie("searchHistory").split(",");
			var isOnly = true;
			$.each(searchHistoryArr, function(index, val) {
				if (val == key) {
					isOnly = false;
				};
			});
			if (isOnly) {
				if (searchHistoryArr.length >= 10) {
					searchHistoryArr.splice(0,1);
				}
				searchHistoryArr.push(key);
			};
		} else {
			searchHistoryArr.push(key);
		};
		cookie("searchHistory",searchHistoryArr);
	};
	var type = $("#key").data('code'),
		district = $("#hdistrict").val(),
		sdistrict = $("#hsdistrict").val(),
		district_cn = $("#hdistrict_cn").val(),
		experience = $("#hexperience").val(),
		experience_cn = $("#hexperience_cn").val(),
		education = $("#heducation").val(),
		education_cn = $("#heducation_cn").val(),
		topclass = $("#htopclass").val(),
		category = $("#hcategory").val(),
		subclass = $("#hsubclass").val(),
		category_cn = $("#hcategory_cn").val(),
		settr = $("#hsettr").val(),
		settr_cn = $("#hsettr_cn").val(),
		wage = $("#hwage").val(),
		wage_cn = $("#hwage_cn").val(),
		nature = $("#hnature").val(),
		nature_cn = $("#hnature_cn").val(),
		scale = $("#hscale").val(),
		scale_cn = $("#hscale_cn").val(),
		sex = $("#hsex").val(),
		sex_cn = $("#hsex_cn").val(),
		talent = $("#htalent").val(),
		talent_cn = $("#htalent_cn").val(),
		photo = $("#hphoto").val(),
		photo_cn = $("#hphoto_cn").val(),
		url = '';
	if (type == "joblist") {
		url = "?key=" + key + "&district=" + district + "&sdistrict=" + sdistrict + "&experience=" + experience + "&education=" + education + "&topclass=" + topclass + "&category=" + category + "&subclass=" + subclass + "&settr=" + settr + "&wage=" + wage + "&nature=" + nature + "&scale=" + scale + "&district_cn=" + district_cn + "&category_cn=" + category_cn + "&experience_cn=" + experience_cn + "&settr_cn=" + settr_cn + "&education_cn=" + education_cn + "&wage_cn=" + wage_cn + "&nature_cn=" + nature_cn + "&scale_cn=" + scale_cn + "";
	} else if (type == "resumelist") {
		url = "?key=" + key + "&district=" + district + "&sdistrict=" + sdistrict + "&experience=" + experience + "&education=" + education + "&topclass=" + topclass + "&category=" + category + "&subclass=" + subclass + "&settr=" + settr + "&sex=" + sex + "&talent=" + talent + "&photo=" + photo + "&district_cn=" + district_cn + "&category_cn=" + category_cn + "&experience_cn=" + experience_cn + "&settr_cn=" + settr_cn + "&education_cn=" + education_cn + "&sex_cn=" + sex_cn + "&talent_cn=" + talent_cn + "&photo_cn=" + photo_cn + "";
	} else {
		url = "jobs-list.php?key=" + key + "";
	};
	window.location.href = url;
}
function cookie(name, value, options) {
if (typeof value != 'undefined') {
   options = options || {};
   if (value === null) {
    value = '';
    options = $.extend({}, options);
    options.expires = -1;
   }
   var expires = '';
   if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
    var date;
    if (typeof options.expires == 'number') {
     date = new Date();
     date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
    } else {
     date = options.expires;
    }
    expires = '; expires=' + date.toUTCString();
   }
   var path = options.path ? '; path=' + (options.path) : '';
   var domain = options.domain ? '; domain=' + (options.domain) : '';
   var secure = options.secure ? '; secure' : '';
   document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
} else {
   var cookieValue = null;
   if (document.cookie && document.cookie != '') {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
     var cookie = jQuery.trim(cookies[i]);
     if (cookie.substring(0, name.length + 1) == (name + '=')) {
      cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
      break;
     }
    }
   }
   return cookieValue;
}
};
var m_selectorbar = {
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
	}
};