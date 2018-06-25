<?php
namespace Home\Controller;
use Think\Controller;

class PublicController extends Controller {

    public $user_info  = "";
    public $user_id    = "";
    public $model_list = array();
    public $province   ="";
    public $city       ="";
    public $district   ="";


    public function _initialize(){
        //获取当前的url
        
        $canonical = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['QUERY_STRING'];
        $this->assign('canonical',$canonical);
        //=====================控制器 方法=====================================
        $this->assign("controller_name", CONTROLLER_NAME);
        $this->assign("action_name", ACTION_NAME);	
        //=====================控制器 方法=====================================
        
        //=========================网站配置====================================
        //$web_config = S("web_config");
        if(empty($web_config)){
            $web_config  = M('web_config')->order("id DESC")->find();
            S("web_config", $web_config, array('expire'=>60*60));
        }
        $this->web_config = $web_config;
        $this->assign('web_config', $web_config);
        //=========================网站配置====================================
        if($_SESSION['user_id']>0){
            $user_info = M("member")->where(array("id"=>$_SESSION['user_id'],"status"=>0, "isdel"=>0))->find();
            if($user_info){
                $this->user_info = $user_info;
                $this->user_id   = $_SESSION['user_id'];
            }else{
                session("user_id", null);
                $this->user_info = "";
                $this->user_id   = "";
            }
            $res = $this->getTopInfo();
            $this->assign("topInfo", $res);
        }

        

        /*根据IP获取省市区*/
        $json = GetIpLookup();
        $this->province = $json['province'];
        $this->city     = $json['city'];
        $this->district = $json['district'];
        //底部导航列表
        $footer = $this->get_footer();
        $this->assign('footer',$footer);
        //城市
        $r = M('region');
        $mmap['diqu']=array('in',array(1,2,3,4));
        $diqu = M('Diqu')->select();
        $tuijian = $r->where($mmap)->select();
        $this->assign('tj',$tuijian);
        $this->assign('diqu',$diqu);
        $sheng = $r->where(array('leveltype'=>1))->select();
        foreach ($sheng as $key => $value) {
            $sheng[$key]['city']=$r->where(array('parentid'=>$value['card'],'leveltype'=>2))->select();
        }
        $this->assign('shengcache',$sheng);

        //用户选择的城市
        $chengshi = I("get.city");
        $cityres="";
        $cityres = $r->where(array('pinyin'=>$chengshi))->getField('shortname'); 
        if($cityres != " "){
            $_SESSION['city11'] = $cityres;
            $this->city = $_SESSION['city11'];
        }else{
            if($_SESSION['city11']){
                $this->city=$_SESSION['city11'];
            }
        }
        //$this->city='杭红';
        
        
        $controller = CONTROLLER_NAME;
        $action     = ACTION_NAME;
        $title = '';
        $keywords = '';
        $des ='';
        //找出相应的seo
        $res = M('SeoView')->where(array('controller'=>$controller,'function'=>$action))->find();
        if($res){
            $title = str_replace('$city',$this->city,$res['title']);
            $keywords = str_replace('$city',$this->city,$res['keywords']);
            $des = str_replace('$city',$this->city,$res['description']);
        }else{
            $title = '洛凡金融';
            $des  = '洛凡金融';
            $keywords = '洛凡金融';
        }
        
        
        $cy = M('Region')->where(array('shortname'=>$this->city))->find();
        $cc = $cy['pinyin'];
        $this->assign('cc',$cc);
        
        
        $this->assign('des',$des);
        $this->assign('keywords',$keywords);
        $this->assign('title',$title);
        $this->assign('city',$this->city);

    }

