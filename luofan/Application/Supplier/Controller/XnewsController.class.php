<?php
namespace Supplier\Controller;
use Supplier\Common\Controller\CommonController;
header("content-Type: text/html; charset=Utf-8");
class XnewsController extends CommonController{
    public function index(){
        $m   = M("News_cate");
        $count = $m->count();    //总数
        $p = getpage($count, 10);
        $res = $m->where(array("pid"=>0))->order("sort asc")->select();           //获取每页数据
        $cate = $m->where(array("pid"=>0))->order("sort Asc")->field("id,classname")->select();
        foreach($res as $k=>$v){
            $res[$k]["data"] = $m->where(array("pid"=>$v['id']))->order("sort Asc")->select();
            foreach ($res[$k]["data"] as $key => $val) {
                $res[$k]["data"][$key]['child'] = $m->where(array("pid"=>$val['id']))->order("sort Asc")->select();
            }
        }
        $this->assign("cache", $res);
        $this->assign("cate",  $cate);
        $this->display();
    }

    /**
     * 增加资讯分类
     */
    public function addCate(){
        if(IS_AJAX){
            $classname   = I("post.classname");
            $sort        = I("post.sort");
            $create_at   = I("post.create_at");
            $pid         = I("post.fid");
            $m = M("News_cate");
            $res = $m->where(array("classname"=>$classname, "pid"=>$pid))->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $data['classname']    = $classname;
            $data['pid']          = $pid;
            $data['sort']         = $sort;
            $data['create_at']    = time();
            $res = $m->add($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }
        }
    }

