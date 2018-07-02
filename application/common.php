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

function get_collection($data)
{
	return array_map(function ($one) { return $one->getData(); }, $data);
}

function sortdata($catArray, $id = 0, $prefix = '')
{
	static $formatCat = array();
	static $floor = 0;

	foreach ($catArray as $key => $val)
	{
		if ($val['parent_id'] == $id)
		{
			$str         = nstr($prefix, $floor);
			$val['name'] = $str.$val['name'];

			$val['floor'] = $floor;
			$formatCat[]  = $val;

			unset($catArray[$key]);

			$floor ++;
			sortdata($catArray, $val['id'], $prefix);
			$floor --;
		}
	}

	return $formatCat;
}

function nstr($str, $num = 0)
{
	$return = '';
	for ($i = 0; $i < $num; $i ++)
	{
		$return .= $str;
	}

	return $return;
}

// 应用公共文件
function sendSMS($mobile, $content, $time = '', $mid = '')
{
	$statusStr = array(
		"0"  => "短信发送成功",
		"-1" => "参数不全",
		"-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
		"30" => "密码错误",
		"40" => "账号不存在",
		"41" => "余额不足",
		"42" => "帐户已过期",
		"43" => "IP地址限制",
		"50" => "内容含有敏感词",
	);
	$smsapi    = config('smsbao')['smsapi'];
	$user      = config('smsbao')['sms_ecmoban_user']; //短信平台帐号
	$pass      = md5(config('smsbao')['sms_ecmoban_password']); //短信平台密码
	$sendurl   = $smsapi."sms?u=".$user."&p=".$pass."&m=".$mobile."&c=".urlencode($content);
	$result    = file_get_contents($sendurl);
	if ($result == '0')
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function random($length = 6, $numeric = 0)
{
	// PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
	if ($numeric)
	{
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	}
	else
	{
		$hash  = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max   = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i ++)
		{
			$hash .= $chars[mt_rand(0, $max)];
		}
	}

	return $hash;
}

//支付接口
function getsign($data, $key, $newkey = 'sign', $linkstr = 'key', $ksorts = '1')
{
	if ($ksorts == 1)
	{
		ksort($data);
	}
	$buff = "";
	foreach ($data as $k => $v)
	{
		if ($k != $newkey && $v != "" && ! is_array($v))
		{
			$buff .= $k."=".$v."&";
		}
	}
	$buff          = trim($buff, "&");
	$buff          = $buff."&".$linkstr."=".$key;
	$sign          = strtoupper(MD5($buff));
	$data[$newkey] = $sign;

	return $data;
}

function arrayToXml($arr)
{
	$xml = "<xml>";
	foreach ($arr as $key => $val)
	{
		$xml .= "<".$key.">".$val."</".$key.">";
	}
	$xml .= "</xml>";

	return $xml;
}

function formsubmithtml($action, $data)
{
	$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$action."' method='POST'>";
	foreach ($data as $key => $value)
	{
		$val   = str_replace("'", "&apos;", $value);
		$sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
	}
	$sHtml = $sHtml."<input type='submit' value='ok' style='display:none;''></form>";

	$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";

	return $sHtml;
}

function wx($data, $url = "https://api.mch.weixin.qq.com/pay/unifiedorder")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$response = curl_exec($ch);
	curl_close($ch);

	return json_decode(json_encode(simplexml_load_string($response, 'simplexmlelement', LIBXML_NOCDATA)), TRUE);
}

function wxresult($data, $key)
{
	return wx(arrayToXml(getsign($data, $key)));
}

function alisign($data)
{
	ksort($data);
	$buff = "";
	foreach ($data as $k => $v)
	{
		if ($k != "sign" && $v != "" && ! is_array($v))
		{
			$buff .= $k."=".$v."&";
		}
	}
	$buff = trim($buff, "&");
	$res  = "-----BEGIN RSA PRIVATE KEY-----\n".
		wordwrap(config('alipay')['rsaPrivateKey'], 64, "\n", TRUE).
		"\n-----END RSA PRIVATE KEY-----";
	openssl_sign($buff, $sign, $res, OPENSSL_ALGO_SHA256);
	$sign         = base64_encode($sign);
	$data['sign'] = $sign;
	$sHtml        = formsubmithtml('https://openapi.alipay.com/gateway.do?charset=utf-8', $data);

	return $sHtml;
}

function yspcpay($data, $otherdata, $key)
{
	// $action='http://www.unspay.com/unspay/page/linkbank/payRequest.do';
	$action     = 'http://180.166.114.155/unspay/page/linkbank/payRequest.do';
	$resultdata = array_merge(getsign($data, $key, 'mac', 'merchantKey', 0), $otherdata);

	// echo '<pre>';var_dump($resultdata);exit;
	return formsubmithtml($action, $resultdata);
}

/**
 * 自定义函数库
 */

/**
 * 数组重组(二维数组)
 * $data 需要重组的数据
 * $k 键值字符
 * $v 值字符
 * */
function get_newData($data, $k = 'id', $v = 'name')
{
	$newdata = array();
	if ($data)
	{
		foreach ($data as $key => $val)
		{
			if ($v == '')
			{
				$newdata[$val[$k]] = $val;
			}
			else
			{
				$newdata[$val[$k]] = $val[$v];
			}
		}
	}

	return $newdata;
}

//将二维数据的指定字段的值用 ，组合成字符串
function data_getstr($data, $name = 'id')
{
	$str     = '';
	$newdate = array();
	if ($data)
	{
		$data = get_newData($data, $name, '');
		foreach ($data as $key => $val)
		{
			$str .= $val[$name].',';
		}
	}
	$str = rtrim($str, ',');

	return $str;
}

//测试日志

function ceshilog($file, $txt, $other = 'wx')
{
	$fp = fopen($file, 'ab+');
	fwrite($fp, '-----------'.$other.':'.date('Y-m-d H:i:s').'-----------------');
	if (is_array($txt))
	{
		fwrite($fp, var_export($txt, TRUE));
	}
	else
	{
		fwrite($fp, $txt);
	}
	fwrite($fp, "\r\n\r\n\r\n");
	fclose($fp);
}