<?php

namespace App\Http\Controllers\Caiji;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Caiji\IqiyiController;
use App\Http\Controllers\Caiji\resourceController;
use Log;

class ContentController extends Controller
{
    public function __construct(){

		set_time_limit(0);
		error_reporting(0);
		ini_set('memory_limit', '-1'); //内存无限
		\DB::connection()->enableQueryLog(); // 开启查询日志 
	}

	//视频站和资源站混合资源
	/*public function collection_resource($url = "")
	{
		$url = empty($url)?$_REQUEST['url']:$url;
		$collection = $this->collection_content($url);
		if ($collection['name']) {
			$type_name_find_all_data = $this->get_all_resource($collection['name']);
			if (count($type_name_find_all_data) == 2) {
				foreach ($type_name_find_all_data as $key => $values) {
					if (!array_key_exists('dd', $values)) {
						break;
					}
					$collection['playfrom'] = $collection['playfrom']."$$$".$values['playfrom'];
					$collection['dd'] = $collection['dd']."$$$".$values['dd'];
					if ($values['state']) {
						$collection['load_status'] = $values['state'];
					}
					if ($values['state1']) {
						$collection['state'] = $values['state1'];
					}
				}
			}
		}
		return $collection;
	}*/

	//自动采集
	public function auto_collection()
	{
		$update_all_data = DB::table('vods')->where('up_time', '<', date('Y-m-d H:i:s', time()))->limit(500)->where('load_status', '!=', '完结')->where('continu', '>', '0')->get(['name','id','dd','continu', 'load_status', 'state']);
		foreach ($update_all_data as $key => $value) {
			preg_match('/^[正片高清]/', $value->dd, $a);
			if ($a) {
				continue;
			} else {
				if (count(explode('$', $value->dd)) <= $value->continu) {
					$a = str_replace(['\r', '#'], '#', $value->dd);
					preg_match('/http.*?html/', $a, $b);
					if ($b) {
						$collection = $this->collection_content($b[0]);
						if ($collection['type_name']) {
							$type_name_find_all_data = $this->get_all_resource($collection['type_name']);
							if ($type_name_find_all_data == "暂无数据!") {
								continue;
							} else {
								foreach ($type_name_find_all_data as $key => $values) {
									// var_dump($values);die;
									if ($values['dd']) {
										$collection['playfrom'] = $collection['playfrom']."$$$".$values['playfrom'];
										$collection['dd'] = $collection['dd']."$$$".$values['dd'];
										if ($values['state']) {
											$collection['load_status'] = $values['state'];
										}
										if ($values['state1']) {
											$collection['state'] = $values['state1'];
										}
										$data = array();
										$data['playfrom'] = $collection['playfrom'];
										$data['downurl'] = $collection['downurl'];
										$data['dd'] = $collection['dd'];
										$data['load_status'] = $collection['load_status'];
										$data['up_time'] = date('Y-m-d H:i:s', time()+30*60);
										
										
										Log::info("ID:".$value->id." Name:".$value->name);  

										DB::table('vods')->where('id', '=', $value->id)->update($data);
									} else {
										continue;
									}
								}
							}
						} else {
							continue;
						}
					}
				} else {
					continue;
				}
			}
		}
	}

