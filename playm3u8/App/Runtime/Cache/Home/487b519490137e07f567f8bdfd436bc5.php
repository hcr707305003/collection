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
                    <h2>插件资源加密设置</h2><br>
                    <div class="form-group">
                        <label class="control-label">资源解密密钥 (需要配合CMS里的密钥一样自行修改,没有可以无视)</label>
                        <div class="control-group">
                            <input class="form-control" id="data_key" type="text" name="" value="null" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">需要加密的播放组类型 必须是CMS播放组里的类型，注意不是解析的type。(可以有多个，用“|”隔开)</label>
                        <div class="control-group">
                            <input class="form-control" id="encrypt_type" type="text" name="" value="null" placeholder="例如youku|iqiyi">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">加密数据失效时间(单位毫秒) </label>
                        <div class="control-group">
                            <input class="form-control" id="encrypt_time" type="text" name="" value="null" placeholder="">
                        </div>
                    </div>
                    <input type="button" id="submit-encrypt" class="btn btn-primary btn-block" value="提交修改">
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
  $("#data_key").val(config.data_key);
  $("#encrypt_type").val(config.encrypt_type);
  $("#encrypt_time").val(config.encrypt_time);
  $("#submit-encrypt").on("click",  function() {
    var data_key = $("#data_key").val();
    var encrypt_type = $("#encrypt_type").val();
    var encrypt_time = $("#encrypt_time").val();
    if(isNaN(encrypt_time)){
      msg("失效时间格式错误，请用数字！");
      return false;
    }
    if (data_key == "" || encrypt_time == "") {
      msg('修改信息有误！');
    } else {
      $.post("index.php?a=encrypt", {
        "key": data_key,
        "type": encrypt_type,
        "time": encrypt_time
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
</script>
</html>