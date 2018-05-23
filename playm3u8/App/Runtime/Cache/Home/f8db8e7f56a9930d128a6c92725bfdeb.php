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
            <h2>其他设置配置</h2>
            <br>
            <div class="form-group">
              <label class="control-label">防盗提示</label>
              <div class="control-group">
                <input class="form-control" id="referer_msg" type="text" value="" placeholder="默认为空"></div>
            </div>
            <div class="form-group">
              <label class="control-label btn-default" data-toggle="tooltip" data-placement="right" title="默认为100%*100%">设置播放器宽高</label>
              <div class="control-group">
                <input class="form-control" id="width_height" type="text" value="" placeholder=""></div>
            </div>
            <div class="form-group">
              <label class="control-label btn-default" data-toggle="tooltip" data-placement="right" title="取消设置为 null">播放器右上角LOGO路径（例如:App/Home/Public/player/logo/cklogo.png）</label>
              <div class="control-group">
                <input class="form-control" id="playm3u8_logo" type="text" value="" placeholder=""></div>
            </div>
            <div class="form-group">
              <label class="control-label btn-default" data-toggle="tooltip" data-placement="right" title="(一)、水平对齐方式，0是左，1是中，2是右。(二)、垂直对齐方式，0是上，1是中，2是下。(三)、水平偏移量。(四)、垂直偏移量 ">播放器右上角LOGO位置调整</label>
              <div class="control-group">
                <input class="form-control" id="pm_logo" type="text" value="" placeholder=""></div>
            </div>
            <div class="form-group">
              <label class="control-label">插件灾难性错误提示,设置了所有的错误提示都会是这个</label>
              <div class="control-group">
                <input class="form-control" id="error_msg" type="text" value="" placeholder="默认为空"></div>
            </div>
            <div class="form-group">
              <label class="control-label">指定二维码跳转地址,如果这里设置了就只会对这个地址有效</label>
              <div class="control-group">
                <input class="form-control" id="qrcode_url" type="text" value="" placeholder="http://www.playm3u8.com"></div>
            </div>
            <div class="form-group">
              <label class="control-label">被盗用自动跳转到指定网站(为空不跳转,设置了防盗提示就不会提示)</label>
              <div class="control-group">
                <input class="form-control" id="referer_url" type="text" value="" placeholder="http://www.playm3u8.com"></div>
            </div>
            <input type="button" id="submit-other" class="btn btn-primary btn-block" value="提交修改">
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
    $("#referer_msg").val(config.referer_msg);
    $("#width_height").val(config.width_height.join('*'));
    $("#playm3u8_logo").val(config.playm3u8_logo);
    $("#pm_logo").val(config.pm_logo);
    $("#error_msg").val(config.error_msg);
    $("#qrcode_url").val(config.qrcode_url);
    $("#referer_url").val(config.referer_url);
    $("#submit-other").on("click",  function() {
    var width_height  = $("#width_height").val();
    var playm3u8_logo = $("#playm3u8_logo").val();
    var pm_logo       = $("#pm_logo").val();
    if (width_height == "" || playm3u8_logo == "" || pm_logo == "") {
      msg('修改信息有误！');
    } else {
      $.post("index.php?a=other", {
        "referer_msg": $("#referer_msg").val(),
        "width_height": $("#width_height").val(),
        "playm3u8_logo": $("#playm3u8_logo").val(),
        "pm_logo": $("#pm_logo").val(),
        "error_msg": $("#error_msg").val(),
        "qrcode_url": $("#qrcode_url").val(),
        "referer_url": $("#referer_url").val(),
      },
      function(data) {
        if (data.code != 200) {
          msg('添加失败！');
          return false;
        } else {
          msg(data.msg,5);
        }
      },
      "json").error(function() {
        msg('请求超时，或数据有误，请重试！');
      });
    }
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
</script></html>