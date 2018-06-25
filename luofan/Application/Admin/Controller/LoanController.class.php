<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class LoanController extends CommonController {
    
    public function _initialize(){//设置数据库
        parent::_initialize();
        $this->Loan_db = M('Loan');
        $this->Cate_db = M('cate');
        $this->Article_db = M('Article');
        $this->Loan_guwen_db   = M('Loan_guwen');
        $this->Loan_Loan_order = M('Loan_order');
        $this->Member_db  = M('member');
        $this->Xiaodai_db  = M('xiaodai');
        $this->lontypeArr = array('single'=>1,'house'=>2,'car'=>3);
        $this->lonsuccess = array('1'=>'listsloan','2'=>'houseloan','3'=>'carloan');
        $this->Attrtype   = array('identitys'=>4,'houses'=>10,'cars'=>18,'honours'=>21,'bodyType'=>30,'MortgageType'=>34,'repayment'=>41);
        
    }
    
    /***个贷start**/
    public function listsloan(){
        $cate_id = 1;
        $where = array('a.cate_id'=>$cate_id,'a.is_del'=>0);
        
        $name = trim(I('name'));
        if($name){
            $where['a.title'] = array('like',"%$name%");
            $this->assign('name',$name);
        }
        //小贷公司id
        $supplier_id = I('get.supplier_id');
        if(!empty($supplier_id)){
            $where['a.supplier_id'] = ['neq',0];
        }else{
            $where['a.supplier_id'] = 0;
        }
        //小贷公司名称
        $supplier_name = trim(I('get.supplier_name'));
        if(!empty($supplier_name)){
            $where['b.personname'] = ['like',"%$supplier_name%"];
        }
        $count =  $this->Loan_db
            ->alias("a")
            ->join("left join app_xiaodai as b on a.supplier_id = b.id")
            ->where($where)
            ->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $data  = $this->Loan_db
            ->alias("a")
            ->join("left join app_xiaodai as b on a.supplier_id = b.id")
            ->where($where)
            ->field("a.*,b.personname as supplier_name")
            ->order('sorts asc,id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
//        foreach($data as $k=>$v){
//            $data[$k]['supplier_name']=$this->Xiaodai_db->where(array('id'=>$v['supplier_id']))->getField('personname');
//        }
        $this->assign('count',$count);
        $this->assign('cache',$data);
        $this->assign('page',$show);
        
        
        $this->assign('lontype','single');
        $this->display();
    }
    

       public function changeHot(){
      $id  = I("post.id");
      $hot = M("newsgonglue")->where(array("id"=>$id))->getField("is_hot");
      $hot = $hot?0:1;
      $res = M("newsgonglue")->where(array("id"=>$id))->setField(array("is_hot"=>$hot));
      if($res){
        $this->ajaxReturn(array('status'=>$hot?1:2,'info'=>'修改成功！'));
      }else{
        $this->ajaxReturn(array('status'=>0,'info'=>'修改失败！'));
      }
   }

    public function delnews(){
       $id=I("post.id");
       $res=M('newsgonglue')->where(array('id'=>$id))->delete();
       if($res){
           $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功"));
           return;
       }else{
           $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
           return;
       }
   }
    /***个贷end**/
    
    /***房贷start**/
    public function houseloan(){
        $cate_id = 2;
        
        $where = array('cate_id'=>$cate_id,'isdel'=>0);
        $name = I('name');
        if($name){
            $where['title'] = array('like',"%$name%");
            $this->assign('name',$name);
        }
        $count =  $this->Loan_db->where($where)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $data  = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($data as $k=>$v){
            $data[$k]['supplier_name']=$this->Xiaodai_db->where(array('id'=>$v['supplier_id']))->getField('personname');
        }
        $this->assign('count',$count);
        $this->assign('cache',$data);
        $this->assign('page',$show);
        
        $this->assign('lontype','house');
        $this->display('listsloan');
    }
    
    /***房贷end**/
    
    /***车贷start**/
    public function carloan(){
        $cate_id = 3;
        
        $where = array('cate_id'=>$cate_id,'isdel'=>0);
        $name = I('name');
        if($name){
            $where['title'] = array('like',"%$name%");
            $this->assign('name',$name);
        }
        $count =  $this->Loan_db->where($where)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $data  = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($data as $k=>$v){
            $data[$k]['supplier_name']=$this->Xiaodai_db->where(array('id'=>$v['supplier_id']))->getField('personname');
        }
        $this->assign('count',$count);
        $this->assign('cache',$data);
        $this->assign('page',$show);
        
        $this->assign('lontype','car');
        $this->display('listsloan');
    }
    
    /***车贷end**/
    
    
    /******添加修改删除statr******/
    public function addLoan(){
        $lontype = trim(I('lontype'));

        //城市信息
        $map = "leveltype=1 or leveltype=0";
        $provinceList = M("region")->where($map)->select();

        foreach($provinceList as $kk=>$vv){
            if($vv['card'] != 100000) {
                $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card']))->select();
            }
        }
        $this->assign('provinceList',$provinceList);

        if(IS_POST){
            $post = $_POST;
            if(!$post['id']){
                $data = array();
                $data = $_POST;
                $data['is_rate']   = $data['is_rate'] ? $data['is_rate'] : 0;
                $data['is_quik']   = $data['is_quik'] ? $data['is_quik'] : 0;
                $data['is_sxjb']   = $data['is_sxjb'] ? $data['is_sxjb'] : 0;
                $data['is_diya']   = $data['is_diya'] ? $data['is_diya'] : 0;
                $data['is_sbzksq'] = $data['is_sbzksq'] ? $data['is_sbzksq'] : 0;
                $data['is_grag']   = $data['is_grab'] ? $data['is_grab'] : 0;
                $data['create_time'] = time();
                $data['identityid'] = implode(',',$post['identityid']);
                $data['houseid'] = implode(',',$post['houseid']);
                $data['carid'] = implode(',',$post['carid']);
                $data['honourid'] = implode(',',$post['honourid']);
                $data['organid'] = implode(',',$post['organid']);
                $data['pledgeid'] = implode(',',$post['pledgeid']);
                $data['repayid'] = implode(',',$post['repayid']);
                $id = $this->Loan_db->fetchsql(false)->add($data);
                if($id){
                    $this->success("添加成功!",U('/Admin/Loan/'.$this->lonsuccess[$_POST['cate_id']]));exit;
                }
            }
            $this->error("添加失败!");exit;
            exit;
        }
        
        //$this->assign('lontype',$lontype);  //显示模块
        $this->checkLonType($lontype);  //显示分类id
        $this->AttrAssign();  //显示属性
        
        $this->display();
    }
    
    
    public function editloan(){
        $lontype = trim(I('lontype'));
        $id = trim(I('id'));
        $find  = $this->Loan_db->where(array('id'=>$id))->find();
        if(!$find){
            $this->error('没有找到产品');exit;
        }
        
        if(IS_POST){
            $data = array();
            $data = $_POST;
            //dump($data['yx']);exit;
            $data['is_rate']   = $data['is_rate'] ? $data['is_rate'] : 0;
            $data['is_quik']   = $data['is_quik'] ? $data['is_quik'] : 0;
            $data['is_sxjb']   = $data['is_sxjb'] ? $data['is_sxjb'] : 0;
            $data['is_diya']   = $data['is_diya'] ? $data['is_diya'] : 0;
            $data['is_sbzksq'] = $data['is_sbzksq'] ? $data['is_sbzksq'] : 0;
            $data['organid'] = implode(',',$data['organid']);
            $data['pledgeid'] = implode(',',$data['pledgeid']);
            $data['repayid'] = implode(',',$data['repayid']);
            //dump($data);exit;
            $id = $this->Loan_db->save($data);
            if($id){
                $this->success("修改成功!",U('/Admin/Loan/'.$this->lonsuccess[$_POST['cate_id']]));exit;
            }
            $this->error("修改失败!");exit;
        }
        
        $this->assign('find',$find);
        //$this->assign('lontype',$lontype);  //显示模块
        $this->checkLonType($lontype);  //显示分类id
        $this->AttrAssign();  //显示属性
        $this->display('addLoan');
    }
    
    
    public function delLoan(){
        $lontype = trim(I('lontype'));
        $id      = trim(I('id')); // dump($id);
        $res     = $this->Loan_db->where(array('id'=>$id))->delete();
        if($res){
            $this->success("删除成功!");exit;
        }else{
            $this->error("删除失败!");exit;
        }
        
    }
    
    /******添加修改删除end******/
    public function checkLonType($lontype='single'){
        $cate_id = $this->lontypeArr[$lontype];
        if(!$cate_id){
            $this->error('类型错误');
            exit;
        }
        $bank = M('card_type')->where(array('pid'=>15))->select();
        $this->assign('bank',$bank);
        $this->assign('cate_id',$cate_id);
        $this->assign('lontype',$lontype);
        return $cate_id;
    }
    
    
    public function AttrAssign(){
        //$identityArr = $this->AttrArr('identitys');  dump($identityArr);
        //$identityArr = $this->AttrArr('houses');  dump($identityArr);
        //$identityArr = $this->AttrArr('cars');  dump($identityArr);
        //$identityArr = $this->AttrArr('honours');  dump($identityArr);
        
        $this->assign('identitys',$this->AttrArr('identitys'));  //职业
        $this->assign('houses',$this->AttrArr('houses'));  //房产
        $this->assign('cars',$this->AttrArr('cars'));  //车
        $this->assign('honours',$this->AttrArr('honours'));  //信用
        $this->assign('bodyType',$this->AttrArr('bodyType')); //机构类型
        $this->assign('MortgageType',$this->AttrArr('MortgageType')); //抵押类型
        $this->assign('repayment',$this->AttrArr('repayment')); //还款方式
    }
    
    public function AttrArr($attrtype='identitys'){//身份4
        return $this->Cate_db->field('id,classname')->where(array('pid'=>$this->Attrtype[$attrtype],'isdel'=>0))->order('sort asc,id asc')->select();
    }
    
    
    /************顾问模块start******/
    public function Guwenlistsloan(){
        if(IS_AJAX){
            $id = I('post.id');
            $res = M('LoanGuwenView')->delete($id);
            if($res){

            }
        }
        $id = I('get.loan_id');
        if($id){
            $data['loan_id'] = I('get.loan_id');
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>'删除成功'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'删除失败'));
            }
        }
        $lontype = 'single';
        $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
        $lists = M('loan_guwen')->where($data)->order('sorts asc,id desc')->select();
        $guwens = M('loan_guwen')->where($data)->order('sorts asc,id desc')->select();

        $this->assign('lists',$lists);
        $this->assign('guwens',$guwens);
        $this->display();
    }
    public function Guwenlistsloans(){
        if(IS_AJAX){
            $data['guwen_id'] = I('post.guwen');
            $data['loan_id'] = I('post.loan_id');
            $res = M('GuwenLoan')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>'添加成功'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'添加失败'));
            }
        }
        $id = I('get.loan_id');
        $data['loan_id'] = $id;
        $lontype = 'single';
        $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
        $lists = M('LoanGuwenView')->where($data)->order('id desc')->select();
        $guwens = M('loan_guwen')->select();
        $this->assign('lists',$lists);
        $this->assign('guwens',$guwens);
        $this->display();
    }
    
    public function Addeditguwenlistsloan(){
        if(IS_AJAX){
            //file_put_contents(ACTION_NAME.'.txt',print_r(array($_POST,date('Y-m-d H:i:s',time())),true).PHP_EOL,FILE_APPEND);  // exit;
            $data = array();
            $data = I("post.");

            if($data['id']){
                $id = $this->Loan_guwen_db->save($data);
                if($id){
                    $this->ajaxReturn(array("status"=>0, "info"=>"修改成功！"));exit;
                }
            }else{
                $data['create_at']=time();
                $id = $this->Loan_guwen_db->add($data);
                if($id){
                    $this->ajaxReturn(array("status"=>0, "info"=>"新增成功！"));exit;
                }
            }
            
            $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));exit;
        }
    }
    
    public function Delguwenlistsloan(){
        $id = trim(I('id'));
        if(!$id){
            $this->error('删除失败');exit;
        }
        
        $id = $this->Loan_guwen_db->where(array('id'=>$id))->delete();
        if($id){
            $this->success("删除成功！");exit;
        }
        $this->error('删除失败');exit;
    }
    
    /************顾问模块end******/
    
    
    
    /*********案例攻略start**********/
    
    public function Articleloan(){
        $lontype = trim(I('lontype'));
        $lontype = $lontype ? $lontype : 'single';
        $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
        
        $typeid = trim(I('typeid'));
        $name = trim(I('name'));
        
        $where = array();
        $where['cate_id'] = $cate_id;
        
        if($typeid !=''){
            $where['typeid'] = $typeid;
            $this->assign('typeid',$typeid);
        }
        if($name){
            $where['title|author|source'] = array('like',"%$name%");
            $this->assign('name',$name);
        }
        
        $count =  $this->Article_db->where($where)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $lists = $this->Article_db->where($where)->order('sorts asc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('count',$count);
        $this->assign('lists',$lists);
        $this->assign('page',$show);
        
        $this->display();
    }
    
    public function Addeditarticleloan(){
        $lontype = trim(I('lontype'));
        $lontype = $lontype ? $lontype : 'single';
        $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
        if(IS_POST){
            $data = array();
            $data = $_POST;
            if(I('id')){
                $id = $this->Article_db->save($data);
                if($id){
                    $this->success("修改成功!",U('/Admin/Loan/Articleloan/lontype/single'));exit;
                }
            }else{
                $data['create_at']=time();
                $id = $this->Article_db->add($data);
                if($id){
                    $this->success("添加成功!",U('/Admin/Loan/Articleloan/lontype/single'));exit;
                }
            }
            $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));exit;
        }
        
        $id = trim(I('id'));
        $find = $this->Article_db->where(array('id'=>$id))->find();
        $this->assign('find',$find);
        $this->display();
    }
    
    public function Delarticleloan(){
        $id = trim(I('id'));
        if(!$id){
            $this->error('删除失败');
        }
        
        $id = $this->Article_db->where(array('id'=>$id))->delete();
        if($id){
            $this->success("删除成功！");exit;
        }
        $this->error('删除失败');
    }

     public function menPro(){
        $title = I('post.title');
        $like['title'] = array('like','%'.$title.'%');
        $problem = M('Problems')
                ->alias('a')
                ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                ->field('a.id,a.user_id,a.title,a.add_time,a.browse,a.reply,a.is_help,a.is_show,a.sort,b.person_name')
                ->where($like)
                ->order('sort desc')
                ->select();
        $data['reply'] = array('gt',0);
        $countB = M('Problems')->where($data)->count();
        $countA = M('Problems')->count();
        $countC = $countA - $countB;
        $this->assign('title',$title);
        $this->assign('countA',$countA);
        $this->assign('countB',$countB);
        $this->assign('countC',$countC);
        $this->assign('problem',$problem);
        $this->display();
    }
    public function menDel(){
        if(IS_GET){
            $id = I('get.id');
            $res = M('Problems')->delete($id);
            $ress = M('Answers')->where(array('p_id'=>$id))->delete();
            if($res){
                $this->redirect('Admin/Problem/Index');
            }else{
                $this->success('删除失败',U('Problem/Index/index'));
            }
        }
    }
    public function menSet(){
        if(IS_POST){
            $id = I('post.id');
            $data['sort'] = I('post.sort');
            $data['is_show'] = I('post.is_show');
            $res = M('Problems')->where(array('id'=>$id))->save($data);
            if($res){
                $this->redirect('Admin/Problem/index');
            }else{
                $this->success('修改失败！',U('Admin/Problem/edit',array('id'=>$id)));
            }
        }else{
            $id =   I('get.id');
            $res = M('Problems')
                    ->alias('a')
                    ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                    ->field('a.title,a.id,a.add_time,a.browse,a.reply,a.is_help,a.is_show,b.person_name')
                    ->where(array('a.id'=>$id))
                    ->find();
            $answer = M('Answers')->where(array('p_id'=>$id))->select();
        }
        $this->assign('answer',$answer);
        $this->assign('res',$res);
        $this->display();
    }
    public function housePro(){
        $title = I('post.title');
        $like['title'] = array('like','%'.$title.'%');
        $problem = M('Problemss')
            ->alias('a')
            ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
            ->field('a.id,a.user_id,a.title,a.add_time,a.browse,a.reply,a.is_help,a.is_show,a.sort,b.person_name')
            ->where($like)
            ->order('sort desc')
            ->select();
        $data['reply'] = array('gt',0);
        $countB = M('Problemss')->where($data)->count();
        $countA = M('Problemss')->count();
        $countC = $countA - $countB;
        $this->assign('title',$title);
        $this->assign('countA',$countA);
        $this->assign('countB',$countB);
        $this->assign('countC',$countC);
        $this->assign('problem',$problem);
        $this->display();
    }
    public function houseDel(){
        if(IS_GET){
            $id = I('get.id');
            $res = M('Problemss')->delete($id);
            $ress = M('Answerss')->where(array('p_id'=>$id))->delete();
            if($res){
                $this->redirect('Admin/Problem/Index');
            }else{
                $this->success('删除失败',U('Problem/Index/index'));
            }
        }
    }
    public function houseSet(){
        if(IS_POST){
            $id = I('post.id');
            $data['sort'] = I('post.sort');
            $data['is_show'] = I('post.is_show');
            $res = M('Problemss')->where(array('id'=>$id))->save($data);
            if($res){
                $this->redirect('Admin/Problem/index');
            }else{
                $this->success('修改失败！',U('Admin/Problem/edit',array('id'=>$id)));
            }
        }else{
            $id =   I('get.id');
            $res = M('Problemss')
                ->alias('a')
                ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                ->field('a.title,a.id,a.add_time,a.browse,a.reply,a.is_help,a.is_show,b.person_name')
                ->where(array('a.id'=>$id))
                ->find();
            $answer = M('Answerss')->where(array('p_id'=>$id))->select();
        }
        $this->assign('answer',$answer);
        $this->assign('res',$res);
        $this->display();
    }

    public function carpro(){
        $title = I('post.title');
        $like['title'] = array('like','%'.$title.'%');
        $problem = M('Problemsss')
            ->alias('a')
            ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
            ->field('a.id,a.user_id,a.title,a.add_time,a.browse,a.reply,a.is_help,a.is_show,a.sort,b.person_name')
            ->where($like)
            ->order('sort desc')
            ->select();
        $data['reply'] = array('gt',0);
        $countB = M('Problemsss')->where($data)->count();
        $countA = M('Problemsss')->count();
        $countC = $countA - $countB;
        $this->assign('title',$title);
        $this->assign('countA',$countA);
        $this->assign('countB',$countB);
        $this->assign('countC',$countC);
        $this->assign('problem',$problem);
        $this->display();
    }
    public function cardel(){
        if(IS_GET){
            $id = I('get.id');
            $res = M('Problemsss')->delete($id);
            $ress = M('Answersss')->where(array('p_id'=>$id))->delete();
            if($res){
                $this->redirect('Admin/Problem/Index');
            }else{
                $this->success('删除失败',U('Problem/Index/index'));
            }
        }
    }
    public function carset(){
        if(IS_POST){
            $id = I('post.id');
            $data['sort'] = I('post.sort');
            $data['is_show'] = I('post.is_show');
            $res = M('Problemsss')->where(array('id'=>$id))->save($data);
            if($res){
                $this->redirect('Admin/Problem/index');
            }else{
                $this->success('修改失败！',U('Admin/Problem/edit',array('id'=>$id)));
            }
        }else{
            $id =   I('get.id');
            $res = M('Problemsss')
                ->alias('a')
                ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                ->field('a.title,a.id,a.add_time,a.browse,a.reply,a.is_help,a.is_show,b.person_name')
                ->where(array('a.id'=>$id))
                ->find();
            $answer = M('Answersss')->where(array('p_id'=>$id))->select();
        }
        $this->assign('answer',$answer);
        $this->assign('res',$res);
        $this->display();
    }
	
	/*********案例攻略end**********/
	
	
	
	/**********订单管理start**********/
	
	public function singleLoanOrder(){//个贷
	
		
		$lontype = trim(I('lontype'));
		$lontype = $lontype ? $lontype : 'single';
		$cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
		$where   = array();
		$where['cate_id'] = $cate_id;
		$name = I('name');
		if($name){
			$where['truename|telephone'] = array('like',"%$name%");
			$this->assign('name',$name);
		}
		$count = $this->Loan_Loan_order->where($where)->count();
		$Page  = getpage($count,10);
		$show  = $Page->show();
		$data = $this->Loan_Loan_order->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('apply_at desc')->select();
		foreach($data as $key =>$val){
			$member = $this->Member_db->field('houseid,carid')->where(array('id'=>$val['uid']))->find();
			// $data[$key]['housename'] = $this->Cate_db->where(array('id'=>$member['houseid']))->getField('classname');
			// $data[$key]['carname']   = $this->Cate_db->where(array('id'=>$member['carid']))->getField('classname');
			foreach ($val as $k => $v) {
				if($k == 'orderlon'){
					$order[$key]['orderlon'] = unserialize($v);
				}else{
					$order[$key][$k] = $v;
				}
			}
		}
        foreach($order as $k=>$v){
            $order[$k]['fenxiao']   =   M('fenxiao')->where(array('id'=>$v['fid']))->getField('personname');
        }
		$this->assign('count',$count);
		$this->assign('cache',$order);
		$this->assign('page',$show);
		
	
		$this->display();
		
	}
	
	public function editsingleloanorder(){//个贷
	
		
		$lontype = trim(I('lontype'));
		$lontype = $lontype ? $lontype : 'single';
		$cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
		
		$where['cate_id'] = $cate_id;
		
		$id = I('id');
		
		if(!$id){
			$this->error('查询失败');
		}
		
		$info = $this->Loan_Loan_order->where(array('id'=>$id))->find();
		$info['orderloninfo'] =unserialize($info['orderlon']);
		$info['supplier_name']=$this->Xiaodai_db->where(array('id'=>$info['supplier_id']))->getField('personname');
		$info['one_name']=$this->Xiaodai_db->where(array('id'=>$info['oneid']))->getField('personname');
		$info['two_name']=$this->Xiaodai_db->where(array('id'=>$info['twoid']))->getField('personname');
		$info['three_name']=$this->Xiaodai_db->where(array('id'=>$info['threeid']))->getField('personname');
		
		$this->assign('info',$info);
	
		$this->display();
		
	}
	
	
	public function houseloanorder(){
		$lontype = trim(I('lontype'));
		$lontype = $lontype ? $lontype : 'house';
		$cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
		$where   = array();
		$where['cate_id'] = $cate_id;
		$name = I('name');
		if($name){
			$where['truename|telephone'] = array('like',"%$name%");
			$this->assign('name',$name);
		}
		
		$count = $this->Loan_Loan_order->where($where)->count();
		$Page  = getpage($count,10);
		$show  = $Page->show();
		$data = $this->Loan_Loan_order->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('apply_at desc')->select();
		foreach($data as $key =>$val){
			// $member = $this->Member_db->field('houseid,carid')->where(array('id'=>$val['uid']))->find();
			// $data[$key]['housename'] = $this->Cate_db->where(array('id'=>$member['houseid']))->getField('classname');
			// $data[$key]['carname']   = $this->Cate_db->where(array('id'=>$member['carid']))->getField('classname');
			foreach ($val as $k => $v) {
				if($k == 'orderlon'){
					$order[$key]['orderlon'] = unserialize($v);
				}else{
					$order[$key][$k] = $v;
				}
			}
		}
        foreach($order as $k=>$v){
            $order[$k]['fenxiao']   =   M('fenxiao')->where(array('id'=>$v['fid']))->getField('personname');
        }
		$this->assign('count',$count);
		$this->assign('cache',$order);
		$this->assign('page',$show);
		
		$this->display('singleLoanOrder');
	}
	
	
	public function carloanorder(){
		$lontype = trim(I('lontype'));
		$lontype = $lontype ? $lontype : 'car';
		$cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
		$where   = array();
		$where['cate_id'] = $cate_id;
		$name = I('name');
		if($name){
			$where['truename|telephone'] = array('like',"%$name%");
			$this->assign('name',$name);
		}
		
		$count = $this->Loan_Loan_order->where($where)->count();
		$Page  = getpage($count,10);
		$show  = $Page->show();
		$data = $this->Loan_Loan_order->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('apply_at desc')->select();
		foreach($data as $key =>$val){
			// $member = $this->Member_db->field('houseid,carid')->where(array('id'=>$val['uid']))->find();
			// $data[$key]['housename'] = $this->Cate_db->where(array('id'=>$member['houseid']))->getField('classname');
			// $data[$key]['carname']   = $this->Cate_db->where(array('id'=>$member['carid']))->getField('classname');
			foreach ($val as $k => $v) {
				if($k == 'orderlon'){
					$order[$key]['orderlon'] = unserialize($v);
				}else{
					$order[$key][$k] = $v;
				}
			}
		}
        foreach($order as $k=>$v){
            $order[$k]['fenxiao']   =   M('fenxiao')->where(array('id'=>$v['fid']))->getField('personname');
        }
		$this->assign('count',$count);
		$this->assign('cache',$order);
		$this->assign('page',$show);
		
		$this->display('singleLoanOrder');
	}
	
	
	/*
	
		$this->Loan_db = M('Loan');
		$this->Cate_db = M('cate');
		$this->Article_db = M('Article');
		$this->Loan_guwen_db   = M('Loan_guwen');
		$this->Loan_Loan_order = M('Loan_order');
	
	*/
	
	
	
	/**********订单管理end**********/

    /**
     * 热评户型
     */
    public function house(){
        $attr_A = M('house')->select();
        // dump($attr_A);exit;
        $this->assign('attr_A',$attr_A);
        $this->display();
    }
    public function house_add(){
        if (IS_AJAX){
            $id = I('post.id');
            $data = I('post.');
            unset($data['id']);
            $c = M('house');
            if (!$id){
                $data['create_at'] = time();
                //不存在ID添加
                $res = $c->add($data);
            }else{
                $r_c = $c->where(array('id'=>$id))->find();
                if(!$r_c){
                    $this->ajaxReturn(array("status"=>0,"info"=>'此类型不存在!'));
                }
                $res = $c->where(array('id'=>$id))->save($data);
            }
            if($res !== false){
                $this->ajaxReturn(array('status'=>1,"info"=>$id?'修改成功!':'添加成功!'));
            }else{
                $this->ajaxReturn(array('status'=>0,"info"=>$id?'修改失败!':'添加失败!'));
            }
        }
    }

    public function house_del(){
        if(IS_AJAX){
            $id = I('post.id');
            $c = M('house');
            $res = $c -> where(array('id'=>$id))->find();
            if (!$res){
                $this->ajaxReturn(array('status'=>0,"info"=>'此户型不存在!'));
            }
            $res_del = $c->where(array('id'=>$id))->delete();
            if ($res_del){
                $this->ajaxReturn(array('status'=>1,"info"=>'删除成功!'));
            }else{
                $this->ajaxReturn(array('status'=>0,"info"=>'删除失败!'));
            }
        }
    }

    /**
     *产品搜索配置*20170714*lq
     */
    public function searchConfig()
    {
        $type = I("get.type");
        if($type){
            $map["type"] = $type;
        }else{
            $map["type"] = 1;
        }
        //查询搜索区间
        $limit_list = M("search_config")->where($map)->select();
        $this->assign("limit_list",$limit_list);

        if(IS_AJAX){
            $data = I("post.");
            if(!$data['start']){
                $this->ajaxReturn(array("status"=>0,"info"=>"请填写起始值"));
            }
            if(!$data['end']){
                $this->ajaxReturn(array("status"=>0,"info"=>"请填写终止值"));
            }
            if(!$data['type']){
                $this->ajaxReturn(array("status"=>0,"info"=>"请填写类型"));
            }
            $data["add_time"] = time();
            if($data["id"]){
                $res = M("search_config")->where(array("id"=>$data["id"]))->save($data);
            }else{
                $res = M("search_config")->add($data);
            }
            if($res !== false){
                $this->ajaxReturn(array("status"=>1,"info"=>$data['id']?"修改成功":"添加成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>$data['id']?"修改失败":"添加失败"));
            }
        }
        $this->display();
    }


    public function delSearch(){
        $m = M("search_config");
        if(IS_AJAX){
            $id  = I("ids");
            $ids = array_filter(explode("-", $id));
            if(empty($ids)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请选择配置！"));
            }
            foreach($ids as $v){
                $res = $m->where(array("id"=>$v))->delete();
                if($res === false){
                    $this->ajaxReturn(array("status"=>0, "info"=>"删除配置失败！"));
                }
            }
            $this->ajaxReturn(array("status"=>1, "info"=>"删除配置成功！"));
        }
        $id  = I("id");
        if(!$id){
            $this->ajaxReturn(array("status"=>0, "info"=>"请选择配置！"));
        }
        $res = $m->where(array("id"=>$id))->delete();
        if($res!==false){
            $this->success("删除成功！");die;
        }
        $this->error("删除失败！");die;
    }
    
     //查询用户贷款申请状态
    public function isapply(){
            $tel    =   I('post.telephone');
            $url="http://cps.ppdai.com/bd/GetUserListing";
            $data = "ChannelId=%s&SourceId=%s&token=%s&phone=%s&sign=%s";
            $ChannelId  =   212;
            $SourceId   =   372;
            $token      =   '293764805a1c4c6a9a2189f4e89fb52a'; 
            $phone      =   $tel;
            $string     =   'token='.$token.'&phone='.$phone;
            $paramMd5Str=   MD5($string);
            $sign       =   MD5($string.'&paramMd5='.$paramMd5Str );
            $rdata = sprintf($data, $ChannelId, $SourceId, $token, $phone,$sign);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$rdata);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($ch);
            curl_close($ch);
            $content    =   json_decode($result);
            $this->ajaxReturn($result);
            //return $result;
     
    }
    public function changeStatus(){
        if(IS_AJAX){
            $id   = I("post.id");
            $item = I("post.item");
            $num  = I("post.num");
            $tab  = I("post.tab");
            $m    = M($tab);
            $res  = $m->where(array("id"=>$id))->find();
            if(!$res){
                $this->ajaxReturn(array("status"=>0 ,"info"=>"修改失败1！"));
            }
                $this->getmoney($res);
            $data[$item] = $num;// `status` tinyint(1) DEFAULT '0' COMMENT '状态0未处理,1处理中,2通过,3不通过,',
            $res2        = $m->where(array('id'=>$id))->save($data);
            if($res2){
                if($tab=='loan_order' && $num==2 && $item='status')
                {
                    $orderinfo = $m->field('id,cate_id,loanid,orderlon')->where(array('id'=>$id))->find();
                    if($orderinfo['cate_id'] && $orderinfo['loanid'])
                    {
                        $id = M('Loan')->where(array('id'=>$orderinfo['loanid'],'cate_id'=>$orderinfo['cate_id']))->setInc('sqrs');
                    }
                }
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0 ,"info"=> "修改失败2！"));
        }
    }
    //提成金额计算
    public function getmoney($res){
    
            $dkje   =   $res['money'];//贷款金额
            $fenxiaoid    =   $res['fid'];//分销商id 
            if($fenxiaoid){
                $card   =   M('loan')->where(array('id'=>$res['card_id']))->find();
                
                $pushtype   =   $card['pushtype'];//提成方式
                $push       =   $card['push'];//提成金额
                if(!$pushtype){
                    $money  =   $push;
                }else{
                    $money  =   $dkje*$push;
                }
                M('fenxiao')->where(array('id'=>$fenxiaoid))->setInc('wallet',$money); 
                    $map   =   array(
                            'title' =>  '发放工资',
                            'pay'   =>  '+'.$money.'元',
                            'addtime'   =>  time(),
                            'fenxiao_id'   =>  $fenxiaoid,     
                    );
                    M('fenxiao_bill')->add($map);
                $fid          =   M('fenxiao')->where(array('id'=>$fenxiaoid))->getField('fid');//上级分销商id
                if($fid){
                    $pushtype1  =   $card['pushtype1'];//上级提成方式
                    $push1      =   $card['push1'];//上级提成金额
                    if(!$pushtype1){
                        $money1  =   $push1;
                    }else{
                        $money1  =   $dkje*$push1;
                    }
                    M('fenxiao')->where(array('id'=>$fid))->setInc('wallet',$money1); 
                        $map   =   array(
                            'title' =>  '发放工资',
                            'pay'   =>  '+'.$money1.'元',
                            'addtime'   =>  time(),
                            'fenxiao_id'   =>  $fid,     
                        );
                        M('fenxiao_bill')->add($map);
                }
            }
    }

    //个贷攻略
    public function persongonglue(){
        //贷款攻略
        $where=array();
        $where['cate_id']=37;
        $where['cate_pid']=93;
        $this->assign("pid",$where['cate_pid']);
        $db=M("newsgonglue");
        $list=$db->where($where)->select();
        $count=count($list);
        $p=getpage($count,10);
        $page=$p->show();
        $list=array_slice($list,$p->firstRow,$p->listRows);
        $this->assign('cache',$list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"个贷攻略");
        $this->display();

    }
    //房贷攻略
    public function housegonglue(){
        //贷款攻略
        $where=array();
        $where['cate_id']=37;
        $where['cate_pid']=91;
        $this->assign("pid",$where['cate_pid']);
        $db=M("newsgonglue");
        $list=$db->where($where)->select();
        $count=count($list);
        $p=getpage($count,10);
        $page=$p->show();
        $list=array_slice($list,$p->firstRow,$p->listRows);
        $this->assign('cache',$list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"房贷攻略");
        $this->display();

    }
    //车贷攻略
    public function cargonglue(){
        //贷款攻略
        $where=array();
        $where['cate_id']=37;
        $where['cate_pid']=92;
        $this->assign("pid",$where['cate_pid']);
        $db=M("newsgonglue");
        $list=$db->where($where)->select();
        $count=count($list);
        $p=getpage($count,10);
        $page=$p->show();
        $list=array_slice($list,$p->firstRow,$p->listRows);
        $this->assign('cache',$list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"车贷攻略");
        $this->display();

    }
    public function addnews(){
        $m  = M('newsgonglue');
        $id = intval(I('param.id'));
        $pid=I("get.pid");
        // $cateList = M("News_cate")->field('id,classname')->where(array("pid"=>$pid))->order('sort asc')->select();
        // if(!$cateList){
        //     $cateList = array(M("News_cate")->field('id,classname')->where(array("id"=>$pid))->find());
        // }
        if($pid==93){
            $cateList[0]=array("classname"=>"个贷攻略","id"=>93);
        }else if($pid==91){
            $cateList[0]=array("classname"=>"房贷攻略","id"=>91);
        }else if($pid==92){
            $cateList[0]=array("classname"=>"车贷攻略","id"=>92);
        }
        $this->assign('cateList',$cateList);
        $this->assign('pid',$pid);
        if(IS_POST){
            $data=array(
                'news_title'=> I("post.news_title"),
                'detail'    => I("post.detail"),
                'is_hot'    => I("post.is_hot"),
                'sort'      => I("post.sort"),
                'cate_name' => I("post.cate"),
                'cate_pid'  => I("post.cate_id"),
                'cate_id'   => 37,
                'author'    => I("post.author")?I("post.author"):'佚名',
                'source'    => I("post.source"),
                'ads'       => I("post.ads"),  //位置
            );
            
            // if($data['cate_pid']){
            //     $data['cate_id'] = M("news_cate")->where(array('id'=>$data['cate_pid']))->getField('pid');
            //     if(!$data['cate_id']){
            //         unset($data['cate_pid']);
                    
            //     }
            // }
            $index = strpos($data['detail'],"/");
            if(!$index){
                $index = strpos($data['detail'],"。");
                if(!$index){
                    $index = strpos($data['detail'],"，");
                }
            }
            $data['thumb_detail'] = substr($data['detail'],0,$index+6);
            $logo_pic= I("post.logo_pic");
            $detail  = I("post.detail");
            if($logo_pic){
                $data['logo_pic']=$logo_pic;
            }
            if($detail){
                $data['detail']=$detail;
            }
            if($id){
                $data['edit_time'] = time();
                $res = $m->where(array('id'=>$id))->save($data);
            }else {
                $data['add_time'] = time();
                $res = $m->add($data);
            }
            $action = array(
                '91'=>'housegonglue',
                '92'=>'cargonglue',
                '93'=>'persongonglue',
            );
            $action = $action[$pid];
            if(!$action)
                $action = 'news';
            if($res){
                $this->success("操作成功",U('/Admin/loan/'.$action));exit;
            }else{
                $this->success("操作失败");exit;
            }
        }
        $pid=I("get.pid");
        if(!empty($id)){
            $info = $m->find($id);
            $this->assign('cache',$info);
        }
        $this->display();
    }

}
