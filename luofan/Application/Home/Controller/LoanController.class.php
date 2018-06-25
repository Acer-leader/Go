<?php
namespace Home\Controller;
use Think\Controller;
class LoanController extends PublicController{
    
    public function _initialize(){
        parent::_initialize();
        $this->Loan_db       = M('Loan');  //贷款产品表
        $this->Cate_db       = M('cate');  //属性表
        $this->Loan_guwen_db = M('Loan_guwen');  //顾问
        $this->Article_db    = M('Article');  //文章
        $this->Loan_order_db = M('Loan_order');  //订单
        $this->Banner_db     = M('Banner');  //adv  广告
        $this->lontypeArr = array('single'=>1,'house'=>2,'car'=>3);  //贷款类别
        $this->lonsuccess = array('1'=>'listsloan','2'=>'houseloan','3'=>'carloan');  //贷款反类别
        $this->Attrtype   = array('identitys'=>4,'houses'=>10,'cars'=>18,'honours'=>21,'organ'=>30,'pledge'=>34,'repay'=>41);  //属性表类型ID
        $this->Articletypeid   = array('anlie'=>1,'gonglue'=>2,'cars'=>18,'honours'=>21);  //属性表类型ID
        $this->AttrAssign();  //显示属性
        $this->fenxiao_id  =   session('fenxiao_id');
        $fid =   I('get.fenxiao_id');
        $this->fid  =   session('fid',$fid);
       

        $this->assign('fenxiao_id',$this->fenxiao_id);
    }
    
    public function selectCityLon($lontype='single',$identityid='',$houseid='',$carid='',$honourid='',$city='',$organ='',$pledge='',$repay='',$limit=6,$is_organ="",$is_sale=1,$is_del=0){
        //dump($lontype);dump($identityid);dump($houseid);dump($carid);dump($honourid);dump($cityid);dump($limit);
        $this->checkLonType($lontype);
        $where = array();
        $where['cate_id'] = $this->cate_id;
        if(is_numeric($identityid) && $identityid!=0){
            $where['identityid'] = array('like',"%$identityid%");
        }
        if(is_numeric($houseid) && $houseid!=0){
            $where['houseid'] = array('like',"%$houseid%");
        }
        if(is_numeric($carid) && $carid!=0){
            $where['carid'] = array('like',"%$carid%");
        }
        if(is_numeric($honourid) && $honourid!=0){
            $where['honourid'] = array('like',"%$honourid%");
        }
        if(is_numeric($organ) && $organ != 0){
            $where['organid'] = array('like',"%$organ%");
        }
        if(is_numeric($pledge) && $pledge != 0){
            $where['pledgeid'] = array('like',"%$pledge%");
        }
        if(is_numeric($repay) && $repay != 0){
            $where['repayid'] = array('like',"%$repay%");
        }
        if($city){
            $where['city'] = $city;
        }
        if(!empty($is_organ) && is_numeric($organ) && $organ!=0){
            $where['organid'] = array(array('like',"%45%"),array('like',"%$organ%"),'and');
        }elseif(!empty($is_organ)){
            $where['organid'] = array('like',"%45%");
        }
        $where['is_sale'] = $is_sale;
        $where['is_del'] = $is_del;
        //哈哈  大神开始分页了
        $count = $this->Loan_db->where($where)->count();
        $page  = getpage($count,$limit);
        $show  = $page->show();
        $listsloanArr = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($page->firstRow.','.$page->listRows)->select(); // dump($lists);
        //dump($where);die;
        //$this->assign('count',$count);
        //$this->assign('listsloanArr',$listsloanArr);
        //$this->assign('page',$show);
        return array($count,$listsloanArr,$show);
    }
    
