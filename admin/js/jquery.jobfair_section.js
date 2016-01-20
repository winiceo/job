//截取字符
function limit(objString,num)
{
	if (num==0)
	{
		return objString;
	}
	var objLength =objString.length;
	if(objLength > num){ 
	return objString.substring(0,num) + "...";
	} 
	return objString;
}
//打开行业(此函数仅限创建招聘会参会行业)
function OpentradeLayer(click_obj,input,input_cn,input_txt,showid,strlen)
{
	$(click_obj).click(function()
	{
			$(this).blur();
			$(this).parent("div").before("<div class=\"menu_bg_layer\"></div>");
			$(".menu_bg_layer").height($(document).height());
			$(".menu_bg_layer").css({ width: $(document).width(), position: "absolute",left:"0", top:"0","z-index":"0","background-color":"#000000"});
			//$(".menu_bg_layer").css("opacity",10);
			$(showid+" .OpenFloatBoxBg").css("opacity", 0.2);
			$(showid).show();			
			$(showid+" .OpenFloatBox").css({"left":($(document).width()-$(showid+" .OpenFloatBox").width())/2-128,"top":"150"});
			SetBoxBg(showid);
			$(showid+"  label").hover(function()	{$(this).css("background-color","#E3F0FF");},function(){	$(this).css("background-color","");});
				$(showid+"  label input").unbind().click(function(e){
					e.stopPropagation();
					var elem = $(this);
						if($(this).is(':checked'))
						{
							checked_num(4,elem);
						}
						else {
							$(showid+"  :checkbox").each(function(index, el) {
								$(this).attr('disabled',false);
							});
						}
				});
		
	});
	//选中几个
	function checked_num (select_num,elem) 
	{
		if($(showid+"  :checkbox[checked]").length > select_num) {
			elem.removeAttr('checked');
			$(showid+"  :checkbox").not(":checked").each(function(index, el) {
				$(this).attr('disabled',true);
			});
		} else {
			$(showid+"  :checkbox").parent().css("color","");
			$(showid+"  :checkbox[checked]").parent().css("color","#009900");
		}
	} 

	//确定选择
	$(showid+" .Set").click(function()
	{
			var a_cn=new Array();
			var a_id=new Array();
			$(showid+" :checkbox[checked]").each(function(index){
			a_cn[index]=$(this).attr("title");
			a_id[index]=$(this).attr("value");
			});
			$("#showtrade").val(limit(a_cn.join("+"),strlen));
			$(input_cn).val(limit(a_cn.join(","),strlen));
			$(input_txt).html(limit(a_cn.join("+"),strlen));
			if ($(input_cn).val()=="")$(input_cn).val("");
			$(input).val(a_id.join(","));
			 DialogClose(showid);
	});
	//设置阴影
	function SetBoxBg(showid)
	{
				var FloatBoxWidth=$(showid+" .OpenFloatBox").width();
				var FloatBoxHeight=$(showid+" .OpenFloatBox").height();
				var FloatBoxLeft=$(showid+" .OpenFloatBox").offset().left;
				var FloatBoxTop=$(showid+" .OpenFloatBox").offset().top;
				$(showid+" .OpenFloatBoxBg").css({display:"block",width:(FloatBoxWidth+12)+"px",height:(FloatBoxHeight+12)+"px"});
				$(showid+" .OpenFloatBoxBg").css({left:(FloatBoxLeft-5)+"px",top:(FloatBoxTop-5)+"px"});
	}
	//关闭
	$(showid+" .OpenFloatBox .DialogClose").hover(function(){$(this).addClass("spanhover")},function(){$(this).removeClass("spanhover")});
	$(showid+" .DialogClose").click(function(){DialogClose(showid);});
	function DialogClose(showid)
	{
		$(".menu_bg_layer").hide();
		$(showid).hide();
	}
	
}
// 选择参会企业
function OpentradeLayer_(click_obj,input,input_cn,input_txt,showid,strlen)
{
	$(click_obj).click(function()
	{
			$(this).blur();
			$(this).parent("div").before("<div class=\"menu_bg_layer\"></div>");
			$(".menu_bg_layer").height($(document).height());
			$(".menu_bg_layer").css({ width: $(document).width(), position: "absolute",left:"0", top:"0","z-index":"0","background-color":"#000000"});
			//$(".menu_bg_layer").css("opacity",10);
			$(showid+" .OpenFloatBoxBg").css("opacity", 0.2);
			$(showid).show();			
			$(showid+" .OpenFloatBox").css({"left":($(document).width()-$(showid+" .OpenFloatBox").width())/2-90,"top":"380"});
			SetBoxBg(showid);
			$(showid+"  label").hover(function()	{$(this).css("background-color","#E3F0FF");},function(){	$(this).css("background-color","");});
				$(showid+"  label input").unbind().click(function(e){
					e.stopPropagation();
					$(showid+"  :checkbox").parent().css("color","");
					$(showid+"  :checkbox[checked]").parent().css("color","#009900");
				});
		
	});
	//确定选择
	$(showid+" .Set").click(function()
	{
			var a_cn=new Array();
			var a_id=new Array();
			$(showid+" :checkbox[checked]").each(function(index){
			a_cn[index]=$(this).attr("title");
			a_id[index]=$(this).attr("value");
			});
			$(input_cn).val(limit(a_cn.join(","),strlen));
			$(input_txt).html(limit(a_cn.join("+"),strlen));
			if ($(input_cn).val()=="")$(input_cn).val("");
			$(input).val(a_id.join(","));
			 DialogClose(showid);
	});
	//设置阴影
	function SetBoxBg(showid)
	{
				var FloatBoxWidth=$(showid+" .OpenFloatBox").width();
				var FloatBoxHeight=$(showid+" .OpenFloatBox").height();
				var FloatBoxLeft=$(showid+" .OpenFloatBox").offset().left;
				var FloatBoxTop=$(showid+" .OpenFloatBox").offset().top;
				$(showid+" .OpenFloatBoxBg").css({display:"block",width:(FloatBoxWidth+12)+"px",height:(FloatBoxHeight+12)+"px"});
				$(showid+" .OpenFloatBoxBg").css({left:(FloatBoxLeft-5)+"px",top:(FloatBoxTop-5)+"px"});
	}
	//关闭
	$(showid+" .OpenFloatBox .DialogClose").hover(function(){$(this).addClass("spanhover")},function(){$(this).removeClass("spanhover")});
	$(showid+" .DialogClose").click(function(){DialogClose(showid);});
	function DialogClose(showid)
	{
		$(".menu_bg_layer").hide();
		$(showid).hide();
	}
	
}
/*新增参会企业填充数据*/
function major_filldata(fillID, data_resourcesP, data_resources, navID, showID, resultlist, resultshowID, resultID, dir) {
	var tradhtm = '',marorPhtm = '';
	data_resourcesP = $(navID+" li");
	data_resources = $(fillID+" major_num_t"); 
	$.each(data_resourcesP, function(indexp, valp) {
		marorPhtm += '<li';
		indexp == 0 ? marorPhtm += ' class="tnon">' : marorPhtm += '>';
		marorPhtm += $(this).text()+'</li>';
		var major_sourseArray = $("#major_num_t"+indexp+" .forNum"),
			sourse_length = parseInt(major_sourseArray.length);
		var rows = 0;
		var subscriptnum = 0;
		if((sourse_length%4) == 0) {
			rows = sourse_length / 4;
		} else {
			rows = parseInt(sourse_length / 4) + 1;
		}
		tradhtm += '<tbody class="major_num_t">';
		for (var i = 0; i < rows; i++) {
			tradhtm += '<tr>';
			for (var j = 0; j < 4; j++) {
				var majorThis = $("#major_num_t"+indexp+" .forNum").eq(subscriptnum);
				tradhtm += '<td><label data="'+majorThis.attr("data")+'">'+majorThis.text()+'</label></td>';
				subscriptnum ++;
			}
			tradhtm += '</tr>';
		}
		tradhtm += '</tbody>';
	});
	$(navID).html(marorPhtm);
	$(fillID).html(tradhtm);
	$(".major_num_t").eq(0).show().siblings('.major_num_t').hide();
	$(navID+" li").unbind().bind('click',function(){
		$(navID+" li").removeClass('tnon');
		$(this).addClass('tnon');
		var ind = $(navID+" li").index(this);
		$(".major_num_t").eq(ind).show().siblings('.major_num_t').hide();
	});
	// 恢复选中
	if ($(resultID).val().length > 0) {
		var rgsidArr = $(resultID).val().split(",");
		$(fillID+" label").each(function(index, el) {
			var rgsdrelArray = $(this).attr("data").split(","),
				$this = $(this);
			$.each(rgsidArr, function(indexr, val) {
				if (val == rgsdrelArray[1]) {
					$this.addClass('gselectra');
				}
			})
		})
	};
	// 单选
	$(fillID+" label").unbind().live("click",function(event) {
		if ($(this).hasClass('gselectra')) {
			$(this).removeClass('gselectra');
		} else {
			$(this).addClass('gselectra');
		}
	});
	// 点击确定
	$("#comSelectorSave").click(function() {
		copy_selected();
		$('.aui_outer').hide();
		$(".ucc-default").removeClass('aui_is_show');
	});
	// 复制选中 赋值
	function copy_selected() {
		var selectedid_array = new Array(),
			selectedtrade_array = new Array(),
			result_datapoolArr = $(fillID+" label.gselectra");
		$.each(result_datapoolArr, function(index, val) {
			var data = $(this).attr("data"),
				dataArr = data.split(",");
			selectedid_array[index]=dataArr[0];
			selectedtrade_array[index]=dataArr[1];
		});
		$("#company_id").val(selectedid_array.join(","));
		$("#trade_id").val(selectedtrade_array.join(","));
		$.post('?act=jobfair_section_company_add', {"jobfair_id": $("#jobfair_id").val(),"company_id":$("#company_id").val(),"trade_id":$("#trade_id").val()}, function(data) {
			if (data == "ok") {window.location.reload();}
			else if(data == "err1")
			{
				alert('招聘会ID丢失！');
			}
			else
			{
				alert('请选择参会企业！');
			}
		});
	}
	// 分割data 返回数组
	function splitdata(arr) {
		if(arr) {
			var arrs_array = arr.split(","),
				arrid_array = arrs_array[0].split(".");
			return arrid_array;
		}
	}
}
// 新增参会企业js
function allaround(dir) {
	// 显示下拉
	$("#add_company").live('click',function() {
		if (!$(this).hasClass('aui_is_show')) {
			$('.aui_outer').hide();
			$(this).addClass('aui_is_show');
			$(this).parent().find('.aui_outer').show();
		} else {
			$(this).removeClass('aui_is_show');
			$(this).parent().find('.aui_outer').hide();
		}
	});
	// 关闭下拉
	$(".selector-close").die().live('click', function(event) {
		$('.aui_outer').hide();
		$("#add_company").removeClass('aui_is_show');
	});
}