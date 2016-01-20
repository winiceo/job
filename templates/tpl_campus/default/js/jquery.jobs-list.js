
// 申请职位
function apply_jobs(ajaxurl)
{
	//全选反选
	$("#selectall").click(function(){
		$("#school-data :checkbox").not('#merge_com_btn').attr('checked',$("#selectall").is(':checked'))
	});
	$(".deliver").click(function()
	{
		var sltlength='';
		if(!$(".detail").length)
		{
			sltlength=$("#school-data .m_jobname_box input:checked").length;
		}
		else 
		{
			sltlength=$("#school-data .resume-item input:checked").length;
		}
		if (sltlength==0)
		{	
			dialog({
				title: '系统提示',
			    content: "请选择职位",
			    width:300
			}).showModal();
		}
		else
		{
			var jidArr=new Array();
			if(!$(".detail").length)
			{
				$("#school-data .m_jobname_box input:checked").each(function(index){jidArr[index]=$(this).val();});
			}
			else 
			{
				$("#school-data .resume-item input:checked").each(function(index){jidArr[index]=$(this).val();});
			}
			var myDialog = dialog();
			jQuery.ajax({
			    url: ajaxurl+"user/user_apply_jobs.php?id="+jidArr.join("-")+"&act=app",
			    success: function (data) {
			        myDialog.content(data);
			        myDialog.title('申请职位');
			        myDialog.width('500');
			    	myDialog.showModal();
			    	/* 关闭 */
					$(".DialogClose").live('click',function() {
						myDialog.close().remove();
					});
			    }
			});
		}
	});
	//单个申请职位
	$(".invite-btn").unbind().click(function(){
		var myDialog = dialog();
		jQuery.ajax({
		    url: ""+ajaxurl+"user/user_apply_jobs.php?id="+$(this).attr("id")+"&act=app",
		    success: function (data) {
		        myDialog.content(data);
		        myDialog.title('申请职位');
		        myDialog.width('500');
		    	myDialog.showModal();
		    	/* 关闭 */
				$(".DialogClose").live('click',function() {
					myDialog.close().remove();
				});
		    }
		});
	});
}
// 收藏职位
function favorites(ajaxurl)
{	
	$(".collecter").click(function()
	{
		var sltlength='';
		if(!$(".detail").length)
		{
			sltlength=$("#school-data .m_jobname_box input:checked").length;
		}
		else 
		{
			sltlength=$("#school-data .resume-item input:checked").length;
		}
		if (sltlength==0)
		{
			dialog({
				title: '系统提示',
			    content: "请选择职位",
			    width:300
			}).showModal();
		}
		else
		{
			var jidArr=new Array();
			if(!$(".detail").length)
			{
				$("#school-data .m_jobname_box input:checked").each(function(index){jidArr[index]=$(this).val();});
			}
			else 
			{
				$("#school-data .resume-item input:checked").each(function(index){jidArr[index]=$(this).val();});
			}
			var myDialog = dialog();
			jQuery.ajax({
			    url: ajaxurl+"user/user_favorites_job.php?id="+jidArr.join("-")+"&act=add",
			    success: function (data) {
			        myDialog.content(data);
			        myDialog.title('收藏职位');
			        myDialog.width('500');
			    	myDialog.showModal();
			    	/* 关闭 */
					$(".DialogClose").live('click',function() {
						myDialog.close().remove();
					});
			    }
			});
		}
	});
	// 单个收藏职位
	$(".collect").unbind().click(function(){
		var myDialog = dialog();
		jQuery.ajax({
		    url: ""+ajaxurl+"user/user_favorites_job.php?id="+$(this).attr("id")+"&act=add",
		    success: function (data) {
		        myDialog.content(data);
		        myDialog.title('收藏职位');
		        myDialog.width('500');
		    	myDialog.showModal();
		    	/* 关闭 */
				$(".DialogClose").live('click',function() {
					myDialog.close().remove();
				});
		    }
		});
	});
}