    public function showDetailLoan($id,$res){//查询单个商品
        $infoloan = $this->Loan_db->where(array('id'=>$id))->find(); //dump($infoloan);
        $infoloan['zlx'] = $infoloan['money']*$infoloan['dkqx']*$infoloan['ylx']/100;
        $infoloan['yg'] = ceil((($infoloan['money']+$infoloan['zlx'])/$infoloan['dkqx'])*100)/100;
        $xiaodai = '';
        if($infoloan['supplier_id']){
            $xiaodai = M('xiaodai')->where(array('id'=>$infoloan['supplier_id']))->getField('personname');
        }
        $title = str_replace('$title',$infoloan['title'],$res['title']);
        $title = str_replace('$xiao',$xiaodai,$title);
        
        $keywords = str_replace('$title',$infoloan['news_title'],$res['keywords']);
        $keywords = str_replace('$xiao',$xiaodai,$keywords);

        $des = mb_substr(strip_tags(htmlspecialchars_decode($infoloan['yaoqiu'])),0,40,'utf-8');
        $this->assign('title',$title);
        $this->assign('keywords',$keywords);
        $this->assign('des',$title);
        $this->assign('infoloan',$infoloan);
        return $infoloan;
    }
    
    
    public function selectGuwenLon($lontype='single',$limit=8){//顾问
        $this->checkLonType($lontype);
        $guwenlists = $this->Loan_guwen_db->where(array('cate_id'=>$this->cate_id))->order('sorts asc,id desc')->limit($limit)->select(); // dump($guwenlists);
        $this->assign('guwenlists',$guwenlists);
    }
    
    
    public function selectArticle($lontype='single',$typename='gonglue',$limit=6){
        $typeid = $this->Articletypeid[$typename] ? $this->Articletypeid[$typename] : $this->Articletypeid['gonglue'];  //dump($typeid);
        $this->checkLonType($lontype);//dump($this->cate_id);
        $articlelists = $this->Article_db->where(array('cate_id'=>$this->cate_id,'typeid'=>$typeid))->order('sorts asc,id desc')->limit($limit)->select();  //dump($articlelists);
        $this->assign('articlelists',$articlelists);
    }
    
    public function findArticle($id){
        $articlefind     = $this->Article_db->where(array('id'=>$id))->find();
        $uponarticlefind = $this->Article_db->where(array('cate_id'=>$articlefind['cate_id'],'typeid'=>$articlefind['typeid'],'id'=>array('lt',$id)))->order('id desc')->find();
        $downarticlefind = $this->Article_db->where(array('cate_id'=>$articlefind['cate_id'],'typeid'=>$articlefind['typeid'],'id'=>array('gt',$id)))->order('id asc')->find();
        $this->assign('articlefind',$articlefind);
        $this->assign('uponarticlefind',$uponarticlefind);
        $this->assign('downarticlefind',$downarticlefind);
    }
    
    public function checkLonType($lontype='single'){//取出贷款分类id
        $this->cate_id = $this->lontypeArr[$lontype] ? $this->lontypeArr[$lontype] : $this->lontypeArr['single'];
        $this->assign('cate_id',$this->cate_id);
    }
    
    public function AttrAssign(){//显示属性
        $this->assign('identitys',$this->AttrArr('identitys'));  //职业
        $this->assign('houses',$this->AttrArr('houses'));  //房产
        $this->assign('cars',$this->AttrArr('cars'));  //车
        $this->assign('honours',$this->AttrArr('honours'));  //信用
        $this->assign('organ',$this->AttrArr('organ'));  //信用
        $this->assign('pledge',$this->AttrArr('pledge'));  //信用
        $this->assign('repay',$this->AttrArr('repay'));  //信用
    }
    
    protected function AttrArr($attrtype='identitys'){//列出属性
        return $this->Cate_db->field('id,classname')->where(array('pid'=>$this->Attrtype[$attrtype],'isdel'=>0))->order('sort asc,id asc')->select();
    }

    /**
     * 20170902 验证会员是否登录
     */
    public function checkLogin()
    {
        if(!$_SESSION['user_id']){
            $this->ajaxReturn(array('status'=>0,'info'=>'失败'));exit;
        }

        //查询用户信息
        $u = M('member')->where(array('id'=>$_SESSION['user_id']))->find();
        if(!$u){
            $this->ajaxReturn(array('status'=>2,'info'=>'用户信息查询失败,请稍后重试'));exit;
        }


        if(!$u['realname'] || !$u['telephone']){
            $this->ajaxReturn(array('status'=>2,'info'=>'请完善真实姓名和电话'));exit;
        }

        if(empty($u['houseid']) || empty($u['carid']) || empty($u['month_money'])){
            $this->ajaxReturn(array('status'=>2, 'info'=>'请完善个人资产信息'));
        }
        $this->ajaxReturn(['status'=>1,'info'=>'已登录']);

    }