    /**
     * 删除分类
     */
    public function delCate(){
        $id = I("id");
        $m  = M("News_cate");
        $data = $m->find($id);
        if(!$data){
            $this->error("分类不存在!");
        }
        if($data['pid']){
            $res = $m->where(array("id"=>$id))->delete();
            if($res){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }else{
            $res1 = $m->where(array("id"=>$id))->delete();
            $res2 = $m->where(array("pid"=>$id))->delete();
            if($res1!==false && $res2!==false){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }
    }

    /*
     * 编辑分类
     */
    public function editCate(){
        if(IS_AJAX){
            $id         = I("post.categoryid");
            $classname  = I("post.classname");
            $pid        = I("post.fid");
            $sort       = I("post.sort");
            $m = M("News_cate");
            $map = array(
                "classname" => $classname,
                "pid"       => $pid,
                "id"        => array("neq", $id),
            );
            $res = $m->where($map)->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $parid = $m->where(array("id"=>$id))->getField("pid");
            if($parid == 0 && $pid != 0){
                $this->ajaxReturn(array("status"=>0, "info"=>"顶级分类无法改变分类！"));
            }
            $data['classname']      = $classname;
            $data['pid']            = $pid;
            $data['sort']           = $sort;
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }


    public function news(){

        $title = I("get.title");
        if($title){
            $map['news_title']=array('like',$title);
        }
        //$map = "news_title like '%{$title}%'";
        $map['supplier_id']=$_SESSION['supplier_id'];
        $count = M('news')->where($map)->count();
        $Page = getpage($count, 10);
        $show  = $Page->show();//分页显示输出
       	$List = M('news')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        //echo M('news')->getLastSql();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->display();
    }

   public function addnews(){
    $id = intval(I('param.id'));
    $m  = M('news');
    $info = $m->find($id);
    $info['city'] = array_filter(explode(",",$info['city']));
    // $info['city_card'] = array_filter(explode(",",$info['city_card']));

    $city = I("post.city");
       $cityName = "";
       $cityCard = "";
    foreach($city as $k=>$v){
      $cityName .= $this->cardChangeName($v).",";
      $cityCard .= $v.",";
    }

   	if(IS_POST){
        $cate_id = I("post.cate");
        //根据cate_id查询分类名称
        $cateInfo = M("news_cate")->where(array("id"=>$cate_id))->find();
        $cateName = $cateInfo["classname"];
        //根据登录id查询该小贷公司名称
        $Supplier_id = $this->supplier_id;
        //查询公司名称
        $supplierName = M("xiaodai")->where(array("id"=>$Supplier_id))->getField("personname");
        $data=array(
            'news_title'=> I("post.news_title"),
            'detail'    => I("post.detail"),
//            'is_hot'    => I("post.is_hot"),
            'sort'      => I("post.sort"),
            'addtime'   => I("post.addtime"),
            'province'  => I("post.province"),
//            'city'      => $cityName,
            'city_card' => $cityCard,
            'cate_name' => $cateName,
        );
        if($cateInfo["pid"]){
            $data['cate_pid'] = $cate_id;
            $data['cate_id'] = $cateInfo["pid"];
        }else{
            $data['cate_id'] = $cate_id;
        }
        if($supplierName){
            $data["author"] = "<a href='".U('CarLoan/companyinfo',array('id'=>$Supplier_id))."'>".$supplierName."</a>";
        }
        $logo_pic= I("post.logo_pic");
		    $detail  = I("post.detail");
        if($logo_pic){
            $data['logo_pic']=$logo_pic;
        }
    		if($detail){
    			$data['detail']=$detail;
    		}

        if($id){
            $data['edit_time']=time();
            $res = $m->where(array('id'=>$id))->save($data);
        }else {
            $data['supplier_id']=$_SESSION['supplier_id'];
            $data['add_time']=time();
            $res = $m->add($data);
        }
        if($res){
			     $this->success("操作成功");exit;
        }else{
			     $this->success("操作失败");exit;
        }
      }

      $map = "LevelType=1 or LevelType=0";
      $provinceList = M("region")->where($map)->select();
      $cateList = M("News_cate")->where(array("pid"=>0))->order('sort asc')->select();
      foreach($cateList as $k=>$v){
            $cateList[$k]["data"] = M("News_cate")->where(array("pid"=>$v['id']))->order("sort Asc")->select();
            // foreach ($cateList[$k]["data"] as $key => $val) {
            //     $cateList[$k]["data"][$key]['child'] = M("News_cate")->where(array("pid"=>$val['id']))->order("sort Asc")->select();
            // }
      }
      $this->assign('cateList',$cateList);
      $this->assign('provinceList',$provinceList);
      $this->assign('cache',$info);
	    $this->display();
   }

   //省市card转名称
   public function cardChangeName($card){
      $name = M("region")->where(array("card"=>$card))->getField('name');
      return $name;
   }


   //多选省市
   public function selCity(){
      if(IS_AJAX){
        $card = I("post.card");
        if($card != 100000){
          $res = M("region")->where(array("parentid"=>$card))->select();
        }
        $str = "";
        if($res){
          foreach($res as $k=>$v){
              $str .= '<input type="checkbox" name="city[]" class="city" value="'.$v['card'].'"/>'.$v['name']."  ";
          }
          $this->ajaxReturn(array('str'=>$str));
        }else{
          $this->ajaxReturn(array('status'=>0,'info'=>'当前没有市区可以选择！'));
        }
      }
   }

   public function changeHot(){
      $id  = I("post.id");
      $hot = M("news")->where(array("id"=>$id))->getField("is_hot");
      $hot = $hot?0:1;
      $res = M("news")->where(array("id"=>$id))->setField(array("is_hot"=>$hot));
      if($res){
        $this->ajaxReturn(array('status'=>$hot?1:2,'info'=>'修改成功！'));
      }else{
        $this->ajaxReturn(array('status'=>0,'info'=>'修改失败！'));
      }
   }

   public function delnews(){
       $id=I("post.id");
       $res=M('news')->where(array('id'=>$id))->delete();
       if($res){
           $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功"));
           return;
       }else{
           $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
           return;
       }
   }


}