	//指定采集
	public function collection_content($url = "")
	{
		$url = empty($url)?$_REQUEST['url']:$url;
		$pasurl = parse_url($url);
		if ($pasurl['host'] == "v.qq.com") {//采集qq视频
			$playfrom = 'qq';
			$content = $this->collection_qqtv($url);
			$content['playfrom'] = $playfrom;
			return $content;
		} else if ($pasurl['host'] == "www.iqiyi.com") {
			$playfrom = 'iqiyi';
		} else if ($pasurl['host'] == "www.mgtv.com") {
			$playfrom = 'mgtv';
			$content = $this->collection_mgtv($url);
			$content['playfrom'] = $playfrom;
			return $content;
		} else {
			$playfrom = $pasurl['host'];
		}
		$content = $this->ff_file_get_contents($url);
		$albumId = $this->get_albumid($content);
		$tvid = $this->get_tvid($content);//获取到爱奇艺的唯一识别id
		$data = $this->get_all($tvid);//获取到残缺的数据
		$all_data['name'] = isset($data['name'])?$data['name']:$this->get_name($content); 
		$all_data['seo'] = isset($data['seo'])?$data['seo']:$this->get_seo($content);
		$all_data['letter'] = isset($data['letter'])?$data['letter']:$this->get_letter($all_data['name']);
		$all_data['type_name'] = isset($data['type_name'])?$data['type_name']:$this->get_type_name($content);
		$all_data['class'] = isset($data['class'])?$data['class']:$this->get_area_class($content)['class'];
		$all_data['lang'] = isset($data['lang'])?$data['lang']:"";
		$all_data['area'] = isset($data['area'])?$data['area']:"";
		$all_data['score'] = isset($data['score'])?$data['score']:$this->get_score($tvid, $this->get_typeid($content));
		@$all_data['last'] = $this->get_last($content)[0];
		$all_data['year'] = substr($all_data['last'], 0,4);
		// $all_data['year'] = isset($data['year'])?$data['year']:$this->get_year($all_data['last']);
		$all_data['note'] = $this->get_note($content)?$this->get_note($content):$data['note'];
		$all_data['state'] = isset($data['state'])?$data['state']:1;
		$all_data['continu'] = $this->get_continu($content);
		$all_data['actor'] = $this->get_actor($content);
		$all_data['director'] = $this->get_director($content);
		$all_data['hit'] = isset($data['hit'])?$data['hit']:"";
		$all_data['vdown'] = isset($data['vdown'])?$data['vdown']:"";
		$all_data['pic'] = isset($data['pic'])?$data['pic']:$this->get_pic($content);
		$all_data['playfrom'] = $playfrom;
		$all_data['des'] = isset($data['des'])?$data['des']:$this->get_des($content);
		$all_data['downurl'] = $url;
		$all_data['reweek'] = $this->get_reweek($content);
		$dd = $this->get_dd($albumId, $all_data['state'])!=""?$this->get_dd($albumId, $all_data['state']):$url;
		$all_data['dd'] = $dd;
		$area_class = $this->get_area_class($content);
		return $all_data;
	}