    /**20170829
     * 申请贷款产品
     * id: "{$infoloan.id}",//产品id
     * money: money,//贷款金额
     * qixian: qixian,//期限
     * ylx: ylx,//月利率
     * yg: yg.toFixed(2),//月供
     * zlx: zlx,//总利息
     * */
    public function order(){


        //查询用户信息
        $u = M('member')->where(array('id'=>$_SESSION['user_id']))->find();
        //查询产品信息
        $p = $this->Loan_db->where(array('id'=>I('id')))->find();
        if(!$p){
            $this->ajaxReturn(array('status'=>2,'info'=>'产品信息查询失败,请稍后重试'));exit;
        }
        $f = $this->Loan_order_db->where(array('uid'=>$u['id'],'loanid'=>$p['id']))->find();
        if($f){
            $this->ajaxReturn(array('status'=>2,'info'=>'您已申请，请不要重复申请'));exit;
        }
        //序列化订单信息
        $orderlon = array();
        $orderlon['id']           = $p['id'];//产品id
        $orderlon['cate_id']      = $p['cate_id'];//产品分类id
        $orderlon['catename']     = $this->Cate_db->where(array('id'=>$p['cate_id']))->getField('classname');//分类名称
        $orderlon['identityid']   = $p['identityid'];//职业身份
        $orderlon['identityname'] = $this->Cate_db->where(array('id'=>$p['identityid']))->getField('classname');//职业身份名称
        $orderlon['houseid']      = $p['houseid'];//房产
        $orderlon['housename']    = $this->Cate_db->where(array('id'=>$p['houseid']))->getField('classname');//房产名称
        $orderlon['carid']        = $p['carid'];//车
        $orderlon['carname']      = $this->Cate_db->where(array('id'=>$p['carid']))->getField('classname');//车描述
        $orderlon['honourid']     = $p['honourid'];//信用
        $orderlon['honourname']   = $this->Cate_db->where(array('id'=>$p['honourid']))->getField('classname');//信用描述
        $orderlon['title']        = $p['title'];//商品名称
        $orderlon['starid']       = $p['starid'];//星级
        $orderlon['money']        = $p['money'];//金额
        $orderlon['money1']       = $p['money1'];
        $orderlon['dkqx']         = $p['dkqx'];//期限
        $orderlon['dkqx1']        = $p['dkqx1'];
        $orderlon['ylx']          = $p['ylx'];//利息
        $orderlon['yg']           = $p['yg'];//月供
        $orderlon['fksj']         = $p['fksj'];//放款时间
        $orderlon['lnsm']         = $p['lnsm'];//利率说明,月管理费
        $orderlon['city']         = $this->city;//城市
        $orderlon['supplier_id']  = $p['supplier_id'];//小贷公司id
        
        //序列化个人信息
        $uinfo = array();
        $uinfo['id']              = $u['id'];           //用户id
        $uinfo['personname']      = $u['personname']?$u['personname']:I("name");//昵称
        $uinfo['realname']        = $u['realname'];//真实姓名
        $uinfo['telephone']       = $u['telephone']?$u['telephone']:I("tel");//手机号
        $uinfo['month_money']     = $u['month_money']?$u['month_money']:I("shouru");//月收入
        $uinfo['identityid']      = $u['identityid'];//职业身份
        $uinfo['identityname']    = $this->Cate_db->where(array('id'=>$u['identityid']))->getField('classname');//职业身份描述
        $uinfo['houseid']         = $u['houseid'];//房子
        $uinfo['housename']       = $this->Cate_db->where(array('id'=>$u['houseid']))->getField('classname')?$this->Cate_db->where(array('id'=>$u['houseid']))->getField('classname'):I("fangzi");//房子描述
        $uinfo['carid']           = $u['carid'];//车
        $uinfo['carname']         = $this->Cate_db->where(array('id'=>$u['carid']))->getField('classname')?$this->Cate_db->where(array('id'=>$u['carid']))->getField('classname'):I("chezi");//车描述

        //订单信息
        $data = array();
        $data['cate_id']         =  $p['cate_id'];//分类id
        $data['orderlon']        =  serialize($orderlon);//产品订单信息
        $data['uid']             =  $u['id'];//用户id
        $data['uinfo']           =  serialize($uinfo);//用户信息
        $data['money']           =  I('money');//贷款金额
        $data['qixian']          =  I('qixian');//贷款期限
        $data['rate']            =  I('ylx');//月利率
        $data['month_money']     =  I('yg');//月供
        $data['all_rate_money']  =  I('zlx');//总利息
        $data['truename']        =  $u['realname'];//用户真实姓名
        $data['telephone']       =  $u['telephone'];//用户手机号
        $data['apply_at']        =  time();//申请时间
        $data['create_at']       =  time();//创建时间
        $data['city']            =  $p['city'];//城市
        $data['loanid']          =  $p['id'];//产品id
        $data['supplier_id']     =  $p['supplier_id'];//小贷公司id
        $data['income']          =  $u['month_money'];//月收入
        $data['is_grab']         =  $p['is_grab'];//是否参与抢单 0不参与 1参与
//        $data['card_id']         =  I('card_id');//
        $data['guwen']          = I("param.guwen");
        if($this->fid){
            $data['fid']         =  $this->fid;
        }

        $id = $this->Loan_order_db->add($data);
        if($id){
            //添加成功
            $this->loan_shuaxin_log($id,$p['city'],$p['cate_id']);
            if($p['supplier_id']){
                //申请成功发送邮件
                $email = M("xiaodai")->where(array("id"=>$p['supplier_id']))->getField("email");
                sendEmail($email);
            }

            session('fid',null);
            $this->ajaxReturn(array('status'=>2,'info'=>'申请成功'));exit;
        }
        
        $this->ajaxReturn(array('status'=>0,'info'=>'申请失败,请重试'));exit;
    }
    
    
    //推送站内消息
    public function loan_shuaxin_log($loan_id,$city,$cate_id){
        
        $region_db = M('region');  //城市代码
        $xiaodai_db = M('xiaodai');  //小贷
        
        $grab_config_db = M('grab_config');  //小贷接收消息城市
        $grabf_config_db = M('grabf_config');  //小贷接收消息类型
        
        //查询城市代码
        $citynum = $region_db->where(array('shortname'=>$city,'leveltype'=>2))->getField('card'); // dump($citynum);
        if(!$citynum){
            return false;
        }
        
        $supplieridarr = $grab_config_db->where(array('city'=>$citynum))->getField('supplier_id',true); // dump($supplieridarr);//城市小贷
        $supplieridarrt = $grabf_config_db->where(array('type'=>array('like','%'.$cate_id.'%')))->getField('supplier_id',true);  //dump($supplieridarrt);//类型小贷
        sort($supplieridarr);sort($supplieridarrt);
        $arr = array_intersect($supplieridarrt,$supplieridarr); // dump($arr); //类型城市小贷
        if(!$arr){
            return false;
        }
        
        foreach($arr as $v){
            $supplier_id = $v;
            $supplier_name =$xiaodai_db->where(array('id'=>$supplier_id))->getField('personname');
            $loan_money=0;
            $log_info = '订单推送';
            //shuaxin_Log($loan_id,$loan_money,$log_info,$supplier_id,$supplier_name,$type=0);
            shuaxin_Log($loan_id,0,$log_info,$v,$supplier_name,4);
        }
    }
    
