<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
//1237853402@1237853402

	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wxe9de09472bb0b6a4';
	//受理商ID，身份标识
	const MCHID = '1439474402';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = '2wwiwef8rr94b76n0i64n3wee4uib0rv';
	//	const KEY = '874164';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = '3073c56798e08f1504e440f8aaa92016';
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://kqmy.unohacha.com/Wxin/Pay/wxpay.html';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = 'D:\kqmy\cert\apiclient_cert.pem';
	const SSLKEY_PATH =  'D:\kqmy\cert\apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = 'http://kqmy.unohacha.com/Wxin/Pay/wxpayNotify';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
	
}
	
?>