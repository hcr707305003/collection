<?php
use Home\Model\Mod;
$_SERVER['PHP_SELF'] = empty($_SERVER['PHP_SELF'])? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF'];

function iphone($get){
    $get['mode'] = 'iphone';
    $get['playext'] = 'iphone';
    $json = get_video($get);
    $playurl = $json['result']['files'];
    if(empty($playurl)){
        json_echo("解析成功，数据不存在。");
    }
    if($json['result']['play_type'] == 'iframe'){
        $play_type = 'iframe';
    } elseif($json['result']['play_type'] == 'ajax'){
        $play_type = 'ajax_'.$json['source'];
    } elseif($json['result']['play_type'] == 'url_list'){
        $play_type = 'url_list';
    } else {
        $play_type = 'h5';
    }
    if(empty($json['ctime'])){
        json_echo($json['result']['video_img'], $play_type, $playurl, 200);
    } else {
        json_echo($json['result']['video_img'], $play_type, $playurl, 200, $json['ctime']);
    }
}

function windows($get,$is_qh = false){
    if(!$is_qh){
        if($get['mode'] == 'iphone'){
            $get['playext'] = $get['mode'];
        } else {
            $get['mode'] = 'iphone';
        }
    }
    $json = get_video($get);
    $caid = md5(time().uuid());
    if(empty($json['result']['files']) && empty($json['result']['filelist'][0]['url'])){
        json_echo("解析成功，数据不存在。");
    } else {
        if($is_qh){
            // M3U8清晰度切换输出地址
            if($json['result']['play_type'] == 'm3u8') {
                header('Location: '.$json['result']['files']);die;
            }
            return $json;
        } else {
            if($json['result']['play_type'] == 'h5mp4'){
                json_echo('', 'h5', $json['result']['files']);
            } elseif ($json['result']['play_type'] == 'iframe'){
                json_echo("", 'iframe', $json['result']['files']);
            } elseif ($json['result']['play_type'] == 'ajax'){
                json_echo('', 'ajax_'.$json['source'], $json['result']['files']);
            } elseif ($json['result']['play_type'] == 'hls'){
                json_echo('', 'hls', $json['result']['files']);
            }
            S($caid, $json, 20);
            $host = parse_url(host_path());
            if(!empty($get['url'])){
                $get['url'] = authcode($get['url'],"JM");
            }elseif(!empty($get['vid'])){
                $get['vid'] = authcode($get['vid'],"JM");
            }
            json_echo("", 'xml', $host['path'].'?a=xml&'.merge_string($get).'&sign='.$caid, 200, null, $json);
        }
    }
}

function echo_xml($data){
    if(empty($data['result']['files']) && empty($data['result']['filelist'][0]['url'])){
        json_echo("解析成功，数据不存在。");
    }
    if($data['result']['play_type'] == 'm3u8' || $data['result']['play_type'] == 'm3u8_lv') {
        m3u8($data);
    } else {
        if(!empty($data['result']['files'])){
            $data['result']['filelist'][0]['url'] = $data['result']['files'];
            unset($data['result']['files']);
        }
        flv($data);
    }
}

function cache_m3u8($json){
    if(!strstr($json['result']['play_type'], 'm3u8')){
        // 不是M3U8类型的一律返回原始据
        unset($json['result']['cache']);
        return $json;
    }
    if($json['result']['cache'] == false){
        // 不允许缓存就输出M3U8地址
        unset($json['result']['cache']);
        return $json;
    }
    $url_ = parse_url($json['result']['files']);
    $sign = md5($url_['path']);
    $query= parse_string($url_['query']);
    $query['t2'] = ($query['t2'] <= 0)? 300 : $query['t2'];
    if($query['decache'] == 1) S($sign, null);
    if(!S($sign)){
        $str_m3u8 = Mod::curl($json['result']['files']);
        if(substr($str_m3u8,0,7) != '#EXTM3U') json_echo("缓存资源获取失败,请重试刷新.",null,null,404);
        S($sign, $str_m3u8, $query['t2']);
    }
    unset($query['sign']);
    $http = player_is_ssl() ? 'https://' : 'http://';
    $json['result']['files'] = $http.$_SERVER['HTTP_HOST']."{$_SERVER['PHP_SELF']}?a=m3u8&sign=".$sign."&".merge_string($query);
    unset($json['result']['cache']);
    $json['result']['play_type'] = strstr($json['result']['play_type'],"hls")? "hls" : $json['result']['play_type'];
    return $json;
}

