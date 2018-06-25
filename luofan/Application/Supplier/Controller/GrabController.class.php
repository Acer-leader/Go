<?php
namespace Supplier\Controller;
//use Think\Controller;
use Supplier\Common\Controller\CommonController;
class GrabController extends CommonController{
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }

    /*
     * 刷新列表
     */
    public function lists(){
        if(IS_POST){
            //查询
            $title = I("post.name");
            $map['title'] = array('like',"%$title%");
        }
        $p_m=M('grab_config');
        $p_mf=M('grabf_config');
        $peizhi=$p_m->where(array('supplier_id'=>$_SESSION['supplier_id']))->select();
        $peizhif=$p_mf->where(array('supplier_id'=>$_SESSION['supplier_id']))->find();
        foreach($peizhi as $kk1=>$vv1){
            $cityname[]= M("region")->where(array("card" => $vv1['city']))->getField('shortname');
        }

        $l_m=M('loan_order');
        $supplier_id=$_SESSION['supplier_id'];
        $map['is_end'] = 0;
        $map['oneid'] = array('neq',$supplier_id);
        $map['twoid'] = array('neq',$supplier_id);
        $map['threeid'] = array('neq',$supplier_id);
        $typeids = array_filter(explode("-", $peizhif['type']));
		if($typeids){
			$map['cate_id']=array('in',$typeids);	
		}

        if($cityname){
			$map['city']=array('in',$cityname);	
		}
       
        $map2['oneid'] = $supplier_id;
        $map2['twoid'] = $supplier_id;
        $map2['threeid'] = $supplier_id;
        $map2['_logic'] = 'or';
		$map22['_complex']=$map2;
		if($typeids){
			$map22['cate_id']=array('in',$typeids);
		}
        if($cityname){
			$map22['city']=array('in',$cityname);
		}


//        $count1 = $l_m->where($map)->count();//可抢

        $count = $l_m->where($map22)->count();//已抢


        if(I('param.islooked')==1){
            $map3=$map22;
        }elseif(I('param.islooked')==3){
            $map4["supplier_id"] = $supplier_id;
            $map3 = $map4;
        }else{
            $map3=$map;
        }
        $counts =  $l_m->where($map3)->count();
        $count4 = $l_m->where(array("supplier_id"=>$supplier_id))->count();
        $this->assign('count4',$count4);
        $map_lq['buy_out'] = ['eq',0];
        $map_lq['is_grab'] = ['eq',1];
        $map_lq['is_end'] = ['eq',0];
        $data = $l_m->field('*')->where($map3)->where(['is_grab'=>1])->fetchsql(false)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($data as $key =>$val){
			$member = M('member')->field('houseid,carid')->where(array('id'=>$val['uid']))->find(); 
			$data[$key]['housename'] = M('cate')->where(array('id'=>$member['houseid']))->getField('classname');
			$data[$key]['carname']   = M('cate')->where(array('id'=>$member['carid']))->getField('classname');

			//根据id查询以往订单是否有该小贷公司id
            $map4['oneid'] = $supplier_id;
            $map4['twoid'] = $supplier_id;
            $map4['threeid'] = $supplier_id;
            $map4['_logic'] = 'or';
            $map_lq['_complex']=$map4;
            $map_lq['uid'] = $val['uid'];
            $history_order = $l_m->where($map_lq)->fetchsql(false)->find();
            //dump($history_order);die;
            if($history_order){
                $data[$key]["islooked"] = 1;
            }

            //查看此订单是否已有人抢单,如没有,可买断
            if($val['oneid'] == 0 && $val['twoid'] == 0 && $val['threeid'] == 0 && $val['supplier_id'] == 0){
                $data[$key]['is_buy_out'] = 1;
            }

		}
		$count1 = count($data);
        $p = getpage($count1,10);
        $show = $p->show();
        $data = array_slice($data,$p->firstRow,$p->listRows);
		//dump($data);die;
        $this->assign('res',$data);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('count1',$count1);
        $this->assign('counts',$counts);
        $this->display();
    }

    /**
     * 刷新详情
     */
    public function destail(){
        $id = I("get.id");
        $data=M('loan_order')->where(array('id'=>$id))->find();
        $this->assign('data',$data);
        $this->display();
    }
    /*
     * 删除消息
     */
    public function Delshuaxin(){
        $id = I("id");
        $res = M("shuaxin_log")->where(array("id" => $id))->save(array("isdel" => 1));
        if ($res !== false) {
            $this->success("删除成功！");
            die;
        }
        $this->error("删除失败！");
        die;
    }

    /*
    * 删除消息
    */
    public function agreedan(){
        $id = I("id");
        M()->startTrans();
        $pz_mf=M('virtual_config');
        $peizhif=$pz_mf->find();
        $xcount=M('xiaodai')->where(array('id'=>$_SESSION['supplier_id']))->find();

        if($xcount['wallet']<$peizhif['grab']){
            $this->ajaxReturn(array("status"=>0, "info"=>"金额不够,添加失败！"));exit;
        }


        $row = M("loan_order")->where(array("id" =>$id))->find();
        if(!$row['oneid']){
            $data['oneid']=$_SESSION['supplier_id'];
        }elseif(!$row['twoid']){
            $data['twoid']=$_SESSION['supplier_id'];
        }elseif(!$row['threeid']){
            $data['threeid']=$_SESSION['supplier_id'];
            $data['is_end']=1;
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'此单已经结束'));exit;
        }

        $pz_m=M('virtual_config');
        $peizhi=$pz_m->find();
        switch($row['cate_id']){
            case 1:$daiktype='个贷';break;
            case 2:$daiktype='车贷';break;
            case 3:$daiktype='房贷';break;
            default:$daiktype='';
        }
        $log_info=$_SESSION['supplier_name'].'接单:'.$row['truename'].$daiktype;
        $type=2;
        //$logres=shuaxin_Log($row['id'],$peizhi['grab'],$log_info,$_SESSION['supplier_id'],$_SESSION['supplier_name'],$type);
        $logres=shuaxin_Log($row['id'],$peizhi['grab'],$log_info,$_SESSION['supplier_id'],$_SESSION['supplier_name'],$type);
        if($logres!=1){
            $this->ajaxReturn(array("status"=>0, "info"=>"记录日志失败！"));exit;
        }
        $res = M("loan_order")->where(array("id" => $id))->save($data);
        if (!$res) {
            M()->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'抢单失败'));exit;
        }
        $kou=M('xiaodai')->where(array('id'=>$_SESSION['supplier_id']))->setDec('wallet',$peizhif['grab']);
        if(!$kou){
            M()->rollback();
            return array("status"=>0, "info"=>"扣款失败！");
        }

        M()->commit();
        $this->ajaxReturn(array('status'=>1,'info'=>'抢单成功'));exit;
    }


    /**
     * 小贷公司买断
     */
    public function buy_out()
    {
        if(IS_AJAX){
            $order_id = I('post.order_id');
            if(empty($order_id)){
                $this->ajaxReturn(['status'=>0,'info'=>'请选择要买断的订单']);
            }
            //根据该订单号查询该订单是否已有人抢单
            $orderInfo = M("loan_order")->where(['id'=>$order_id])->field("oneid,twoid,threeid")->find();
            if($orderInfo['oneid'] != 0 || $orderInfo['twoid'] != 0 || $orderInfo['threeid'] != 0 ){
                $this->ajaxReturn(['status'=>0,'info'=>"该订单已有人抢单"]);
            }
            //买断,查询买断所需的货币数
            $buy_out_money = M("virtual_config")->where(['id'=>1])->getField("buy_out");
            //判断当前小贷公司的货币是否充足
            $supplier_money = M("xiaodai")->where(['id'=>$_SESSION['supplier_id']])->getField('wallet');
            if($supplier_money < $buy_out_money){
                $this->ajaxReturn(['status'=>0,'info'=>'货币不足,请充值']);
            }
            //开启事务,减用户货币,修改申请订单信息
            M()->startTrans();
            //减用户货币
            $result = M("xiaodai")->where(['id'=>$_SESSION['supplier_id']])->setDec('wallet',$buy_out_money);
            if($result === false){
                M()->rollback();
                $this->ajaxReturn(['status'=>0,'info'=>'买断失败']);
            }
            //修改订单的信息
            $result = M("loan_order")->where(['id'=>$order_id])->setField('buy_out',$_SESSION['supplier_id']);
            if($result === false){
                M()->rollback();
                $this->ajaxReturn(['status'=>0,'info'=>'买断失败']);
            }
            M()->commit();
            //记录消费流水账
            $data = [
                'user_id' => $_SESSION['supplier_id'],
                'type' => 2,
                'cate' => 1,
                'amount' => $buy_out_money,
                'order_id' => $order_id,
                'message' => '买断申请订单'
            ];
            moneyWater($data);
            $this->ajaxReturn(['status'=>1,'info'=>"买断成功"]);
        }
    }

}
