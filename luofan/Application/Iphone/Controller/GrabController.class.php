<?php
namespace Iphone\Controller;
use Think\Controller;
class GrabController extends PublicController{

    public function index(){
        $Loan_Loan_order = M('Loan_order');
        $Cate_db = M('cate');
        $Member_db  = M('member');
		
        $where= array(
            'is_del' => 0,
            'status' => array('in', array(0,1)) 
        );
		$type = I("get.type");
		if($type){
			$where['cate_id']=$type;
			$this->assign('type',$type);
		}

		$data = $Loan_Loan_order->where($where)->limit(16)->order('apply_at desc')->select();
		foreach($data as $key =>$val){
            $data[$key]['telephone'] = substr_replace($val['telephone'],'*****',3,5); 
            $data[$key]['truename'] = msubstr($val['truename'],0,1,"utf-8");
            $data[$key]['cate'] = $Cate_db->where(array('id'=>$val['cate_id']))->getField('classname');
			$member = $Member_db->field('houseid,carid')->where(array('id'=>$val['uid']))->find();
			$data[$key]['housename'] = $Cate_db->where(array('id'=>$member['houseid']))->getField('classname');
			$data[$key]['carname']   = $Cate_db->where(array('id'=>$member['carid']))->getField('classname');
            if($data[$key]['housename'] == NULL){
                $data[$key]['housename'] = '无房';
            }
            if($data[$key]['carname'] == NULL){
                $data[$key]['carname'] = '无车';
            }
		}
		$this->assign('cache',$data);

        $loan_config =  M("loan_config")->where(array("id" => 1))->find();
        $this->assign('loan', $loan_config);
		
		$memInfo = M('member')->where(array('id'=>$this->user_id))->find();
		if($memInfo){
			$xiaodaiInfo = M('xiaodai')->where(array('telephone'=>$memInfo['telephone']))->find();
			if($xiaodaiInfo){
				$this->assign("xiaodaiInfo",$xiaodaiInfo);
			}
		}
		
		

        $this->display();
    }
    public function checkregisterajax(){
        // 检查验证码
        $code = I('param.send_code','');
//        echo $code;
        $telephone     = I("post.telephone");
//        echo $telephone;
        $password      = I("post.password");
        if(!$telephone){
            $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
        }
        if(!$password){
            $this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
        }
        //检测验证码
        $res = checkMessage($telephone,$code, 5);
        if($res['status'] == 1){
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
			//添加小贷公司seo
			$company = I("post.company");
			$sql['title'] = $company;
			$sql['key'] = $company;
			$sql['des'] = $company;
			$sql['supplier_id'] = $_SESSION['supplier_id'];
			M('SmallSeo')->add($sql);
            $this->ajaxReturn(array('status'=>1,'info'=>'账号申请成功','url'=>U('Supplier/index/gyindex')));exit;

        }
        elseif($rs == 2)
        {
            $this->ajaxReturn(array('status'=>0,'info'=>'帐号已经存在!','url'=>''));exit;
        }
        else
        {
            $this->ajaxReturn(array('status'=>0,'info'=>'帐号已禁用~','url'=>''));exit;
        }
    }

    /**wzz 20170425
     * 发送短信验证码的方法
     * $telephone           手机号码
     * $code_type           短信验证码类型  1-注册   2-找回密码   3-验证码登录  4-首页免费申请 5小贷公司注册
     * verify_code          验证码
     * $m = M('member')
     */
    public function getMessage(){
        if(IS_AJAX){
            $m= M('Xiaodai');
            $telephone   = trim(I("post.telephone"));
            $code_type   = trim(I("post.code_type"));
            $verify_code = trim(I("post.verify_code"));

            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            if(!in_array($code_type,array('1','2','3','4','5'))){
                $this->ajaxReturn(array("status"=>0, "info"=>"参数有误！"));
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
}

?>