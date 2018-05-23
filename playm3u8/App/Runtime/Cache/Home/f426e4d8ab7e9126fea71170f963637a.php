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
            <h2>Cookie绑定列表</h2>
            <br>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>id</th>
                  <th>站点type</th>
                  <th>账号ID</th>
                  <th>状态</th>
                  <th>操作</th></tr>
              </thead>
                <?php if($config["cookie"] == true): if(is_array($config["cookie"])): foreach($config["cookie"] as $k=>$vo): ?><tbody>
                      <tr>
                        <th scope="row"><?php echo ($k+1); ?></th>
                        <td><?php echo ($vo["type"]); ?></td>
                        <td><?php echo ($vo["user"]); ?></td>
                        <td>
                          <?php if($vo["state"] == true): ?>√
                            <?php else: ?>X<?php endif; ?></td>
                        <td>
                          <a type="button" href="javascript:;" onclick="edit('<?php echo ($k); ?>','<?php echo ($vo["type"]); ?>','<?php echo ($vo["user"]); ?>','<?php echo ($vo["cookie"]); ?>','<?php echo ($vo["api"]); ?>')" class="btn btn-primary btn-xs">编辑</a>
                          <a type="button" href="javascript:;" onclick="del('<?php echo ($k); ?>')" class="btn btn-danger btn-xs">删除</a></td>
                      </tr>
                    </tbody><?php endforeach; endif; ?>
                <?php else: ?>
                  <tbody>
                    <tr class="builder-data-empty">
                      <td class="text-center empty-info" colspan="9"><br>暂时没有数据</td>
                    </tr>
                  </tbody><?php endif; ?>
            </table>
            <div class="form-group">
              <label for="exampleInputName1">站点type</label>
              <input type="text" class="form-control" id="type" placeholder="iqiyi">
              <input type="text" style="display:none" id="type-id" value="">
            </div>
            <div class="form-group">
              <label for="exampleInputName1">账号名称</label>
              <input type="text" class="form-control" id="user" placeholder="123456789@qq.com">
            </div>
            <div class="form-group">
              <label for="exampleInputEmail2">Cookie信息 <a target="_blank" href="http://917.tzyee.net/list/2VTCu">帐号购买指引</a></label>
              <textarea class="form-control" id="cookie" rows="2" placeholder="账号的登录Cookie"></textarea>
            </div>
            <div class="form-group">
              <label for="exampleInputName1">回调解析服务器接口 <a target="_blank" href="index.php?a=downapi"> 获取回调接口</a> 接口文件放在任意国内的外网服务器上(BAE或SAE不要放)</label>
              <input type="text" class="form-control" id="api" placeholder="http://123444.com/api.php">
            </div>
            <input type="button" id="submit-cookie" class="btn btn-primary btn-block" value="添加新信息">
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
  function edit(id, type, user, cookie,api) {
    $("#type").val(type);
    $("#user").val(user);
    $("#cookie").val(cookie);
    $("#api").val(api);
    $("#type-id").val(id);
    $("#submit-cookie").val("更新Cookie");
  }
  function del(id) {
    $.post("index.php?a=cookie", {
      "ct": "del",
      "id": id
    },
    function(data) {
      if (data.code != 200) {
        msg('删除失败！');
        return false;
      } else {
        window.location.href = 'index.php?a=cookie';
      }
    },
    "json").error(function() {
      msg('请求超时，或数据有误，请重试！');
    });
  }
  $("#submit-cookie").on("click",
  function() {
    var id = $("#type-id").val();
    var type = $("#type").val();
    var user = $("#user").val();
    var cookie = $("#cookie").val();
    var api = $("#api").val();
    if(type == 'tianyi'){
      if(api != ""){
        msg('此类型Cookie设置无需设置回调接口地址'); return false;
      }
    }
    if (type == "" || user == "" || cookie == "") {
      msg('添加信息不能为空！');
    } else {
      $.post("index.php?a=cookie", {
        "ct": "add",
        "id": id,
        "type": type,
        "user": user,
        "cookie": cookie,
        "api":api,
      },
      function(data) {
        if (data.code != 200) {
          msg(data.msg);
          return false;
        } else {
          $("#type-id").val("");
          $("#submit-cookie").val("添加新信息");
          window.location.href = 'index.php?a=cookie';
        }
      },
      "json").error(function() {
        msg('请求超时，或数据有误，请重试！');
      });
    }
  });</script>
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