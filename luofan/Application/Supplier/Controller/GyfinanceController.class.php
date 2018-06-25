<?php
namespace Supplier\Controller;
use Supplier\Common\Controller\CommonController;
class GyfinanceController extends CommonController {

    //财务统计
    public function statistics(){
        $type       = I("get.type");
        $dayStartTime  = I("post.dayStartTime")?I("post.dayStartTime"):"";
        $dayEndTime = I("post.dayEndTime")?I("post.dayEndTime"):"";

        $monthStartTime  = I("post.monthStartTime")?I("post.monthStartTime"):"";
        $monthEndTime  = I("post.monthEndTime")?I("post.monthEndTime"):"";

        $yearStartTime = I("post.yearStartTime")?I("post.yearStartTime"):"";
        $yearEndTime = I("post.yearEndTime")?I("post.yearEndTime"):"";

        $this->assign('type',$type);
        $this->assign('dayStartTime',$dayStartTime);
        $this->assign('dayEndTime',$dayEndTime);
        $this->assign('monthStartTime',$monthStartTime);
        $this->assign('monthEndTime',$monthEndTime);
        $this->assign('yearStartTime',$yearStartTime);
        $this->assign('yearEndTime',$yearEndTime);

        //日订单统计
        $day= $this->get_money(1,2,$dayStartTime,$dayEndTime);
        //月订单统计
        $month=$this->get_money(2,2,$monthStartTime,$monthEndTime);
        //年账单
        $year= $this->get_money(3,2,$yearStartTime,$yearEndTime);
        //dump($year);

        $this->assign('day',$day);
        $this->assign('month',$month);
        $this->assign('year',$year);
        $this->display();
    }

    //订单明细
    public function money(){
        $m= M('money_water');
        $count = $m->count();    //总数
        $p = getpage($count, 10);
        $moneyList = $m->limit($p->firstRow,$p->listRows)->order('posttime desc')->select();
        foreach($moneyList as $k=>$val){
            $moneyList[$k]['nickname'] = M("member")->where(array('id'=>$val['user_id']))->getField('nickname');
        }
        $this->assign('count',$count);
        $this->assign('page',$p->show());
        $this->assign('cache',$moneyList);
        $this->display();
    }

    public function index(){
        $m = M("takemoney_log");
        $type  = I("type");
        $title = I("title");
        $admins = M("user")->select();
        foreach($admins as $v){
            $admin[$v['id']] = $v['username'];
        }
        if($type!==""){
            $map['status'] = $type;
        }
        if($title){
            $map['username'] = array("like","%{$title}%");
            $map['telephone'] = array("like","%{$title}%");
            $map['bank_no'] = array("like","%{$title}%");
            $map["_logic"] = "or";
        }
        if(!empty($map)){
            $m->where($map);
        }
        $res = $m->order("create_at desc")->select();
        foreach($res as $k=>$v){
            $res[$k]['admin'] = $admin[$v['admin_id']];
        }
        $this->assign("title",  $title);
        $this->assign("count",  $m->count());
        $this->assign("count0", $m->where(array('status'=>0))->count());
        $this->assign("count1", $m->where(array('status'=>1))->count());
        $this->assign("count2", $m->where(array('status'=>2))->count());
        $this->assign("sum",    $m->sum("money"));
        $this->assign("sum0",   $m->where(array('status'=>0))->sum("money"));
        $this->assign("sum1",   $m->where(array('status'=>1))->sum("money"));
        $this->assign("sum2",   $m->where(array('status'=>2))->sum("money"));
        $this->assign("cache",  $res);
        $this->display();
    }

    public function tixian(){
        if(IS_AJAX){
            $id   = I("post.id");
            $data = array(
                    "status"    => 1,
                    "admin_id"  => $_SESSION['admin_id'],
                    "deal_at"   => time(),
                );
            $res = M("takemoney_log")->where(array('id'=>$id,"status"=>0))->save($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"提现成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"提现失败！"));
            }
        }
    }

