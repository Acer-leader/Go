<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class GoodsController extends CommonController {

    /**wzz 20170415
     * 产品分类列表
     */
    public function cateList(){
        $m     = M("cate");
        $res   = $m->where(array("pid"=>0, "isdel"=>0))->order("sort asc")->select();           //获取每页数据
        $cate  = $m->where(array("pid"=>0, "isdel"=>0))->order("sort Asc")->field("id,classname")->select();
        foreach($res as $k=>$v){
            $res[$k]["data"] = $m->where(array("pid"=>$v['id'], "isdel"=>0))->order("sort Asc")->select();
            foreach ($res[$k]["data"] as $key => $val) {
                $res[$k]["data"][$key]['child'] = $m->where(array("pid"=>$val['id'], "isdel"=>0))->order("sort Asc")->select();
            }
        }
        $this->assign("cache", $res);
        $this->assign("cate",  $cate);
        $this->assign("comptype", 0);
        $this->display();
    }


    /**wzz 20170415
     * 增加产品分类
     */
    public function addCate(){
        if(IS_AJAX){
            $classname 		= I("post.classname");
            $englishname 	= I("post.englishname");
            $pid       		= I("post.fid");
            $pic       		= I("post.pic");
            $pic1           = I("post.pic1");
            $pic2       	= I("post.pic2");
            $color       	= I("post.color");
            $sort           = I("post.sort");
            $is_recommend   = I("post.is_recommend");
            if(!$is_recommend){
                $is_recommend = 0;
            }
            $describe1      = I("post.describe1");
            $describe2      = I("post.describe2");
            $m = M("cate");
            $res = $m->where(array("classname"=>$classname, "pid"=>$pid, "isdel"=>0))->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }

            $data['classname']      = $classname;
            $data['is_recommend']   = $is_recommend;
            $data['englishname'] 	= $englishname;
            $data['pid']       		= $pid;
            $data['sort']      		= $sort;
            $data['pic']            = $pic;
            $data['pic1']           = $pic1;
            $data['pic2']      		= $pic2;
            $data['color'] 			= $color;
            $data['create_at'] 		= time();
            $res = $m->add($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }
        }
    }



    /**wzz 20170415
     * 删除产品分类
     */
    public function delCate(){
        $m  = M("cate");if(IS_AJAX){
            $id = I('post.id');

            $arr = explode('_',$id);
            $arr = array_filter($arr);

            foreach($arr as $key => $val){

                $data = $m->find($val);
                if(!$data){
                    $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
                }
                if($data['pid']){
                    $res = $m->where(array("id"=>$id))->delete();
                    if($res){
                        $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('Admin/Goods/cate')));
                    }else{
                        $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
                    }
                }else{
                    $res1 = $m->where(array("id"=>$id))->delete();
                    $res2 = $m->where(array("pid"=>$id))->delete();
                    if($res1!==false && $res2!==false){
                        $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('Admin/Goods/cate')));
                    }else{
                        $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
                    }
                }
            }
        }
    }




    /**wzz 20170415
     * 编辑产品分类
     */
    public function editCate(){

        if(IS_AJAX){

            $id        = I("post.categoryid");
            $classname = I("post.classname");
            $englishname = I("post.englishname");
            $is_recommend = I("post.is_recommend");
            $pid       = I("post.fid");
            $pic       = I("post.pic");
            $pic1       = I("post.pic1");
            $pic2       = I("post.pic2");
            $color       = I("post.color");
            $sort        = I("post.sort");
            $describe1      = I("post.describe1");
            $describe2      = I("post.describe2");
            $m = M("cate");
            $map = array(
                "classname" => $classname,
                "pid"       => $pid,
                "id"        => array("neq", $id),
                "isdel"     => 0,
            );
            $res = $m->where($map)->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $parid = $m->where(array("id"=>$id, "isdel"=>0))->getField("pid");

            if($parid == 0 && $pid != 0){
                $this->ajaxReturn(array("status"=>0, "info"=>"顶级分类无法改变分类！"));
            }

            $data['classname']      = $classname;
            $data['englishname']    = $englishname;
            $data['pid']            = $pid;
            $data['color']          = $color;
            $data['sort']           = $sort;
            $data['describe1']      = $describe1;
            $data['describe2']      = $describe2;
            $data['pic']            = $pic;
            $data['pic1']           = $pic1;
            $data['pic2']           = $pic2;
            $data['is_recommend']   = $is_recommend;
            if(!$is_recommend){
                $data['is_recommend'] = 0 ;
            }
            if($pic==""){
                unset($data['pic']);
            }
            if($pic1==""){
                unset($data['pic1']);
            }
            if($pic2==""){
                unset($data['pic2']);
            }
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }


	/**
	 * 系列列表
	 */
	public function seriesList(){
		$m   = M("series");
		//$count = $m->where(array("pid"=>0, "isdel"=>0))->count();
		//$p = getpage($count, 3);
		$res = $m->where(array("pid"=>0, "isdel"=>0))->
		// limit($p->firstRow,$p->listRows)->
		order("sort asc")->select();

		$series = $m->where(array("pid"=>0, "isdel"=>0))->order("sort asc")->field("id,classname")->select();
		foreach($res as $k=>$v){
			$res[$k]["data"] = $m->where(array("pid"=>$v['id'], "isdel"=>0))->order("sort asc")->select();
		}
		// $this->assign("page",  $p->show());

		//dump($res);
		$this->assign("cache", $res);
		$this->assign("series",  $series);
		$this->display();
	}

	/**
	 * 增加系列分类
	 */
	public function addSeries(){
		if(IS_AJAX){
			$classname = I("post.classname");
			$pid       = I("post.fid");
			$pic       = I("post.pic");
			$sort      = I("post.sort");
			$m = M("series");
			$res = $m->where(array("classname"=>$classname, "pid"=>$pid, "isdel"=>0))->find();
			if($res){
				$this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
			}
			$data['classname'] = $classname;
			$data['pid']       = $pid;
			$data['sort']      = $sort;
			$data['create_at'] = time();
			$pic && $data['pic'] = $pic;
			$res = $m->add($data);
			if($res){
				$this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
			}else{
				$this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
			}
		}
	}

	/**
	 * 删除系列分类
	 */
	public function delSeries(){
		$id = I("id");
		$m  = M("series");
		$data = $m->find($id);
		if(!$data){
			$this->error("分类不存在!");
		}
		if($data['pid']){
			$res = $m->where(array("id"=>$id))->setField("isdel", 1);
			if($res){
				$this->success("删除成功！");die;
			}else{
				$this->error("删除失败！");
			}
		}else{
			$res1 = $m->where(array("id"=>$id))->setField("isdel", 1);
			$res2 = $m->where(array("pid"=>$id))->setField("isdel", 1);
			if($res1!==false && $res2!==false){
				$this->success("删除成功！");die;
			}else{
				$this->error("删除失败！");
			}
		}
	}

	/**
	 * 编辑系列分类
	 */
	public function editSeries(){
		if(IS_AJAX){
			$id        = I("post.seriesgoryid");
			$classname = I("post.classname");
			$pid       = I("post.fid");
			$pic       = I("post.pic");
			$sort      = I("post.sort");
			$m = M("series");
			$map = array(
				"classname" => $classname,
				"pid"       => $pid,
				"id"        => array("neq", $id),
				"isdel"     => 0,
			);
			$res = $m->where($map)->find();
			if($res){
				$this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
			}
			$parid = $m->where(array("id"=>$id, "isdel"=>0))->getField("pid");
			if($parid == 0 && $pid != 0){
				$this->ajaxReturn(array("status"=>0, "info"=>"顶级分类无法改变分类！"));
			}
			$data['classname'] = $classname;
			$data['pid']       = $pid;
			$data['sort']      = $sort;
			$pic && $data['pic'] = $pic;
			$res = $m->where(array('id'=>$id))->save($data);
			if($res !== false){
				$this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
			}else{
				$this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
			}
		}
	}


    /**
     * sku列表
     */
    public function skuList(){
        $m   = M("skuAttr");
        $goodsid = I("goods_id",0,"intval");

        $map = array(
            "pid" => 0,
            "goods_id" => $goodsid,
            "isdel" => 0,
        );
        $res = $m->where($map)->order("sort desc")->select();

        foreach($res as $k=>$v){
            $res[$k]["data"] = $m->where(array("pid"=>$v['id'], "isdel"=>0))->select();
        }

        $this->assign("cache", $res);
        $this->assign("cate",  $res);
        $this->assign("comptype", 1);
        $this->display();
    }


    /**
     * 增加sku
     */
    public function addSku(){
        if(IS_AJAX){
            $classname = I("post.classname");
            $pid       = I("post.fid");
            $sort      = I("post.sort");
            $img       = I("post.img");
            $goodsid   = I("post.goodsid");
            $m = M("skuAttr");
            $res = $m->where(array("classname"=>$classname, "pid"=>$pid, "isdel"=>0,"goods_id"=>$goodsid))->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $data['classname'] = $classname;
            $data['goods_id']  = $goodsid;
            $data['pid']       = $pid;
            $data['sort']      = $sort;
            $data['img']       = $img;
            $data['create_at'] = time();
            $res = $m->add($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }
        }
    }



    /**
     * 删除sku
     */
    public function delSku(){
        $id = I("id");
        $m  = M("skuAttr");
        $data = $m->find($id);
        if(!$data){
            $this->error("sku不存在!");
        }
        if($data['pid']){
            $res = $m->where(array("id"=>$id))->setField("isdel", 1);
            if($res){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }else{
            $res1 = $m->where(array("id"=>$id))->setField("isdel", 1);
            $res2 = $m->where(array("pid"=>$id))->setField("isdel", 1);
            if($res1!==false && $res2!==false){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 编辑sku
     */
    public function editSku(){
        if(IS_AJAX){
            $id        = I("post.categoryid");
            $classname = I("post.classname");
            $pid       = I("post.fid");
            $sort      = I("post.sort");
            $img       = I("post.img");
            $m = M("skuAttr");
            $map = array(
                "classname" => $classname,
                "pid"       => $pid,
                "id"        => array("neq", $id),
            );
            $sku_g = $m->find($id);
            $res = $m->where($map)->find();
            if($res['goods_id'] == $sku_g['goods_id']){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $parid = $m->where(array("id"=>$id, "isdel"=>0))->getField("pid");
            if($parid == 0 && $pid != 0){
                $this->ajaxReturn(array("status"=>0, "info"=>"顶级sku无法改变sku！"));
            }
            $data['classname'] = $classname;
            $data['pid']       = $pid;
            $data['sort']      = $sort;
            if($img){
                $data['img'] = $img;
            }
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }

    /**
     * 产品列表
     */
    public function goodsList(){
        $cate_id=I('post.cate_id');
        $name=I('post.name');
        $this->assign('cate_id', $cate_id);
        $maxprice = I('post.maxprice');
        $minprice = I('post.minprice');
        $class = I('post.class');
        $this->assign('class',$class);
        $is_sku = I('post.is_sku');
        $this->assign('is_sku',$is_sku);
        $is_sale = I("post.is_sale");
        $this->assign('is_sale',$is_sale);
        $this->assign('maxprice', $maxprice);
        $this->assign('minprice', $minprice);
        $this->assign('name', $name);
        //查询分类
        if($cate_id){
            $pid=M('cate')->where(array('id'=>$cate_id))->getfield('pid');
            if(!$pid)
            {
                $arr=M('cate')->where(array('pid'=>$cate_id))->getfield('id', true);
                $map['cate_id'] = array('in',$arr);
            }else{
                $map['cate_id'] = $cate_id;
            }
        }
        //查询商品名称
        if($name)
        {
            $map['goods_name'] = array('like',"%$name%");
        }
        //查询价格区间
        if($minprice){
            $map['oprice'] = array('egt',$minprice);
        }
        if($maxprice){
            $map['oprice'] = array('elt',$maxprice);
        }
        //查询sku
        if($is_sku != null && $is_sku == 0){
            $map['is_sku']  = 0;
        }
        if($is_sku != null && $is_sku == 1){
            $map['is_sku']  = 1;
        }
        //查询上下架
        if($is_sale != null && $is_sale == 0){
            $map['is_sale']  = 0;
        }
        if($is_sale != null && $is_sale == 1){
            $map['is_sale']  = 1;
        }
        //查询 class
        if($class != null){
            $map[$class] = 1;
        }


        $m   = M("goods");
        $map['isdel'] = 0;
        if($is_onsale = I("get.is_sale")){
            $map['is_sale'] = intval($is_onsale)-1;
        }
        $count=$m->where($map)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();//分页显示输出
        $res = $m->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('ID desc')->select();

        foreach($res as $k=>$v){
            $res[$k]['classname'] = M("cate")->where(array('id'=>$v['cate_id']))->getField('classname');
        }

        //分类列表
        $categorylist = M("cate")->where(array("pid"=>0, "isdel"=>0))->select();
        foreach($categorylist as $k=>$v){
            $categorylist[$k]['cate'] = M("cate")->where(array('pid'=>$v['id']))->select();

            foreach ($categorylist[$k]['cate'] as $key => $value) {
                $categorylist[$k]['cate'][$key]['child'] = M("cate")->where(array('pid'=>$value['id']))->select();
            }


        }
        $this->assign("categorylist", $categorylist);

        $this->assign("page",$show);
        $this->assign("counts", $m->where(array("isdel"=>0))->count());     //全部
        $this->assign("count1", $m->where(array("isdel"=>0, "is_sale"=>1))->count());  //出售
        $this->assign("count2", $m->where(array("isdel"=>0, "is_sale"=>0))->count());   //未出售


        $this->assign("cache", $res);
        $this->assign("comptype", 3);
        $this->display();
    }


    /**
     * sku_list列表
     */
    public function setSKUimg(){
        $m   = M("sku_list");
        $gm  = M("goods");
        $id  = I("get.id");
        //获取当前商品的信息
        $info = $gm ->field('goods_name') ->where(array('id'=>$id)) ->find();

        $res  = $m ->where(array('goods_id'=>$id, 'status'=>1)) ->select();//dump($res);die;
        foreach ($res as $k => $v) {
            $sku_list = array_filter(explode("-",$v['attr_list']));//var_dump($sku_list);die;
            $sku = "";
            foreach ($sku_list as $key => $value) {
                $sku .= M('sku_attr')->where(array('id'=>$value))->getField("classname").";";
            }
            $res[$k]['attr_list'] = trim($sku,";");
        }
        $this->assign('info', $info);
        $this->assign('res', $res);

        $this->display();
    }
    /**
     * 编辑sku图片
     */
    public function editSKUimg(){
        if(IS_AJAX){
            $id        = I("post.id");
            $img       = I("post.img");
            $m = M("sku_list");
            if($img){
                $data['img'] = $img;
            }
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }




    /**
     * 新增产品
     */
    public function addGoods(){

        if(IS_POST){
            $m = M("goods");
            $g_s_m = M("goodsSlide");
            $data = I("post.");
            $slide_pic = $data['pic1'];
            if(I("post.is_sale")){
                $data['sale_at']=time();
            }
            unset($data['pic1']);
            $data['create_at'] = time();
            $res = $m->add($data);
            if($res){
                $ewmtext = $res;
                $filename=date('YmdHis', time()) . rand(1000, 9999).".png";
                $picPath="Uploads/Picture/QRcode";
                $filename = qrcode($ewmtext,$filename,$picPath,false,5);
                $file['codeimg'] = $filename;
                $save = $m->where(array('id'=>$res))->save($file);
                foreach($slide_pic as $k=>$v){
                    $slide_data = array(
                        "goods_id"   => $res,
                        "sort"       => $k,
                        "create_at"  => time(),
                        "pic"        => $v,
                        "status"     => 1,
                    );
                    $g_s_m->add($slide_data);
                }
                $this->success("新增成功！",U('Admin/Goods/goodslist'));exit;
            }else{
                $this->error("新增失败！");
            }

        }
        $c    = M("cate");
        $categorylist = $c->where(array("pid"=>0, "isdel"=>0))->select();
        foreach($categorylist as $k=>$v){
            $categorylist[$k]['cate'] = $c->where(array('pid'=>$v['id'],'isdel'=>0))->select();
            foreach ($categorylist[$k]['cate'] as $key => $value) {
                $categorylist[$k]['cate'][$key]['child'] = M("cate")->where(array('pid'=>$value['id']))->select();
            }
        }
        $this->assign("categorylist", $categorylist);
        $this->display();
    }




    /**
     * 编辑产品
     */
    public function editGoods(){
        if(IS_POST){
            $m = M("goods");
            $g_s_m = M("goodsSlide");
            $data = I("post.");
            $id   = $data['id'];
            $slide_pic = $data['pic1'];
            unset($data['id']);
            unset($data['pic1']);
            $data['create_at'] = time();
            if(!$id){
                $this->error("缺少参数！");
            }
            $res = $m->where(array("id"=>$id,'isdel'=>0))->save($data);
            if($res !== false){
                foreach($slide_pic as $k=>$v){
                    $slide_data = array(
                        "goods_id"   => $id,
                        "sort"       => $k,
                        "create_at"  => time(),
                        "pic"        => $v,
                        "status"     => 1,
                    );
                    $g_s_m->add($slide_data);
                }
                $this->success("修改成功!",U('Admin/Goods/goodslist'));exit;
            }else{
                $this->error("修改失败！");
            }
        }
        $id = I("id");
        if(!$id){
            echo "<script>alert('缺少参数！');window.history.back();</script>";die;
        }
        $goods = M("goods")->where(array('id'=>$id, "isdel"=>0))->find();
        if(!$goods){
            echo "<script>alert('无此商品！');window.history.back();</script>";die;
        }
        $c    = M("cate");
        $goods['classname'] = $c->where(array('id'=>$goods['cate_id'],'isdel'=>0))->getField('classname');
        $categorylist = $c->where(array("pid"=>0, "isdel"=>0))->order('sort asc')->select();
        foreach($categorylist as $k=>$v){
            $categorylist[$k]['cate'] = $c->where(array('pid'=>$v['id']))->order('sort asc')->select();
            foreach ($categorylist[$k]['cate'] as $key => $value) {
                $categorylist[$k]['cate'][$key]['child'] = M("cate")->where(array('pid'=>$value['id']))->select();
            }
        }
        $goods_slide = M("goodsSlide")->where(array('goods_id'=>$id, "status"=>1,'isdel'=>0))->select();
        $this->assign("goods_slide", $goods_slide);
        $this->assign("cache", $goods);
        $this->assign("categorylist", $categorylist);
        $this->display();
    }


    /**
     * 删除产品
     */
    public function delGoods(){
        if(IS_AJAX){
            $id  = I("ids");
            $type  = I("type");
            if($type){
                $m   = M("bathgoods");
            }else{
                $m   = M("goods");
            }

            $ids = array_filter(explode("-", $id));
            if(empty($ids)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请选择商品！"));
            }
            foreach($ids as $v){
                $res = $m->where(array("id"=>$v))->save(array("isdel"=>1));
                if($res === false){
                    $this->ajaxReturn(array("status"=>0, "info"=>"删除商品失败！"));
                }
            }
            $this->ajaxReturn(array("status"=>1, "info"=>"删除商品成功！"));
        }
        $id  = I("id");
        $res = M("goods")->where(array("id"=>$id))->save(array("isdel"=>1));
        if($res!==false){
            $this->success("删除成功！");die;
        }
        $this->error("删除失败！");die;
    }


    public function delGoodsSlide(){
        if(IS_POST){
            $id = I('param.id');
            $m = M('goodsSlide');
            if($id){
                $del = $m->where(array('id'=>$id))->delete();
                if($del){
                    $arrys = array('status'=>1);
                }else{
                    $arrys = array('status'=>0,'info'=>"删除失败");
                }

                echo json_encode($arrys);exit;

            }


        }

    }

    /**
     * 修改商品部分状态
     */
    public function changeStatus(){
        if(IS_AJAX){
            $id   = I("post.id");
            $item = I("post.item");
            $m    = M("goods");
            $res  = $m->where(array("id"=>$id))->find();
            if(!$res){
                $this->ajaxReturn(array("status"=>0 ,"info"=>"修改失败！"));
            }
            if($item == "is_sku"){
                $res3 = M("skuList")->where(array("goods_id"=>$id, "status"=>1))->count();
                if(!$res3 || !$res['goods_sku_info']){
                    $this->ajaxReturn(array("status"=>0 ,"info"=>"请先设置sku！"));
                }
            }
            $res2 = $m->where(array("id"=>$id))->setField($item, 1-intval($res[$item]));/*array('$item'=>'ThinkPHP','email'=>'ThinkPHP@gmail.com');*/
            if($res2){
                $sale=$m->where(array("id"=>$id))->getfield("is_sale");
                if($sale==1)
                {
                    $m->where(array("id"=>$id))->setField('sale_at',time());
                }else{
                    $m->where(array("id"=>$id))->setField('sale_at'."");
                    if($item == "is_sale"){
                        M("customer_goods")->where(array('goods_id'=>$id))->setField('is_onsale',0);
                    }
                }
                $arr = array(1,2);
                $this->ajaxReturn(array("status"=>$arr[$res[$item]]));
            }
            $this->ajaxReturn(array("status"=>0 ,"info"=>"修改失败！"));
        }
    }




    /**
     * 修改选中商品部分状态
     */
    public function changeAllStatus(){
        if(IS_AJAX){
            $ids   = array_filter(explode("-", I("post.ids")));
            $item = I("post.item");
            $type = I("post.type");
            if($type){
                $m    = M("bathgoods");
            }else{
                $m    = M("goods");

            }

            foreach($ids as $id){
                $res  = $m->where(array("id"=>$id))->find();
                if(!$res){
                    $this->ajaxReturn(array("status"=>0 ,"info"=>"部分产品修改失败！"));
                }
                if($item == "is_sku"){
                    $res3 = M("skuList")->where(array("goods_id"=>$id, "status"=>1))->count();
                    if(!$res3 || !$res['goods_sku_info']){
                        $this->ajaxReturn(array("status"=>0 ,"info"=>"部分产品修改失败！"));
                    }
                }
                $res2 = $m->where(array("id"=>$id))->setField($item, 1-intval($res[$item]));
                if(!$res2){
                    $sale=$m->where(array("id"=>$id))->getfield("is_sale");
                    if($sale==1)
                    {
                        if($item == "is_sale"){
                            M("customer_goods")->where(array('goods_id'=>$id))->setField('is_onsale',0);
                        }
                        $m->where(array("id"=>$id))->setField('sale_at',time());
                    }else{
                        $m->where(array("id"=>$id))->setField('sale_at'."");
                    }

                    $this->ajaxReturn(array("status"=>0 ,"info"=>"部分产品修改失败！"));
                }
            }
            $this->ajaxReturn(array("status"=>1));
        }
    }



    /**
     * 设置sku页面
     */
    public function setSKU(){
        if(!($id = I("id"))){
            echo "<script>alert('缺少参数！');window.history.back();</script>";die;
        }
        $m = M("skuAttr");
        $goods = M("goods")->find($id);
        if(!$goods){
            echo "<script>alert('没有这个产品！');window.history.back();</script>";die;
        }
        $table_str = "<tr></tr>";
        $goods_skus = M("sku_list")->where(array("goods_id"=>$id, "status"=>1))->select();

        foreach($goods_skus as $k=>$v){
            $table_str .= "<tr>";
            $skus_arr = array_filter(explode("-", $v['attr_list']));
            foreach($skus_arr as $kk=>$vv){
                $table_str .= "<td class='sku-attr-data' data-id='{$vv}'>";
                $table_str .= $m->where(array('id'=>$vv))->getField("classname");
                $table_str .= "</td>";
            }
            $table_str .= "<td>库存<input name='store' value='{$v['store']}' style='width:50px;'></td>";
            $table_str .= "<td>原价<input name='oprice' value='{$v['oprice']}' style='width:50px;'></td>";
            $table_str .= "<td>现价<input name='price' value='{$v['price']}' style='width:50px;'></td>";
            $table_str .= "<td>spu<input name='spu' value='{$v['spu']}' style='width:150px;'></td>";
            $table_str .= "<td>图片<img src='{$v[img]}' width='50' ></td>";
            $table_str .= "</tr>";
        }
        $sku   = $this->skuNameList($id);

        $this->assign("sku",   json_encode($sku));
        $this->assign("table", $table_str);
        $this->assign("goods_name", $goods['goods_name']);
        $this->assign("id",    $id);
        $this->assign("skuCache", $sku);
        $this->display();
    }


    /**
     * sku列表
     */
    public function skuNameList($id){
        $m   = M("skuAttr");
        $map = array(
            "goods_id" => $id,
            "pid"      => 0,
            "isdel"    => 0,
        );
        $res = $m->where($map)->field("id,classname")->select();
        return $res?$res:array();
    }


    /**
     * 生成对应的sku表格
     *     得到sku的id,组合成表格
     */
    /**
     * 生成对应的sku表格
     *     得到sku的id,组合成表格
     */
    public function makeSkuTable(){
        $ids = I("ids");
        $goods_id = I("goods_id");
        // 获取到子sku参数
        $m = M("skuAttr");
        $skuarr=array();
        $str = "<tr>";
        foreach($ids as $id){
            $skuarr[] = $m->where(array("pid"=>$id, "isdel"=>0, "status"=>1))->field("id,classname")->order("sort desc")->select();
            $str .= "<th>";
            $str .= $m->where(array('id'=>$id))->getField("classname");
            $str .= "</th>";
        }
        $str .= "<th>库存</th><th>会员价</th><th>优惠价</th><th>spu</th><th></th></tr>";
        $arr = mixSku($skuarr);
        foreach($arr as $v){
            $str .= "<tr>";
            $sku_array = array();
            $s_data = array();
            foreach($v as $vv){
                $sku_array[] = $vv['id'];
                $s_data[$vv['id']] = $vv['classname'];
                $str .= "<td class='sku-attr-data' data-id='{$vv[id]}'>{$vv[classname]}</td>";
            }
            sort($sku_array);

            $sku_str = implode("-", $sku_array);

            $sku_str = "-".$sku_str."-";

            $data = M("sku_list")->where(array('attr_list'=>$sku_str,"goods_id"=>$goods_id))->find();

            $str .= "<td><input name='store' value='{$data['store']}' style='width:50px;'></td>
                    <td><input name='oprice' value='{$data['oprice']}' style='width:50px;'></td>
                    <td><input name='price' value='{$data['price']}' style='width:50px;'></td>
                    <td><input name='spu' value='{$data['spu']}' style='width:100px;'></td>
                    <td><img src='{$data['img']}' style='width:50px;'></td>";
            $str .= "</tr>";
        }

        exit(json_encode(array(
            "data" => $skuarr,
            "html" => $str
        )));

    }

    /**
     * 保存商品对应的sku
     * 保存sku，并保存商品对应的sku选项，例如：
     *   array(
     *       "颜色" => array("1"=>"红色","2"=>"蓝色"),
     *       "尺寸" => array(3=>"S",4=>"M",5=>"L"),
     *   )
     */
    public function addGoodsSkuAttr(){
        if(IS_AJAX){
            $m        = M("skuList");
            $sku_m    = M("skuAttr");
            $sku_arr  = I("post.");
            $goods_id = $sku_arr['goods_id'];
            unset($sku_arr['goods_id']);
            // 查询旧sku，将暂时用不到的sku isdel=》1

            // 添加新的sku
            $arrs=array();
            $goods_sku_info=array();
            foreach($sku_arr as $k=>$v){
                $ids_arr = array_filter(explode("-",$v['ids']));
                sort($ids_arr);
                foreach($ids_arr as $kk=>$vv){
                    $sku_a  = $sku_m->where(array('id'=>$vv))->find();
                    $pid  = $sku_a['pid'];
                    $psku = $sku_m->where(array('id'=>$pid, "pid"=>0, "isdel"=>0))->getField("classname");
                    if(array_key_exists($psku, $goods_sku_info)){
                        $goods_sku_info[$psku][$sku_a['id']] = array('name'=>$sku_a['classname'],"img"=>$sku_a['img']);
                    }else{
                        $goods_sku_info[$psku] = array($sku_a['id']=>array('name'=>$sku_a['classname'],"img"=>$sku_a['img']));
                    }
                }
                $arrs[$k] = "-".implode("-", $ids_arr)."-";
            }
            // 得到对应pid的classname，并组成下表
            $old_sku = $m->where(array("goods_id"=>$goods_id))->select();
            $arr_keys = array_flip($arrs);
            foreach($old_sku as $k=>$v){
                if(in_array($v['attr_list'], $arrs)){
                    // 这里未做完
                    $data = array(
                        "oprice" => $sku_arr[$arr_keys[$v['attr_list']]]['oprice'],
                        "price"  => $sku_arr[$arr_keys[$v['attr_list']]]['price'],
                        "store"  => $sku_arr[$arr_keys[$v['attr_list']]]['store'],
                        "spu"    => $sku_arr[$arr_keys[$v['attr_list']]]['spu'],
                        "desc"   => $sku_arr[$arr_keys[$v['attr_list']]]['desc'],
                        "status" => 1,
                    );
                    // 这里未做完
                    $m->where(array('id'=>$v['id']))->save($data);
                    unset($sku_arr[$arr_keys[$v['attr_list']]]);
                }else{
                    $m->where(array('id'=>$v['id']))->setField("status", 0);
                }
            }
            foreach($sku_arr as $k=>$v){
                $data = array(
                    "goods_id"  => $goods_id,
                    "attr_list" => $arrs[$k],
                    "oprice"    => $v['oprice'],
                    "price"     => $v['price'],
                    "store"     => $v['store'],
                    "desc"     => $v['desc'],
                    "status"    => 1,
                );
                $m->add($data);
            }
            $goods_sku_info = serialize($goods_sku_info);
            M("goods")->where(array('id'=>$goods_id))->setField(array("goods_sku_info"=>$goods_sku_info,"is_sku"=>1));
            $this->ajaxReturn(array("status"=>1 ,"info"=>"执行成功！"));
        }
    }




    /**
     * 查看套餐关联的产品
     */
    public function editbathlinkcate(){
        $id = I("id");
        $catelist = M("bathgoods_cate")->select();
        $cate = array();

        foreach($catelist as $v){
            $cate[$v['id']] = $v;
        }


        $m = M("package_sku_attr");
        $attr_list = M("package_sku_list")->where(array('id'=>$id))->getField('attr_list');
        $table_str = "";
        $skus_arr = array_filter(explode("-", $attr_list));
        foreach($skus_arr as $kk=>$vv){
            $table_str .= "[".$m->where(array('id'=>$vv))->getField("classname")."]";
        }
        $this->assign("sku_info",$table_str);
        $table_str = trim($table_str, "——");
        $goods_skus[$k]['sku_info'] = $table_str;

        $links = M("bath_link_cate")->where(array("sku_id"=>$id))->order("sort desc")->select();


        $bath_id = M("package_sku_list")->where(array('id'=>$id))->getField('goods_id');


        $this->assign("bath_id", $bath_id);

        $bath_name = M("bathgoods")->where(array('id'=>$bath_id))->getField("goods_name");

        $this->assign("bath_name",$bath_name);

        $this->assign("sku_id", $id);



        foreach($links as $v){
            $bath_cate[] = array(
                "id" => $v['id'],
                "classname" => M("bathgoods_cate")->where(array('id'=>$v['cate_id']))->getField('classname'),
            );
            $bath_cate_list[] = $v['cate_id'];
        }
        foreach($catelist as $k=>$v){
            if(in_array($v['id'], $bath_cate_list)){
                $catelist[$k]['disabled'] = 1;
            }
        }
        $this->assign("catelist", $catelist);

        $this->assign("bath_cate", $bath_cate);
        $m = M("goods");
        $DB_PREFIX = C("DB_PREFIX");
        foreach($links as $k=>$v){
            $join_str = "join {$DB_PREFIX}goods_link_cate as l on l.bath_id={$bath_id} and l.link_id={$v[id]} and l.goods_id=g.id";
            $links[$k]['isdel'] = $cate[$v['cate_id']]['isdel'];
            $links[$k]['status'] = $cate[$v['cate_id']]['status'];
            $links[$k]['classname'] = $cate[$v['cate_id']]['classname'];
            $data = $m->alias('g')->field("l.*,g.goods_name,g.oprice,g.price,g.index_pic,g.goods_des,g.weight")->
            join($join_str)->order("l.sort desc")->select();
            foreach($data as $k1=>$v1){
                if($v1['sku_id']){
                    $sku_info = M("sku_list")->find($v1['sku_id']);
                    $data[$k1]['price'] = $sku_info['price'];
                    $data[$k1]['oprice'] = $sku_info['oprice'];
                    if($sku_info['img']){
                        $data[$k1]['index_pic'] = $sku_info['img'];
                    }
                }
            }
            $links[$k]['data'] = $data;
            $links[$k]['count'] = count($data);
        }

        $this->assign("cache", $links)->display();
    }



    /**
     * 删除套装中的内商品
     */
    public function delgoodslinkcate(){
        $id = I("id",0,"intval");
        if(!($id>0)){
            $this->error("删除无效！");
        }
        $res = M('goods_link_cate')->delete($id);
        if($res){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }

    /**
     * 模糊搜索得到商品
     */
    public function searchGoods(){
        if(IS_AJAX){
            $keywords = I("post.keywords");
            $map['isdel'] = 0;
            $map['goods_name'] = array('like',"%{$keywords}%");
            $goods = M('goods')->where($map)->select();
            if(empty($goods)){
                $this->ajaxReturn(array('status'=>0,"info"=>"未找到相应的商品"));
            }
            $str = "<option value=''>选择商品</option>";
            foreach($goods as $v){
                $str .= "<option value='{$v[id]}'>【{$v[id]}】{$v[goods_name]}</option>";
            }
            $this->ajaxReturn(array('status'=>1,"html"=>$str));
        }
    }

    public function getGoodsSku(){
        if(IS_AJAX){
            $id = I("post.id");
            // $id = 46;
            $goods = M("goods")->find($id);
            $str = "";
            if($goods['is_sku']){
                $skus = M('sku_list')->where(array('goods_id'=>$id,"status"=>1))->select();
                foreach($skus as $k=>$v){
                    $sku_arr = array_filter(explode("-", $v['attr_list']));
                    $str .= "<option value='{$v[id]}'>";
                    foreach($sku_arr as $k1=>$v1){
                        $str .= M('sku_attr')->where(array('id'=>$v1))->getField('classname');
                        $str .= ",";
                    }
                    $str = rtrim($str,",");
                    $str .= "</option>";
                }
                $this->ajaxReturn(array('status'=>1,"html"=>$str));
            }else{
                $this->ajaxReturn(array('status'=>0));
            }
        }
    }







}
