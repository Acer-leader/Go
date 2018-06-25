<?php
namespace Home\Controller;
use Think\Controller;

class UserController extends PublicController {

    /**20170425
     * 登录模块
     */
    public function login(){
        $nowurl=I('get.nowurl','');
        $this->assign('nowurl',$nowurl);
        $this->display();
    }
    
    public function shengming(){
        $this->display();
    }


    /**20170425
     * 登录模块
     */
    public function reg(){
        $this->display();
    }

    /**wzz 20170425
     * 执行登录
     */
    public function doLogin(){
        if(IS_AJAX){
            $mem = M('member');
            $url = U("Home/Ucenter/index");
            $telephone = I("post.telephone");
            $password  = I("post.password");
            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            if(!$password){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
            }
            if(!preg_match("/.{6,20}/", $password)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请输入6~20位字母数字密码！"));
            }
            $res = M("member")->where(array('telephone'=>$telephone, "isdel"=>0))->find();
            if(!$res){
                $this->ajaxReturn(array("status"=>0, "info"=>"账号不存在！"));
            }
            if($res['status']){
                $this->ajaxReturn(array("status"=>0, "info"=>"您的账号已被冻结，请联系管理员"));
            }
            if($res["password"] == encrypt_pass($password)){
                session("user_id",$res['id']);
                $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"密码错误！"));
            }
        }
    }

    /**wzz 20170425
     * 执行验证码登录
     */
    public function doCodeLogin(){
        if(IS_AJAX){
            $mem = M('member');
            $url = U("Home/Ucenter/index");
            $telephone    = I("post.telephone");
            $verify_code  = I("post.verify_code");
            $code         = I("post.code");
            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            if(!$verify_code){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写验证码！"));
            }
            /*if(!check_verify($verify_code)){
                $this->ajaxReturn(array("status"=>0,"info"=>'亲，验证码输错了哦！'));
            }*/
            if(!$code){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写短信验证码！"));
            }
            //=======================检测验证码=================
            $res = checkMessage($telephone, $code, 1);
            //$res['status']  =1;
            //=======================检测验证码=================
            if($res['status']!=1){
                $this->ajaxReturn($res);
            }

            $res = M("member")->where(array('telephone'=>$telephone, "isdel"=>0))->find();
            if(!$res){
                $log_data=array(
                    'telephone' => $telephone,
                    'isdel'     => 0,
                    'status'    => 0,
                    'add_time'  => date("Y-m-d H:i:s", time()),
                    'last_login_time'=> date("Y-m-d H:i:s", time()),
                    );
                $res11=$mem->add($log_data);
                if($res11){
                    session("user_id",$res['id']);
                    $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));
                }
                $this->ajaxReturn(array("status"=>0, "info"=>"登录失败！"));
            }
            if($res['status']){
                $this->ajaxReturn(array("status"=>0, "info"=>"您的账号已被冻结，请联系管理员"));
            }
            session("user_id",$res['id']);
            $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));			
        }
    }


    /** wzz 20170425
     * 执行注册
     */
    public function doReg(){
        if(IS_AJAX){
            $data = I("post.");
            $person_name= $data['person_name'];
            $sex        = $data['sex'];
            $telephone  = $data['telephone'];
            $password   = $data['password'];
            $repassword = $data['repassword'];
            $code       = $data['code'];
            if(!$person_name){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写姓名！"));
            }
            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            $tel = M('Member')->where(array('telephone'=>$telephone))->count();
            if($tel){
                $this->ajaxReturn(array("status"=>0, "info"=>"该手机已被注册！"));
            }
            if(!$password){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
            }
            if(!preg_match("/.{6,20}/", $password)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请输入6~20位字母数字密码！"));
            }
            if(!$repassword){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写确认密码！"));
            }
            /* if(!preg_match("/.{6,24}/", $repassword)){
                $this->ajaxReturn(array("status"=>0, "info"=>"确认密码格式错误！"));
            } */
            if($password !==$repassword)
                $this->ajaxReturn(array("status"=>0, "info"=>"两次密码不一致！"));
            //=======================检测验证码=================
            $res = checkMessage($telephone, $code, 1);
            //$res['status']  =1;
            //=======================检测验证码=================
            if($res['status']==1){
                $data['telephone']  = $telephone;
                $data['password']   = encrypt_pass($password);
                $data['person_name']= $person_name;
                $data['add_time']   = date("Y-m-d H:i:s", time());
                $res = M("Member")->add($data);
                if($res){
                    session("user_id", $res);
                    $this->ajaxReturn(array("status"=>1, "info"=>"注册成功！"));
                }else{
                    $this->ajaxReturn(array("status"=>0, "info"=>"注册失败！"));
                }
            }else{
                $this->ajaxReturn($res);
            }
            
        }
    }
    /**
    *忘记密码
    */
    public function ajax_forget(){
        if(IS_AJAX){
            $telephone = I('post.telephone');
            $code = I('post.code');
            $password = I('post.password');
            $repassword = I('post.passwords');
            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            $tel = M('Member')->where(array('telephone'=>$telephone))->count();
            if(!$tel){
                $this->ajaxReturn(array("status"=>0, "info"=>"该手机不存在！"));
            }
            if(!$password){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
            }
            if(!preg_match("/.{6,20}/", $password)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请输入6~20位字母数字密码！"));
            }
            if(!$repassword){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写确认密码！"));
            }
            /* if(!preg_match("/.{6,24}/", $repassword)){
                $this->ajaxReturn(array("status"=>0, "info"=>"确认密码格式错误！"));
            } */
            if($password !== $repassword){
                $this->ajaxReturn(array("status"=>0, "info"=>"两次密码不一致！"));
            }
            $res = checkMessage($telephone, $code, 2);
            //=======================检测验证码=================
            if($res['status']!=1){
                $this->ajaxReturn($res);
            }
            $forget = M('Member')->where(array('telephone'=>$telephone))->save(array('password'=>encrypt_pass($password)));
            if($forget){
                $this->ajaxReturn(array('status'=>1, 'info'=>'修改密码成功!'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'修改密码失败!'));
            }
        }
    }

    //退出登录
    public function loginOut(){
        session('user_id',null);
        $this->redirect('Home/User/login');
    }

    
    
    
    /**
     * 忘记密码模块
     */
    public function forget(){
        $this->display();
    }


    
    
}