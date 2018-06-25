<?php
namespace Supplier\Controller;
use Think\Controller;
class UserController extends Controller {

    /**
     * 后台登录页面
     *
     * @author Chandler_qjw  ^_^
     */
    public function login(){
    	
        $this->display();
    }

    public function register(){

        $this->display();
    }

    /**
     *
     * 验证码生成
     */
    public function verify_c(){
        ob_clean();
        $Verify = new \Think\Verify();

        $Verify->fontSize = 18;
        $Verify->length   = 4;
        $Verify->useNoise = false;
        $Verify->codeSet = '0123456789';
        $Verify->imageW = 130;
        $Verify->imageH = 50;

        $Verify->entry();
    }

    
	
	


    /**
     * 后台检测登录
     * @param
     * @author Chandler_qjw  ^_^
     */
    public function checkloginajax(){

        // 检查验证码
        $verify = I('param.vcode','');
        if(!check_verify($verify)){
//            $this->error("亲，验证码输错了哦！");
            $data['info']   =   '亲，验证码输错了哦！'; // 提示信息内容
            $data['status'] =   0;  // 状态 如果是success是1 error 是0
            $data['url']    =   ''; // 成功或者错误的跳转地址
            $this->ajaxReturn($data);
            return;
        }

		$action=D("Xiaodai");
		$rs = $action->login();

		if($rs == 1)
		{
			$data['info']   =   '登录成功咯~'; // 提示信息内容
			$data['status'] =   1;  // 状态 如果是success是1 error 是0
			$data['url']    =   ''; // 成功或者错误的跳转地址

		}
		elseif($rs == 0)
		{
			$data['info']   =   '帐号或者密码错误~'; // 提示信息内容
			$data['status'] =   0;  // 状态 如果是success是1 error 是0
			$data['url']    =   ''; // 成功或者错误的跳转地址
		}
        elseif($rs == 3)
        {
            $data['info']   =   '请等待洛凡金融审核完成'; // 提示信息内容
            $data['status'] =   0;  // 状态 如果是success是1 error 是0
            $data['url']    =   ''; // 成功或者错误的跳转地址
        }
		else
		{
			$data['info']   =   '帐号已禁用~'; // 提示信息内容
			$data['status'] =   0;  // 状态 如果是success是1 error 是0
			$data['url']    =   ''; // 成功或者错误的跳转地址
		}
		
        $this->ajaxReturn($data);return;
    }

    public function checkregisterajax(){
        // 检查验证码
        $code = I('param.send_code','');
        $telephone     = I("post.telephone");
        $password      = I("post.password");
        if(!$telephone){
            $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
        }
        if(!$password){
            $this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
        }
        //检测验证码
        $res = checkMessage($telephone,$code, 5);
        if($res['status'] != 1){
            $this->ajaxReturn($res);
        }

        if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
            $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
        }
        // 检测密码格式
        if(!preg_match("/.{3,24}/", $password)){
            $this->ajaxReturn(array("status"=>0, "info"=>"密码格式错误！"));
        }

