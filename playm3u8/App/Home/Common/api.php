<?php
error_reporting(0);

$get = $_GET;

if(empty($get['url'])){
	exit('no url!');
} else {
    $get['url'] = base64_decode($get['url']);
}

if(!empty($get['post'])){
    $get['post'] = base64_decode($get['post']);
}

if(!empty($get['cookie'])){
    $get['cookie'] = base64_decode($get['cookie']);
}

if(!empty($get['headers'])){
    $get['headers'] = parse_string(base64_decode($get['headers']));
}

$data = post($get['url'],$get['post'],$get['cookie'],Null,$get['headers'],Null,Null,3,True)->body;
exit($data);






class Http{

    public $body     = "";
    public $json     = "";
    public $code     = "";
    public $headers  = "";
    public $cookies  = "";
    public $location = "";


    public function __construct($url ,$data ,$cookies ,$referer ,$heahers ,$gzip,$ip ,$times,$jump,$noreferer,$getheaders){

		$headers_data = array(
			'Accept: */*',
			'Accept-Language: zh-cn',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36',
		);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if(!empty($ip)){
            if(!strpos($ip,':')){
                $this->body = 'Curl Error: example "127.0.0.1:80"';
                curl_close($curl);
                return $this;
            }
            curl_setopt($curl, CURLOPT_PROXY, $ip);
            curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }
        if($gzip) curl_setopt($curl, CURLOPT_ENCODING ,'gzip');
        if(!empty($cookies)) curl_setopt($curl, CURLOPT_COOKIE, $cookies);
        if(!$noreferer) curl_setopt($curl, CURLOPT_REFERER, empty($referer) ? $url : $referer);
        if(is_array($heahers)) $headers_data = $this->array_merge_update($headers_data, $heahers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_data);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_HEADER , true);
        if($getheaders){
            curl_setopt($curl, CURLOPT_NOBODY, true);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, (empty($times)? 20 : $times));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION,($jump ? true : false));
        $response = curl_exec($curl);
        if(curl_errno($curl)){
            $this->body     = 'Curl Error:'.curl_error($curl);
        }else{
            $this->code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $headerSize     = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $this->body     = substr($response, $headerSize);
            $this->json     = json_decode($this->body, true);
            $this->headers  = explode("\r\n",substr($response, 0, $headerSize - 4));
            $this->location = $this->get_headers('location');
            $this->cookies  = $this->get_headers('Set-Cookie');
        }
        curl_close($curl);
        return $this;
    }

    public function jsonify(){
        return $this->json_encode_jsonify($this->json);
    }

    public function zuo($z){
        $wz = strpos( $this->body , $z);
        if(empty($wz)) return Null;
        return substr( $this->body , 0 , $wz );
    }

    public function zhong($z, $y){
        $left = strpos($this->body, $z);
        if (empty($left)) return Null;
        $right = strpos($this->body, $y, $left + strlen($z));
        if (empty($left) or empty($right)) return Null;
        return substr($this->body, $left + strlen($z), $right - $left - strlen($z));
    }

    public function you($y){
        $wz = strrpos($this->body,$y);
        if(empty($wz)) return Null;
        return substr($this->body, $wz + strlen($y));
    }

    public function get_headers($name){
        if(empty($name)) return;
        $name = $name . ': ';
        $return_headers = '';
        foreach ($this->headers as $key) {
            if($name == 'Set-Cookie: '){
                if(strstr($key,'Set-Cookie')){
                    $qian   = strlen($name);
					$cookie = substr($key,$qian,strpos($key,';')-$qian);
					if(!strstr($cookie,'=deleted')){
						$return_headers .= $cookie.'; ';
					}
                }
            }else{
                if(strtolower($this->strzuo($key,': ')).': ' == strtolower($name)){
                    $qian = strlen($name);
                    $hou  = strlen($key);
                    $return_headers = substr($key, $qian, $hou);
                }
            }
        }

        if(substr($return_headers, -2, strlen($return_headers)) == '; '){
            $return_headers = substr($return_headers, 0, -2);
        }
        return $return_headers;
    }

    private function json_encode_jsonify($json_arr){
        if (is_array($json_arr)){
            header('content-type:application/json;charset=utf8');
            return json_encode($json_arr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        }
    }

    private function array_merge_update($to1,$to2){
        foreach ($to2 as $v) {
            $a = explode(':',$v);
            $i = 0;
            foreach ($to1 as $va) {
                $b = explode(':',$va);
                if($b[0] == $a[0]){
                    $to1[$i] = $v;
                    break;
                }
                if(count($to1) == $i+1){
                    $to1[$i+1] = $v;
                }
                $i++;
            }
        }
        return $to1;
    }

    private function strzuo( $str , $zuo ){
        $wz = strpos( $str , $zuo);
        if(empty($wz)) return Null;
        return substr( $str , 0 , $wz );
    }
}

function p($arr) {
    echo "<pre>";
    print_r($arr);die;
}


function parse_string($s) {
    if (is_array($s)) {
        return $s;
    }
    parse_str($s, $r);
    return $r;
}

function Post(
        $url       = "",
        $data      = "",
        $cookies   = "",
        $referer   = "",
        $heahers   = array(),
        $gzip      = false,
        $ip        = "",
        $times     = 20,
        $jump      = false,
        $noreferer = false,
        $getheaders= false
    ){
    $object = new Http($url ,$data ,$cookies ,$referer ,$heahers ,$gzip ,$ip ,$times ,$jump ,$noreferer ,$getheaders);
    return $object;
}
