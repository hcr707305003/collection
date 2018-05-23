<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\Mod;
class IndexController extends Controller {


    public function index(){
        $get = I('');
        if(!empty($get['u']) && !empty($get['p']) && !empty($get['y']) && !empty($get['z'])){
            exit($this->feifeicms($get));
        }
        $config = auto_config();
        $auth  = auth_referer();
        if(!empty($auth)){
            if(empty($config['referer_url'])){
                error_msg($auth);
            } else {
                redirect($config['referer_url']);
            }
        }
        if(empty($config['apikey'])) {
            error_msg('请 <a target="_blank" href="index.php?a=login">登陆后台</a> 设置apikey！');
        } else {
            if(strlen($config['apikey']) != 16) {
                error_msg('授权apikey格式错误！');
            }
        }
        if(IS_AJAX && IS_POST && strstr($_SERVER['HTTP_ACCEPT'],'text/javascript')){
            if($get['refres'] > 1){
                usleep(500000);
            }
            if($config['js_encryption']){
                $strstr = base64_decode(urldecode($get['data']));
                $mcrypt = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,md5($config['key1']), $strstr, MCRYPT_MODE_CBC,$config['key2']);
            } else {
                $mcrypt = urldecode($get['data']);
            }
            $data = parse_string(authcode($mcrypt));
            unset($get['data']);
            if(empty($get['cip'])) unset($get['cip']);
            if(!$data || !strstr($data['referer'],"://")){
                json_echo('请求失败！');
            }
            $get = array_merge($data, $get);
            if(isset($get['refres'])) unset($get['refres']);
            if(isset($get['play_title'])) unset($get['play_title']);
            if($config['cookie']){
                $i = 0;
                foreach ($config['cookie'] as $key => $cookie) {
                    if($cookie['state'] == true){
                        if(strstr($get['type'],$cookie['type']) !== false){
                            $cook[$i]['id']  = $key;
                            $cook[$i]['user'] = $cookie['user'];
                            $cook[$i]['api'] = $cookie['api'];
                            $cook[$i]['cookie'] = $cookie['cookie'];$i++;
                        }
                    }
                }
                if($cook) $get['cookie'] = json_encode($cook);
            }
            if(is_mobile()){
                iphone($get);
            } else {
                windows($get);
            }
        }
        if(empty($get['hd'])){
            $get['hd'] = $config['play_hd'];
        }
        if($config['js_encryption']){
            if(!function_exists('mcrypt_decrypt') && !function_exists('mcrypt_cbc')) {
                error_msg('PHP环境缺少mcrypt扩展，请安装。');
            }   
        }
        if(empty($get['vid']) && empty($get['url'])){
            error_msg('哈喽！已准备就绪。');
        }
        C('getde',false);
        if(!empty($get['vid'])){
            if(is_de($get['vid'])){
                C('getde',true);
                $get['vid'] = authcode($get['vid'],'DECODE',$config['data_key']);
            } else {
                /*
                $enstr = acode($get['vid'],'DECODE',$config['data_key']);
                if(!empty($enstr)){
                    $get['vid'] = $enstr;
                }
                */
            }
        } elseif(!empty($get['url'])){
            if(is_de($get['url'])){
                C('getde',true);
                $get['url'] = authcode($get['url'],'DECODE',$config['data_key']);
            } else {
                /*
                $enstr = acode($get['url'],'DECODE',$config['data_key']);
                if(!empty($enstr)){
                    $get['url'] = $enstr;
                }
                */
            }
        }
        if(!empty($get['vid'])){
            if(substr($get['vid'],0,7) == 'http://' || substr($get['vid'],0,8) == 'https://'){
                $get['url'] = $get['vid'];
                unset($get['vid']);
            }
        } elseif(!empty($get['url'])){
            if(substr($get['url'],0,7) != 'http://' && substr($get['url'],0,8) != 'https://'){
                $get['vid'] = $get['url'];
                unset($get['url']);
            }
        }
        if(substr($get['url'],0,1) == 'C' || substr($get['vid'],0,1) == 'C'){
            $get['type'] = 'youku_ac';
        } elseif(substr($get['url'],0,2) == '58' || substr($get['vid'],0,2) == '58'){
            // $get['type'] = 'youku_ac';
        } elseif($get['type'] == 'ykyun'){
            $get['type'] = 'youku_ac';
        }
        if(!empty($get['vid'])){
            if(empty($get['type'])){
                error_msg('当前vid模式请求无法识别资源类型');
            }
        }
        if(!empty($get['play_title'])){
            $config['play_title'] = $get['play_title'];
        }
        if(!empty($get['url'])){
            if(empty($get['type'])){
                $get['type'] = get_type($get['url']); 
            }
        }
        $encrypt_type = explode('|',$config['encrypt_type']);
        if(in_array($get['type'], $encrypt_type)){
            if(C('getde') == false){
                error_msg('亲，请加密数据。');
            }
        } else {
            if(C('getde') == true){
                error_msg('亲，请使用明文数据。');
            }
        }
        if(!empty($get['width']) && !empty($get['height'])){
            $config['width_height'][0] = $get['width'];
            $config['width_height'][1] = $get['height'];
        }