	//腾讯采集
	public function collection_qqtv($url = "")
	{
		$url = empty($url)?$_REQUEST['url']:$url;
		$data = array();
		$data['downurl'] = $url;
		$json_content = json_encode(file_get_contents($url));
		$content = json_decode($json_content);
		// var_dump($content);die;
		//获取视频唯一id
		preg_match('/COVER_INFO.*?,/', $content, $id);
		preg_match('/id.*?,/', $id[0], $id);
		$id = trim(explode(':', trim($id[0], ','))[1], '"');

		preg_match('/<title>.*?<\/title>/', $content, $a);
		if (!$a) return '页面无法获取，请重新获取';
		if ($a) {
			$data['seo'] = trim(strip_tags($a[0]));
		} else {
			$data['seo'] = "";
		}
		preg_match('/<a.*?videolist:title.*?<\/a>/', $content, $b);
		if ($b) {
			$data['name'] = trim(strip_tags($b[0]));
		} else {
			$data['name'] = "";
		}

		preg_match('/<div class=[\'|\"]director.*?<\/div>/ism', $content, $c);
		if ($c) {
			$d = trim(strip_tags($c[0]));
			$e = explode(':', $d);
			if ($e[1]) {
				preg_match('/.*:?&nbsp;/', trim($e[1]), $director);
				if ($director) {
					$data['director'] = trim(trim($director[0], '&nbsp;'));
				} else {
					$data['director'] = "";
				}
			} else {
				$data['director'] = "";
			}

			if (trim($e[2])) {
				$qian=array(" ","　","\t","\n","\r");
				$data['actor'] = str_replace($qian, '', trim($e[2]));
			} else {
				$data['actor'] = "";
			}

		} else {
			$data['actor'] = "";
			$data['director'] = "";
		}
		$data['letter'] = $this->get_letter($data['name']);
		preg_match('/type_name.*?,/', $content, $type_name);
		if ($type_name) {
			$f = trim(explode(':', trim($type_name[0], ','))[1], '"');
			$data['type_name'] = $f;
		} else {
			$data['type_name'] = "";
		}
		preg_match('/<meta itemprop=[\'|\"]inLanguage.*?content=[\'|\"](.+?)[\'|\"].*?>/', $content, $lang);
		if ($lang) {
			$data['lang'] = $lang[1];
		} else {
			$data['lang'] = "";
		}
		
		preg_match('/<meta itemprop=[\'|\"]contentLocation.*?content=[\'|\"](.+?)[\'|\"].*?>/', $content, $area);
		if ($area) {
			$data['area'] = $area[1];
		} else {
			$data['area'] = "";
		}

		preg_match('/score.*?,/', $content, $score);
		if ($score) {
			$f = trim(explode(':', trim($score[0], ','))[1], '"');
			$data['score'] = $f;
		} else {
			$data['score'] = "";
		}

		preg_match('/<em id=[\'|\"]mod_cover_playnum.*?<\/em>/', $content, $hit);
		if ($hit) {
			$data['hit'] = trim(strip_tags($hit[0]));
		} else {
			$data['hit'] = "";
		}

		preg_match('/brief.*?,/', $content, $note);
		if ($note) {
			$f = trim(explode(':', trim($note[0], ','))[1], '"');
			$data['note'] = $f;
		} else {
			$data['note'] = "";
		}

		if ($id) {
			$detail_url = 'https://v.qq.com/detail/1/'.$id.'.html';
			$detail_content = file_get_contents($detail_url);
			preg_match('/<div class=[\'|\"]type_item.*?别　名.*?<\/div>/ism', $detail_content, $subname);
			if ($subname) {
				preg_match('/<span.*?type_txt.*?<\/span>/', $subname[0], $subname1);
				$data['subname'] = trim(strip_tags($subname1[0]));
			}
			preg_match_all('/<div class=[\'|\"]type_item.*?<\/div>/ism', $detail_content, $year);
			foreach ($year[0] as $key => $value) {
				preg_match('/[上映出品]时间/', $value, $value1);
				if ($value1) {
					$data['year'] = trim(explode(':',trim(strip_tags($value)))[1]);
				} else {
					continue;
				}
			}
			foreach ($year[0] as $key => $value) {
				preg_match('/[更新]时间/', $value, $value1);
				if ($value1) {
					$data['last'] = trim(explode(':',trim(strip_tags($value)))[1]);
				} else {
					continue;
				}
			}
			foreach ($year[0] as $key => $value) {
				preg_match('/总集数/', $value, $value1);
				if ($value1) {
					$data['continu'] = trim(explode(':',trim(strip_tags($value)))[1]);
				} else {
					continue;
				}
			}
			preg_match_all('/<a class=[\'|\"]tag[\'|\"].*?<\/a>/', $detail_content, $class);
			if ($class) {
				$data['class'] = strip_tags(implode(',', $class[0]));
			} else {
				$data['class'] = "";
			}

			preg_match('/<span class=[\'|\"]txt _desc_txt_lineHight.*?<\/span>/ism', $detail_content, $des);
			if ($des) {
				$data['des'] = strip_tags($des[0]);
			} else {
				$data['des'] = "";
			}

			preg_match('/<meta name=[\'|\"]twitter:image[\'|\"].*? content=[\'|\"](.+?)[\'|\"]/', $detail_content, $pic);
			if ($pic) {
				$data['pic'] = $pic[1];
			} else {
				$data['pic'] = "";
			}
			preg_match('/<div class=[\'|\"]mod_episode[\'|\"].*?<\/div>/ism', $detail_content, $dd);
			if ($dd) {
				preg_match_all('/<a href=[\'|\"](.+?)[\'|\"].*?<\/a>/ism', $dd[0], $dd1);
				for ($i=0; $i < count($dd1[0]); $i++) { 
					$data['dd'] = $data['dd'].trim(strip_tags($dd1[0][$i]))."$".$dd1[1][$i]."#";
				}
			} else {
				preg_match('/column_id.*?,/', $content, $vid);
				if ($vid) {
					$vid = intval(trim(explode(':', trim($vid[0], ','))[1], '"'));
					$detail_url = 'https://v.qq.com/detail/7/'.$vid.'.html';
					$detail_data = $this->ff_file_get_contents($detail_url);
					preg_match_all('/<li class=[\'|\"]list_item[\'|\"].*?<\/li>/ism', $detail_data, $li);
					if ($li[0]) {
						foreach ($li[0] as $key => $value) {
							preg_match('/<a href=[\'|\"](.+?)[\'|\"].*?<\/a>/', $value, $a);
							if (!$a) {
								break;
							}
							$data['dd'] = $data['dd'].strip_tags($a[0])."$".$a[1]."#";
						}
					} else {
						$data['dd'] = $url;
					}
				} else {
					return '获取不到视频id,无法扑捉视频数据!';
				}
			}
		}
		$data['dd'] = trim($data['dd'], '#');
		var_dump($data);die;
		return $data;
	}

