<?php

namespace App\Http\Controllers\Caiji;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Caiji\ContentController;

class resourceController extends Controller
{
    public function __construct()
    {
    	$this->caiji =  new ContentController();
    }
	//最大资源网采集
    public function zuidazy($name = "")
    {
    	$name = empty($name)?$_REQUEST['name']:$name;
    	if (!$name) {
    		return "暂无数据!";
    	}

    	$post_data = [
				'wd' => $name
		];
		$url = "http://zuidazy.com";
    	$path = $url.'/index.php?m=vod-search';
    	$arr_data = [];
    	$content = $this->caiji->ff_file_get_contents($path, $post_data);

    	preg_match('/<span class=[\'|\"]xing_vb4.*?<\/span>/', $content, $a);
    	if ($a) {
    		//获取更新集数
			preg_match('/<span>(.+?)<\/span>/', $a[0], $b);
			if ($b[1]) {
				$arr_data['state'] = $b[1];
			} else {
				$arr_data['state'] = "";
			}

			//获取下载地址
			preg_match('/href=[\'|\"](.+?)[\'|\"]/', $a[0], $c);
			if ($c[1]) {
				$arr_data['downurl'] = $url.$c[1];
			} else {
				$arr_data['downurl'] = "";
			}

			//获取详情页
			if ($arr_data['downurl']) {
				$detail = $this->caiji->ff_file_get_contents($arr_data['downurl']);
				// var_dump($detail);die;
				//获取封面图
				preg_match('/<img class=[\'|\"]lazy[\'|\"] src=[\'|\"](.+?)[\'|\"].*?>/', $detail, $d);
				if ($d[1]) {
					$arr_data['pic'] = parse_url($d[1])['host']?$d[1]:$url.$d[1];
				}

				//获取到ul数据
				preg_match('/<div class=[\'|\"]vodinfobox.*?<\/div>/ism', $detail, $ul);
				if ($ul) {
					preg_match_all('/<li.*?<\/li>/ism', $ul[0], $li);
					$arr_data['subname'] = strip_tags($li[0][0]);
					$arr_data['actor'] = strip_tags($li[0][1]);
					$arr_data['director'] = strip_tags($li[0][2]);
					$arr_data['class'] = strip_tags($li[0][3]);
					$arr_data['area'] = strip_tags($li[0][4]);
					$arr_data['lang'] = strip_tags($li[0][5]);
					$arr_data['year'] = strip_tags($li[0][6]);
					$arr_data['long'] = strip_tags($li[0][7]);
					$arr_data['last'] = strip_tags($li[0][8]);
					$arr_data['hit'] = strip_tags($li[0][9]);
					$arr_data['dayhits'] = strip_tags($li[0][10]);
				}

				//获取所有地址
				preg_match_all('/<div class=[\'|\"]vodplayinfo.*?<\/div>/ism', $detail, $data);
				if ($data) {
					//获取简介
					$arr_data['des'] = strip_tags($data[0][1]);
				}

				//获取所有播放源
				preg_match_all('/<div id=[\'|\"]play_.*?<\/div>/ism', $detail, $e);
				if ($e[0]) {
					foreach ($e[0] as $key => $value) {
						//获取标识
						preg_match('/<h3.*?<\/h3>/ism', $value, $f);
						$f = explode("：",strip_tags($f[0]))[1];
						$arr_data['playfrom'][] = $f;

						//获取播放地址
						preg_match('/<ul.*?<\/ul>/ism', $value, $g);
						if ($g) {
							// var_dump($g);die;
							preg_match_all('/<li.*?<\/li>/ism', $g[0], $h);
							if ($h[0]) {
								$a = implode("\r", $h[0]);
								$arr_data['dd'][] = strip_tags($a);
							}
							// $arr_data['dd'] = $arr_data['dd'].strip_tags($dd[0])."\r";
						}
					}
				}
			}
			$arr_data['dd'] = implode("$$$", $arr_data['dd']);
			$arr_data['playfrom'] = implode("$$$", $arr_data['playfrom']);
    		return $arr_data;
		}
		return '暂无数据!';
    }

    public function yongjiuzy($name = "")
    {
    	$name = empty($name)?$_REQUEST['name']:$name;
    	if (!$name) {
    		return "暂无数据!";
    	}
    	$post_data = [
				'wd' => $name
		];
		$url = "http://yongjiuzy.com";
    	$path = $url.'/index.php?m=vod-search';
    	$arr_data = [];
    	$content = $this->caiji->ff_file_get_contents($path, $post_data);
    	if ($content) {
    		preg_match('/<td class.*?<\/td>/ism', $content, $a);

    		//获取连载状态
    		preg_match('/<span class=[\'|\"]bts_1[\'|\"].*?<\/span>/ism', $content, $state);
    		$arr_data['load_status'] = trim(strip_tags($state[0]));
    		//获取下载地址
    		if ($a) {
    			preg_match('/href=[\'|\"](.+?)[\'|\"]/ism', $a[0], $b);
    			if ($b) {
    				$arr_data['downurl'] = $url.$b[1];
    			} else {
    				$arr_data['downurl'] = "";
    			}

    			//获取状态
    			preg_match('/<font.*?<\/font>/', $a[0], $c);
    			$arr_data['state'] = str_replace(['[', ']'], '',trim(strip_tags($c[0])));
	    			
	    		$detail = $this->caiji->ff_file_get_contents($arr_data['downurl']);
	    		if ($detail) {
	    			preg_match('/<div class=[\'|\"]videoDetail[\'|\"].*?<\/div>/ism', $detail, $c);

	    			//获取封面图
	    			preg_match('/<div class=[\'|\"]videoPic.*?src=[\'|\"](.+?)[\'|\"].*?<\/div>/ism', $detail, $pic);
	    			if ($pic[1]) {
						$arr_data['pic'] = parse_url($pic[1])['host']?$pic[1]:$url.$pic[1];
					}
	    			if ($c) {
	    				//获取影片的信息
	    				preg_match_all('/<li.*?<\/li>/ism', $c[0], $li);
	    				$arr_data['name'] = strip_tags($li[0][0]);
	    				$arr_data['subname'] = strip_tags($li[0][1]);
	    				$arr_data['note'] = strip_tags($li[0][2]);
	    				$arr_data['director'] = strip_tags($li[0][3]);
	    				$arr_data['actor'] = strip_tags($li[0][4]);
	    			}

	    			//获取简介
	    			preg_match('/<div class=[\'|\"]contentNR.*?<\/div>/ism', $detail, $des);
	    			if ($des) {
	    				$arr_data['des'] = strip_tags($des[0]);
	    			}

	    			//获取播放标识
	    			// preg_match('/<div class=[\'|\"]contentURL.*?<\/div>/ism', $detail, $dd);
	    			preg_match('/<!--播放类型开始>(.+?)<播放类型结束-->/', $detail, $downurl);
	    			$arr_data['playfrom'] = trim($downurl[1], '$$$');

	    			//获取播放地址
	    			preg_match('/<!--播放地址开始>(.+?)<播放地址结束-->/', $detail, $dd);
	    			$arr_data['dd'] = str_replace(['<br>', '#'], "\r",trim($dd[1], '$$$'));
	    		}
    			return $arr_data;
    		} else {
    			return '暂无数据!';
    		}
    	} 
    	return '暂无数据!';
    }
}
