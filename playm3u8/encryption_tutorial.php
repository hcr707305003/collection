<?php

// PHP加密方式如下：
// 载入加密函数文件和播放器配置文件
// 注意：CMS资源加密了请求播放器就必须指定type资源类型，加密的密码和需要加密类型都在config.php配置文件里设置
// 引入插件加密函数文件路径
// require_once dirname(__FILE__)."/playm3u8/App/Home/Common/encryp.php";
// echo playm3u8_encryp("http://v.youku.com/v_show/id_XMTUwNDA4ODY2NA==.html","youku");


//*********海洋CMS资源加密教程***************
// 1. 修改路径 /video/index.php文件
// 2. 搜索代码 $str=implode('$$$',$arr1)  ,把如下代码复制到搜索代码上面一行。(注释不要复制)
/*
$dzarr = array();
foreach($arr1 as $key=>$dz){
	$str_txt = Null;
	$dzexplode = explode('#',$dz);
	foreach($dzexplode as $key1=>$dz1){
		$dz1 = str_replace('$$','&&',$dz1);
		$dzexplode1 = explode('$',$dz1);
		$str_txt .= str_replace(array($dzexplode1[1],'&&'),array(playm3u8_encryp($dzexplode1[1],$dzexplode1[2]),'$$'),$dz1)."#";
	}
	$dzarr[] = rtrim($str_txt,'#');
}
$arr1 = $dzarr;
*/

// 3. 搜索代码 require_once(sea_INC."/main.class.php") ,把如下代码复制到搜索代码下面一行。(注释不要复制)
// 注意播放器下面引入的文件是在播放器目录里，如果目录修改过请用修改过的路径。
/*
require_once dirname(__FILE__)."/playm3u8/App/Home/Common/encryp.php";
 */



//*********苹果8X CMS资源加密加密***************
// 1. 修改文件路径为: /inc/module/vod.php  
// 2. 搜索 $method=='play' 在if语句下面的 $db->getRow($sql) 这个方法下面加上如下代码
/*
// 播放器目录文件
require_once dirname(__FILE__)."/playm3u8/App/Home/Common/encryp.php";
$ii = 0;
$str3 = Null;
$dz_url  = explode('$$$',$row['d_playurl']);
$dz_type = explode('$$$',$row['d_playfrom']);
$row['d_playurl'] = Null;
foreach ($dz_type as $key => $type) {
	$str1 = Null;
	$exp_url = explode('#',$dz_url[$key]);
	foreach ($exp_url as $key2 => $value2) {
		$arurl = explode('$',$value2);
		$arurl[1] = playm3u8_encryp($arurl[1],$type);
		$str2 = null;
		foreach ($arurl as $key3 => $value3) {
		    $str2 .= $value3 ."$";
		}	
		$str1 .= rtrim($str2,'$')."#";
	}
	$str3 .= rtrim($str1,'#')."$$$";
}
$row['d_playurl'] = rtrim($str3,'$$$');
//*********苹果8X CMS资源加密加密***************
*/