    public function ceShi(){
        $this->loan_shuaxin_log(7,'杭州',1);
    }
    
    public function advBanner($type=5,$limit=1){
        return $this->Banner_db->where(array('type'=>$type,'isdel'=>0))->order('sort asc,id desc')->limit($limit)->select();
    }
    
        //分享生成二维码
    public function sharedo(){

        if(IS_AJAX){
            $url        =   I('post.url');
          
            $card_id    =   I('post.card_id');
            $save_path	= "./Uploads/qrcode/";
            $pic    =   $this->qrcode($url,$save_path);

            $res    =   M('fenxiao_share')->where(array('card_id'=>$card_id,'fenxiao_id'=>$this->fenxiao_id))->find();
            if($res){
                $data   =   array('id'=>$res['id'],'addtime'=>time());
                $share  =   M('fenxiao_share')->save($data);
            }else{
                $data   =   array('url'=>$url,'pic'=>$pic,'fenxiao_id'=>$this->fenxiao_id,'card_id'=>$card_id,'addtime'=>time());
                $share  =   M('fenxiao_share')->add($data);
            }
               
            if($share){
                $this->ajaxReturn(array('status'=>1));
            }else{
                $this->ajaxReturn(array('status'=>0));
            }
           
        }
    }

    //生成二维码
    public function qrcode($qr_data="",$save_path="",$web_path="",$qr_level="",$qr_size="",$save_prefix=""){
        $save_path = $save_path?$save_path:"./Uploads/qrcode/";  //图片存储的路径
        $web_path = $web_path?$web_path:__ROOT__.'/Uploads/qrcode/';        //图片在网页上显示的路径
        $qr_data = $qr_data?$qr_data:'http://www.zetadata.com.cn/';
        $qr_level = $qr_level?$qr_level:'H';
        $qr_size = $qr_size?$qr_size:'4';
        $save_prefix = $save_prefix?$save_prefix:'unohacha_';
        if($filename = createQRcode($save_path,$qr_data,$qr_level,$qr_size,$save_prefix)){
            $pic = $web_path.$filename;
            $img = $save_path.$filename;
        }
    
        $image = new \Think\Image();   
        $image->open($img); 
        //水印
         $image->water('./Public/Iphone/images/water.jpg',5,100)->save($img,NULL,100,true);

        //$image = new \Think\Image();   
        //$image->open($img); 
        //水印
         //$image->water('./Public/Iphone/images/water.jpg',5)->save($img,NULL,100,true);
        // $image->text('123', './Public/Iphone/fonts/fontawesome-webfont.ttf', 12, $color = '#00000000',6, $offset = 0, $angle = 0)->save($img,NULL,100,true);
         //$image->text->save($img);
         //$image->text->save($img);
        //echo "<img src='".$pic."'>";
        return $pic;
    }  

}

?>