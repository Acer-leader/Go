<?php
namespace Supplier\Controller;
use Supplier\Common\Controller\CommonController;
class XloanController extends CommonController
{

    public function _initialize()
    {//设置数据库
        parent::_initialize();
        $this->Loan_db = M('Loan');
        $this->Cate_db = M('cate');
        $this->Article_db = M('Article');
        $this->Loan_guwen_db = M('Loan_guwen');
        $this->lontypeArr = array('single' => 1, 'house' => 2, 'car' => 3);
        $this->lonsuccess = array('1' => 'listsloan', '2' => 'houseloan', '3' => 'carloan');
        $this->Attrtype = array('identitys' => 4, 'houses' => 10, 'cars' => 18, 'honours' => 21,'bodyType'=>30,'MortgageType'=>34,'repayment'=>41);

    }


    public function zhiding()
    {
        if (IS_AJAX) {
            $id = trim(I('param.id'));
            if (!$id) {
                $this->ajaxReturn(array("status" => 0, "info" => "请选择商品！"));
                exit;
            }
            $pz_mf = M('virtual_config');
            $peizhif = $pz_mf->find();
            $xcount = M('xiaodai')->where(array('id' => $_SESSION['supplier_id']))->find();
            $zongprice = $peizhif['top'];
            if ($xcount['wallet'] < $zongprice) {
                $this->ajaxReturn(array("status" => 0, "info" => "金额不够,添加失败！"));
                exit;
            }
            M()->startTrans();
            $row = $this->Loan_db->where(array('id' => $id))->find();
            $log_info = $_SESSION['supplier_name'] . '置顶商品:' . $row['title'];
            $type = 1;
            $logres = shuaxin_Log($row['id'], $peizhif['top'], $log_info, $_SESSION['supplier_id'], $_SESSION['supplier_name'], $type);
            if (!$logres) {
                M()->rollback();
                return array("status"=>0, "info"=>"记录日志失败！");
            }
            $data['is_top'] = 1;
            $res = $this->Loan_db->where(array('id' => $id))->save($data);
            if ($res === false) {
                M()->rollback();
                return array("status"=>0, "info"=>"置顶商品失败！");
            }
            $kou=M('xiaodai')->where(array('id'=>$_SESSION['supplier_id']))->setDec('wallet',$zongprice);
            if(!$kou){
                M()->rollback();
                return array("status"=>0, "info"=>"扣款失败！");
            }
            M()->commit();
            $this->ajaxReturn(array("status"=>1, "info"=>"置顶商品成功！"));
        }
    }

    /*
     * 刷新置顶商品
     */
    public function shuaxin()
    {
        if (IS_AJAX) {


            $id   = trim(I('param.id'));
            $ids=trim(I('param.ids'));

            if($ids){
                $ids = array_filter(explode("-", $ids));
                $gscount=count($ids);
            }
            if($id){
                $gscount=1;
            }
            if (empty($ids) && !$id) {
                $this->ajaxReturn(array("status" => 0, "info" => "请选择商品！"));
                exit;
            }

            $pz_mf = M('virtual_config');
            $peizhif = $pz_mf->find();
            $xcount = M('xiaodai')->where(array('id' => $_SESSION['supplier_id']))->find();
            $zongprice = $peizhif['refresh'] * $gscount;
            if ($xcount['wallet'] < $zongprice) {
                $this->ajaxReturn(array("status" => 0, "info" => "金额不够,添加失败！"));
                exit;
            }

            M()->startTrans();

            if($ids){
                foreach($ids as $v){
                   $result=$this->zhixingsx($v);
                    if($result['status']!=1){
                        $this->ajaxReturn($result);
                    }
                }
            }else{
                   $result=$this->zhixingsx($id);
                   if($result['status']!=1){
                    $this->ajaxReturn($result);
                   }
            }
            $kou=M('xiaodai')->where(array('id'=>$_SESSION['supplier_id']))->setDec('wallet',$zongprice);
            if(!$kou){
                M()->rollback();
                return array("status"=>0, "info"=>"扣款失败！");
            }
            M()->commit();
            $this->ajaxReturn(array("status"=>1, "info"=>"刷新商品成功！"));
        }
    }

