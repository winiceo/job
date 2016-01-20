function resumelist()
{
	var key=$('#key').val();
	if (key)
	{
		key_arr=key.split(" ");
		for (x in key_arr)
		{
		if (key_arr[x]) $('.striking').highlight(key_arr[x]);
		}
	}
	$(".list:odd").css("background-color","#F4F5FB");
	$(".titsub").hover(	function()	{$(this).addClass("titsub_h");},function(){$(this).removeClass("titsub_h");});
	function setlistbg()
	{
			$(".li_left_check input[type='checkbox']").each(function(i){
				if ($(this).attr("checked"))
				{
					$(this).parent().parent().addClass("seclect");
				}
				else
				{
					$(this).parent().parent().removeClass("seclect");
				}
			}); 
	 }
	//点击选择后重新加样式
	$("#formresumelist input[type='checkbox']").click(function(){setlistbg();});
}
function favorites(ajaxurl)
{
	//全选反选
	$("#selectall").click(function(){
		$("#school-data :checkbox").attr('checked',$("#selectall").is(':checked'))
	});
	$(".add_favorites").unbind().click(function(){	
	var url=ajaxurl+"user/user_favorites_resume.php?id="+$(this).attr("id")+"&act=add";
	dialog("添加到人才库","url:get?"+url,"500px","auto","");	
	});	
	
	$(".allfavorites").click(function()
	{
		var sltlength=$("#school-data .r-seq-item input:checked").length;
		if (sltlength==0)
		{
			dialog({
				title: '系统提示',
			    content: "请选择简历",
			    width:300
			}).showModal();
		}
		else
		{
			var jidArr=new Array();
			$("#school-data .r-seq-item :checkbox[checked]").each(function(index){jidArr[index]=$(this).val();});
			var myDialog = dialog();
			jQuery.ajax({
			    url: ""+ajaxurl+"user/user_favorites_resume.php?id="+jidArr.join("-")+"&act=add",
			    success: function (data) {
			        myDialog.content(data);
			        myDialog.title('收藏简历');
			        myDialog.width('500');
			    	myDialog.showModal();
			    }
			});
		}
	});

	// 单个收藏简历
	$(".collect").unbind().click(function(){
		var myDialog = dialog();
		jQuery.ajax({
		    url: ""+ajaxurl+"user/user_favorites_resume.php?id="+$(this).attr("id")+"&act=add",
		    success: function (data) {
		        myDialog.content(data);
		        myDialog.title('收藏职位');
		        myDialog.width('500');
		    	myDialog.showModal();
		    }
		});
	});
}