	//芒果tv设置多线程
	public function find_name_mgtv()
	{

	}

	//芒果视频采集
	public function collection_mgtv($url = "")
	{
		/*preg_match('/http.*?html/', $url, $url);
		$url = $url[0];
		$caiji = new IqiyiController();
		$path = "http://cj.tv6.com/mox/inc/mgtv.php?ac=videolist&rid=&h=&pg=";
		for ($i = 0; $i < 1; $i++) {
			$admin = array();
			$admin['action'] = 'all';
			$admin['xmlurl'] = base64_encode($path.$i);
			$admin['xmltype'] = NULL;
			$admin['page'] = 1;
			$vod = $caiji->vod($admin);
			// var_dump($vod);die;
			//格式化部份数据字段
			if ($vod['status'] != 200) {
				return $vod['infos'];
			}
			//获取总页数并获取到分页数据
			$maxpage = intval($vod['infos']['page']['pagecount']);
			//起名
			if ($maxpage > 40) {
				$all = ceil($maxpage/40);
			}
			$find = new IqiyiController();
			$find_dd = 'find';
			for ($c = 1; $c <= 1; $c++) {
				// $$find_dd."_".$c;
				var_dump($find_dd."_".$c = new IqiyiController());	
			}
			for ($a=0; $a < 20; $a++) { 
				$admin['page'] = $a;
				$vod = $caiji->vod($admin);
				for ($b=0; $b < count($vod['infos']['data']); $b++) { 
					if (strstr($vod['infos']['data'][$b]['vod_name'], '家')) {
						$aa[] = 11;
					}
					if (strstr($vod['infos']['data'][$b]['vod_url'], $url)) {
						echo 11;
					}
				}
			}
		}*/
		
		//被芒果禁ip时的操作
		$caiji = new IqiyiController();
		$caiji->iqiyi('http://cj.tv6.com/mox/inc/mgtv.php');

		//没被芒果禁ip的操作
		die;
		$url = empty($url)?$_REQUEST['url']:$url;
		$content = $this->ff_file_get_contents($url);
		$data = array();
		preg_match('/<head.*?<\/head>/ism', $content, $a);
		if (!$a) return '页面无法获取，请重新获取';
		$data['downurl'] = $url;
		//获取vid
		preg_match('/vid.*?,/', $content, $vid);
		if ($vid) {
			$vid = intval(trim(explode(':', trim($vid[0], ','))[1], '"'));
		} else {
			return '获取不到视频id,无法扑捉视频数据!';
		}

		//获取标题
		preg_match('/title.*?,/', $content, $title);
		if ($title) {
			$a = str_replace(['"',"'",' '], '', trim(explode(':', trim($title[0], ','))[1], '"'));
			$data['seo'] = $a;
		} else {
			$data['seo'] = "";
		}

		//获取类型,名称
		preg_match('/<div class=[\'|\"]v-panel-route.*?<\/div>/ism', $content, $name);
		if ($name) {
			preg_match_all('/<a.*?<\/a>/ism', $name[0], $a);
			if (count($a[0]) == 3) {
				$data['name'] = strip_tags($a[0][0]);
				$data['type_name'] = strip_tags($a[0][2]);
			} 
		}

		//获取地区,类型,演员,导演
		preg_match('/<div class=[\'|\"]v-panel-meta.*?<\/div>/ism', $content, $panel_meta);
		if ($panel_meta) {
			preg_match_all('/<p.*?<\/p>/ism', $panel_meta[0], $all_data);
			foreach ($all_data[0] as $key => $value) {
				preg_match('/导演/', $value, $director);
				if ($director) {
					preg_match('/<a.*?<\/a>/', $value, $director);
					$data['director'] = trim(strip_tags($director[0]));
				} else {
					continue;
				}
			}
			foreach ($all_data[0] as $key => $value) {
				preg_match('/主演/', $value, $actor);
				if ($actor) {
					preg_match_all('/<a.*?<\/a>/', $value, $actor);
					if ($data['actor']) {
						continue;
					} else {
						$data['actor'] = trim(strip_tags(implode(',', $actor[0])));
					}
				} else {
					continue;
				}
			}

			foreach ($all_data[0] as $key => $value) {
				preg_match('/地区/', $value, $area);
				if ($area) {
					preg_match('/<a.*?<\/a>/', $value, $area);
					$data['area'] = trim(strip_tags($area[0]));
				} else {
					continue;
				}
			}

			foreach ($all_data[0] as $key => $value) {
				preg_match('/类型/', $value, $class);
				if ($class) {
					preg_match_all('/<a.*?<\/a>/', $value, $class);
					if ($data['class']) {
						continue;
					} else {
						$data['class'] = trim(strip_tags(implode(',', $class[0])));
					}
				} else {
					continue;
				}
			}

			foreach ($all_data[0] as $key => $value) {
				preg_match('/简介/', $value, $des);
				if ($des) {
					preg_match('/<span class=[\'|\"]details.*?<\/span>/', $value, $des);
					$data['des'] = trim(strip_tags($des[0]));
				} else {
					continue;
				}
			}
		}

		//获取首字母
		$data['letter'] = $this->get_letter($data['name']);

		//获取评分
		$score_url = 'https://vc.mgtv.com/v2/dynamicinfo?vid='.$vid;
		$score_data = json_decode($this->ff_file_get_contents($score_url));
		if ($score_data) {
			$data['score'] = $score_data->data->allStr;
		}

		//获取图片
		preg_match('/<i class=[\'|\"]img[\'|\"]>.*?src=[\'|\"](.+?)[\'|\"].*?<\/i>/ism', $content, $pic);
		if ($pic) {
			$data['pic'] = $pic[1];
		}

		//获取总集数
		$video_url = 'https://pcweb.api.mgtv.com/episode/list?video_id='.$vid;
		$video_data = $this->ff_file_get_contents($video_url);
		if ($video_data) {
			$video_data = json_decode($video_data);
			$data['continu'] = $video_data->data->total;
			$data['state'] = $video_data->data->count;
			$data['note'] = $video_data->data->info->desc;
		}

		//获取到爬取总页数
		if ($video_data->data->current_page) {
			$maxpage = $video_data->data->current_page;
			for ($i=1; $i <= $maxpage ; $i++) {
				$page_data = $this->ff_file_get_contents($video_url."&page=".$i);
				if ($page_data) {
					$page_data = json_decode($page_data);
					if ($page_data->data->list) {
						$url = 'https://www.mgtv.com';
						foreach ($page_data->data->list as $key => $value) {
							$data['dd'] = $data['dd'].$value->t4."$".$url.$value->url."#";
						}

					}
				}
			}
			$data['dd'] = rtrim($data['dd'], '#');
		}
		return $data;
	}

