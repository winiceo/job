 /*
 * 74cms ���޼��ز��
 * ============================================================================
 * ��Ȩ����: ��ʿ���磬����������Ȩ����
 * ��վ��ַ: http://www.74cms.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
*/
function pinterest(url) {
	$(window).on('load', function(event) {
		/* ������ʱ����¼� */
		var isGet = true;
		window.onscroll = function() {
			/* �������ĸ߶ȴ����ٽ��ĸ߶ȵ�ʱ��ʼ���ظ������� */
			if (scrollside()) {
				$('.loadinglist').show(); /* ���ڼ�����ʾ */
				$('.remindnoinfo').hide(); /*  û�и���ְλ����ʾ����ʧ */
				/* ���Ŀǰ�������б�ĸ����������λ�ÿ�ʼ�ٻ�ȡN�� */
				var offset = $("#container .box").size(),
					rows = 5;
				if (isGet) {
					isGet = false;
					$.get(url, {"offset":offset, "rows":rows}, function(data) {
						if(data == "-1"){ /* -1����û������ */
							$('.loadinglist').hide(); /* ���ڼ������� */
					    	$('.remindnoinfo').show(); /* û�и���ְλ����ʾ����ʾ */
					    } else {
					    	$(data).appendTo($('#container')); /* ���ص�������ӵ������� */
							$('.loadinglist').hide(); /* ��������������ڼ������� */
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

/* �ж��Ƿ�������ٽ�� */
function scrollside() {
	var box = $(".box");
	/* ��λ�ٽ��Ϊ���һ��boxһ���λ�� */
	var lastboxHeight = box.last().get(0).offsetTop + Math.floor(box.last().height()/2); 
	var documentHeight = $(window).height();
	var scrollHeight = $(window).scrollTop();
	return (lastboxHeight < documentHeight+scrollHeight) ? true : false;
}

/* �ؼ������� */
$(function(){
	$(".w-icon-search-news").on('click', function(event) {
		window.location.href = "?key=" + $("#key").val() +"&type_id=" + $("#selectedTag").val();
	});
});