    public function zhixingsx($v)
    {
        $data['update_time'] = time();
        $row = $this->Loan_db->where(array('id' => $v))->find();
        if (!$row) {
            M()->rollback();
            return array("status"=>0, "info"=>"没有找到这个商品！");
        }
        $pz_m=M('virtual_config');
        $peizhi=$pz_m->find();
        $log_info=$_SESSION['supplier_name'].'刷新商品:'.$row['title'];
        $type=0;
        $logres=shuaxin_Log($row['id'],$peizhi['refresh'],$log_info,$_SESSION['supplier_id'],$_SESSION['supplier_name'],$type);
        if(!$logres){
            M()->rollback();
            return array("status"=>0, "info"=>"记录日志失败！");
        }
        $res = $this->Loan_db->where(array('id'=>$v))->save($data);
        if($res === false){
            M()->rollback();
            return array("status"=>0, "info"=>"刷新商品失败！");
        }
        return array("status"=>1, "info"=>"刷新商品成功！");
    }



    /***个贷start**/
    public function listsloan()
    {
        $cate_id = 1;
        $where = array('cate_id' => $cate_id, 'isdel' => 0);
        $where['supplier_id'] = $_SESSION['supplier_id'];
        $count = $this->Loan_db->where($where)->count();
        $Page = getpage($count, 10);
        $show = $Page->show();
        $data = $this->Loan_db->where($where)->order('create_time asc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('count', $count);
        $this->assign('cache', $data);
        $this->assign('page', $show);
        $this->assign('lontype', 'single');
        $this->display();
    }

    /***个贷end**/

    /***房贷start**/
    public function houseloan()
    {
        $cate_id = 2;
        $where = array('cate_id' => $cate_id, 'isdel' => 0);
        $where['supplier_id'] = $_SESSION['supplier_id'];

        $count = $this->Loan_db->where($where)->count();
        $Page = getpage($count, 10);
        $show = $Page->show();
        $data = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('count', $count);
        $this->assign('cache', $data);
        $this->assign('page', $show);

        $this->assign('lontype', 'house');
        $this->display('listsloan');
    }

    /***房贷end**/

    /***车贷start**/
    public function carloan()
    {
        $cate_id = 3;
        $where = array('cate_id' => $cate_id, 'isdel' => 0);
        $where['supplier_id'] = $_SESSION['supplier_id'];

        $count = $this->Loan_db->where($where)->count();
        $Page = getpage($count, 10);
        $show = $Page->show();
        $data = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('count', $count);
        $this->assign('cache', $data);
        $this->assign('page', $show);

        $this->assign('lontype', 'car');
        $this->display('listsloan');
    }

    /***车贷end**/


    /******添加修改删除statr******/
    public function addLoan()
    {
        $map["leveltype"] = array(array("eq",0),array("eq",1),'or');
        $provinceList = M("region")->where($map)->select();

        foreach($provinceList as $kk=>$vv){
            if($vv['card'] != 100000) {
                $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card']))->select();
            }
        }

        //dump($provinceList);exit;
        $this->assign('provinceList',$provinceList);
        //当前登陆的小贷公司id
        $supplierId = $_SESSION['supplier_id'];
        $address_limit = M("xiao_authority")->where(array("user_id"=>$supplierId))->find();
        if(!empty($address_limit["address_city"]) && $address_limit["address_city"]!=100000){
            //存在地址限制
            $map["card"] = array("in",$address_limit["province"]);
            $provinceList = M("region")->where($map)->select();
            foreach($provinceList as $kk=>$vv){
                if($vv['card'] != 100000) {
                    $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card'],"card"=>array("in",$address_limit["address_city"])))->select();
                }
            }
        }

        $this->assign("dizhi",$address_limit['province']);
        $dizhi['pro'] = explode(',',$address_limit["province"]);
        $dizhi['city'] = explode(',',$address_limit["address_city"]);
        $this->assign("dizhi",$dizhi);
        //dump($provinceList);exit;
        $this->assign('provinceList',$provinceList);
        $this->assign("is_url",$address_limit["is_url"]);
        //查询该小贷公司的客服列表
        $service_list = M("small_config")->where(array("supplier_id"=>$this->supplier_id,"isdel"=>0))->select();
        $this->assign("service_list",$service_list);

        $lontype = trim(I('lontype'));
        $pcount = $this->Loan_db->where(array('supplier_id' => $_SESSION['supplier_id']))->count();
        $xcount = M('xiaodai')->where(array('id' => $_SESSION['supplier_id']))->find();
        if ($pcount >= $xcount['goods_limit']) {
            $this->error("已达到添加上限!");
            exit;
        }

        if (IS_POST) {
            if (!$_POST['id']) {
                $data = array();
                $data = $_POST;
                $data['is_rate'] = $data['is_rate'] ? $data['is_rate'] : 0;
                $data['is_quik'] = $data['is_quik'] ? $data['is_quik'] : 0;
                $data['is_sxjb'] = $data['is_sxjb'] ? $data['is_sxjb'] : 0;
                $data['is_diya'] = $data['is_diya'] ? $data['is_diya'] : 0;
                $data['is_sbzksq'] = $data['is_sbzksq'] ? $data['is_sbzksq'] : 0;
                $data['create_time'] = time();
                $data['city'] = trim($data["cityid"],',');
                $data['identityid'] = implode(',',$data['identityid']);
                $data['houseid'] = implode(',',$data['houseid']);
                $data['carid'] = implode(',',$data['carid']);
                $data['honourid'] = implode(',',$data['honourid']);
                $data['organid'] = implode(',',$data['organid']);
                $data['pledgeid'] = implode(',',$data['pledgeid']);
                $data['repayid'] = implode(',',$data['repayid']);
                //$data['update_time']=time();
                $data['supplier_id'] = $_SESSION['supplier_id'];
                $pz_m = M('virtual_config');
                $peizhi = $pz_m->find();
                if ($xcount['wallet'] < $peizhi['release_pro']) {
                    $this->error("金额不够,添加失败!");
                    exit;
                }
                $id = $this->Loan_db->add($data);
                if (!$id) {
                    $this->error("添加失败!");
                    exit;
                }

                $log_info = $_SESSION['supplier_name'] . '添加商品:' . $data['title'];
                $type = 3;
                $logres = shuaxin_Log($id, $peizhi['release_pro'], $log_info, $_SESSION['supplier_id'], $_SESSION['supplier_name'], $type);
                if (!$logres) {
                    $this->error("日志更新失败!");
                    exit;
                }
                $this->success("添加成功!", U('/Supplier/Xloan/' . $this->lonsuccess[$_POST['cate_id']]));
                exit;
            }
            $this->error("添加失败!");
            exit;
        }

        //$this->assign('lontype',$lontype);  //显示模块
        $this->checkLonType($lontype);  //显示分类id
        $this->AttrAssign();  //显示属性
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


    public function editloan()
    {

        //城市信息
        $map["leveltype"] = array(array("eq",0),array("eq",1),'or');
        $provinceList = M("region")->where($map)->select();

        foreach($provinceList as $kk=>$vv){
            if($vv['card'] != 100000) {
                $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card']))->select();
            }
        }
        //当前登陆的小贷公司id
        $supplierId = $_SESSION['supplier_id'];
        $address_limit = M("xiao_authority")->where(array("user_id"=>$supplierId))->find();
        if($address_limit["address_city"]){
            //存在地址限制
            $map["card"] = array("in",$address_limit["province"]);
            $provinceList = M("region")->where($map)->select();
            foreach($provinceList as $kk=>$vv){
                if($vv['card'] != 100000) {
                    $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card'],"card"=>array("in",$address_limit["address_city"])))->select();
                }
            }
        }
        $dizhi['pro'] = explode(',',$address_limit["province"]);
        $dizhi['city'] = explode(',',$address_limit["address_city"]);
        $this->assign("dizhi",$dizhi);
        $this->assign('provinceList',$provinceList);
        $this->assign("is_url",$address_limit["is_url"]);
        //dump($provinceList);exit;

        $lontype = trim(I('lontype'));
        $id = trim(I('id'));
        $find = $this->Loan_db->where(array('id' => $id, 'supplier_id' => $_SESSION['supplier_id']))->find();
        if (!$find) {
            $this->error('没有找到产品');
            exit;
        }

        if (IS_POST) {
            $data = array();
            $data = $_POST;
            //dump($data['yx']);exit;
            $data['is_rate'] = $data['is_rate'] ? $data['is_rate'] : 0;
            $data['is_quik'] = $data['is_quik'] ? $data['is_quik'] : 0;
            $data['is_sxjb'] = $data['is_sxjb'] ? $data['is_sxjb'] : 0;
            $data['is_diya'] = $data['is_diya'] ? $data['is_diya'] : 0;
            $data['is_sbzksq'] = $data['is_sbzksq'] ? $data['is_sbzksq'] : 0;
            $data['city'] = trim($data["cityid"],',');
            $data['identityid'] = implode(',',$data['identityid']);
            $data['houseid'] = implode(',',$data['houseid']);
            $data['carid'] = implode(',',$data['carid']);
            $data['honourid'] = implode(',',$data['honourid']);
            $data['organid'] = implode(',',$data['organid']);
            $data['pledgeid'] = implode(',',$data['pledgeid']);
            $data['repayid'] = implode(',',$data['repayid']);
            $id = $this->Loan_db->save($data);
            if ($id !== false) {
                $this->success("修改成功!", U('/Supplier/Xloan/' . $this->lonsuccess[$_POST['cate_id']]));
                exit;
            }
            $this->error("修改失败!");
            exit;
        }

        $this->assign('find', $find);
        //$this->assign('lontype',$lontype);  //显示模块
        $this->checkLonType($lontype);  //显示分类id
        $this->AttrAssign();  //显示属性
        $this->display('addLoan');
    }


    public function delLoan()
    {
        $lontype = trim(I('lontype'));
        $id = trim(I('id')); // dump($id);
        $res = $this->Loan_db->where(array('id' => $id, 'supplier_id' => $_SESSION['supplier_id']))->delete();
        if ($res) {
            $this->success("删除成功!");
            exit;
        } else {
            $this->error("删除失败!");
            exit;
        }

    }

    /******添加修改删除end******/
    public function checkLonType($lontype = 'single')
    {
        $cate_id = $this->lontypeArr[$lontype];
        if (!$cate_id) {
            $this->error('类型错误');
            exit;
        }
        $this->assign('cate_id', $cate_id);
        $this->assign('lontype', $lontype);
        return $cate_id;
    }


    public function AttrAssign()
    {
        //$identityArr = $this->AttrArr('identitys');  dump($identityArr);
        //$identityArr = $this->AttrArr('houses');  dump($identityArr);
        //$identityArr = $this->AttrArr('cars');  dump($identityArr);
        //$identityArr = $this->AttrArr('honours');  dump($identityArr);

        $this->assign('identitys', $this->AttrArr('identitys'));  //职业
        $this->assign('houses', $this->AttrArr('houses'));  //房产
        $this->assign('cars', $this->AttrArr('cars'));  //车
        $this->assign('honours', $this->AttrArr('honours'));  //信用
        $this->assign('bodyType',$this->AttrArr('bodyType')); //机构类型
        $this->assign('MortgageType',$this->AttrArr('MortgageType')); //抵押类型
        $this->assign('repayment',$this->AttrArr('repayment')); //还款方式
    }

    public function AttrArr($attrtype = 'identitys')
    {//身份4
        return $this->Cate_db->field('id,classname')->where(array('pid' => $this->Attrtype[$attrtype], 'isdel' => 0))->order('sort asc,id asc')->select();
    }


    /************顾问模块start******/
    public function Guwenlistsloan()
    {
        // $lontype = 'single';
        // $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
        // $lists = $this->Loan_guwen_db->where(array('cate_id' => $cate_id))->order('sorts asc,id desc')->select();
        // $this->assign('lists', $lists);
        // $this->display();
        $status=0;
        $goods_id=I("get.gid");
        $guwen=M("loan")->field("guwen_id")->find($goods_id);
        $guwens=rtrim($guwen['guwen_id'],",");
        $where=array();
        $where["supplier_id"]=$this->supplier_id;
        //非该商品顾问
        if(!empty($guwens)){
            $where['id']=array("not in",$guwens);
        }
        $alist=M("small_config")->where($where)->select();
        //如果没有可添加的商品顾问 定义一个状态 禁用添加按钮
        if(empty($alist)){
            $status=1;
        }
        $this->assign("status",$status);
        //该商品的顾问
        $where['id']=array("in",$guwens);
        $lists=M("small_config")->where($where)->select();

        $this->assign('alist', $alist);
        $this->assign('goods_id', $goods_id);
        $this->assign('lists', $lists);
        $this->display();        

    }

    public function Addeditguwenlistsloan()
    {
        if (IS_AJAX) {
            //file_put_contents(ACTION_NAME.'.txt',print_r(array($_POST,date('Y-m-d H:i:s',time())),true).PHP_EOL,FILE_APPEND);  // exit;
            $data = array();
            $data = I("post.");
            $guwen=M("loan")->field("guwen_id")->find($data['gid']);
            $res=array();
            $res['id']=$data['gid'];
            $res['guwen_id']=$guwen['field'].$data['guwen'].",";
            $result=M("loan")->save($res);

            // $id = $this->Loan_guwen_db->add($data);

            if ($result) {
                $this->ajaxReturn(array("status" => 0, "info" => "新增成功！"));
                exit;
            }
            $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            exit;
        }
    }

    public function Delguwenlistsloan()
    {
        // $id = trim(I('id'));
        // $gid= trim(I("gid"));
        $data = I("post.");
        $guwen=M("loan")->where(array("id"=>$data['gid']))->getfield("guwen_id");
        $res=array();
        $res['id']=$data['gid'];
        $res['guwen_id']=str_replace($data['id'].",",'',$guwen);
        $result=M("loan")->save($res);
        if ($result) {
            $this->ajaxReturn(array("status" => 0, "info" => "删除成功！"));
            exit;
        }
        $this->ajaxReturn(array("status" => 0, "info" => "删除失败！"));
        exit;
    }

    /************顾问模块end******/


    /*********案例攻略start**********/

    public function Articleloan()
    {
        $lontype = trim(I('lontype'));
        $lontype = $lontype ? $lontype : 'single';
        $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id

        $typeid = trim(I('typeid'));
        $name = trim(I('name'));

        $where = array();
        $where['cate_id'] = $cate_id;

        if ($typeid != '') {
            $where['typeid'] = $typeid;
            $this->assign('typeid', $typeid);
        }
        if ($name) {
            $where['title|author|source'] = array('like', "%$name%");
            $this->assign('name', $name);
        }

        $count = $this->Article_db->where($where)->count();
        $Page = getpage($count, 10);
        $show = $Page->show();
        $lists = $this->Article_db->where($where)->order('sorts asc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('count', $count);
        $this->assign('lists', $lists);
        $this->assign('page', $show);

        $this->display();
    }

    public function Addeditarticleloan()
    {
        $lontype = trim(I('lontype'));
        $lontype = $lontype ? $lontype : 'single';
        $cate_id = $this->checkLonType($lontype);  //dump($cate_id);//显示分类id
        if (IS_POST) {
            $data = array();
            $data = $_POST;
            if (I('id')) {
                $id = $this->Article_db->save($data);
                if ($id) {
                    $this->success("修改成功!", U('/Supplier/Xloan/ArticleXloan/lontype/single'));
                    exit;
                }
            } else {
                $data['create_at'] = time();
                $id = $this->Article_db->add($data);
                if ($id) {
                    $this->success("添加成功!", U('/Supplier/Xloan/ArticleXloan/lontype/single'));
                    exit;
                }
            }
            $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            exit;
        }

        $id = trim(I('id'));
        $find = $this->Article_db->where(array('id' => $id))->find();
        $this->assign('find', $find);
        $this->display();
    }

    public function Delarticleloan()
    {
        $id = trim(I('id'));
        if (!$id) {
            $this->error('删除失败');
        }

        $id = $this->Article_db->where(array('id' => $id))->delete();
        if ($id) {
            $this->success("删除成功！");
            exit;
        }
        $this->error('删除失败');
    }

    /*********案例攻略end**********/
    /**
     *产品中心选择地址*20170712*lq
     */
    public function address()
    {
        $m  = M('grab_config');
        $m2  = M('grabf_config');
        $id=I('param.id');
        $info = $m->where(array('supplier_id'=>$_SESSION['supplier_id']))->select();
        $types = $m2->where(array('supplier_id'=>$_SESSION['supplier_id']))->find();
        $dizhi=array();
        foreach($info as $k1=>$v1){
            $dizhi['city'][]=$v1['city'];
            $jiesuan= M("region")->where(array("card" => $v1['city']))->find();
            $dizhi['pro'][]= M("region")->where(array("card" => $jiesuan['parentid']))->getField('card');
            //echo  M("region")->getLastSql();
        }
        $dizhi['city']=$dizhi['city']?$dizhi['city']:'';
        $dizhi['pro']=$dizhi['pro']?$dizhi['pro']:'';

        $types['ids'] = array_filter(explode("-", $types['type']));
        $this->assign('types',$types);
        $this->assign('dizhi',$dizhi);
        $map = "leveltype=1 or leveltype=0";
        $provinceList = M("region")->where($map)->select();

        foreach($provinceList as $kk=>$vv){
            if($vv['card'] != 100000) {
                $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card']))->select();
            }
        }

        //dump($provinceList);exit;
        $this->assign('provinceList',$provinceList);
        $this->assign('cache',$info);
        if(IS_AJAX){
                $city = trim(I("post.city"),',');
                if(empty($city)){
                    $this->ajaxReturn(array('status'=>0,"info"=>"请选择城市"));
                }
                session("city",$city);
                $this->ajaxReturn(array("status"=>1,"info"=>"选择成功"));
        }
        $this->display();
    }

}
