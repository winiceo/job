(function($){
	/* 投递简历 电话联系 对应相应的职位 */
    function load_jobs(index,utype)
    {
        var _job = $($(".posit_details").get(index));
        if(utype=="2")
        {
        	$(".job_btn_list > .add").attr("jobs_id", _job.attr("jobs_id"));
	        $(".job_btn_list > .add").attr("jobs_name", _job.attr("jobs_name"));
	        $(".job_btn_list > .add").attr("company_id", _job.attr("company_id"));
	        $(".job_btn_list > .add").attr("company_name", _job.attr("company_name"));
	        $(".job_btn_list > .add").attr("company_uid", _job.attr("company_uid"));
	        $(".job_btn_list > .tel").attr("href", "tel:"+_job.attr("jobs_tel"));
        }
        else
        {
        	$(".job_btn_list > .add").attr("href","login.php");
          $(".job_btn_list > .tel").attr("href","login.php");
        }
        
    }
	/* 滑动 效果 */
	function swipe_self()
	{
        var winWidth = window.innerWidth;
        var winHeight = window.innerHeight;
        var screenFix = function(){
          $("#poster_contain").css({
            width:winWidth+"px",
            height:winHeight+"px"
          });
        };
        screenFix();
        var orientationEvent = ('onorientationchange' in window) ? 'orientationchange' : 'resize';
        window.addEventListener(orientationEvent, function() {
          window.setTimeout(function(){
            screenFix();
          }, 600);
        }, false);

        $(".layer, .wx_layer").on("click", function(){
          $(this).hide();
        });
        var _indexSwipeUp=0;
        var _indexSwipe=0;
        // 滑动
        $("#poster_contain").swipeUp({
          index:_indexSwipeUp,
          childrenClass:".poster_wrap",
          init:function()
          {
            $(".arrow_con").addClass("show");
            setTimeout(function(){
              $($("#poster_contain .poster_wrap").get(_indexSwipeUp)).addClass("focus");
            }, 300);
          },
          afterSwipe:function(index)
          {
            var _pw = $("#poster_contain .poster_wrap");
            _pw.removeClass("focus");
            $(_pw.get(index)).addClass("focus");
            if(index == _pw.length-1){
            $(".arrow_con").removeClass("show");
            }else{
            $(".arrow_con").addClass("show");
            }

            var rewardHomeCon=$('.reward_home_con'),
            rewardHomeConLength=rewardHomeCon.length; 
            if(rewardHomeConLength>0){
              if(rewardHomeCon.hasClass("homt_active")){
                rewardHomeCon.removeClass("homt_active");
              }
              var rewardPlus=$('.reward_plus');
              if(rewardPlus.hasClass("plus_animate")){
                rewardPlus.removeClass("plus_animate");
              }
            }

            if(index == 2){
              welfInterval = setInterval(function(){
              var _s = parseInt(Math.random()*6);
              $(".welf_bg > div").css({"-webkit-animation":"none"});
              $($(".welf_bg > div").get(_s)).css({"-webkit-animation":"fuli"+(_s%2)+" 1s ease-out"});
              }, 1000);
            }else{
              if(typeof(welfInterval) != "undefined"){
              clearInterval(welfInterval);
              }
              $(".welf_bg > div").css({"-webkit-animation":"none"});
            }
          }
        });
        // 职位滑动
        var _width = winWidth;
        _width = _width-30*2;
        $(".posit_details").css({width:_width+"px"})
        $(".posit_details img").css({width:_width+"px"})
        var _ulWidth = $(".posit_details").length * (_width+15) + 15;

        $(".posit_list_ul").css({width:_ulWidth+"px"});

        $(".posit_list_ul").swipe({
          index:_indexSwipe,
          width:_width + 15,
          afterSwipe:function(index)
          {
            load_jobs(index,utype);
          }
        });
        //分享按钮
        $('.praise_share_btn').on('click',function(){
          var agent = navigator.userAgent.toLowerCase();
          if(agent.indexOf('micromessenger') < 0)
          {
            share_();
          }
          else
          {
            share();
          }
        });
        $(".layer, .wx_layer").on("click", function(){
          $(this).hide();
        });
        
    };
    // 点赞
    function praise(company_id)
    {
    	$(".praise_btn_click").on('click',function(event)
        {
          setCookie('praise_'+company_id+'','1');
          if($(".praise_btn").hasClass('praise_btn_click')){
              $.get('?act=praise_click&company_id='+company_id, function(data){
                if(data!="-1")
                {
                  $("#praise_num").html(data);
                  $(".praise_btn").addClass('on').removeClass('praise_btn_click');
                }
             });
          }
        });
    }
    function setCookie(name,value) 
    { 
        var Days = 30; 
        var exp = new Date(); 
        exp.setTime(exp.getTime() + Days*24*60*60*1000); 
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString(); 
    } 

    //读取cookies 
    function getCookie(name) 
    { 
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
     
        if(arr=document.cookie.match(reg))
     
            return unescape(arr[2]); 
        else 
            return null; 
    } 
    function delCookie(name) 
    { 
        var exp = new Date(); 
        exp.setTime(exp.getTime() - 1); 
        var cval=getCookie(name); 
        if(cval!=null) 
            document.cookie= name + "="+cval+";expires="+exp.toGMTString(); 
    }
        
    /* 延时加载 time */
   	function loading(time){
   		var time=time?time:1000;
    	setTimeout(function(){
	      $("#load").hide();
	      swipe_self();
	    }, time);
    };
    /* 申请职位 弹出框 */
    function showFloatBox()
    {
        var width = window.innerWidth;
        var height = window.innerHeight;
        $("body").prepend("<div class=\"menu_bg_layer\"></div>");
        $(".menu_bg_layer").css({ height:height+'px',width: width+"px", position: "absolute",left:"0", top:"0","z-index":"999","background-color":"#000000"});
        $(".menu_bg_layer").css("opacity",0.3);
    };
    /* 申请职位 操作 */
    function jobs_apply()
    {
    	$("#jobs_apply").on('click',function()
    	{
	        var href= $(this).attr("href")
	        if(href=="javascript:;")
	        {
	            showFloatBox();
	            var height = window.innerHeight;
	            var choose_menu_h = document.getElementById('choose_menu').offsetHeight;
	            var top_ = (height-choose_menu_h)/2;
	            $(".choose_menu").css("top",top_+"px");
	            $(".choose_menu").css({"opacity":1,"z-index":9999});

	            var jobs_id = $(this).attr("jobs_id");
	            var jobs_name = $(this).attr("jobs_name");
	            var company_id = $(this).attr("company_id");
	            var company_name = $(this).attr("company_name");
	            var company_uid = $(this).attr("company_uid");

	            $("#but_left").on('click',function(){
	                var resume_id=$("input[name='resume_list']:checked").val();
	                var resume_title=$("input[name='resume_list']:checked").attr("title");
	                if(resume_id){
	                $(".choose_menu").css({"opacity":0,"z-index":0});
	                $(".menu_bg_layer").remove();
	                $.post("personal/wap_apply.php?act=apply_add",{jobs_id:jobs_id,jobs_name:jobs_name,company_id:company_id,company_name:company_name,company_uid:company_uid,resume_id:resume_id,resume_title:resume_title},function(data){
	                if(data=="ok"){
	                  alert("申请职位成功");
	                  window.location.reload();
	                }else if(data=="err"){
	                  alert("申请职位失败");
	                  window.location.reload();
	                }else{
	                  alert(data);
	                  window.location.reload();
	                }
	                });
	                }else{
	                  alert("请选择简历");
	                }
	            });
	            $("#but_right").on('click',function(){
	                $(".choose_menu").css({"opacity":0,"z-index":0});
	                $(".menu_bg_layer").remove();
	            })
	        }

	    });
    };
    /* 左侧 菜单*/
    function left_menu()
    {
    	// 显示菜单
    	$(".nav_btn_con").on("touchstart", function(){
			$(".reward_manager_list_con, .reward_manager_list_con_bg").addClass("on");
		});
		// 隐藏菜单
		$(".reward_manager_list_con_bg").on("touchstart", function(){
			$(".reward_manager_list_con, .reward_manager_list_con_bg").removeClass("on");
		});
    };
    /* 显示分享 覆盖层 */
    function share(){
      $.get('?act=share&company_id='+company_id, function(data){
        if(data=='1')
        {
          $(".wx_layer").show();
        }
       });
	};
	function share_(){
    $.get('?act=share&company_id='+company_id, function(data){
      if(data=='1')
      {
        $(".layer").show();
      }
    });
	};
	// /* 微信 分享 */
	// function weixin_share(wecha_id,title,desc,imgUrl,linkUrl) {
	// 	$(document).on("WeixinJSBridgeReady", function () {
	// 		try {
	// 			WeixinJSBridge.call('hideToolbar');
	// 			//WeixinJSBridge.call('hideOptionMenu');
				
	// 			// 发送给好友; 
	// 			WeixinJSBridge.on('menu:share:appmessage', 
	// 			  function(argv) {
	// 				WeixinJSBridge.invoke(
	// 					'sendAppMessage',
	// 					 {
	// 					  //"wecha_id"      : wecha_id,
	// 					  "img_url"    : imgUrl,
	// 					  "img_width"  : "640",
	// 					  "img_height" : "640",
	// 					  "link"       : linkUrl,
	// 					  "desc"       : desc,
	// 					  "title"      : title
	// 					  }, 
	// 					  function(res) {
	// 					  }
	// 				  );
	// 			  }
	// 			);

	// 			// 分享到朋友圈;
	// 			WeixinJSBridge.on('menu:share:timeline',
	// 				function(argv){
	// 					WeixinJSBridge.invoke(
	// 						'shareTimeline',
	// 						{
	// 						  "img_url"    : imgUrl,
	// 						  "img_width"  : "640",
	// 						  "img_height" : "640",
	// 						  "link"       : linkUrl,
	// 						  "desc"       : desc ,
	// 						  "title"      : title 
	// 						},
	// 						function(res) {
	// 						}
	// 					);
	// 				}
	// 			);
	// 		} catch (e) {
	// 		}
	// 	});
	// };
	loading();
	left_menu();
	load_jobs(0,utype);
	praise(company_id);
       jobs_apply();
  if(getCookie('praise_'+company_id+'')==1)
  {
    $(".praise_btn").addClass('on').removeClass('praise_btn_click');
  }
})(jq);