function m3u8($data){
    $config = auto_config();
    $runtime = empty($data['ctime'])? 0 : $data['ctime'];
    header( 'Content-Type: text/xml;charset=utf-8 ');
    $m3u8_xml  = '<?xml version="1.0" encoding="UTF-8"?>';
    $m3u8_xml .= '<ckplayer>';
    $m3u8_xml .= '<Name>'.$data['platform'].' Ver'.$config['Ver'].'</Name>';
    $m3u8_xml .= '<qq>'.$data['contact'].'</qq>';
    $m3u8_xml .= '<flashvars>';
    $url  =  str_replace(array('&hd=1','&hd=2','&hd=3','&new=1'),'',current_url());
    if(!empty($data['result']['definitionList'])){
        $m3u8_xml .= '{f->'.C('TMPL_PARSE_STRING.__PUBLIC__').'/player/swf/m3u8.swf}{s->4}{a-><![CDATA['.$data['result']['files'].']]>}{defa-><![CDATA['.$url.'&new=1&hd=1]]>|<![CDATA['.$url.'&new=1&hd=2]]>|<![CDATA['.$url.'&new=1&hd=3]]>}';
    } else {
        $fj = ($data['result']['play_type'] == 'm3u8_lv')? "{lv->1}":"";
        $m3u8_xml .= '{f->'.C('TMPL_PARSE_STRING.__PUBLIC__').'/player/swf/m3u8.swf}'.$fj.'{s->4}{a-><![CDATA['.$data['result']['files'].']]>}';
    }
    $m3u8_xml .= '</flashvars>';
    $m3u8_xml .= '<video><file><![CDATA[]]></file></video>';
    if($runtime > 0){
        $m3u8_xml .= '<ctime>'.$runtime.'</ctime>';
    }
    $m3u8_xml .= '</ckplayer>';
    exit($m3u8_xml);
}

function flv($data){
    $config = auto_config();
    $runtime = empty($data['ctime'])? 0 : $data['ctime'];
    header("Content-Type: text/xml");
    $xml  = '<?xml version="1.0" encoding="utf-8"?>'.chr(13);
    $xml .= '<ckplayer>'.chr(13);
    $xml .= '<Name>'.$data['platform'].' Ver'.$config['Ver'].'</Name>';
    $xml .= '<qq>'.$data['contact'].'</qq>';
    $url  =  str_replace(array('&hd=1','&hd=2','&hd=3','&new=1'),'',current_url());
    $hds  = explode('|', $data['result']['definitionList']);
    foreach ($hds as $value) {
        $hd[] = substr($value,0,1);
    } 
    $xml .= '<flashvars>{f-><![CDATA['.$url.'&new=1&hd=[$pat]]]>}{a->'.$data['result']['definition'].'}{defa->'.implode('|',$hd).'}</flashvars>'.chr(13);
    $data = $data['result']['filelist'];
    foreach ($data as $v) {
        $xml .= '<video>'.chr(13);
        $xml .= '<file><![CDATA['.$v['url'].']]></file>'.chr(13);
        if(isset($v['size'])){
            $xml .= '<size><![CDATA['.(($v['size'] < 1)? 1 : $v['size']).']]></size>'.chr(13);
        } 
        if(isset($v['seconds'])){
            $xml .= '<seconds><![CDATA['.$v['seconds'].']]></seconds>'.chr(13);     
        }
        $xml .= '</video>'.chr(13);
    }
    if($runtime > 0){
        $xml .= '<ctime>'.$runtime.'</ctime>';
    }
    $xml .= '</ckplayer>'.chr(13);
    exit($xml);
}

