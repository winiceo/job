/* 此js 中方法需要引用 dialog-min.js*/

/*
  个人测评
*/
function is_answer_dialog(className)
{
      $(''+className+'').on('click', function(){
            var url = $(this).data("url"),
            paperid = $(this).data("id"),
            ajax_url = "evaluation_ajax.php?act=is_answer&paperid="+paperid,
            msg = '';
      var myDialog = dialog();
      myDialog.title('测评提示');
      myDialog.content("加载中...");
      myDialog.width('300');
      myDialog.showModal();
      jQuery.ajax({
        url: ajax_url,
        success: function (data) {
          msg = data.split("|")[1];
                  myDialog.content('<div class="del-dialog"><div class="tip-block"><span class="del-tips-text">'+msg+'</span></div></div><div class="center-btn-wrap"><input type="button" value="确定" class="btn-65-30blue btn-big-font DialogSubmit" /><input type="button" value="取消" class="btn-65-30grey btn-big-font DialogClose" /></div>');
                  /* 关闭 */
                  $(".DialogClose").live('click',function() {
                    myDialog.close().remove();
                  });
                  /* 确定操作 */
                  $(".DialogSubmit").click(function() 
                  {
                        if(data.split("|")[0] == '1')
                        {
                              $.post("evaluation_ajax.php?act=is_answer_next",{paperid:paperid},
                              function(result)
                              {
                                    if(result == 'ok')
                                    {
                                          window.location.href=url;
                                    }
                                    else
                                    {
                                          myDialog.content(result);
                                    }
                              });
                        }
                        else if(data.split("|")[0] == '3')
                        {
                          window.location.href="my_evaluation.php";
                        }
                        else
                        {
                          window.location.reload();
                        }
                  });
              }
      });
  })
}