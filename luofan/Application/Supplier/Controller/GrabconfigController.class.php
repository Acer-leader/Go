<?php
namespace Supplier\Controller;
use Supplier\Common\Controller\CommonController;
class GrabconfigController extends CommonController{
    public function index(){
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
//        dump($types);
//        dump($dizhi);die;
        if(IS_POST){
            M()->startTrans();
            $rdata=I('param.');
            $rres = $m->where(array('supplier_id'=>$_SESSION['supplier_id']))->delete();
            if($info && !$rres){
                M()->rollback();
                $this->error('更新失败');
            }
            $type['type']=$rdata['type'][0].'-'.$rdata['type'][1].'-'.$rdata['type'][2];
            $type['supplier_id']=$_SESSION['supplier_id'];
            if($id){
                $rrs=$m2->where(array('id'=>$id))->save($type);
            }
            if(!$id){
                $rrs=$m2->add($type);
            }

            foreach($rdata['city'] as $val){
                $ji['supplier_id']=$_SESSION['supplier_id'];
               // dump($val);
                $ji['city']=$val;
                $ji['add_time']=time();
                $res = $m->add($ji);
                if(!$res){
                    $this->error("更新失败");exit;
                }
            }
            M()->commit();
            $this->success("更新成功");exit;

        }
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
        $this->display();
    }



   public function addnews(){
    $id = intval(I('param.id'));
    $m  = M('grab_config');
    $info = $m->find($id);
    $info['city'] = array_filter(explode(",",$info['city']));
    // $info['city_card'] = array_filter(explode(",",$info['city_card']));
    $city = I("post.city");
    foreach($city as $k=>$v){
      $cityName .= $this->cardChangeName($v).",";
      $cityCard .= $v.",";
    }
   	if(IS_POST){
        $data=array(
            'news_title'=> I("post.news_title"),
            'detail'    => I("post.detail"),
            'is_hot'    => I("post.is_hot"),
            'sort'      => I("post.sort"),
            'addtime'   => I("post.addtime"),
            'province'  => I("post.province"),
            'city'      => $cityName,
            'city_card' => $cityCard,
            'cate_name' => I("post.cate"),

        );
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
          $cheid=I('param.cheid');
        if($card != 100000){
          $res = M("region")->where(array("parentid"=>$card))->select();
        }
        $str = "";
        if($res){
          foreach($res as $k=>$v){
              $str .= '<dd><input type="checkbox" name="city[]" class="city" value="'.$v['card'].'"/>'.$v['name'].'</dd>';
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



}
