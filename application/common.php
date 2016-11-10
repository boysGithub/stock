<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * [getListStocks 得到股票列表]
 * @return [string] [返回一个字符串]
 */
function getStock($key,$prefix='',$suffix=''){
	if(is_array($key)){
		$k = '';
		for ($i=0; $i < count($key); $i++) { 
			$k .= completion($key[$i],$prefix,$suffix).",";
		}
		$key = $k;
	}else{
		$key = completion($key,$prefix,$suffix);
	}
	$url = 'http://hq.sinajs.cn/list='.$key;
	$tmp = sendGetCurl($url);
	$tmpArr = explode(';',$tmp);
	array_pop($tmpArr);
	for ($i=0; $i < count($tmpArr); $i++) { 
		$tArr = explode('=',$tmpArr[$i]);
		if($tArr[1] == ""){
			exit(JN(['status'=>'failed','data'=>'股票不存在，或者网络错误']));
		}
		$keys[] = mb_substr($tArr[0],14);
		$values[] = explode(',',$tArr[1]);
	}

	if(is_array($keys[0][0])){
		return JN(['status'=>'failed','data'=>'接口内部错误，请检查',]);
	}

	if(count($keys) != count($values)){
		return JN(['status'=>'failed','data'=>'数组的长度不相等',]);
	}
	return array_combine($keys,$values);
}

/**
 * [sendGetCurl 发送get method curl 请求]
 * @return [sting] [字符串类型]
 */
function sendGetCurl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper("get")); 
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	//去掉字符串中所有的空格
	$rule=array(" ","　","\t","\n","\r",'"');
	$take=array("","","","","","");
	$output = iconv('GBK', 'UTF-8', str_replace($rule,$take,$output));
	curl_close($ch);
	return  $output;
}

/**
 * [JN json返回格式]
 */
function JN($val){
	header("Content-type: application/json");
	return json_encode($val);
}

/**
 * [completion 补全股票代码]前缀后缀不能同时存在
 * @return [string] [返回字符串]
 */
function completion($str,$prefix='',$suffix=''){
	if(strlen($str) < 6){
        exit(JN(['status'=>'failed','data'=>'股票代码长度不足6位']));
    }
	if($prefix !='' && $prefix != "s_"){
		$prefix='s_';
	}
	if($suffix !='' && $suffix != "_i"){
		$suffix='_i';
	}
	$tmp = $str{0};
	if($tmp === '6'){
		return $prefix."sh".$str.$suffix;
	}else if($tmp === '0' || $tmp === '3'){
		return $prefix."sz".$str.$suffix;
	}else{
		exit(JN(['status'=>'failed','data'=>'股票不存在']));
	}
}

/**
 * [filterSpecialCharacter 过滤掉特殊字符]
 * @return [string] [返回字符串]
 */
function filterSpecialCharacter($str){
	//需要过滤字符的数组
	$vowels = ['"',"'",'#'];
	//需要替换成的数组
	$yummy = [''];
	return str_replace($vowels,'',$str);
}

/**
 * [redis 链接redis]
 * @return [boolean] [description]
 */
function redis(){
	$redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);
    return $redis;
}