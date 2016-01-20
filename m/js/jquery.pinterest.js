 /*
 * 74cms 无限加载插件
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
function pinterest(url) {
	$(window).on('load', function(event) {
		/* 滚动的时候绑定事件 */
		var isGet = true;
		window.onscroll = function() {
			/* 当滚动的高度大于临界点的高度的时候开始加载更多数据 */
			if (scrollside()) {
				$('.loadinglist').show(); /* 正在加载显示 */
				$('.remindnoinfo').hide(); /*  没有更多职位的提示框消失 */
				/* 获得目前容器中列表的个数，从这个位置开始再获取N个 */
				var offset = $("#container .box").size(),
					rows = 5;
				if (isGet) {
					isGet = false;
					$.get(url, {"offset":offset, "rows":rows}, function(data) {
						if(data == "-1"){ /* -1代表没有数据 */
							$('.loadinglist').hide(); /* 正在加载隐藏 */
					    	$('.remindnoinfo').show(); /* 没有更多职位的提示框显示 */
					    } else {
					    	$(data).appendTo($('#container')); /* 返回的数据添加到容器中 */
							$('.loadinglist').hide(); /* 如果返回数据正在加载隐藏 */
							$('.thisurl').on('click', function(event) {
								window.location.href =  $(this).attr('url');
							});
					    }
					    isGet = true;
					});
				};
			};
		}
	});
}

/* 判断是否滚动到临界点 */
function scrollside() {
	var box = $(".box");
	/* 定位临界点为最后一个box一半的位置 */
	var lastboxHeight = box.last().get(0).offsetTop + Math.floor(box.last().height()/2); 
	var documentHeight = $(window).height();
	var scrollHeight = $(window).scrollTop();
	return (lastboxHeight < documentHeight+scrollHeight) ? true : false;
}

/* 关键字搜索 */
$(function(){
	$(".w-icon-search-news").on('click', function(event) {
		window.location.href = "?key=" + $("#key").val() +"&type_id=" + $("#selectedTag").val();
	});
});