<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>PlayM3u8插件设置</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <link rel="shortcut icon" href="App/Home/Public/images/playm3u8.png">
    <link rel="stylesheet" type="text/css" href="App/Home/Public/css/lyui.min.css">
	<style type="text/css">
		#btn-jike-video:after {
			content: "新";
			color: #fff;
			position: absolute;
			top: 1px;
			right: 0;
			padding: 3px 3px 3px 3px;
			z-index: 9999999;
			background: #d9534f;
			border-radius: 50%;
			font-size: 12px;
			line-height: 1;
			border: 1px solid #d43f3a
		}
	</style> 
    <script type="text/javascript" src="App/Home/Public/js/jquery.min.js" charset="utf-8"></script>
    <script type="text/javascript" src="App/Home/Public/js/lyui.min.js"></script>
    <script type="text/javascript" src="App/Home/Public/layer/layer.js"></script>
</head>
<body style="background-color: #f6f6f6;">
    <div class="container">
        <br><br><br><br><br>
        <div class="row">
          <div class="col-xs-12 col-sm-6 col-sm-offset-3">
              <div class="panel panel-default">
                  <div class="panel-body">
                      <h2>PlayM3u8插件后台</h2><br>
                      <div class="form-group">
                          <label class="control-label">Apikey</label>
                          <div class="control-group">
                              <input class="form-control" id="apikey" type="text" name="" value="" placeholder="">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="control-label">Secretkey</label>
                          <div class="control-group">
                              <input class="form-control" id="secretkey" type="text" name="" value="" placeholder="">
                          </div>
                      </div>
                      <input type="button" id="submit-login" class="btn btn-primary btn-block" value="进入后台">
                  </div>
                  <div class="panel-footer">
  <span>版权所有 (c) 2015－<?php echo date("Y",time());?> PlayM3u8云平台 保留所有权利。</span>
</div>
              </div>
          </div>
        </div>
    </div>
</body>
<script type="text/javascript">
  $("#submit-login").on("click", function() {
      var apikey    = $("#apikey").val();
      var secretkey = $("#secretkey").val();
      if(apikey == "" || secretkey == ""){
        msg('请输入apikey和secretkey！',5);
        return false;
      }
      if(apikey.length != 16){
        msg('输入apikey格式错误!');
        return false
      }
      if(secretkey.length != 32){
        msg('输入secretkey格式错误!');
        return false;
      }
      $.post("index.php?a=login", {"apikey":apikey,"secretkey":secretkey},
      function(data){
          if(data.code != 200){
              msg(data.msg); return false;
          }else{
            window.location.href = 'index.php?a=admin';
          }
      },"json").error(function() { 
          msg("请求超时，或数据有误，请重试！");
      });
  });
</script>
<script type='text/javascript'>
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	function msg(str,id=6) {
		layer.msg(str, {
		  offset: '100px',
		  anim: id
		});
	}
</script>
</html>