function get_video($data){
    $config = auto_config();
    $data['userip'] = getuserip();
    $data['apikey'] = $config['apikey'];
    if(!strstr($data['referer'],'://')){
        if($data['player_api'] == false){
            json_echo("插件请求失败！",Null,Null,403);
        }
    }
    $data['referer']    = base64_encode(base64_encode(urldecode($data['referer'])));
    $data['player_referer'] = base64_encode(base64_encode(urldecode($_SERVER['HTTP_REFERER'])));
    $data['User-Agent'] = base64_encode(base64_encode($_SERVER['HTTP_USER_AGENT']));
    if($data['player_api'] == false){
       $type_arr = array('youku','qq','bilibili','iqiyi','tudou');
    }
    if(!player_is_ssl()){
        array_push($type_arr,'youku_ac');
    }
    if(in_array($data['type'], $type_arr)){
        $data['ext'] = 'ajax';
    }
    if($data['type'] == 'iqiyi') $data['cupid'] = 'qc_100001_100102';
    $json = Mod::obtain(array('type'=>'api','data'=>$data));
    if($json['code'] == 407){
        $data['url'] = "";
        $data['type'] = $json['result']['to'];
        $data['vid']  = $json['result']['vid'];
        if(in_array($data['type'], $type_arr)){
            if($data['type'] == 'iqiyi') $data['cupid'] = 'qc_100001_100102';
            $data['ext'] = 'ajax';
        }
        $json = Mod::obtain(array('type'=>'api','data'=>$data));
    }
    if($json['code'] == 405){
        $data['type'] = $json['source'].'_vip';
        if(in_array($data['type'], $type_arr)){
            if($data['type'] == 'iqiyi') $data['cupid'] = 'qc_100001_100102';
            $data['ext'] = 'ajax';
        }
        $json = Mod::obtain(array('type'=>'api','data'=>$data));
    }elseif($json['code'] == 406){
        $data['type'] = strzuo($json['source'],'_vip');
        if(in_array($data['type'], $type_arr)){
            if($data['type'] == 'iqiyi') $data['cupid'] = 'qc_100001_100102';
            $data['ext'] = 'ajax';
        }
        $json = Mod::obtain(array('type'=>'api','data'=>$data));
    }
    if($json['code'] != 200) {
        if($json['code'] == 999){
            $id = strzhong($json['message'],':[',']');
            if($id >= 0){
                $config['cookie'][$id]['state'] = false;
                F('config',$config);
            }
            $json['code'] = 403;
        }
        if(empty($json['message'])) {
            $json['message'] = "请求无响应，请刷新。";
        }
        if(!empty($config['error_msg'])) {
            $json['message'] = $config['error_msg'];
        }
        if($data['player_api']){
            header('content-type:application/json;charset=UTF-8');
            echo json_encode($json);die;
        } else {
            json_echo($json['message'],Null,Null,$json['code']);
        }
    }
    $json = cache_m3u8($json);
    return $json;
}

function strzhong($str, $leftStr, $rightStr){
    if (!empty($str)) {
        $left = strpos($str, $leftStr);
        if ($left === false) {
            return '';
        }
        $right = strpos($str, $rightStr, $left + strlen($leftStr));
        if ($left === false or $right === false) {
            return '';
        }
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }
}

function json_echo($msg, $ext="", $url="",$code=403, $ctime = 0, $json = array()) {
    header('content-type:application/json;charset=UTF-8');
    if(empty($ext) || empty($url)) {
        $data['code'] = $code;
    } else {
        $data['code'] = 200;
    }
    $data['ext'] = $ext;
    if($ext == 'h5' || strstr($ext,'ajax_') || $ext == 'hls'){
        if($ctime > 0){
            $data['ctime'] = $ctime;
        }
        $data['img'] = $msg;
        $msg = "";
    }
    if($data['code'] == $code) {
        $data['msg'] = $msg;
    } else {
        $data['msg'] = "ok";
    }
    $data['url'] = $url;
    if($json['result']['dvd_point']){
        $data['k'] = $json['result']['dvd_point']['k'];
        $data['n'] = $json['result']['dvd_point']['n'];
    }
    exit(json_encode($data));
}


function host_path(){
    global $config;
    if(empty($config['diy_port'])){
        @list($hosturl, $end) = explode('?', $_SERVER['HTTP_HOST'].getRequestUri());
    }else{
        @list($hosturl, $end) = explode('?', $_SERVER['HTTP_HOST'].':'.$config['diy_port'].getRequestUri());
    }
    return (player_is_ssl()?'https://':'http://').$hosturl;
}

function getRequestUri() {
  if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { 
     // check this first so IIS will catch 
     $requestUri = $_SERVER['HTTP_X_REWRITE_URL']; 
   } elseif (isset($_SERVER['REDIRECT_URL'])) { 
     // Check if using mod_rewrite 
     $requestUri = $_SERVER['REDIRECT_URL']; 
   } elseif (isset($_SERVER['REQUEST_URI'])) { 
     $requestUri = $_SERVER['REQUEST_URI']; 
   } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { 
     // IIS 5.0, PHP as CGI 
     $requestUri = $_SERVER['ORIG_PATH_INFO']; 
     if (!empty($_SERVER['QUERY_STRING'])) { 
       $requestUri .= '?' . $_SERVER['QUERY_STRING']; 
     } 
   } 
   return $requestUri; 
}