	//获取更新时间
	public function get_reweek($content = "")
	{
		if (!$content) {
			return "";
		}
	// http://www.iqiyi.com/v_19rrdg7orc.html
		preg_match('/<p.*?更新.*?<\/p>/', $content, $a);
		if ($a) {
			return $a[0];
		} else {
			preg_match('/<span.*?更新.*?<\/span>?/',$content, $a);
			if (!$a) return "";
		}
		return strip_tags($a[0]);
	}

	//获取资源站的播放数据
	public function get_all_resource($name = "")
	{
		$name = empty($name)?$_REQUEST['name']:$name;
		$resource = new resourceController();
		$zuidazy = $resource->zuidazy($name);
		$yongjiuzy = $resource->yongjiuzy($name);
		if ($zuidazy != '暂无数据!') {
			$array[] = $zuidazy;
		}
		if ($yongjiuzy != '暂无数据!') {
			$array[] = $yongjiuzy;
		}
		return $array;
	}

	//获取albumid
	private function get_albumid($content = "")
	{
		if (!$content) {
			return 0;
		}
		preg_match('/albumId.*?,/ism', $content, $a);
		if ($a[0]) {
			$a = trim($a[0], ",");
			$b = explode(':', $a);
			if ($b[1]) {
				preg_match('/\d{1,}/', $b[1], $c);
				return $c[0];
			} else {
				return 0;
			}
		} else {
			return 0;
		}

	}

