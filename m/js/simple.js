 /*
 * 74cms 触屏微商圈
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
$(function(){
	$('.thisurl').click( function () {window.location.href =  $(this).attr('url');});
	/* 刷新 */
	$(".refresh").on('click', function(event) {
		var jobid = $(this).data("jobid");
		layer.prompt({
		    title: '请输口令，并确认',
		    formType: 1
		}, function(pass){
			$.get('simple-job-operation.php?act=refresh', {"pass":pass , "jobid":jobid}, function(data) {
				layer.msg(data);
			});
		});
	});

	/* 删除 */
	$(".delete").on('click', function(event) {
		var jobid = $(this).data("jobid");
		layer.prompt({
		    title: '请输口令，并确认',
		    formType: 1
		}, function(pass){
		   	$.get('simple-job-operation.php?act=delete', {"pass":pass , "jobid":jobid}, function(data) {
				layer.msg(data);
				window.history.go(-1);
			});
		});
	});
	/* 刷新 */
	$(".resume_refresh").on('click', function(event) {
		var resumeid = $(this).data("resumeid");
		layer.prompt({
		    title: '请输口令，并确认',
		    formType: 1
		}, function(pass){
			$.get('simple-resume-operation.php?act=resume_refresh', {"pass":pass , "resumeid":resumeid}, function(data) {
				layer.msg(data);
			});
		});
	});

	/* 删除 */
	$(".resume_delete").on('click', function(event) {
		var resumeid = $(this).data("resumeid");
		layer.prompt({
		    title: '请输口令，并确认',
		    formType: 1
		}, function(pass){
		   	$.get('simple-resume-operation.php?act=resume_delete', {"pass":pass , "resumeid":resumeid}, function(data) {
				layer.msg(data);
				window.history.go(-1);
			});
		});
	});
}); 