function p($array){
    echo "<pre>";
    print_r($array);die;
}

function uuid($prefix = '',$f = '') {
   $chars = md5(uniqid(mt_rand(), true));
   $uuid  = substr($chars,0,8) . $f;
   $uuid .= substr($chars,8,4) . $f;
   $uuid .= substr($chars,12,4) . $f;
   $uuid .= substr($chars,16,4) . $f;
   $uuid .= substr($chars,20,12);
   return $prefix . $uuid;
}

function auto_config() {
    $config = F('config');
    if(empty($config['Ver'])){
        $config = C('PLAYM3U8_CONF');
        F('config',$config);
    } else {
        $config_ = C('PLAYM3U8_CONF');
        if($config['Ver'] != $config_['Ver']){
            $config['Ver'] = $config_['Ver'];
            F('config',$config);
        }
    }
    return $config;
}

function auth_referer(){
    $config = auto_config();
    $host = isset($_SERVER['HTTP_HOST']) ? addslashes($_SERVER['HTTP_HOST']) : "";
    $refs = isset($_SERVER['HTTP_REFERER']) ? addslashes($_SERVER['HTTP_REFERER']) : "";
    $res_error = "";
    if($config['debug']) {
        if(empty($config['auth_domain'])) return; // 没有域名白名单就是开放模式
        $config['auth_domain'][count($config['auth_domain'])] = $host;
        if(!empty($refs)) {
            $refs = parse_url($refs);
            if(!in_array($refs['host'], $config['auth_domain'])){
                if(!empty($config['referer_msg'])){
                    $res_error = $config['referer_msg'];
                } else {
                    $res_error = '请求被拒绝，请添加['.$refs["host"].']域名为白名单。';
                }
            }
        }
    } else {
        if(empty($refs)) {
            if(!empty($config['referer_msg'])){
                $res_error = $config['referer_msg'];
            } else {
                $res_error = '请求被拒绝，浏览器调试请开启调试。';
            }
        } else {
            if(empty($config['auth_domain'])) return; // 没有域名白名单就是开放模式
            $config['auth_domain'][count($config['auth_domain'])] = $host;
            $refs = parse_url($refs);
            if(!in_array($refs['host'], $config['auth_domain']) ){
                if(!empty($config['referer_msg'])){
                    $res_error = $config['referer_msg'];
                } else {
                    $res_error = '请求被拒绝，请添加['.$refs["host"].']域名为白名单。';
                }
            }
        }
    }
    return $res_error;
}


function current_url() {
    static $url;
    $config = auto_config();
    if (empty($url)) {
        if(is_mobile()){
            $url = 'http://';
        }else{
            $url = player_is_ssl() ? 'https://' : 'http://';
        }
        if(empty($config['diy_port'])){
            $url .= $_SERVER['HTTP_HOST'];
        }else{
            $url .= $_SERVER['HTTP_HOST'].':'.$config['diy_port'];
        }
        $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/');
        $url = parse_url($url);
        $url['query'] = empty($url['query']) ? Array() : parse_string($url['query']);
        $url['query'] = merge_string($url['query']);
        $url = merge_url($url);
    }
    return $url;
}

function is_mobile(){
    return preg_match("/(iPhone|iPad|iPod|Linux|Android)/i", strtoupper($_SERVER['HTTP_USER_AGENT'])) ? true : false;
    static $a;
    if (isset($a)) {
    } elseif (empty($_SERVER['HTTP_USER_AGENT'])) {
        $a = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false) {
        $a = true;
    } else {
        $a = false;
    }
    return $a;
}

function stryou( $str , $you){
    $wz = strrpos($str,$you);
    if($wz === false){
        return null;
    }else{
        return substr($str, $wz + strlen($you));
    }
}
function host_url(){
    @list($hosturl, $end) = explode('?', $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]);
    return (is_ssl()?'https://':'http://').$hosturl;
}

function player_is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ('https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])){
        return true;
    }
    return false;
}

