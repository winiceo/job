function wapcheckall(saveurl) {
    // 绑定管理事件
	$("#operateaction").click(function(event) {
		if ($(this).hasClass("c")) {
			$(this).text('管理');
			$(".classifylist .title .name, .classifylist .txt .left, .classifylist .txt .right").removeClass('perc');
			$(".classifylist .chk input[type=checkbox]:checked").each(function(index, el) {
				$(this).trigger("click");
			});
		} else {
			$(this).text('取消');
			$(".classifylist .title .name, .classifylist .txt .left, .classifylist .txt .right").addClass('perc');
		};
		$(this).toggleClass('c');
		$('.chkabaout').toggle();
	});
	// label绑定与其对应的input
	$(".classifylist .chk .check-box").each(function(index, el) {
		$(this).prev().attr("id", index+ "cbchk");
		$(this).attr("for",$(this).prev().attr("id"));
	});
	// 全选
	$("#opecheckall").on('click', function(event) {
		var inputchkedlength = $(".classifylist .chk input[type=checkbox]:checked").length,
			listlength = $(".classifylist").length;
		if (inputchkedlength == listlength) {
			$(".classifylist .chk label").each(function(index, el) {
				$(this).trigger("click");
			});
		} else {
			$(".classifylist .chk input[type=checkbox]").not(':checked').each(function(index, el) {
				$(this).trigger("click");
			});
		};
	});
	// 删除
	$("#opecheckdelete").on('click', function(event) {
		var idArr = new Array();
		$(".classifylist .chk input[type=checkbox]:checked").each(function(index, el) {
			idArr[index] = $(this).attr("id[]");
		});
		$.post(saveurl, {id: idArr}, function(data) {
			alert(data);
		});
	});
};