        $get['referer'] = isset($_SERVER['HTTP_REFERER']) ? addslashes($_SERVER['HTTP_REFERER']) : current_url();
        if(empty($config['qrcode_url'])) $config['qrcode_url'] = $get['referer'];
        if(in_array($get['type'],array('tianyi','pptvyun','mgtv','mgtv_vip','iqiyi','iqiyi_vip','youku_ac','acfun'))){
            $config['flashvars_h'] = 0;
        }
        if(in_array($get['type'],array('sohu','mysohu'))){
            $config['flashvars_h'] = 1;
        }
        $config['get']['str']  = authcode(merge_string($get),'JM');
        $config['get']['type'] = $get['type'];
        $hosturl = substr(host_url(), 0, strlen(host_url())-strlen(stryou(host_url(), '/')));
        $config['public_path'] = $hosturl.C('TMPL_PARSE_STRING.__PUBLIC__');
        unset($config['apikey'],$config['auth_domain'],$config['key1'],$config['key2'],$config['diy_port']);
        unset($config['back_host'],$config['data_key'],$config['encrypt_type'],$config['encrypt_time'],$config['cookie']);
        $this->assign('config',$config)->display();
    }

    public function xml(){
        $auth = auth_referer();
        if(!empty($auth)){
            header('HTTP/1.1 403 Internal Server Error');die;
        }
        $get   = I('');
        if($get['new'] == 1){
            unset($get['new']);
            unset($get['sign']);
            if(!empty($get['url'])){
                $get['url'] = authcode($get['url']);
            }elseif(!empty($get['vid'])){
                $get['vid'] = authcode($get['vid']);
            }
            $cache = windows($get, true);
        } else {
            $cache = S($get['sign']);
        }
        if($cache){
            S($get['sign'],Null);
            echo_xml($cache);
        } else {
            header('HTTP/1.1 404 Internal Server Error');die;
        }
    }

    public function setxml(){
        $get   = I('');
        header("Content-Type: text/xml");
        $xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<ckplayer>';
        $xml .= '<flashvars>{f-><![CDATA['.base64_decode($get['url']).']]>}{a->2}{defa->1|2|3}</flashvars>';
        $xml .= '<video>';
        $xml .= '<file><![CDATA['.base64_decode($get['url']).']]></file>';
        $xml .= '<size><![CDATA[0]]></size>';
        $xml .= '<seconds><![CDATA[0]]></seconds>';
        $xml .= '</video>';
        $xml .= '</ckplayer>';
        exit($xml);
    }

    public function setswf(){
        header("Content-Type: text/xml");
        $get = I('');
        $get   = parse_string(base64_decode($get['data']));
        $hosturl = substr(host_url(), 0, strlen(host_url())-strlen(stryou(host_url(), '/')));
        if($get['site'] == 'acfun'){
            $hds = array(1=>1,2=>2,3=>3);
        } else {
            $hds = array(1=>'mp4hd',2=>'mp4hd2',3=>'mp4hd3');
        }
        $hdb = array('mp4hd3' => 3,'mp4hd2' => 2,'mp4hd' => 1);
        foreach ($hds as $key => $value) {
            $defa[$key] = $hosturl."?a=setswf&data=".base64_encode("ccode=".$get['ccode']."&vid=".$get['vid']."&site=".$get['site']."&playtype=".$get['playtype']."&sign=".$get['sign']."&stype=".$value."&weparser_swf_url=".$get['weparser_swf_url']);
        }
        $xml='<ckplayer><flashvars><![CDATA[{s->3}{h->3}{f->'.$get['weparser_swf_url'].'}{a->'.$defa[$hdb[$get['stype']]].'}{defa->'.implode('|',$defa).'}';
        $xml.='{deft->标清|高清|超清}{site->'.$get['site'].'}{playtype->'.$get['playtype'].'}{sign->'.$get['sign'].'}{vid->'.$get['vid'].'}{stype->'.$get['stype'].'}{ccode->'.$get['ccode'].'}';
        $xml.=']]></flashvars>';
        $xml.='<videos><file><![CDATA[]]></file></videos>';
        $xml.='</ckplayer>';
        $xml='<?xml version="1.0" encoding="utf-8"?>'.$xml;
        exit($xml);
    }


    public function setm3u8(){
        $get = I('');
        $data['result']['files'] = base64_decode($get['url']);
        m3u8($data);
    }

    public function m3u8(){
        $get   = I('');
        if(IS_POST){
            $get['m3u'] = base64_decode($get['m3u']);
            if(strlen($get['m3u']) <= 100){
                $data['code'] = 404;
                $data['url'] = "";
                echo json_encode($data); die;
            }
            $data['code'] = 200;
            preg_match("|http://(.*?)\/|", $get['m3u'], $host);
            $get['m3u'] = str_replace('http://'.$host[1],"index.php?a=url&path=".$host[1],$get['m3u']);
            $vid = strzhong($get['url'],'com/mus/','/');
            $caid = md5(time().uuid());
            S($caid, $get['m3u'],60);
            $json['code'] = 200;
            $json['source'] = $get['type'];
            $_SERVER['PHP_SELF'] = empty($_SERVER['PHP_SELF'])? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF'];
            $json['result']['files'] = "{$_SERVER['PHP_SELF']}?a=m3u8&sign=".$caid;
            $json['result']['play_type'] = "m3u8";
            S(md5('json_'.$vid),$json, 60);
            $data['url']  = "{$_SERVER['PHP_SELF']}?a=xml&vid=".authcode($vid, "JM")."&hd=2&type=".$get['type']."&sign=".md5('json_'.$vid);
            echo json_encode($data); die;
        } else {
            $m3u8 = S($get['sign']);
            if(!$m3u8){
                header('HTTP/1.1 404 Internal Server Error');die;
            }
            if($get['decache'] == 1){
                S($get['sign'], null);
            }
            if(strstr($m3u8,'data.vod.itc.cn')){
                $m3u8 = str_replace("http://", "index.php?a=url&path=", $m3u8);
            }
            header('Content-Type: application/vnd.apple.mpegurl');
            header('Content-disposition: attachment; filename=playm3u8.m3u8');
            exit($m3u8);
        }
    }

    public function url(){
        $get = I('get.');
        $url = "http://".$get['path'];
        unset($get['path']);
        $location = $url.'&'.merge_string($get);
        if(strstr($location,'data.vod.itc.cn')){
            $suid = play_verify();
            $location = str_replace(strzhong($location,"&uid=","&"), $suid[0], $location);
            $location = str_replace(strzhong($location,"&SOHUSVP=","&"), $suid[1], $location);
        }
        header('HTTP/1.1 301 Moved Permanently');
        Header("Location: {$location}"); 
    }

    public function admin(){
        $config = auto_config();
        if($this->isLogin($config) == false){
            redirect('index.php?a=login');
        }
        if(IS_AJAX){
            $get = I('');
            $config['apikey'] = $get['apikey'];
            $config['debug']  = $get['debug'];
            $config['js_encryption'] = $get['js_encryption'];
            $config['player_skin']   = $get['player_skin'];
            if(!empty($get['auth_domain'])){
                $auth_domain = preg_replace("/\s/","",str_replace(PHP_EOL,'',$get['auth_domain']));
                if(empty($auth_domain)){
                    $return['code'] = 403;
                    $return['msg']  = "添加的白名单有误，请检查！";
                    exit(json_encode($return));
                }
                $config['auth_domain'] = explode("|",$auth_domain);
            } else {
                $config['auth_domain'] = "";
            }
            $config['back_host']   = $get['back_host'];
            $config['diy_port']    = $get['diy_port'];
            $config['play_title']  = $get['play_title'];
            $config['start_msg']   = $get['start_msg'];
            $config['auto_play']   = $get['auto_play'];
            $config['definition']  = $get['definition'];
            $config['qrcode']      = $get['qrcode'];
            $config['downspeeds']  = $get['downspeeds'];
            $config['prompttext']  = $get['prompttext'];
            $config['play_hd']     = $get['play_hd'];
            F('config',$config);
            $return['code'] = 200;
            $return['msg']  = "操作成功！";
            exit(json_encode($return));
        }
        $this->assign('config',$config)->display();
    }

    public function cookie(){
        $config = auto_config();
        if($this->isLogin($config) == false){
            redirect('index.php?a=login');
        }
        if(IS_AJAX){
            $get = I('');
            if($get['ct'] == 'del'){
                unset($config['cookie'][$get['id']]);
                $config['cookie'] = array_merge($config['cookie']);
            } elseif($get['ct'] == 'add') {
                $data['type'] = $get['type'];
                $data['user'] = $get['user'];
                $data['api']  = $get['api'];
                $get['cookie'] = str_replace(PHP_EOL,'',$get['cookie']);
                if($data['type'] == 'iqiyi'){
                    $data['cookie'] = "P00001=".strzhong($get['cookie'],'P00001=',';').";";
                } else {
                    $data['cookie'] = $get['cookie'];
                }
                if(in_array($data['type'], array('iqiyi','youku','qq'))){
                    if(empty($data['api'])){
                        $return['code'] = 403;
                        $return['msg']  = "请设置回调接口地址。";
                        exit(json_encode($return)); 
                    }
                }
                if(!empty($get['api'])){
                    if(!strstr($get['api'],"http://")){
                        $return['code'] = 403;
                        $return['msg']  = "回调接口地址格式错误！";
                        exit(json_encode($return)); 
                    }
                }
                $data['api'] = $get['api'];
                $data['state']  = true;
                if($get['id'] !== ""){
                    $config['cookie'][$get['id']] = $data;
                } else {
                    $config['cookie'][] = $data;
                }
            }
            F('config',$config);
            $return['code'] = 200;
            $return['msg']  = "操作成功！";
            exit(json_encode($return));
        }
        $this->assign('config',$config)->display();
    }

    public function encrypt(){
        $config = auto_config();
        if($this->isLogin($config) == false){
            redirect('index.php?a=login');
        }
        if(IS_AJAX){
            $get = I('');
            $config['data_key']     = $get['key'];
            $config['encrypt_type'] = $get['type'];
            $config['encrypt_time'] = $get['time'];
            F('config',$config);
            $return['code'] = 200;
            $return['msg']  = "操作成功！";
            exit(json_encode($return));
        }
        $this->assign('config',$config)->display();
    }

    public function other(){
        $config = auto_config();
        if($this->isLogin($config) == false){
            redirect('index.php?a=login');
        }
        if(IS_AJAX){
            $get = I('');
            $config['pm_logo']       = $get['pm_logo'];
            $config['error_msg']     = $get['error_msg'];
            $config['qrcode_url']    = $get['qrcode_url'];
            $config['referer_url']   = $get['referer_url'];
            $config['referer_msg']   = $get['referer_msg'];
            $config['width_height']  = explode("*",$get['width_height']);
            $config['playm3u8_logo'] = $get['playm3u8_logo'];
            F('config',$config);
            $return['code'] = 200;
            $return['msg']  = "操作成功！";
            exit(json_encode($return));
        }
        $this->assign('config',$config)->display();
    }

    public function test(){
        $config = auto_config();
        if($this->isLogin($config) == false){
            redirect('index.php?a=login');
        }
        $this->assign('config',$config)->display();
    }

    public function login(){
        $config = auto_config();
        if($this->isLogin($config)){
            redirect('index.php?a=admin');
        }
        if(IS_AJAX){
            $get  = I('');
            if($config['apikey'] != $get['apikey']){
                $return['code'] = 403;
                $return['msg']  = "非法禁止登陆！";
            } else {
                $data = Mod::obtain(array('type'=>'admin','data'=>$get));
                if($data['code'] != 200){
                    $return['code'] = 403;
                    $return['msg']  = empty($data['message'])? "亲，登录失败了哦！":$data['message'];
                } else {
                    $return['code'] = 200;
                    $return['msg']  = "登录成功！";
                    cookie('playm3u8',authcode(md5($get['apikey']),'JM',$config['data_key'],604800),604800);
                }
            }
            exit(json_encode($return));            
        }
        $this->assign('config',$config)->display();
    }

    private function feifeicms($get){
        $get['u'] = base64_decode($get['u']);
        $get['y'] = base64_decode($get['y']);
        $get['z'] = base64_decode($get['z']);
        return 'var cms_player = {"url":"'.$get['u'].'","name":"'.$get['p'].'","copyright":0,"time":10,"buffer":"'.$get['y'].'","next_url":"'.$get['u'].'"};document.write('."'".'<ifr'."'".'+'."'".'ame class="embed-responsive-item" src="playm3u8/index.php?url='.$get['u'].'&type='.$get['p'].'" width="100%" scrolling="no" height="100%" align="middle" frameborder="no" hspace="0" vspace="0" marginheight="0" marginwidth="0" name="tv"></ifr'."'".'+'."'".'ame>'."'".');';
    }

    public function cache_del(){
        @unlink(RUNTIME_PATH.'common~runtime.php');
        if(!isEmpty(RUNTIME_PATH.'Temp/')) delDir(RUNTIME_PATH.'Temp/');
        if(!isEmpty(RUNTIME_PATH.'Cache/'))delDir(RUNTIME_PATH.'Cache/');
        if(!isEmpty(RUNTIME_PATH.'Logs/')) delDir(RUNTIME_PATH.'Logs/');
        echo('清除成功');
    }

    private function isLogin($config){
        $cookie = authcode(cookie('playm3u8'),'DECODE',$config['data_key']);
        if($cookie != md5($config['apikey'])){
            return false;
        } else {
            return true;
        }
    }

    public function downapi(){
        if(!down_file(APP_PATH."Home/Common/api.php")){
            $this->success('下载失败！','/',100);  
        }
    }

    // API接口 json
    public function api(){
        $get = I('get.');
        $get['player_api'] = true;
        header('content-type:application/json;charset=UTF-8');
        if(empty($get['apikey'])){
            echo json_encode(array('code' => 403, 'message' => 'no apikey!'));die;
        }
        $config = auto_config();
        if($get['apikey'] != $config['apikey']){
            echo json_encode(array('code' => 403, 'message' => 'error apikey!'));die;
        }
        echo json_encode(get_video($get));
    }
}
