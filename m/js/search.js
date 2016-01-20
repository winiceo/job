$(function() {
	FastClick.attach(document.body);
	var layerCtrl = $('.filter-control .condition-item'),
		layerContent = $('.filter-content .filter-class');

	$('.search-condition-box .condition-item').each(function() {
		var bodyNode = $(document.body);
		$(this).on('click', function() {
			var index = $(this).index();
			var mask = $('<div id="filter-mask"></div>');
			bodyNode.append(mask);
			$('.search-popup').show();
			$('body').css('overflow', 'hidden');
			layerCtrl.eq(index).addClass('curr');
			layerContent.eq(index).show();
		})
	});
	layerCtrl.each(function() {
		$(this).on('click', function() {
			var lIndex = $(this).index();
			$(this).addClass('curr').siblings().removeClass('curr');
			layerContent.eq(lIndex).show().siblings().hide();
		})
	})
	$('body').on('click', '#filter-mask', function() {
		CloesLayer();
	})
	function CloesLayer() {
		$('#filter-mask').remove();
		$('.search-popup').hide();
		$('body').css('overflow', 'visible');
		layerCtrl.removeClass('curr');
		layerContent.hide();
	};
	$('.complex-row').find('.complex-col').on('click', function() {
		$(this).addClass('select').siblings().removeClass('select');
	})
});