<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class FinanceController extends CommonController {
	/*wzz 20170415
	 * 财务中心
	 * */
    public function index(){

        $this->display();
    }


	/*wzz 20170415
	 * 消费明细
	 * */
	public function money(){
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

		$m= M('money_water_view');
		$count = $m->count();    //总数
        $p = getpage($count, 10);
		$moneyList = $m->limit($p->firstRow,$p->listRows)->order('posttime desc')->select();
		$this->assign('count',$count);
		$this->assign('page',$p->show());
		$this->assign('cache',$moneyList);
		$this->display();
	}


	/*wzz 20170415
	 * 消费明细导出
	 * */
	public function moneyExport(){
		$m= M('money_water_view');
		$moneyList = $m->order('posttime desc')->select();
		$i=1;
		foreach($moneyList as $key=>$val){
			if($val['type']==1){
				$type="收入";
			}else{
				$type="支出";
			}
			$data[$key]['i']			 =$i;
			$data[$key]['cus_name']          = $val['cus_name'];
			$data[$key]['user_id']      = $val['user_id'];
			$data[$key]['person_name']  = $val['person_name'];
			$data[$key]['realname']      = $val['realname'];
			$data[$key]['type']          = $type;
			$data[$key]['amount']     = $val['amount'];
			$data[$key]['way_name']       = $val['way_name'];
			$data[$key]['posttime']      =  $val['posttime']?date("Y-m-d H:i:s",$val['posttime']):"";
			$i++;
		}
		$title=array('序号','商家','用户ID','用户昵称','真实姓名','交易类型','交易金额','详情','交易时间');
		$name ="消费流水详情".date("Y.m.d");
		$this->put_csv($name,$data,$title);
	}


}