    public function jujue(){
        if(IS_AJAX){
            $id  = I("post.id");
            $m   = M("takemoney_log");
            $m_m = M("member");
            $m  ->startTrans();
            $m_m->startTrans();
            $info = $m->find($id);
            if(!$info){
                $this->ajaxReturn(array("status"=>0, "info"=>"数据不存在！"));
            }
            $res1 = $m_m->where(array("id"=>$info['user_id']))->setInc("wallet", $info['money']);
            if(!$res1){
                $this->ajaxReturn(array("status"=>1, "info"=>"用户返现失败，拒绝失败！"));
            }
            $data = array(
                    "status"    => 2,
                    "admin_id"  => $_SESSION['admin_id'],
                    "deal_at"   => time(),
                );
            $res = $m-> where(array('id'=>$id,"status"=>0))->save($data);
            if($res){
                $m  ->commit();
                $m_m->commit();
                $this->ajaxReturn(array("status"=>1, "info"=>"拒绝成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"拒绝失败！"));
            }
        }
    }



    //获得消费金额/退款金额
    //$type =1日   2月    3年
    //$class 类型 用户【1收入，2支出】
    public function get_money($type,$class,$startTime="",$endTime=""){
        if($type==1){
            if($startTime && $endTime){
                $Time[1]=strtotime($startTime);
                $Time[2]=strtotime($endTime);
                //$Time= get_twoday_time($startTime,$endTime);
            }else{
                $Time= get_day_time();
            }
        }elseif($type==2){
            if($startTime && $endTime){
                $Time[1]=strtotime($startTime);
                $Time[2]=strtotime($endTime);
                //$Time= get_twomonth_time($startTime,$endTime);
            }else{
                $Time= get_month_time();
            }
        }elseif($type==3){
            if($startTime && $endTime){
                $Time[1]=strtotime($startTime);
                $Time[2]=strtotime($endTime);
                //$Time= get_twoyear_time($startTime,$endTime);
            }else{
                $Time[1]= 0;
                $Time[2]= time();
            }
        }
        //供应商
        $gyorder_m=M('gyorder_info');
        $gygoods_m=M('gyorder_goods');
        $map['order_time'] = array('between',array($Time[1],$Time[2]));
        $map['supplier_id']=$_SESSION['supplier_id'];
        $cache['all']=$gyorder_m->where($map)->count();
        $map['order_status']=2;
        $cache['accept']=$gyorder_m->where($map)->count();
        $gyorder=$gyorder_m->where($map)->select();
        $gytotal_fee=0;
        foreach($gyorder as $gk=>$gv){
            $gygoods=$gygoods_m->where(array('supplier_id'=>$_SESSION['supplier_id'],'order_id'=>$gv['id']))->select();
            foreach($gygoods as $gkk=>$gvv){
                $gytotal_fee +=$gvv['goods_price']*$gvv['goods_nums'];
            }
        }

        $cache['order_price']=$gytotal_fee;
        return $cache;
    }


    //$type =1日   2月    3年
    //$class 1 收入 2 支出
    public function get_integral($type,$class,$startTime="",$endTime=""){
        if($type==1){
            if($startTime && $endTime){
                $Time= get_twoday_time($startTime,$endTime);
            }else{
                $Time= get_day_time();
            }
        }elseif($type==2){
            if($startTime && $endTime){
                $Time= get_twomonth_time($startTime,$endTime);
            }else{
                $Time= get_month_time();
            }
        }elseif($type==3){
            if($startTime && $endTime){
                $Time= get_twoyear_time($startTime,$endTime);
            }else{
                $Time= get_year_time();
            }
        }
        $map['create_at'] = array('between',array($Time[1],$Time[2]));
        $map['way']     = $class;
        $cache=M('integral_water')->where($map)->field('SUM(integral) as integral')->find();
        return $cache;
    }

//$type =1日   2月    3年
    public function get_tixian($type,$startTime="",$endTime=""){
        if($type==1){
            if($startTime && $endTime){
                $Time= get_twoday_time($startTime,$endTime);
            }else{
                $Time= get_day_time();
            }
        }elseif($type==2){
            if($startTime && $endTime){
                $Time= get_twomonth_time($startTime,$endTime);
            }else{
                $Time= get_month_time();
            }
        }elseif($type==3){
            if($startTime && $endTime){
                $Time= get_twoyear_time($startTime,$endTime);
            }else{
                $Time= get_year_time();
            }
        }
        $map['addtime'] = array('between',array($Time[1],$Time[2]));
        $map['status']     = 1;
        $cache=M('takemoney_log')->where($map)->field('SUM(money) as money')->find();
        return $cache;
    }
	
	
	
	
}