    /**wzz 20170425
     * 生成验证码
     */
    public function createCode(){
        ob_clean();
        $Verify = new \Think\Verify();
        $Verify->fontSize = 30;
        $Verify->length   = 4;
        $Verify->useNoise = false;
        $Verify->codeSet = '0123456789';
        $Verify->imageW = 250;
        $Verify->imageH = 100;
        //$Verify->expire = 600;
        $Verify->entry();
    }

/**wzz 20170425
 * 发送短信验证码的方法
 * $telephone           手机号码
 * $code_type           短信验证码类型  1-注册   2-找回密码   3-验证码登录  4-首页免费申请 15-贷款申请  
 * verify_code          验证码
 * $m = M('member')
 */
public function getMessage(){
    if(IS_AJAX){
        $m= M('member');
        $telephone   = trim(I("post.telephone"));
        $code_type   = trim(I("post.code_type"));
        $verify_code = trim(I("post.verify_code"));
        if(!$telephone){
            $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
        }
        if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
            $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
        }
        if(!in_array($code_type,array('1','2','3','4','15'))){
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

    
    /**
     * 底部导航
     */
    public function get_footer(){
        $footer = M("footer_cate")->where(array('isdel'=>0))->order('sort asc')->select();
        foreach($footer as $k=>$val){
            $footer[$k]['data'] = M("footer_url")->where(array('cate_id'=>$val['id'],'isdel'=>0))->select();
        }
        return $footer;
    }
    /**
     * 检测用户登录的方法
     */
    public function checkLogin(){
        if($_SESSION['user_id']>0){
            if($this->user_info['status']){
                unset($_SESSION);
                $this->redirect("Home/User/login");
            }
        }else{
            $this->redirect("Home/User/login");
        }
    }

    /**
     * 得到头部导航栏的方法
     */
    public function getTopInfo(){
        if($this->user_id){
            $data['id'] 		 = $this->user_info["id"];
            $data['person_name'] = $this->user_info["person_name"];
            return $data;
        }else{
            return array();
        }
    }

    /**
     * innoDB数据库一次性提交事务的方法
     */
    public function commitAll(){
        foreach($this->model_list as $k=>$v){
            $this->model_list[$k]->commit();
        }
    }

    /**
     * innoDB数据库一次性回滚事务的方法
     */
    public function rollbackAll(){
        foreach($this->model_list as $k=>$v){
            $this->model_list[$k]->rollback();
        }
    }

    /**
     * 引导未知控制器
     */
    public function __call($method, $param){
        $this->redirect("Home/Index/index");die;
    }


    /**
     * 发送系统通知的方法
     * @param int     $userid    接受消息者的id
     * @param string  $msg       需要推送的消息
     * @param array   $data 	 需要修改的参数
     */
    public function sendSystemMessage($userid, $msg, $data=array()){
        $data["msg"]       = $msg;
        $data['user_id']   = $userid;
        $data['create_at'] = time();
        $res = M("systemMessage")->add($data);
        if($res){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 得到sku介绍的字符串
     */
    // private function getSkuDes($skuid){
    public function getSkuDes($skuid=""){
        if(!$skuid){

            return "";
        }
        $sku_l_m = M("sku_list");
        $sku_a_m = M("sku_attr");
        $skuids  = $sku_l_m->find($skuid);
        if(!$skuids){
            return "";
        }
        $sku_arr = array_filter(explode("-", $skuids['attr_list']));
        $str     = "";
        foreach($sku_arr as $v){
            $sku_info = $sku_a_m->where(array("id"=>$v))->find();
            $sku_pname = $sku_a_m->where(array("id"=>$sku_info['pid']))->getField('classname');
            $str .= $sku_pname.":".$sku_info['classname']."<br>";
        }
        return trim($str, "<br>");
    }
    public function error($msg="啊~哦~ 您要查看的页面不存在或已删除！"){
        $this->assign("error_msg", $msg);
        $this->display("Public:error");die;
    }



    public function order_error($msg="支付失败！"){
        $this->assign("error_msg", $msg);
        $this->display("Public:order_error");die;
    }

    public function order_success($order_no, $msg="支付成功！！"){
        $this->assign("error_msg", $msg);
        $this->assign("order_no",  $order_no);
        $this->display("Public:order_success");die;
    }

}