	//获取到所有集数
	private function get_dd($albumId, $state)
	{
		if (!$albumId && !$state) {
			return "";
		}
		$arr_data = "";
		if ($state > 50) {
			$once = intval(ceil($state/50));
			for ($i=1; $i <= $once; $i++) { 
				if ($i == null) {
					break;
				}
				$url = 'http://cache.video.iqiyi.com/jp/avlist/'.$albumId.'/'.$i.'/50/?albumId='.$albumId;
				$content = json_decode(ltrim($this->ff_file_get_contents($url), "var tvInfoJs="));
				if (!$content->data) {
					return 1;
				} else if (!$content->data->vlist) {
					return 1;
				} else {
					foreach ($content->data->vlist as $key => $value) {
						$arr_data .= $value->pd."$".$value->vurl."#";
					}
				}
			}
			return rtrim($arr_data, "#");
		} else if ($state == 1) {
			return "";
		} else {
			$url = 'http://cache.video.iqiyi.com/jp/avlist/'.$albumId.'/1/50/?albumId='.$albumId;
			$content = json_decode(ltrim($this->ff_file_get_contents($url), "var tvInfoJs="))->data->vlist;
			foreach ($content as $key => $value) {
						$arr_data .= $value->pd."$".$value->vurl."#";
					}
			return rtrim($arr_data, "#");
		}
	}

	//获取所有数据
	private function get_all($tvid)
	{
		if (!$tvid) {
			return [
				'msg' => "没有传入tvid"
			];
		}
		$url = "http://mixer.video.iqiyi.com/jp/mixin/videos/".$tvid;
		$content = $this->ff_file_get_contents($url);
		$content = trim(ltrim($content, 'var tvInfoJs='));
		if (json_decode($content) != "") {
			$all = json_decode($content);
			// return $all;
			$all_data['name'] = $all->name; //名字
			$all_data['seo'] = $all->sourceName;//标题
			$all_data['type_name'] = $all->crumbList[2]->title;//分类名称
			$all_data['class'] = $all->categories[1]->name;//分类
			$all_data['lang'] = $all->categories[0]->name;//语言
			$all_data['area'] = $all->categories[0]->name;//地区
			$all_data['score'] = $all->score;//评分
			$all_data['year'] = date('Y', intval($all->issueTime)); //年份
			$all_data['score'] = $all->score;//评分
			$all_data['note'] = $all->focus;//备注
			$all_data['state'] = $all->latestOrder;//连载
			$all_data['hit'] = $all->playCount;//总点击量
			$all_data['vdown'] = $all->downCount;//踩数
			$all_data['pic'] = $all->imageUrl;//封面
			$all_data['last'] = date('Y-m-d H:i:s', intval($all->issueTime));//封面
			$all_data['des'] = $all->description;//简介
			$all_data['downurl'] = $all->url;//下载地址
			$all_data['downfrom'] = 'iqiyi';//下载组
			return $all_data;
		} else {
			return [
				'msg' => "无法返回数据"
			];
		}
	}

