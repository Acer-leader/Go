<?php
namespace Supplier\Controller;
use Think\Controller;
class PayController extends Controller {
	public function pay(){
		$interfaceName = 'ICBC_WAPB_B2C';//接口名称//必输
		$interfaceVersion = '1.0.0.6';//接口版本号//必输
		$orderDate = date('YmdHis',time());//交易日期时间 //必输
		$installmentTimes = '1';//分期付款期数取值：1、3、6、9、12、18、24 //必输
		$curType = '001';//支付币种//必输
		$verifyJoinFlag = '0';//联名校验标志 //必输
		$merURL = 'zhongka';//通知商户URL//必输
		$notifyType = 'HS';//通知类型//必输//实时通知
		$goodsID = '';//商品编号
		$goodsName = '';//商品名称
		$goodsNum = '';//商品数量
		$carriageAmt = '';//已含运费金额
		$Language = 'zh_CN';//语言版本
		$merVAR = '';//返回商户变量
		$resultType = '0';//结果发送类型、、0无论支付成功或者失败银行都向商户发送交易通知信息
		$remark1 = '';//备注字段1
		$remark2 = '';//备注字段2
		$merHint = '';//商城提示
		//获取商户帐号信息
		$merID = '';//商户代码//必输
		$merAcct = '';//商户账号//必输
		
		//查询用户订单
		
		$userId = session('user_id');
		$orderid = '';//订单号//必输
		$amount = '';//订单金额//必输
		if(empty($orderid)){
			echo '订单不能为空';
			exit;
		}
		if(empty($amount)){
			echo '支付金额不能为空';
			exit;
		}
		
		//构建订单tranData的xml的数据，中间不能有空格或换行
		$tranData = "<?xml version='1.0' encoding='GBK' standalone='no'?><B2CReq><interfaceName>".$interfaceName."</interfaceName><interfaceVersion>".$interfaceVersion."</interfaceVersion><orderInfo><orderDate>".$orderDate."</orderDate><orderid>".$orderid."</orderid><amount>".$amount."</amount><installmentTimes>".$installmentTimes."</installmentTimes><curType>".$curType."</curType><merID>".$merID."</merID><merAcct>".$merAcct."</merAcct></orderInfo><custom><verifyJoinFlag>".$verifyJoinFlag."</verifyJoinFlag><Language>".$Language."</Language></custom><message><goodsID>".$goodsID."</goodsID><goodsName>".$goodsName."</goodsName><goodsNum>".$goodsNum."</goodsNum><carriageAmt>".$carriageAmt."</carriageAmt><merHint>".$merHint."</merHint><remark1>".$remark1."</remark1><remark2>".$remark2."</remark2><merURL>".$merURL."</merURL><merVAR>".$merVAR."</merVAR><notifyType>".$notifyType."</notifyType><resultType>".$resultType."</resultType><backup1></backup1><backup2></backup2><backup3></backup3><backup4></backup4></message></B2CReq>";
		//调用签名方法并自动提交form表单
		$this->signSubmit($tranData);
	}
	//签名提交
	public function signSubmit($tran){
		$merCertKey = '';//商户私钥文件路径
		$merCertKeyPasswd = '';//获取密钥口令
		$tranData1 = $tran;//获取订单提交数据
		$merCert = '';//读取商户公钥文件
		
		/*加载PHP签名模块，详细参加部署文档*/
		if (!extension_loaded('infosec'))
		{
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			{
				dl('php_infosec.dll');
			}
		else
			{
				dl('infosec.so');
			}
		}
			//读取商户私钥文件
			$keyfile=$merCertKey;
			if(strlen($keyfile) <= 0)
			{
				echo "WARNING : no key data input<br/>";
				exit();
			}
			
			$fp = fopen($keyfile,"rb");
			if($fp == NULL)
			{
				echo "open file error<br/>";
				exit();
			}
			
			fseek($fp,0,SEEK_END);
			$filelen=ftell($fp);
			fseek($fp,0,SEEK_SET);
			$contents = fread($fp,$filelen);
			fclose($fp);
			
			$key = substr($contents,2);
			//获取密钥口令
			$pass=$merCertKeyPasswd;
			if(strlen($pass) <= 0)
			{
				echo "WARNING : no key password input<br/>";
				exit();
			}
			//获取订单提交数据
			$tranData=$tranData1;
			if(strlen($tranData) <= 0)
			{
				echo "WARNING : no tranData input<br/>";
				exit();
			}
			//读取商户公钥文件
			$merCert=$merCert;
			if(strlen($merCert) <= 0)
			{
				echo "WARNING : no merCert input<br/>";
				exit();
			}
			else
			{
				$fp2 = fopen($merCert,"rb");
				if($fp2 == NULL)
				{
					echo "open file error<br/>";
					exit();
				}
				fseek($fp2,0,SEEK_END);
				$filelen2=ftell($fp2);
				fseek($fp2,0,SEEK_SET);
				$cert = fread($fp2,$filelen2);
				fclose($fp2);
			}
			
		/*签名*/
		$signature = sign($tranData,$key,$pass);//签名
		$code = current($signature);//获取签名数据
		$len = next($signature);//获取签名数据长度
		$signcode = base64enc($code);//对签名数据BASE64编码
		$merSignMsg = current($signcode);
		$tranDataBase64 = base64enc($tranData);//对表单数据BASE64编码
		$merCertBase64 = base64enc($cert);//对证书BASE64编码
		//构建自动提交订单支付的表单
		$form = '<!DOCTYPE HTML>
				<html>
				<head>
					<meta charset="utf-8">
					<title>支付</title>
				</head>
				<body>
					<div style="text-align:center">跳转中...</div>
					<form id="pay_form" name="pay_form" action="https://mywap2.dccnet.com.cn:447/ICBCWAPBank/servlet/ICBCWAPEBizServlet" method="post">
						<INPUT NAME="interfaceName" TYPE="text" value="ICBC_WAPB_B2C">
						<INPUT NAME="interfaceVersion" TYPE="text" value="1.0.0.6">
						<INPUT NAME="clientType" TYPE="text" value="0">
						<INPUT NAME="tranData" TYPE="text" value="%s">
						<INPUT NAME="merSignMsg" TYPE="text" value="%s">
						<INPUT NAME="merCert" TYPE="text" value="%s">
					</form>
					<script type="text/javascript">
						document.onreadystatechange = function(){
							if(document.readyState == "complete") {
								document.pay_form.submit();
							}
						};
					</script>
				</body>
				</html>';
	$str = sprintf($form,$tranDataBase64,$merSignMsg,$merCertBase64);
	echo $str;
		
	}
	//接收工行支付回调信息 工行以post方式回复信息
	public function messagePay(){
		if(isset($_POST)){
			$psot = $_POST;
			var_dump($post);
		}
	}
}