        // 判断手机号是否存在
        $count = M("xiaodai")->where(array("telephone"=>$telephone))->count();
        if($count){
            $this->ajaxReturn(array("status"=>0, "info"=>"手机号已存在！"));
        }
        /*$res = $this->checkMessage($telephone, $send_code, 2);
        	if($res['status'] != 1){$this->ajaxReturn($res);}*/
        $action=D("Xiaodai");
        $rs = $action->register();
        if($rs == 1)
        {
            $data['info']   =   '账号申请成功'; // 提示信息内容
            $data['status'] =   1;  // 状态 如果是success是1 error 是0
            $data['url']    =   ''; // 成功或者错误的跳转地址

        }
        elseif($rs == 2)
        {
            $data['info']   =   '帐号已经存在!'; // 提示信息内容
            $data['status'] =   0;  // 状态 如果是success是1 error 是0
            $data['url']    =   ''; // 成功或者错误的跳转地址
        }
        else
        {
            $data['info']   =   '帐号已禁用~'; // 提示信息内容
            $data['status'] =   0;  // 状态 如果是success是1 error 是0
            $data['url']    =   ''; // 成功或者错误的跳转地址
        }
        $this->ajaxReturn($data);
        return;
    }
    /**
     * 后台退出的登录
     *
     * @author Chandler_qjw  ^_^
     */
    public function  logout(){

        $username=I("get.sid");
        if(!empty($username)){
            session('supplier_name',null); // 删除登录信息
            session('supplier_id',null); // 删除name
            $this->redirect("/Supplier/Index/login"); //直接跳转，不带计时后跳转
        }
    }


    public function updatepwd(){
        if(IS_POST){
            $action=D('Xiaodai');
            $pass=I('post.password');
            $re=$action->getupdatepass($pass);
            if($re){
                $this->success('修改成功',U('/Supplier/Supplier/gyindex'));
            }else{
                $this->error('修改失败');
            }
        }
        $this->assign('munetype',9);
        $this->display();
    }

    public function getMessage(){
        if(IS_AJAX){
            $m= M('Xiaodai');
            $telephone   = trim(I("post.telephone"));
            $code_type   = 5;
            $verify_code = trim(I("post.verify_code"));
            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            if(!in_array($code_type,array('1','2','3','4','5'))){
                $this->ajaxReturn(array("status"=>0, "info"=>"参数有误！ 333"));
            }
            if($verify_code && !check_verify($verify_code)){
                $this->ajaxReturn(array("status"=>0,"info"=>'亲，验证码输错了哦！'));
            }
            if($code_type==1){
                $res=$m->where(array('telephone'=>$telephone))->find();
                if($res){
                    $this->ajaxReturn(array("status"=>0, "info"=>"手机号已被注册！"));
                }
            }elseif($code_type==2){
                $res=$m->where(array('telephone'=>$telephone))->find();
                if(!$res){
                    $this->ajaxReturn(array("status"=>0, "info"=>"手机号未注册！"));
                }
            }
            //$this->ajaxReturn(array('status'=>1,'info'=>'验证码发送成功，请注意查收'));
            //发送验证码
            $res = sendMessage($telephone,$code_type);
            $this->ajaxReturn($res);
        }
    }

//    /**
//     * 一个检测验证码的方法
//     * 1为未过期，0为过期，2为完成注册，3为成功找回密码  checkMessage($telephone,5, $code, 3);
//     */
//    public function checkMessage($phone,$type,$identify,$status){
//        $over_time=300;
//        $now_time=time();
//        $sql=M('user_verify')->where(array('telephone'=>$phone,"type"=>$type,"status"=>0))->order('create_at desc')->find();
//        if(!$sql){
//            return array("status"=>'2',"info"=>"验证码错误");
//        }
//        $time=$sql['create_at'];
//        if ($now_time<($time+$over_time)){
//            $code=$sql['code'];
//            if ($code==$identify){
//                M('user_verify')->where(array('id'=>$sql['id']))->save(array("status"=>$status));
//                return array("status"=>'1',"info"=>"ok");
//            }else{
//                return array("status"=>'10',"info"=>"验证码错误!");
//            }
//        }else{
//            M('user_verify')->where(array('id'=>$sql['id']))->save(array("status"=>9));
//            return array("status"=>'3',"info"=>"验证码已过期");
//        }
//    }


//    /**
//     * 发送验证码的方法
//     */
//    public function getMessage(){
//        if(IS_AJAX){
//            $telephone = I("post.telephone");
//            if(!$telephone){
//                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
//            }
//            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
//                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
//            }
//            $res = sendmessage(1,$telephone);
//            $result=array('-2','-3','-4','-5','-6','-8','-9','-990');
//            if(!in_array($res,$result) && $result){
//                $this->ajaxReturn(array('status'=>1,'info'=>'短信发送成功'));
//            }else{
//                $this->ajaxReturn(array('status'=>0,'info'=>'短信发送失败'));
//            }
//
//        }
//    }

}