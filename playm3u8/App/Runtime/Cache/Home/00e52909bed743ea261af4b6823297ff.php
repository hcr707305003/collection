<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="description" content="PlayM3u8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="App/Home/Public/images/playm3u8.png">
    <title><?php echo ($config["play_title"]); ?></title>
    <?php if(!is_mobile()): ?><link rel="stylesheet" href="App/Home/Public/css/dplayer.min.css?v=<?php echo str_replace('.','',$config['Ver']);?>">
      <style type="text/css">body,html{background-color:#000;padding: 0;margin: 0;width:<?php echo $config['width_height'][0]?>;height:<?php echo $config['width_height'][1]?>;color:#aaa;}}</style>
    <?php else: ?>
      <style type="text/css">body,html,div{background-color:#000;padding: 0;margin: 0;width:<?php echo $config['width_height'][0]?>;height:<?php echo $config['width_height'][1]?>;color:#aaa;}}</style><?php endif; ?>
    <?php if(!is_mobile()): ?><script type="text/javascript" src="App/Home/Public/js/hls.min.js?v=<?php echo str_replace('.','',$config['Ver']);?>"></script>
      <script type="text/javascript" src="App/Home/Public/js/dplayer.min.js?v=<?php echo str_replace('.','',$config['Ver']);?>"></script><?php endif; ?>
    <script type="text/javascript" src="App/Home/Public/player/js/jquery.js?v=<?php echo str_replace('.','',$config['Ver']);?>"></script>
  </head>
  <body style="overflow-y:hidden;">
    <div id="loading" style="font-weight:bold;padding-top:120px;" align="center">
        <?php echo ($config["start_msg"]); ?> . . .
        <br><br>
        <img border="0" <img src="App/Home/Public/player/logo/load.gif" /></div>
    <div id="a1" style="display:none;" class="dplayer"></div>
    <div id="error" style="display:none;font-weight:bold;padding-top:120px;" align="center"></div>
    <script type="text/javascript">
        var PlayM3u8 = <?php echo json_encode($config);?>;
        PlayM3u8.refres = 1;
        PlayM3u8.prefix = 'ajax_';
        PlayM3u8.isiPad = navigator.userAgent.match(/iPad|iPhone|Linux|Android|iPod/i) != null;
    </script>
    <?php if(!is_mobile()): ?><script type="text/javascript" src="App/Home/Public/player/skin/ckplayer<?php echo ($config["player_skin"]); ?>/ckplayer.js?v=<?php echo str_replace('.','',$config['Ver']);?>"></script><?php endif; ?>
    <script type="text/javascript" src="App/Home/Public/js/player.js?v=<?php echo str_replace('.','',$config['Ver']);?>"></script>
  </body>
</html>