	//获取视频唯一id
	private function get_tvid($content = "")
	{
		if (!$content) {
			return 0;
		}
		preg_match('/tvId.*?,/ism', $content, $a);
		if ($a[0]) {
			$a = trim($a[0], ",");
			$b = explode(':', $a);
			if ($b[1]) {
				preg_match('/\d{1,}/', $b[1], $c);
				return $c[0];
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	//获取到封面
	private function get_pic($content = "")
	{
		if (!$content) {
			return 0;
		}
		preg_match('/<meta property=[\"|\']og:image.*?\/>/ism', $content, $a);
		if ($a[0]) {
			preg_match('/content=\"(.+?)\"/ism', $a[0], $b);
			if ($b[1]) {
				return $b[1];
			} else {
				return "";
			}
		} else {
			return "";
		}
	}

	//获取到视频的类型id
	private function get_typeid($content = "")
	{
		if (!$content) {
			return 0;
		}
		preg_match('/albumPurType.*?,/ism', $content, $a);
		if ($a[0]) {
			$a = trim($a[0], ',');
			$b = explode(':', $a);
			if ($b[1]) {
				return intval($b[1]);
			} else {
				return 0;
			}
		}
	}

	//获取评分
	private function get_score($tvid, $typeid)
	{
		if (!$tvid && $typeid) {
			return 0.0;
		}
		$url = 'http://score-video.iqiyi.com/beaver-api/get_sns_score?qipu_ids='.$tvid.'&appid=1&tvid='.$tvid;
		$content = $this->ff_file_get_contents($url);
		if ($content) {
			preg_match('/sns_score.*?,/ism', $content, $a);
			$b = explode(':', $a[0]);
			if ($b[1]) {
				preg_match('/[0-9.-]{1,}/ism', $b[1], $c);
				return $c[0];
			} else {
				return 0.0;
			}
		}
	}

	//更新时间
	private function get_last($content = "")
	{
		if(!$content) {
			return "";
		}
		preg_match('/<meta itemprop=[\'|\"]uploadDate.*?\/>/ism', $content, $a);
		if ($a[0] != "") {
			preg_match('/[0-9-]{1,}/ism', $a[0], $b);
			return $b;
		} else {
			return '';
		}
	}

	//更新年份
	private function get_year($last = "2018-05-10")
	{
		if(!$last) {
			return "";
		}
		$a = explode('-', $last);
		return $a[0];
	}


	//获取名字
	private function get_name($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<h1 class=[\'|\"]mod-play-tit.*?<\/h1>/ism', $content, $a);
		if ($a[0] != "") {
			preg_match('/<a.*?<\/a>/ism', $a[0], $b);
			if ($b[0]) {
				return strip_tags($b[0]);
			} else {
				return trim(strip_tags($a[0]));
			}
		} else {
			return "";
		}
	}

	//获取首字母
	private function get_letter($str = "")
	{
		if (!$str) {
			return 0;
		}
		$str=str_replace('・','',$str);
        $firstchar_ord=ord(strtoupper($str{0})); 
        if (($firstchar_ord>=65 and $firstchar_ord<=91)or($firstchar_ord>=48 and $firstchar_ord<=57)) return $str{0}; 
        $s=iconv("UTF-8","gbk", $str); 
        $asc=ord($s{0})*256+ord($s{1})-65536; 
        if($asc>=-20319 and $asc<=-20284)return "A";
        if($asc>=-20283 and $asc<=-19776)return "B";
        if($asc>=-19775 and $asc<=-19219)return "C";
        if($asc>=-19218 and $asc<=-18711)return "D";
        if($asc>=-18710 and $asc<=-18527)return "E";
        if($asc>=-18526 and $asc<=-18240)return "F";
        if($asc>=-18239 and $asc<=-17923)return "G";
        if($asc>=-17922 and $asc<=-17418)return "H";
        if($asc>=-17417 and $asc<=-16475)return "J";
        if($asc>=-16474 and $asc<=-16213)return "K";
        if($asc>=-16212 and $asc<=-15641)return "L";
        if($asc>=-15640 and $asc<=-15166)return "M";
        if($asc>=-15165 and $asc<=-14923)return "N";
        if($asc>=-14922 and $asc<=-14915)return "O";
        if($asc>=-14914 and $asc<=-14631)return "P";
        if($asc>=-14630 and $asc<=-14150)return "Q";
        if($asc>=-14149 and $asc<=-14091)return "R";
        if($asc>=-14090 and $asc<=-13319)return "S";
        if($asc>=-13318 and $asc<=-12839)return "T";
        if($asc>=-12838 and $asc<=-12557)return "W";
        if($asc>=-12556 and $asc<=-11848)return "X";
        if($asc>=-11847 and $asc<=-11056)return "Y";
        if($asc>=-11055 and $asc<=-10247)return "Z";
        return 0;//null  
	}

	//获取标题
	private function get_seo($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<title.*?<\/title>/ism', $content, $a);
		if ($a[0] != "") {
			return strip_tags($a[0]);
		} else {
			return "";
		}
	}

	//获取总集数
	private function get_continu($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/"videoCount".*?,/ism', $content, $a);
		if ($a[0]) {
			preg_match('/\d{1,}/', $a[0], $b);
			return $b[0];
		} else {
			return 0;
		}
	}

	//获取分类名称
	private function get_type_name($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<meta.*name=[\'|\"]irCategory(.*?) content=\"(.+?)\".*?\/>/ism', $content, $a);
		if ($a[2]) {
			return $a[2];
		} else {
			return "";
		}
	}

	//获取地区和分类
	private function get_area_class($content = "")
	{
		if (!$content) {
			return [
				'area' => "",
				'class' => ""
			];
		}
		preg_match('/<span class=[\'|\"]mod-tags_item.*?<\/span>/ism', $content, $a);
		if ($a[0] != "") {
			preg_match_all('/<a rseat=[\'|\"]bread3.*?<\/a>/ism', $a[0], $b);
			if (count($b[0]) == 3) {
				$c['area'] = strip_tags($b[0][0]);
				unset($b[0][0]);
				$str = "";
				foreach ($b[0] as $key => $value) {
					$str .= strip_tags($value).",";
				}
				$c['class'] = trim($str, ",");
				return $c;
			} else if(count($b[0]) > 1) {
				return [
					'area' => strip_tags($b[0][0]),
					'class' => strip_tags($b[0][0])
				];
			} else {
				return [
					'area' => strip_tags($b[0][0]),
					'class' => ""
				];
			}
		} else {
			return [
				'area' => "",
				'class' => ""
			];
		}
	}

	//获取备注
	private function get_note($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<div class=[\'|\"]playList-update-tip.*?<\/div>/ism', $content, $a);
		if ($a[0] != "") {
			return trim(strip_tags($a[0]));
		} else {
			return "";
		}
	}

	//获取导演
	private function get_director($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<p class=[\'|\"]progInfo_rtp.*?导演.*?<\/p>/ism', $content, $a);
		if ($a[0] != "") {
			preg_match('/<a itemprop=[\'|\"]director.*?<\/a>/', $a[0], $b);
			if ($b[0] != "") {
				return strip_tags($b[0]);
			} else {
				return "";
			}
		}
	}

	//获取演员
	private function get_actor($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<p class=[\'|\"]progInfo_rtp.*?主演.*?<\/p>/ism', $content, $a);
		if ($a[0] != "") {
			preg_match_all('/<a itemprop=[\'|\"]actor.*?<\/a>/ism', $a[0], $b);
			if (count($b[0]) == 0) {
				return "";
			} else {
				$str = "";
				foreach ($b[0] as $key => $value) {
					$str .= strip_tags($value).",";
				}
				return rtrim($str, ",");
			}
		}
	}

	//获取简介
	private function get_des($content = "")
	{
		if (!$content) {
			return "";
		}
		preg_match('/<p class=[\'|\"]progInfo_intr.*?<\/p>/ism', $content, $a);
		if ($a[0] != "") {
			preg_match('/<span class=[\'|\"]type-con.*?<\/span>/ism', $a[0], $b);
			if ($b[0] != "") {
				return trim(strip_tags($b[0]));
			} else {
				return "";
			}
		}
	}

	//采集内核
	function ff_file_get_contents($url, $post_data='', $timeout=5, $referer=''){
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt ($ch, CURLOPT_REFERER, $referer);
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
			//post
			if($post_data){
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			}
			//https
			$http = parse_url($url);
			// var_dump($http);die;
			if($http['scheme'] == 'https'){
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			}
			$content = curl_exec($ch);
			curl_close($ch);
			// var_dump($content);die;
			if($content){
				return $content;
			}
		}
		$ctx = stream_context_create(array('http'=>array('timeout'=>$timeout)));
		$content = @file_get_contents($url, 0, $ctx);
		if($content){
			return $content;
		}
		return false;
	}
}
