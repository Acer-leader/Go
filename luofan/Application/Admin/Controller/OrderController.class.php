<?php
namespace Admin\Controller;
//use Think\Controller;
use Common\Controller\CommonController;
class OrderController extends CommonController {

    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }

    /**
	 订单状态（0：已取消，1：待付款,2：待发货，3：已发货，4：待评价，5：已完成 ，6：退款中， 7：退款完成已关闭  8：前台订单删除）
     */

    //显示
    public function index(){
            $keywords = I('post.keywords');
            $starttime= I("post.starttime");
            $endtime  = I("post.endtime");
            if($keywords){
                $map['id']          = array('like',"%$keywords%");
                $map['order_no']    = array('like',"%$keywords%");
                $map['person_name'] = array('like',"%$keywords%");
                $map['telephone']   = array('like',"%$keywords%");
                $map['_logic']      ="or";
                $this->assign('keywords',$keywords);
            }
            if($starttime && $endtime){
                $arr[0] = strtotime($starttime);
                $arr[1] = strtotime($endtime);
                $map['order_time'] = array('in',$arr);
                $this->assign('starttime',$starttime);
                $this->assign('endtime',$endtime);
            }

            /*分状态*/
            $order_status = I('order_status');
            if($order_status!==""){
                $map['order_status'] = $order_status;
            }

            $m   = M("order_view");
            $mg  = M('order_goods');
            $count=$m->where($map)->count();
            $Page  = getpage($count,5);
            $show  = $Page->show();//分页显示输出
            $res = $m->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
            foreach($res as $key=>$val){
                $res[$key]['goods']=$mg->where(array('order_id'=>$val))->select();
            }

            $count  = $m->count();
            $count0 = $m->where(array("order_status"=>0))->count();//取消
            $count1 = $m->where(array("order_status"=>1))->count();//待付款
            $count2 = $m->where(array("order_status"=>2))->count();//待发货
            $count3 = $m->where(array("order_status"=>3))->count();//待签收
            $count4 = $m->where(array("order_status"=>4))->count();//待评价
            $count5 = $m->where(array("order_status"=>5))->count();//已完成
            $count6 = $m->where(array("order_status"=>6))->count();//退款中
            $count7 = $m->where(array("order_status"=>7))->count();//退款完成
            $count8 = $m->where(array("order_status"=>8))->count();//用户删除

            $this->assign("count", $count);
            $this->assign("count0", $count0);
            $this->assign("count1", $count1);
            $this->assign("count2", $count2);
            $this->assign("count3", $count3);
            $this->assign("count4", $count4);
            $this->assign("count5", $count5);
            $this->assign("count6", $count6);
            $this->assign("count7", $count7);
            $this->assign("count8", $count8);
            $this->assign('cache',$res);
            $this->assign('page',$show);

            $express = M("express")->order("id asc")->select();
            $this->assign("express_list",$express);
            $this->display();

        }


    //发货
    //选择快递公司
    public function express(){
        $data["express_name"]    = I("post.express_name");//编码

        $data["express_no"]      = I("post.express_no");
        $data["is_send"]         = 1;
        $id                      = I('post.id');
        $m          = M('order_info');
        $res = $m->where(array("id"=>$id))->save($data);
        $Info=$m->where(array("id"=>$id))->find();
		$data["express_name"]    = M("express")->where(array('express_ma'=>$data['express_name']))->getField("express_company");//快递公司名称
        if($res){
            //发货成功添加发货时间 修改订单状态
            $res1 = $m->where(array("id"=>$id))->setField(array("order_status"=>3,"shipping_time"=>time()));
            if($res1){
                $d=array(
                    'order_id'=>$Info['id']
                );
                $this->sendSystemMessage($Info['user_id'],"订单已发货","您的订单【".$Info['order_no']."】已发货，【".$data['express_name']."】运单编号：".$data["express_no"]."请注意查收！",$d);
                $this->ajaxReturn(array("status"=>1,'info'=>"发货成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,'info'=>"发货失败"));
            }
        }else{
            $this->ajaxReturn(array("status"=>0,'info'=>"发货失败"));
        }
    }

    //修改物流单号及快递公司
    public function update_express(){
        if(IS_AJAX){
            $data['express_name'] = I("post.express_name");
            $data['express_no'] = I("post.express_no");
            $id = I("post.order_id");
            $m = M("order_info");
            $ress = $m->where(array('id'=>$id))->save($data);
            if($ress){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"操作失败！"));
            }
        }
    }

    //同意退款
    public function alreturn(){
        $id     = $_GET['id'];
        $m      = M('order_info');
        $time   = time();//退款时间
        //支付状态pay_status 已退款2
        //同意退款关闭订单5
        //要收回货之后才能退钱 客服确定收货退款此时的订单应该是被关闭
        $Info=$m->where(array("id"=>$id))->find();
        $res = $m->where(array("id"=>$id))->setField(array("order_status"=>5,"refund_time"=>$time,"pay_status"=>2,'is_refund'=>1,'refund_fee'=>$Info['pay_price']));
        if($res){
            $data=array(
                'order_id'=>$Info['id']
            );
            $this->sendSystemMessage($Info['user_id'],"您的退款进度消息","您的订单退款已完成，请注意查收！",$data);
            $this->success("退款成功");exit;
        }else{
            $this->success("退款失败");exit;
        }
    }
    //取消退款
    public function dereturn(){
        $id     = $_GET['id'];
        $m      = M('order_info');
        $res = $m->where(array("id"=>$id))->find();
        //未发货的状态
        if($res['is_send'] == 0){
            //改成待发货的状态
            $result  = $m->where(array("id"=>$id))->setField(array("order_status"=>2,"is_refund"=>2));
            if($result){
                $data=array(
                    'order_id'=>$res['id']
                );
                $this->sendSystemMessage($res['user_id'],"您的退款进度消息","您的订单退款被拒绝，请注意查收！",$data);
                $this->success("取消成功");
            }else{
                $this->error("取消失败");
            }
        }
        //已发货
        if($res['is_send'] == 1){
            //改成待收货的状态
            $result  = $m->where(array("id"=>$id))->setField(array("order_status"=>3,"is_refund"=>2));
            if($result){
                $data=array(
                    'order_id'=>$res['id']
                );
                $this->sendSystemMessage($res['user_id'],"您的退款进度消息","您的订单退款被拒绝，请注意查收！",$data);
                $this->success("取消成功");
            }else{
                $this->error("取消失败");
            }
        }

    }

    //查看详情
    public function orderDetail(){
        $id    = I("get.id");
		
        $order = M("order_info")->find($id);
        if(!$order){
            goback("没有此订单！");
        }
        $order['user']        = M('member')->find($order['user_id']);

        $order_goods = M("order_goods")->where(array('order_id'=>$order['id']))->select();
		
        foreach($order_goods as $k=>$v){
            $goods_info = M('customer_goods')->where(array('goods_id'=>$v['goods_id'],'bank_id'=>$order['bank_id']))->find();
			
            if($goods_info['is_sku']==1){
                $sku_info = M("sku_list_view")->where(array('goods_id'=>$v['goods_id'],"sku_list_id"=>$v['sku_list_id'],'bank_id'=>$order['bank_id']))->find();
                $order_goods[$k]['integral'] = (int)$sku_info['integral'];
                $order_goods[$k]['oprice']   = $goods_info['oprice'];
                $goods_info['oprice']        = $goods_info['oprice'];
                $goods_info['integral']      = (int)$sku_info['integral'];
            }else{
                $order_goods[$k]['integral']        = intval($goods_info['integral']);
                $order_goods[$k]['oprice']          = $goods_info['oprice'];
            }
            $order_goods[$k]['s_integral']      = $goods_info['s_integral'];
            $order_goods[$k]['integral_limit']  = intval($goods_info['integral_limit']);
            if($goods_info['integral_limit'] == 0)$order_goods[$k]['xiaoji_price'] ="¥".$v['goods_nums']*$goods_info['oprice'];
            if($goods_info['integral_limit'] != 0 && intval($goods_info['integral_limit']) < intval($goods_info['integral'])){
                $jifenmoney = integralTomoney(($goods_info['integral']-$goods_info['integral_limit'])*$v['goods_nums']);
                $order_goods[$k]['xiaoji_price'] = $goods_info['integral_limit']*$v['goods_nums']."积分".'¥'.$jifenmoney;
            }
            if(intval($goods_info['integral_limit']) >= intval($goods_info['integral']))$order_goods[$k]['xiaoji_price'] =$v['goods_nums']*$goods_info['integral']."积分";

        }
        //dump($order_goods);exit;
        $order['goods'] = $order_goods;
        $order['c_time'] =M('order_goods')->where(array('order_id'=>$order['id']))->order('c_time asc')->getField('c_time');
	
		/**
         * 加入评价
         */
        $c_m = M("goods_comment");
        foreach($order_goods as $k=>$v){
            if($v['status']){
                $order_goods[$k]['comment'] = $c_m->where(array('goods_id'=>$v['goods_id'],"order_id"=>$v['order_id']))->find();
            }
        }

        //物流信息
        $express = get_express_info($order['express_ma'],$order['express_no']);
        $this->assign("cache", $order);
        $this->assign("express", $express);
        $express = M("express")->order("id asc")->select();
        $this->assign("express_list",$express);
        $this->display();
    }




    // 下单错误报告
    public function errorReport(){
        $m = M("order_error_log");
        $type = I("type");
        $title = I("title");
        if($type!==""){
            $map['e.status'] = $type;
        }
        if($title){
            $map['m.person_name'] = array("like","%{$title}%");
            $map['m.telephone'] = array("like","%{$title}%");
            $map['o.order_no'] = array("like","%{$title}%");
            $map["_logic"] = "or";
        }
        if(!empty($map)){
            $m->where($map);
        }
		$count=$m->where($map)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();//分页显示输出
		
        $DB_PREFIX = C("DB_PREFIX");
        $join_str1 = "left join {$DB_PREFIX}member as m on e.user_id=m.id";
        $join_str2 = "left join {$DB_PREFIX}goods as g on e.goods_id=g.id";
        $join_str3 = "left join {$DB_PREFIX}order_info as o on e.order_id=o.id";
        $res = $m->alias("e")->
            join($join_str1)->
            join($join_str2)->
            join($join_str3)->
            order("create_at")->
            field("m.person_name,m.telephone,g.goods_name,g.logo_pic,e.*,o.order_no,o.trade_no")->
            limit($Page->firstRow.','.$Page->listRows)->
            order("create_at desc")->
            select();
        $admins = M("user")->select();
        foreach($admins as $v){
            $admin[$v['id']] = $v['username'];
        }
        foreach($res as $k=>$v){
            $res[$k]['admin'] = $admin[$v['admin_id']];
        }
        $this->assign("title",  $title);
		$this->assign("page",$show);
        $this->assign("count",  $m->count());
        $this->assign("count1", $m->where(array('status'=>0))->count());
        $this->assign("count2", $m->where(array('status'=>1))->count());
        $this->assign("cache",  $res)->display();
    }

    // 处理错误报告
    public function dealErrorReport(){
        if(IS_AJAX){
            $id = I("post.id");
            $data['admin_id'] = $_SESSION['admin_id'];
            $data['deal_at']  = time();
            $data['status']   = 1;
            $res = M("order_error_log")->where(array('id'=>$id))->save($data);
            if($res){
                $this->ajaxReturn(array('status'=>1, "info"=>"处理成功！"));
            }else{
                $this->ajaxReturn(array('status'=>0, "info"=>"处理失败！"));
            }
        }
    }

    /**
     * 订单错误报告日志详情
     *     显示商品详情：订单详情
     */
    public function errorReportDetail(){
        $id = I("id");
        $m = M("order_error_log");
        $error_info = $m->find($id);
        if(!$error_info){
            echo "<script>alert('没有这个错误报告！');window.history.back();</script>";die;
        }
        $user_info   = M("member")->find($error_info['user_id']);
        $order_info  = M("order_info")->find($error_info['order_id']);
        if($error_info['goods_id']){
            $order_goods = M("order_goods")->where(array('order_id'=>$error_info['order_id'],'goods_id'=>$error_info['goods_id']))->find();
        }
        if($error_info['sku_id']){
            $sku_info    = M("sku_list")->where(array('id'=>$error_info['sku_id']))->find();
        }
        $admin = $this->getAdmin();
        $error_info['admin'] = $admin[$error_info['admin_id']];

        $this->assign("user_info",   $user_info);
        $this->assign("order_info",  $order_info);
        $this->assign("order_goods", $order_goods);
        $this->assign("sku_info",    $sku_info);
        $this->assign("error_info",  $error_info);
        $this->display();
    }

    public function getAdmin(){
        $admins = M("user")->select();
        foreach($admins as $v){
            $admin[$v['id']] = $v['username'];
        }
        return $admin;
    }

	



	//导出订单详情
	public function orderExport(){
		
		$db = M('order_view');
	
		$status = I("get.status");
		if($status === null) {
			$orders = $db->select();	
		}else{
			$orders = $db->where(array('status'=>$status))->select();	
		}
		$i = 1; //开始标示

		foreach($orders as $key=>$val){
			$data[$key]['i']			 =$i;
			$data[$key]['cus_name']			 =$val['cus_name'];
			$data[$key]['order_time']    = $val['order_time'];
			$data[$key]['order_no']      = $val['order_no'];
			$data[$key]['order_status']  = $val['order_status'];
			$data[$key]['realname']      = $val['realname'];
			$data[$key]['person_name']   = $val['person_name'];
			$data[$key]['telephone']     = $val['telephone'];
			$data[$key]['pay_way']       = $val['pay_way'];
			$data[$key]['pay_price']     = $val['pay_price'];
			$data[$key]['pay_integral']  = $val['pay_integral'];
			$data[$key]['pay_time']      =  $val['pay_time']?date("Y-m-d H:i:s",$val['pay_time']):"";
			$data[$key]['total_fee']     = $val['total_fee'];
			$data[$key]['total_integral']    = $val['total_integral'];
			$data[$key]['discount_integral'] = $val['discount_integral'];
			$data[$key]['return_integral']   = $val['return_integral'];
			$data[$key]['buy_integral']      = $val['buy_integral'];
			$data[$key]['goods_id']          = $val['goods_id'];
			$data[$key]['goods_name']        = $val['goods_name'];
			$data[$key]['sku_info']          = $val['sku_info'];
			$data[$key]['shipping_time']     = $val['shipping_time']? date("Y-m-d H:i:s",$val['shipping_time']):""; 
			$data[$key]['consignee']         = $val['consignee'];
			$data[$key]['address']           = $val['province'].$val['city'].$val['district'].$val['address'];
			$data[$key]['mobile']            = $val['mobile'];
			$data[$key]['express_name']      = $val['express_name'];
			$data[$key]['express_no']        = $val['express_no'];
			$data[$key]['receive_time']      = $val['receive_time']?date("Y-m-d H:i:s",$val['receive_time']):""; 
			$data[$key]['posttime']          = $val['posttime']?date("Y-m-d H:i:s",$val['posttime']):""; 
			$data[$key]['content']           = $val['content'];
			$data[$key]['applyrefund_time']  = $val['applyrefund_time']?date("Y-m-d H:i:s",$val['applyrefund_time']):""; 
			$data[$key]['refund_time']       = $val['refund_time']?date("Y-m-d H:i:s",$val['refund_time']):""; 
			$data[$key]['refund_fee']        = $val['refund_fee'];
			$data[$key]['refund_integral']   = $val['refund_integral'];
			
			$i++;
		}
		$title=array('序号','归属商家','下单时间','订单号','订单状态','买家真实姓名','买家昵称','买家电话','支付方式','订单支付金额','订单支付积分','订单支付时间','订单总金额','订单总积分','订单折扣总积分','订单返还积分','订单金额返积分','商品ID','商品名称','商品属性','发货时间','收货人姓名','收货人地址','收货人电话','快递公司','快递编号','收货时间','评价时间','评价内容','申请退款时间','退款时间','退款金额','退款积分');
		
		//$csv=new Csv();
		$name ="订单详情".date("Y.m.d");
		$this->put_csv($name,$data,$title); 
	}
	
	

	 //导出csv文件
    public function put_csv($name, $list, $title){
	
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename='.$name.".xls" );
        header ( 'Cache-Control: max-age=0' );
        header("Expires: 0");
        $file = fopen('php://output',"a");
        $limit=1000;
        $calc=0;
        //foreach ($title as $v){
        //    $tit[]=iconv('UTF-8', 'GB2312//IGNORE',$v);
        //}
        fputcsv($file,$title,"\t");
        foreach ($list as $v){
            $calc++;
            if($limit==$calc){
                ob_flush();
                flush();
                $calc=0;
            }
            fputcsv($file,$v,"\t");
        }
        unset($list);
        fclose($file);
        exit();
    }
	
	
}
?>