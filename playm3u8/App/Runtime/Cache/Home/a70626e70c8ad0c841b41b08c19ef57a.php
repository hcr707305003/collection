<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
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
        <div class="navbar navbar-inverse navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="sr-only">导航开关</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" target="_blank" href="index.php?url=">插件接口</a>
            </div>
            <div class="collapse navbar-collapse nav-collapse">
                <ul class="nav navbar-nav" id="step">
                    <?php if(ACTION_NAME == admin): ?><li class="active"><a href="javascript:;">系统设置</a></li>
                    <?php else: ?>
                        <li><a href="index.php?a=admin">系统设置</a></li><?php endif; ?>
                    <?php if(ACTION_NAME == cookie): ?><li class="active"><a href="javascript:;">Cookie绑定</a></li>
                    <?php else: ?>
                        <li><a href="index.php?a=cookie">Cookie绑定</a></li><?php endif; ?>
                    <?php if(ACTION_NAME == encrypt): ?><li class="active"><a href="javascript:;">加密设置</a></li>
                    <?php else: ?>
                        <li><a href="index.php?a=encrypt">加密设置</a></li><?php endif; ?>
                    <?php if(ACTION_NAME == other): ?><li class="active"><a href="javascript:;">其他设置</a></li>
                    <?php else: ?>
                        <li><a href="index.php?a=other">其他设置</a></li><?php endif; ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="index.php?m=update">检查更新</a></li>
                    <!-- <li><a id="btn-jike-video" href="index.php?m=update">检查更新</a></li> -->
                    <?php if(ACTION_NAME == test): ?><li class="active"><a href="javascript:;">测试</a></li>
                    <?php else: ?>
                        <li><a href="index.php?a=test">测试</a></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
        <div class="col-xs-12 col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h2>PlayM3u8插件后台配置 <small> Ver <?php echo ($config["Ver"]); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="cache_del()">清除缓存</a></small></h2>
                    <br>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label btn-default" data-toggle="tooltip" data-placement="top" title="开启为开放模式，没有任何限制，会被别人盗用的哦">调试模式(部署到服务器请关闭)</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover0">
                                    <input type="radio" id="cover0" class="radio" name="cover0" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover1">
                                    <input type="radio" id="cover1" class="radio" name="cover0" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label btn-default" data-toggle="tooltip" data-placement="top" title="此项需要php安装mcrypt扩展">采用Js脚本加密(防盗级别更高)</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover00">
                                    <input type="radio" id="cover00" class="radio" name="cover00" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover01">
                                    <input type="radio" id="cover01" class="radio" name="cover00" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>

                      <div class="col-md-4">

                        <div class="form-group">
                            <label class="control-label btn-default" data-toggle="tooltip" data-placement="left" title="">播放器皮肤风格</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover02">
                                    <input type="radio" id="cover02" class="radio" name="cover11" value="0">风格1
                                </label>
                                <label class="radio-inline" for="cover03">
                                    <input type="radio" id="cover03" class="radio" name="cover11" value="1">风格2
                                </label>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">授权Apikey信息</label>
                        <div class="control-group">
                            <input class="form-control" id="apikey" type="text" name="" value="" placeholder="e951965b02412e46">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">绑定域名白名单(子域名一样需要绑定,多个域名用“|”隔开)不限制留空即可</label>
                        <div class="control-group">
                            <textarea class="form-control" id="auth_domain" rows="3" placeholder="test.com|www.test.com|m.test.com"></textarea>
                        </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">

                        <div class="form-group">
                            <label class="control-label">备用接口(默认为空)</label>
                            <div class="control-group">
                                <input class="form-control" id="back_host" type="text" name="" value="" placeholder="http://www.xxxxxx.com">
                            </div>
                        </div>

                      </div>
                      <div class="col-md-6">

                        <div class="form-group">
                            <label class="control-label btn-default" data-toggle="tooltip" data-placement="top" title="插件在无法识别端口才配置的哦">插件自定义端口</label>
                            <div class="control-group">
                                <input class="form-control" id="diy_port" type="text" name="" value="" placeholder="默认为空">
                            </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">

                        <div class="form-group">
                            <label class="control-label">播放器页面标题</label>
                            <div class="control-group">
                                <input class="form-control" id="play_title" type="text" name="" value="" placeholder="为空则默认">
                            </div>
                        </div>

                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">播放器页面开始加载提示</label>
                            <div class="control-group">
                                <input class="form-control" id="start_msg" type="text" name="" value="" placeholder="为空则不显示">
                            </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">默认输出清晰度</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover3">
                                    <input type="radio" id="cover3" class="radio" name="cover1" value="1">标清
                                </label>
                                <label class="radio-inline" for="cover4">
                                    <input type="radio" id="cover4" class="radio" name="cover1" value="2">高清
                                </label>
                                <label class="radio-inline" for="cover5">
                                    <input type="radio" id="cover5" class="radio" name="cover1" value="3">超清
                                </label>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">插件是否自动播放</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover6">
                                    <input type="radio" id="cover6" class="radio" name="cover2" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover7">
                                    <input type="radio" id="cover7" class="radio" name="cover2" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">开启清晰度切换显示</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover8">
                                    <input type="radio" id="cover8" class="radio" name="cover3" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover9">
                                    <input type="radio" id="cover9" class="radio" name="cover3" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">开启控制栏二维码</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover10">
                                    <input type="radio" id="cover10" class="radio" name="cover4" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover11">
                                    <input type="radio" id="cover11" class="radio" name="cover4" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">开启下载速度显示</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover12">
                                    <input type="radio" id="cover12" class="radio" name="cover5" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover13">
                                    <input type="radio" id="cover13" class="radio" name="cover5" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">开启播放器动作提示</label>
                            <div class="control-group">
                                <label class="radio-inline" for="cover14">
                                    <input type="radio" id="cover14" class="radio" name="cover6" value="0">开启
                                </label>
                                <label class="radio-inline" for="cover15">
                                    <input type="radio" id="cover15" class="radio" name="cover6" value="1">关闭
                                </label>
                            </div>
                        </div>
                      </div>
                    </div>
                    <input type="button" id="submit-admin" class="btn btn-primary btn-block" value="提交修改">
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
  var config = eval("(" + '<?php echo json_encode($config);?>' + ")");
  if (config.debug) {
    $('#cover0').prop('checked', true);
  } else {
    $('#cover1').prop('checked', true);
  }
  if (config.js_encryption) {
    $('#cover00').prop('checked', true);
  } else {
    $('#cover01').prop('checked', true);
  }
  if (config.player_skin == 1) {
    $('#cover02').prop('checked', true);
  } else if (config.player_skin == 2) {
    $('#cover03').prop('checked', true);
  }
  if (config.auto_play) {
    $('#cover6').prop('checked', true);
  } else {
    $('#cover7').prop('checked', true);
  }
  if (config.definition) {
    $('#cover8').prop('checked', true);
  } else {
    $('#cover9').prop('checked', true);
  }
  if (config.qrcode) {
    $('#cover10').prop('checked', true);
  } else {
    $('#cover11').prop('checked', true);
  }
  if (config.downspeeds) {
    $('#cover12').prop('checked', true);
  } else {
    $('#cover13').prop('checked', true);
  }
  if (config.prompttext) {
    $('#cover14').prop('checked', true);
  } else {
    $('#cover15').prop('checked', true);
  }
  if (config.play_hd == 1) {
    $('#cover3').prop('checked', true);
  } else if (config.play_hd == 2) {
    $('#cover4').prop('checked', true);
  } else if (config.play_hd == 3) {
    $('#cover5').prop('checked', true);
  }
  $("#apikey").val(config.apikey);
  $("#diy_port").val(config.diy_port);
  $("#back_host").val(config.back_host);
  $('#play_title').val(config.play_title);
  $('#start_msg').val(config.start_msg);
  if(config.auth_domain != ""){
    $("#auth_domain").val(config.auth_domain.join('|'));
  }
  $("#submit-admin").on("click",  function() {
    if($("#apikey").val().length != 16){
      msg("apikey格式错误！");
      return false;
    }
    if($("#back_host").val() != ""){
      if($("#back_host").val().indexOf("http://") < 0){
        msg("备用接口地址格式错误！");
        return false;
      }
    }
    if($("#diy_port").val() != ""){
      if(isNaN($("#diy_port").val())){
        msg("端口格式错误，请用数字！");
        return false;
      }
    }
    if($('#cover3').prop('checked')){
      var play_hd = 1; 
    } else if($('#cover4').prop('checked')){
      var play_hd = 2; 
    } else if($('#cover5').prop('checked')){
      var play_hd = 3; 
    }
    if (apikey == "") {
      msg('修改信息有误！');
    } else {
      $.post("index.php?a=admin",{
        "apikey":$("#apikey").val(),
        "debug" :$('#cover0').prop('checked')? 1:"",
        "js_encryption":$('#cover00').prop('checked')? 1:"",
        "player_skin": $('#cover03').prop('checked')? 2:1,
        "auth_domain":$("#auth_domain").val(),
        "back_host":$("#back_host").val(),
        "diy_port": $("#diy_port").val(),
        "play_title":$("#play_title").val(),
        "start_msg":$("#start_msg").val(),
        "auto_play":$('#cover6').prop('checked')? 1:"",
        "definition":$('#cover8').prop('checked')? 1:"",
        "qrcode":$('#cover10').prop('checked')? 1:"",
        "downspeeds":$('#cover12').prop('checked')? 1:"",
        "prompttext":$('#cover14').prop('checked')? 1:"",
        "play_hd":play_hd,
      },
      function(json) {
        if (json.code != 200) {
          msg(json.msg);
          return false;
        } else {
          msg(json.msg,5);
        }
      },
      "json").error(function() {
        msg('请求超时，或数据有误，请重试！');
      });
    }
  });
  function cache_del(){
    $.get("index.php?a=cache_del",function(json){},"json").error(
    function() {
      msg('清除成功！',5);
    });
  }
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