function parse_string($s) {
    if (is_array($s)) {
        return $s;
    }
    parse_str($s, $r);
    return $r;
}

function merge_string($a) {
    if (!is_array($a) && !is_object($a)) {
        return (string) $a;
    }
    return http_build_query(to_array($a));
}

function to_array($a) {
    $a = (array) $a;
    foreach ($a as &$v) {
        if (is_array($v) || is_object($v)) {
            $v = to_array($v);
        }
    }
    return $a;
}

function merge_url($parse = array()) {
    $url = '';
    if (isset($parse['scheme'])) {
        $url .= $parse['scheme'] . '://';
    }
    if (isset($parse['user'])) {
        $url .= $parse['user'];
    }
    if (isset($parse['pass'])) {
        $url .= ':' . $parse['pass'];
    }
    if (isset($parse['user']) || isset($parse['pass'])) {
        $url .= '@';
    }
    if (isset($parse['host'])) {
        $url .= $parse['host'];
    }
    if (isset($parse['port'])) {
        $url .= ':'. $parse['port'];
    }
    if (isset($parse['path'])) {
        $url .= $parse['path'];
    } else {
        $url .= '/';
    }
    if (isset($parse['query']) && $parse['query'] !== '') {
        $url .= '?'. $parse['query'];
    }

    if (isset($parse['fragment'])) {
        $url .= '#'. $parse['fragment'];
    }
    return $url;
}

