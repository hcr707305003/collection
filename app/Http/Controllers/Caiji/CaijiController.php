<?php
	
namespace App\Http\Controllers\Caiji;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class CaijiController extends Controller
{
	public function __construct(){

		set_time_limit(0);
		error_reporting(0);
		ini_set('memory_limit', '-1'); //内存无限
		\DB::connection()->enableQueryLog(); // 开启查询日志 
		$this->xmlurl = [
			'http://8laoye.com/inc/api.php',
			'http://www.ziyuanpian.com/inc/api.php',
			'http://www.kukuzy.com/inc/api.php',
			'http://zy.ckmov.com/inc/api.php',
			'http://api.myzyzy.com/api/max.asp',
			'http://zy.ataoju.com/inc/api.php',
			'http://www.jijizy.com/inc/api.asp',
			'http://www.33uudy.com/inc/api.php',
			'http://www.haozy.cc/inc/api.php'
		];
	}
	public function __destruct(){

	}

	//采集入口
	/* 
	 * action 用get请求传参数
	 * data xmlurl,xmltype (注意xmlurl必须要用base64_encode(xmlurl)的形式发送过来)
	 *
	 */
	public function apis(){
		$admin = array();
		// $admin['cjid']   = $_REQUEST['cjid'];//采集项目ID
		$admin['action'] = 'all';
		$admin['xmlurl'] = base64_encode('http://cj.tv6.com/mox/inc/qiyi.php?ac=videolist&h=24&rid=&pg=1');//采集网址
		$admin['xmltype'] = NULL;//资源站类型 json|xml
		$admin['page']   = !empty($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$vod = $this->vod($admin);
		var_dump($vod);die;
		//格式化部份数据字段
		if ($vod['status'] != 200) {
			return $vod['infos'];
		}
			
		//获取总页数并获取到分页数据
		$maxpage = intval($vod['infos']['page']['pagecount']);
		for ($a=0; $a < $maxpage; $a++) { 
			$admin['page'] = $a;
			$vod = $this->vod($admin);
			if ($alldata = $vod['infos']['data']) {
				$this->continu_judge($alldata);
			}
		sleep(5);
		}
	}

	//爱奇艺采集视频
	public function iqiyi($url="http://cj.tv6.com/mox/inc/qiyi.php")
	{	
		$path = $url."?ac=videolist&rid=&h=24&pg=";//爱奇艺地址
		$page = simplexml_load_string($this->ff_file_get_contents($path))->list->attributes()->pagecount;
		for ($i = 0; $i < intval($page); $i++) {
			$admin = array();
			$admin['action'] = 'all';
			$admin['xmlurl'] = base64_encode($path.$i);
			$admin['xmltype'] = NULL;
			$admin['page'] = 1;
			$vod = $this->vod($admin);
			//格式化部份数据字段
			if ($vod['status'] != 200) {
				return $vod['infos'];
			}
			
			//获取总页数并获取到分页数据
			$maxpage = intval($vod['infos']['page']['pagecount']);
			for ($a=0; $a < $maxpage; $a++) { 
				$admin['page'] = $a;
				$vod = $this->vod($admin);
				if ($alldata = $vod['infos']['data']) {
					$this->continu_judge($alldata);
				}
			}
			sleep(3);
		}
	}

	//开启数据库调试模式查询最后插入的语句
	public function getLastSql($sql)
	{
		$queries = \DB::getQueryLog(); // 获取查询日志  
		return $queries[0]['query']; // 即可查看执行的sql 
	}

	//自行查询视频接口
	public function auto_apis($maxpage = 0){
		// var_dump(Cache::get('page'));die;
		for ($i = 0; $i < count($this->xmlurl); $i++) {
			$admin = array();
			$admin['action'] = 'all';
			$admin['xmlurl'] = base64_encode($this->xmlurl[$i]);
			$admin['xmltype'] = NULL;
			$admin['page'] = 1;
			$vod = $this->vod($admin);

			//格式化部份数据字段
			if ($vod['status'] != 200) {
				return $vod['infos'];
			}
			//获取总页数并获取到分页数据
			$maxpage = empty($maxpage)?intval($vod['infos']['page']['pagecount']):$maxpage;
			for ($a=0; $a < $maxpage; $a++) { 
				$admin['page'] = $a;
				$vod = $this->vod($admin);
				if ($alldata = $vod['infos']['data']) {
					$this->continu_judge($alldata);
				}
			}
			sleep(3);
		}
	}

	//将数据插入到数据库
	protected function insert_into($alldata, $type=""){	
		if ($type == "film") {//如果是电影的话，要审核
			//判断数据库是否存在这条数据
			//状态为5
			$id = $this->exists_video($alldata['name']);
			if ($id != "404") {
				foreach ($id as $key => $value) {
					/*if (mb_strlen($alldata['dd']) >= 8388608) {
						$this->updateLink($value->id, $alldata['vod_url']);
						file_put_contents('text/longtext.txt', $arr);
						unset($alldata['dd']);
						$alldata['dd'] = '';
					}*/
					DB::table('vods')->where('id', "=", $value->id)
        			->update(['dd' => $alldata['dd']]);
				}
			} else {
				/*if (mb_strlen($alldata['dd']) >= 8388608) {
					$alldd = $alldata['dd'];
					unset($alldata['dd']);
					$alldata['dd'] = '';
					$ids = DB::table('vods')->insertGetId($alldata);
					$this->updateLink($ids, $alldd);
				} else {*/
					DB::table('vods')->insertGetId($alldata);
				// }
			}
		} else {//如果是电视剧的话，不需要审核
			//判断数据库是否存在这条数据
			//状态为0
			$id = $this->exists_video($alldata['name']);
			if ($id != "404") {
				foreach ($id as $key => $value) {
					/*if (mb_strlen($alldata['vod_url']) >= 8388608) {
						$this->updateLink($value->id, $alldata['vod_url']);
						file_put_contents('text/longtext.txt', $arr);
						unset($alldata['dd']);
						$alldata['dd'] = '';
					}*/
					DB::table('vods')->where('id', "=", $value->id)
        			->update(['dd' => $alldata['dd']]);
        			$update_into_id = $value->id;
        			$this->ff_file_get_contents($url = 'http://haoniux.com/code/api/test', $timeout = 3, $referer = "", $post_data = ['id' => intval($update_into_id)]);
				}
			} else {
				/*if (mb_strlen($alldata['dd']) >= 1000) {
				// var_dump(mb_strlen($alldata['dd']));die;
					$alldd = $alldata['dd'];
					unset($alldata['dd']);
					$alldata['dd'] = '';
					$insert_into_id = DB::table('vods')->insertGetId($alldata);
					$this->updateLink($insert_into_id, $alldd);
				} else {
				}*/
					$insert_into_id = DB::table('vods')->insertGetId($alldata);
				$this->ff_file_get_contents($url = 'http://haoniux.com/code/api/test', $timeout = 3, $referer = "", $post_data = ['id' => intval($insert_into_id)]);
			}
		}
	}

	//数据存在需要更新链接里面的视频源
    public function updateLink($id, $dd)
    {
        if (!file_exists('text')) {
			mkdir('text',0777,true); 
			file_put_contents('text/longtext.txt','');
		} else {
			if (!is_file('text/longtext.txt')) {
				file_put_contents('text/longtext.txt', '');	
				$arr .= $id."##".$dd.'$$$'.file_get_contents('text/longtext.txt');
			} else {
				$data = file_get_contents('text/longtext.txt');
				if ($data != "") {
					$aaa = explode("$$$", $data);
					// var_dump($aa);die;
					$geturllink = array();
					foreach ($aaa as $key => $values) {
			            if (!$values) {
			                unset($values);
			            } else {
			                $aa = explode('##', $values);
			                var_dump($aa[0]);
			                if ($aa[0] == $id) {
			                	$geturllink[$aa[0]] = $dd;
			                } else {
			                	$geturllink[$aa[0]] = $aa[1];
			                }
			            }
			        }
			        foreach ($geturllink as $k => $v) {
			        	$arr .= $k."##".$v."$$$";
			        }
				} else {
					$arr .= $id."##".$dd.'$$$'.file_get_contents('text/longtext.txt');
				}					
			}
		} 
    }

	//判断数据库的信息是否重复
	protected function exists_video($name)
	{	
		$all_id = DB::table('vods')->where('name', 'like', '%'.$name.'%')->get(['id']);
		if (count($all_id) == 0){
			return '404';
		} else {
			return $all_id;
		}
	}

	//判断视频是否是连载或则是电影
	private function continu_judge($alldata){
		foreach ($alldata as $key => $value) {
			if ($value['list_name'] == "宅男福利" || $value['list_name'] == "福利" || $value['list_name'] == "视讯美女" || $value['list_name'] == "腿模写真") {
				unset($value);continue;
			}
			$http_url = explode('$',explode('$$$' ,$value['vod_url'])[0]);
			if (!@stristr($http_url[1], 'http')) {
				unset($value);continue;
			}
			$video_data = array();
			$video_data['seo'] = $value['vod_name'];
			$video_data['name'] = $value['vod_name']; 
			$video_data['continu'] = $value['vod_continu']; 
			$video_data['letter'] = $this->getFirstCharter($value['vod_name']);
			$video_data['type_name'] = $value['list_name']; 
			$video_data['pic'] = $value['vod_pic']; 
			$video_data['lang'] = $value['vod_language']; 
			$video_data['area'] = $value['vod_area']; 
			// $video_data['score'] = ; 
			$video_data['year'] = $value['vod_year']; 
			$video_data['last'] = strtotime($value['vod_addtime']); 
			$video_data['des'] = trim($value['vod_content'], ' ');
			$video_data['dd'] = trim($value['vod_url'], ' '); 
			$video_data['playfrom'] = $value['vod_play']; 
			$video_data['downurl'] = trim($value['vod_reurl'], ' '); 
			$video_data['actor'] = $value['vod_actor'];  
			$video_data['director'] = $value['vod_director'];  
			$video_data['state'] = $value['vod_continu'];  
			$video_data['note'] = $value['vod_title'];  
			if (intval($value['vod_continu']) == 0) {
				//判断数据库是否存在该，如果不存在则插入，存在则更新视频源(这里的视频审核的状态不一样)
	        	$this->insert_into($video_data,'film');
			} else {//走这里的都是连载直接插入
				$video_data['status'] = 5;
				$video_data['vod_status'] = 5;
	        	$this->insert_into($video_data);
			}
	    }
	}

	public function getFirstCharter($str)//取首拼音
    {
        if (empty($str)) {
            return '';
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

	public function vod($admin){
		$params['h'] = NULL;
		$params['cid'] = NULL;
		$params['inputer'] = NULL;
		$params['play'] = NULL;
		$params['wd'] = NULL;
		$params['limit'] = NULL;
		$params['g'] = 'plus';
		$params['m'] = 'api';
		$params['a'] = 'json';
		$params['p'] = $admin['page'];
		ksort($params);

		if($admin['xmltype'] == 'xml'){
			return $this->vod_xml($admin, $params);
		}elseif($admin['xmltype'] == 'json'){
			return $this->vod_json($admin, $params);
		}else{
			$data = $this->vod_json($admin, $params);
			// var_dump($data);
			if($data['status'] == 200){
				return $data;
			}else{
				return $this->vod_xml($admin, $params);
			}
		}
	}

	private function vod_xml($admin, $params){
		// echo 11;die;
		return $this->vod_xml_caiji($admin, $params);
	}

	private function vod_json($admin, $params){
		for ($i = 0; $i < count($this->xmlurl); $i++) {
		$url = base64_decode($admin['xmlurl']).'?'.http_build_query($params);
		// var_dump($url);die;
		$html = $this->ff_file_get_contents($url);
		// var_dump($html);
		// die;
		//是否采集到数据
		if(!$html){
			return array('status'=>601, 'infos'=>'连接API资源库失败，通常为服务器网络不稳定或禁用了采集。');
		}
		//数据包验证
		$json = json_decode($html, true);
		// var_dump($json);die;
		if( is_null($json) ){
			return array('status'=>602, 'type'=>'json', 'infos'=>'JSON格式不正确，不支持采集。');
		}
		//资源库返回的状态501 502 503 3.3版本前没有status字段
		if($json['status'] && $json['status'] != 200){
			return array('status'=>$json['status'], 'type'=>'json', 'infos'=>$json['data']);
		}
		//不是feifeicms的格式
		if(!$json['list']){
			return array('status'=>602, 'type'=>'json', 'infos'=>'不是FeiFeiCms系统的接口，不支持采集。');
		}
		//返回正确的数据集合
		return array('status'=>200, 'type'=>'json', 'infos'=>$json);
		}
	}

	// 采集内核
	function ff_file_get_contents($url, $timeout=5, $referer='', $post_data=''){
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

	//xml资源库采集
	private function vod_xml_caiji($admin, $params){
		$url = array();
		if($admin['action']=='show' && $params['wd']){ 
			$url['ac'] = 'list'; 
		}else{
			$url['ac'] = 'videolist';
		}
		$url['wd'] = $params['wd'];
		$url['t'] = $params['cid'];
		$url['h'] = $params['h'];
		$url['rid'] = $params['play'];
		$url['ids'] = isset($params['vodids'])?$params['vodids']:NULL;
		$url['pg'] = $admin['page'];
		$url_detail = base64_decode($admin['xmlurl']).'?'.http_build_query($url);
		$url_list   = base64_decode($admin['xmlurl']).'?ac=list&t=9999';
		$xml_detail = $this->ff_file_get_contents($url_detail);
		if(!$xml_detail){
			return array('status'=>601, 'infos'=>'连接API资源库失败，通常为服务器网络不稳定或禁用了采集。');
		}
		@$xml = simplexml_load_string($xml_detail);
		// $object = json_decode($xml->list, true);
		// var_dump($object);die;
		if( is_null($xml) ){
			return array('status'=>602, 'type'=>'xml', 'infos'=>'XML格式不正确，不支持采集。');
		}
		$key = 0;
		$array_vod = array();
		foreach(@$xml->list->video as $video){
			$array_vod[$key]['vod_id'] = (string)$video->id;
			$array_vod[$key]['vod_cid'] = (string)$video->tid;
			$array_vod[$key]['vod_name'] = (string)$video->name;
			$array_vod[$key]['vod_title'] = (string)$video->note;
			$array_vod[$key]['list_name'] = (string)$video->type;
			$array_vod[$key]['vod_pic'] = (string)$video->pic;
			$array_vod[$key]['vod_language'] = (string)$video->lang;
			$array_vod[$key]['vod_area'] = (string)$video->area;
			$array_vod[$key]['vod_year'] = (string)$video->year;
			$array_vod[$key]['vod_continu'] = (string)$video->state;
			$array_vod[$key]['vod_actor'] = (string)$video->actor;
			$array_vod[$key]['vod_director'] = (string)$video->director;
			$array_vod[$key]['vod_content'] = (string)$video->des;
			$array_vod[$key]['vod_reurl'] = base64_decode($admin['xmlurl']).'?id='.(string)$video->id;
			$array_vod[$key]['vod_status'] = 1;
			$array_vod[$key]['vod_type'] = str_replace('片','',$array_vod[$key]['list_name']);
			$array_vod[$key]['vod_addtime'] = (string)$video->last;
			$array_vod[$key]['vod_total'] = 0;
			$array_vod[$key]['vod_isend'] = 1;
			if($array_vod[$key]['vod_continu']){
				$array_vod[$key]['vod_isend'] = 0;
			}
			//格式化地址与播放器
			$array_play = array();
			$array_url = array();
			//videolist|list播放列表不同
			if($count=count($video->dl->dd)){
				for($i=0; $i<$count; $i++){
					$array_play[$i] = str_replace('qiyi','iqiyi',(string)$video->dl->dd[$i]['flag']);
					$array_url[$i] = $this->vod_xml_replace((string)$video->dl->dd[$i]);
				}
			}else{
				$array_play[]=(string)$video->dt;
			}
			$array_vod[$key]['vod_play'] = implode('$$$', $array_play);
			$array_vod[$key]['vod_url'] = implode('$$$', $array_url);
			$key++;
		}
		//分页信息
		preg_match('<list page="([0-9]+)" pagecount="([0-9]+)" pagesize="([0-9]+)" recordcount="([0-9]+)">', $xml_detail, $page_array);
		$array_page = array('pageindex'=>$page_array[1], 'pagecount'=>$page_array[2], 'pagesize'=>$page_array[3], 'recordcount'=>$page_array[4]);
		//栏目分类
		$array_list = array();
		// var_dump($admin);die;
		if($admin['action'] == 'show'){
			$xml = simplexml_load_string(ff_file_get_contents($url_list));
			$key = 0;
			foreach($xml->class->ty as $list){
				$array_list[$key]['list_id'] = (int)$xml->class->ty[$key]['id'];
				$array_list[$key]['list_name'] = (string)$list;
				$key++;
			}
		}
		return array('status'=>200,'type'=>'xml', 'infos'=>array('page'=>$array_page,'list'=>$array_list,'data'=>$array_vod));
	}

	//xml资源库播放地址格式化
	private function vod_xml_replace($playurl){
		$array_url = array();
		$arr_ji = explode('#',str_replace('||','//',$playurl));
		foreach($arr_ji as $key=>$value){
			$urlji = explode('$',$value);
			if( count($urlji) > 1 ){
				$array_url[$key] = $urlji[0].'$'.trim($urlji[1]);
			}else{
				$array_url[$key] = trim($urlji[0]);
			}
		}
		// var_dump(implode(chr(13),$array_url));die;
		return implode(chr(13),$array_url);	
	}
}