<?php
namespace Iphone\Controller;
use Think\Controller;
class ApplyController extends PublicController{
    public function _initialize(){
        parent::_initialize();
        $this->data   =   session('apply');
        $this->assign('data',$this->data);

    }
    
    
    public function sqdaikuan(){
        
        $res    =   M('loan')->find($data['card_id']);
        $this->assign('res',$res);
        //dump($res);
        //dump($data);
        $this->display();
    }
    public function sqdaikuan1(){
        if(!$this->data['tel']){
            $this->redirect('/Iphone/Apply/sqdaikuan/');
        }
        $this->display();
    }
    public function sqdaikuan2(){
        if(!$this->data['tel']){
            $this->redirect('/Iphone/Apply/sqdaikuan/');
        }
    
        $this->display();
    }
    public function sqdaikuan3(){
        $this->display();
    }
    
    public function daikuando(){
        if(IS_AJAX){
            $name   =   I('post.name');
            $tel    =   I('post.tel');
            session('apply.name',$name);
            session('apply.tel',$tel);
            $res = sendMessage($tel,4);
            $this->ajaxReturn($res);   
        }

    }
    public function daikuando1(){
        if(IS_AJAX){
            $code         = I("post.code");
            if(!$code){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写短信验证码！"));
            }
            $telephone  =   $this->data['tel'];
         
            //=======================检测验证码=================
            $res = $this->checkMessage($telephone, $code, 4);
            //$res['status']  =1;
            //=======================检测验证码=================
            if($res['status']!=1){
                $this->ajaxReturn($res);
            }
            $this->ajaxReturn(array('status'=>1));   
        }
        
    }
    public function daikuando2(){
         if(IS_AJAX){
            $zhiye   =   I('post.zhiye');
            $is_house    =   I('post.is_house');
            $is_car    =   I('post.is_car');
            $wage    =   I('post.wage');
            $datas   =   session('apply');
            /* dump($datas); */
            if(empty($datas)){
                 $this->ajaxReturn(array(status=>0,'info'=>'申请失败'));
            }
            $city   =   session('city');
            
            $cate_id    =   M('loan')->where(array('id'=>$datas['card_id']))->getField('cate_id');
            
            //查询用户信息
		$u = M('member')->where(array('id'=>$this->user_id))->find();
		if(!$u){
			$this->ajaxReturn(array('status'=>2,'info'=>'用户信息查询失败,请稍后重试'));exit;
		}
		
/* 		
		if(!$u['realname'] || !$u['telephone']){
			$this->ajaxReturn(array('status'=>2,'info'=>'请完善真实姓名和电话'));exit;
		} */
		
		//查询产品信息
		$p = M('loan')->where(array('id'=>$datas['card_id']))->find();
		if(!$p){
			$this->ajaxReturn(array('status'=>2,'info'=>'产品信息查询失败,请稍后重试'));exit;
		}
		
		if(($u['identityid'] != $p['identityid']) || ($u['houseid'] != $p['houseid']) || ($u['carid'] != $p['carid'])){
			//$this->ajaxReturn(array('status'=>2,'info'=>'不符合申请条件'));exit;
		}
		
		$f = M('loan_order')->where(array('uid'=>$u['id'],'loanid'=>$p['id']))->find();
		if($f){
			$this->ajaxReturn(array('status'=>2,'info'=>'不要重复申请'));exit;
		}

		//unset($p['logo_pic']);unset($p['logo_pic1']);unset($p['yaoqiu']);unset($p['sqtj']);unset($p['sxcl']);unset($p['dnjh']);unset($p['dnjh']);
		
		
		//序列化订单信息
		$orderlon = array();
		$orderlon['id']           = $p['id'];
		$orderlon['cate_id']      = $p['cate_id'];
		$orderlon['catename']     = M('cate')->where(array('id'=>$p['cate_id']))->getField('classname');
		
		$orderlon['identityid']   = $p['identityid'];
		$orderlon['identityname'] = M('cate')->where(array('id'=>$p['identityid']))->getField('classname');
		$orderlon['houseid']      = $p['houseid'];
		$orderlon['housename']    = M('cate')->where(array('id'=>$p['houseid']))->getField('classname');
		$orderlon['carid']        = $p['carid'];
		$orderlon['carname']      = M('cate')->where(array('id'=>$p['carid']))->getField('classname');
		$orderlon['honourid']     = $p['honourid'];
		$orderlon['honourname']   = M('cate')->where(array('id'=>$p['honourid']))->getField('classname');
		
		
		$orderlon['title']        = $p['title'];
		$orderlon['starid']       = $p['starid'];
		$orderlon['money']        = $p['money'];
		$orderlon['money1']       = $p['money1'];
		$orderlon['dkqx']         = $p['dkqx'];
		$orderlon['dkqx1']        = $p['dkqx1'];
		$orderlon['ylx']          = $p['ylx'];
		$orderlon['yg']           = $p['yg'];
		$orderlon['fksj']         = $p['fksj'];
		$orderlon['lnsm']         = $p['lnsm'];
		$orderlon['city']         = $p['city'];
		$orderlon['supplier_id']  = $p['supplier_id'];
		
		//序列化个人信息
		$uinfo = array();
		$uinfo['id']              = $u['id'];
		$uinfo['personname']      = $u['personname'];
		$uinfo['realname']        = $u['realname'];
		$uinfo['telephone']       = $u['telephone'];
		$uinfo['month_money']     = $wage;
		
		
		$uinfo['identityid']      = $u['identityid'];
		$uinfo['identityname']    = $zhiye;
		$uinfo['houseid']         = $u['houseid'];
		$uinfo['housename']       = $is_house;
		$uinfo['carid']           = $u['carid'];
		$uinfo['carname']         = $is_car;
		//$uinfo['honourid']      = $u['honourid'];
		//$uinfo['honourname']    = $this->Cate_db->where(array('id'=>$u['honourid']))->getField('classname');
		
		
		
		//订单信息
		$data = array();
		$data['cate_id']         =$p['cate_id'];
		$data['orderlon']        =serialize($orderlon);
		$data['uid']             =$u['id'];
		$data['uinfo']           =serialize($uinfo);
		$data['money']           =$datas['money'];
		$data['qixian']          =$datas['qixian'];
		$data['rate']            =$datas['ylx'];
		$data['month_money']     =$p['yg'];
		$data['all_rate_money']  =$p['zlx'];
		$data['truename']        =$datas['name'];
		$data['telephone']       =$datas['tel'];
		$data['apply_at']        =time();
		$data['create_at']       =time();
		$data['city']            =$p['city'];
		$data['loanid']          =$p['id'];
		$data['supplier_id']     =$p['supplier_id'];
		$data['income']          =$wage;
        $data['card_id']          =$datas['card_id'];
        $data['fid']            =   $datas['shareuser_id'];
        
        $reset =  M('loan_order')->add($data);
    
           if($reset){
               M('loan')->where(array('id'=>$datas['card_id']))->setInc('sqrs');
               session('apply',null); 
               $this->ajaxReturn(array(status=>1));   
           }else{
                $this->ajaxReturn(array(status=>0,'info'=>'申请失败'));   
           }
           
        }
        
    }
     public function checkMessage($phone, $identify, $status){
        $over_time=C('over_time');
        $now_time=time();
        $sql=M('user_verify')->where(array('telephone'=>$phone,"status"=>0))->order('add_time desc')->find();
        if($sql['code']!=$identify){
            return array("status"=>'2',"info"=>"验证码错误");
        }
        $addtime=$sql['add_time'];
        if ($now_time<($addtime+$over_time)){
            $code=$sql['code'];
            if ($code==$identify){
                M('user_verify')->where(array('id'=>$sql['id']))->save(array("status"=>$status));
                return array("status"=>'1',"info"=>"ok");
            }else {
                return array("status"=>'2',"info"=>"验证码错误");
            }
        }else{
            M('user_verify')->where(array('id'=>$sql['id']))->save(array("status"=>9));
            return array("status"=>'3',"info"=>"验证码已过期");
        }
    }
}
?>