function Curls($url,$cookie="",$data="",$ref=""){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $host = GetIP_();
    $header = array(
        "X-FORWARDED-FOR: ".$host,
        "CLIENT-IP: ".$host
    );
    if($ref != '')
        $header[] = 'Referer: '.$ref;
    if($cookie != '')
        $header[] = 'Cookie: '.$cookie;
    $header[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.87 Safari/537.36 QQBrowser/9.2.5584.400';
    curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    if($data != ''){
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($ch,CURLOPT_TIMEOUT,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

function play_verify(){
    $svp  = "gw3xTd45_pWtOwyhblItcO4qyiztT8ldX5NQwxpQsHs";
    $data = S($svp);
    if($data == false){
        $html = Curls('https://pv.sohu.com/suv/?t='.time().'501084_400_700?r?=https://tv.sohu.com/20171122/n600265964.shtml');
        $uid  = strzhong($html, 'SUV=', ';');
        $cookie = "SUV=".$uid."; SOHUSVP=".$svp;
        for ($i=0; $i < 8; $i++) { 
            Curls("https://z.m.tv.sohu.com/h5_cc.gif?t=".time()."000&uid=".$uid."&position=play_verify",$cookie);
        }
        $data = array($uid,$svp);
        S($svp , $data, 2);
    }
    return $data;
}

function GetIP_() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP") , "unknown")) $ip = getenv("HTTP_CLIENT_IP");
    elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR") , "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR");
    elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR") , "unknown")) $ip = getenv("REMOTE_ADDR");
    elseif (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")) $ip = $_SERVER["REMOTE_ADDR"];
    else $ip = "unknown";
    return ($ip);
}

function getuserip(){
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $userip = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
        return $userip[0];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function get_type($url){
    if(strstr($url,'hunantv.com')){
        $type = 'mgtv';
    }elseif(strstr($url,'56.com')){
        $type = 'w56';
    }if(strstr($url,'letv.com')){
        $type = 'le';
    }elseif(strstr($url,'1905.com')){
        $type = 'm1905';
    }elseif(strstr($url,'17173.com')){
        $type = 'v17173';
    }elseif(strstr($url,'cctv.com') || strstr($url,'cntv.cn')){
        $type = 'cntv';
    }elseif(strstr($url,'.m3u8') || strstr($url,'.mp4')){
        $type = 'playm3u8';
    }elseif(strstr($url,'m.tv.sohu.com')){
        $type = 'sohu';
    }else{
        $type = strzuo(top_domain($url),'.');
        if(empty($type)){
            error_msg("无法识别资源类型。");
        }
    }
    return $type;
}


function utf8encode($str){
    $str = iconv("gb2312", "utf-8//IGNORE", $str);
    return urlencode($str);
}

function strzuo( $str , $zuo ){
    $wz = strpos( $str , $zuo);
    if(empty($wz)){
        return null;
    }
    if ( !$text = substr( $str , 0 , $wz )){
        return null;
    }else{
        return $text;
    }
}

function is_de($str){
    if(substr($str,0,8) == 'PlayM3u8'){
        return true;
    } else {
        return false;
    }   
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 3600) {
    $config = auto_config();
    if($operation == 'DECODE'){
        if(!is_de($string)){
            return '';
        } else {
            $string = substr($string,8,strlen($string));
        }
    }
    $expiry = empty($config['encrypt_time'])? $expiry : $config['encrypt_time'];
    $string = ($operation == 'DECODE')? base64_decode(trim($string)) : trim($string);
    $ckey_length = 4;
    $key = md5($key ? $key : date("Y-m-d"));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        $keystr = $keyc.str_replace('=', '', base64_encode($result));
        $keystr = ($operation != 'DECODE')? base64_encode($keystr) : $keystr;
        return 'PlayM3u8'.str_replace('=','',$keystr);
    }
}

function error_msg($msg){
    Exit('<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><meta name="description"content="PlayM3u8"><meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"name="viewport"/><meta http-equiv="Content-Type"content="text/html; charset=utf-8"/><link rel="shortcut icon"href="'.C('TMPL_PARSE_STRING.__PUBLIC__').'/images/playm3u8.png"><title>PlayM3u8 错误提示</title><style type="text/css">body,html,div{background-color:#000;padding:0;margin:0;width:100%;height:100%;color:#aaa}</style></head><body style="overflow-y:hidden;"><div id="loading"style="font-weight:bold;padding-top:120px;"align="center">'.$msg.'<br><br><img border="0"<img src="'.C('TMPL_PARSE_STRING.__PUBLIC__').'/player/logo/load.gif"/></div></body></html>');
}


function top_domain($domain) {
    if (substr($domain, 0, 7) == 'http://') {
        $domain = substr($domain, 7);
    } elseif (substr($domain, 0, 8) == 'https://') {
        $domain = substr($domain, 8);
    }
    if (strpos($domain, '/') !== false) {
        $domain = substr($domain, 0, strpos($domain, '/'));
    }
    $domain = strtolower($domain);
    $iana_root = array('ac', 'ad', 'ae', 'aero', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'arpa', 'as', 'asia', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bl', 'bm', 'bn', 'bo', 'bq', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cat', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'com', 'coop', 'cr', 'cu', 'cv', 'cw', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'edu', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'info', 'int', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jobs', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mf', 'mg', 'mh', 'mil', 'mk', 'ml', 'mm', 'mn', 'mo', 'mobi', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'museum', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'name', 'nc', 'ne', 'net', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'org', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'pro', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'ss', 'st', 'su', 'sv', 'sx', 'sy', 'sz', 'tc', 'td', 'tel', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'travel', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'xxx', 'ye', 'yt', 'za', 'zm', 'zw','club');
    $sub_domain = explode('.', $domain);
    $top_domain = '';
    $top_domain_count = 0;
    for ($i = count($sub_domain) - 1;$i >= 0;$i--) {
        if ($i == 0) {
            // just in case of something like NAME.COM
            break;
        }
        if (in_array($sub_domain[$i], $iana_root)) {
            $top_domain_count++;
            $top_domain = '.' . $sub_domain[$i] . $top_domain;
            if ($top_domain_count >= 2) {
                break;
            }
        }
    }
    $top_domain = $sub_domain[count($sub_domain) - $top_domain_count - 1] . $top_domain;
    return $top_domain;
}


function isEmpty($directory)
{
    $handle = opendir($directory);
    while (($file = readdir($handle)) !== false)
    {
        if ($file != "." && $file != "..")
        {
            closedir($handle);
            return false;
        }
    }
    closedir($handle);
    return true;
}


function delDir($directory,$subdir=true)
{
    if (is_dir($directory) == false)
    {
        exit("The Directory Is Not Exist!");
    }
    $handle = opendir($directory);
    while (($file = readdir($handle)) !== false)
    {
        if ($file != "." && $file != "..")
        {
        is_dir("$directory/$file")?
            delDir("$directory/$file"):
            unlink("$directory/$file");
        }
    }
    if (readdir($handle) == false)
    {
        closedir($handle);
        rmdir($directory);
    }
}

function down_file($filename){
    if(!is_file($filename)) return false; 
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer"); 
    header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
    header("Content-Type: application/zip"); //zip格式的   
    header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
    header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小   
    @readfile($filename); die;
}