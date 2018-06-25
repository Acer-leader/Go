<?php
namespace Iphone\Controller;
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
	}
	
	public function selectCityLon($lontype='single',$identityid='',$houseid='',$carid='',$honourid='',$city='',$organ='',$pledge='',$repay='',$limit=6,$is_sale=1,$is_del=0){
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
		$infoloan['zlx'] = $infoloan['ylx'] * 100 * 12;
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
		$articlefind = $this->Article_db->where(array('id'=>$id))->find();
		
		
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
	
	public function order(){
		//file_put_contents(ACTION_NAME.'.txt',print_r(array($_POST,date('Y-m-d H:i:s',time())),true).PHP_EOL,FILE_APPEND);
		
		if(!$_SESSION['user_id']){
			$this->ajaxReturn(array('status'=>1,'info'=>'失败'));exit;
		}
		
		//查询用户信息
		$u = M('member')->where(array('id'=>$_SESSION['user_id']))->find();
		if(!$u){
			$this->ajaxReturn(array('status'=>2,'info'=>'用户信息查询失败,请稍后重试'));exit;
		}
		
		
		if(!$u['realname'] || !$u['telephone']){
			$this->ajaxReturn(array('status'=>2,'info'=>'请完善真实姓名和电话'));exit;
		}
		
		//查询产品信息
		$p = $this->Loan_db->where(array('id'=>I('id')))->find();
		if(!$p){
			$this->ajaxReturn(array('status'=>2,'info'=>'产品信息查询失败,请稍后重试'));exit;
		}
		
		if(($u['identityid'] != $p['identityid']) || ($u['houseid'] != $p['houseid']) || ($u['carid'] != $p['carid'])){
			//$this->ajaxReturn(array('status'=>2,'info'=>'不符合申请条件'));exit;
		}
		
		$f = $this->Loan_order_db->where(array('uid'=>$u['id'],'loanid'=>$p['id']))->find();
		if($f){
			$this->ajaxReturn(array('status'=>2,'info'=>'不要重复申请'));exit;
		}

		//unset($p['logo_pic']);unset($p['logo_pic1']);unset($p['yaoqiu']);unset($p['sqtj']);unset($p['sxcl']);unset($p['dnjh']);unset($p['dnjh']);
		
		
		//序列化订单信息
		$orderlon = array();
		$orderlon['id']           = $p['id'];
		$orderlon['cate_id']      = $p['cate_id'];
		$orderlon['catename']     = $this->Cate_db->where(array('id'=>$p['cate_id']))->getField('classname');
		
		$orderlon['identityid']   = $p['identityid'];
		$orderlon['identityname'] = $this->Cate_db->where(array('id'=>$p['identityid']))->getField('classname');
		$orderlon['houseid']      = $p['houseid'];
		$orderlon['housename']    = $this->Cate_db->where(array('id'=>$p['houseid']))->getField('classname');
		$orderlon['carid']        = $p['carid'];
		$orderlon['carname']      = $this->Cate_db->where(array('id'=>$p['carid']))->getField('classname');
		$orderlon['honourid']     = $p['honourid'];
		$orderlon['honourname']   = $this->Cate_db->where(array('id'=>$p['honourid']))->getField('classname');
		
		
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
		$uinfo['month_money']     = $u['month_money'];
		
		
		$uinfo['identityid']      = $u['identityid'];
		$uinfo['identityname']    = $this->Cate_db->where(array('id'=>$u['identityid']))->getField('classname');
		$uinfo['houseid']         = $u['houseid'];
		$uinfo['housename']       = $this->Cate_db->where(array('id'=>$u['houseid']))->getField('classname');
		$uinfo['carid']           = $u['carid'];
		$uinfo['carname']         = $this->Cate_db->where(array('id'=>$u['carid']))->getField('classname');
		//$uinfo['honourid']      = $u['honourid'];
		//$uinfo['honourname']    = $this->Cate_db->where(array('id'=>$u['honourid']))->getField('classname');
		
		
		
		//订单信息
		$data = array();
		$data['cate_id']         =$p['cate_id'];
		$data['orderlon']        =serialize($orderlon);
		$data['uid']             =$u['id'];
		$data['uinfo']           =serialize($uinfo);
		$data['money']           =I('money');
		$data['qixian']          =I('qixian');
		$data['rate']            =I('ylx');
		$data['month_money']     =I('yg');
		$data['all_rate_money']  =I('zlx');
		$data['truename']        =$u['realname'];
		$data['telephone']       =$u['telephone'];
		$data['apply_at']        =time();
		$data['create_at']       =time();
		$data['city']            =$p['city'];
		$data['loanid']          =$p['id'];
		$data['supplier_id']     =$p['supplier_id'];
		$data['income']          =$u['month_money'];
		
		$id = $this->Loan_order_db->add($data);
		if($id){
			$this->loan_shuaxin_log($id,$p['city'],$p['cate_id']);
            if($p['supplier_id']){
                //申请成功发送邮件
                $email = M("xiaodai")->where(array("id"=>$p['supplier_id']))->getField("email");
                sendEmail($email);
            }


			$this->ajaxReturn(array('status'=>2,'info'=>'申请成功,请在我的订单中心查看'));exit;
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

}

?>