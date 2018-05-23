<?php
function playm3u8_encryp($str, $type = ''){
	if(empty($str) || empty($type)){
		return $str;
	}
	$config = playm3u8_config();
	if(!$config) return $str;
	if(!in_array($type, $config['encrypt_type'])){
		return $str;
	} else {
		return playm3u8_code($str,"JM",$config['data_key'],$config['encrypt_time']);
	}
}

function playm3u8_config(){
	$filename = dirname(__FILE__).'/../../Runtime/Data/config.php';
	if(!is_file($filename)){
		return false;
	}
	$file = unserialize(file_get_contents($filename));
	$file['encrypt_type'] = explode("|",$file['encrypt_type']);
	return $file;
}

function playm3u8_code($string, $operation = 'DECODE', $key = '', $expiry = 3600) {
    if($operation == 'DECODE'){
	    if(!playm3u8_is_de($string)){
	    	return '';
	    } else {
	    	$string = substr($string,8,strlen($string));
	    }
    }
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

function playm3u8_is_de($str){
	if(substr($str,0,8) == 'PlayM3u8'){
		return true;
	} else {
		return false;
	}	
}
