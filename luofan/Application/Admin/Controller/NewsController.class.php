<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class NewsController extends CommonController{
    public function index(){
        $m   = M("News_cate");
        $list=$m->order("pid asc,sort asc")->select();
        $newlist=array();
        $plist=array();
        foreach ($list as $key => $value) {
            if($value['pid']==0){
               $value['classnames']=$value['classname'];
               $newlist[]=$value;
               $plist[]=$value;
               foreach ($list as $key1 => $value1) {
                   if($value1['pid']==$value['id']){
                      $value1['classnames']="　　|--".$value1['classname'];
                      $newlist[]=$value1;
                   }
               }
            }
        }
        $count=count($newlist);
        $p=getpage($count,40);
        $page=$p->show();
        $newlist=array_slice($newlist,$p->firstRow,$p->listRows);
        
        $this->assign("page",$page);
        $this->assign("plist",$plist);
        $this->assign("newlist",$newlist);
        // dump($newlist);
        // die;


        // $count = $m->count();    //总数
        // $p = getpage($count, 10);
        // $res = $m->where(array("pid"=>0))->order("sort asc")->select();           //获取每页数据
        // $cate = $m->where(array("pid"=>0))->order("sort Asc")->field("id,classname")->select();
        // foreach($res as $k=>$v){
        //     $res[$k]["data"] = $m->where(array("pid"=>$v['id']))->order("sort Asc")->select();
        //     foreach ($res[$k]["data"] as $key => $val) {
        //         $res[$k]["data"][$key]['child'] = $m->where(array("pid"=>$val['id']))->order("sort Asc")->select();
        //     }
        // }
        // $this->assign("cache", $res);
        // $this->assign("cate",  $cate);
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
        $title  = I("get.title");
        $class_id = I("get.class");
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('news_title',$title);
        }
        if($class_id){
          $map['cate_id|cate_pid'] = $class_id;
          $this->assign('cate_id',$class_id);
        }
        $m = M('NewsCate');
        $news_type = $m->select();
        $this->assign('news_type',$news_type);
        $this->assign('news_type',$news_type);
        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 10);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->display();
    }

    public function addnews(){
        $m  = M('News');
        $id = intval(I('param.id'));
        $pid=I("get.pid");
        $cateList = M("News_cate")->field('id,classname')->where(array("pid"=>$pid))->order('sort asc')->select();
        if(!$cateList){
            $cateList = array(M("News_cate")->field('id,classname')->where(array("id"=>$pid))->find());
        }
        $this->assign('cateList',$cateList);
        $this->assign('pid',$pid);
        if(IS_POST){
            $data=array(
                'news_title'=> I("post.news_title"),
                'detail'    => I("post.detail"),
                'is_hot'    => I("post.is_hot"),
                'sort'      => I("post.sort"),
                'addtime'   => I("post.addtime"),
                'province'  => I("post.province"),
                //'city'      => $cityName,
                //'city_card' => $cityCard,
                'cate_name' => I("post.cate"),
                'cate_pid'  => I("post.cate_id"),
                'author'    => I("post.author")?I("post.author"):'佚名',
                'source'    => I("post.source"),
                'ads'       => I("post.ads"),  //位置
            );
            if($data['cate_pid']){
                $data['cate_id'] = M("news_cate")->where(array('id'=>$data['cate_pid']))->getField('pid');
                if(!$data['cate_id']){
                    unset($data['cate_pid']);
                    $data['cate_id'] = I("post.cate_id");
                }
            }
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
                '59'=>'personlist',
                '29'=>'houselist',
                '63'=>'carlist',
                //'66'=>'creditlist',
                '64'=>'baikelist',
                '66'=>'lifelist',
                '62'=>'licailist',
            );
            $action = $action[$pid];
            if(!$action)
                $action = 'news';
            if($res){
                $this->success("操作成功",U('/Admin/news/'.$action));exit;
            }else{
                $this->success("操作失败");exit;
            }
        }
        // $map = "LevelType=1 or LevelType=0";
        // $provinceList = M("region")->where($map)->select();
        // foreach($cateList as $k=>$v){
        //       $cateList[$k]["data"] = M("News_cate")->where(array("pid"=>$v['id']))->order("sort Asc")->select();
        //       foreach ($cateList[$k]["data"] as $key => $val) {
        //           $cateList[$k]["data"][$key]['child'] = M("News_cate")->where(array("pid"=>$val['id']))->order("sort Asc")->select();
        //       }
        // }
        $pid=I("get.pid");
        if(!empty($id)){
            $info = $m->find($id);
            $this->assign('cache',$info);
        }
        $this->display();
    }

     public function addperson(){
         if(IS_AJAX){
            $data=I("post.");
            if($data['id']>0){
                $data['edit_time']=date("Y-m-d H:i:s",time());
                $res=M("news_person")->save($data);
            }else{
                $data['add_time']=date("Y-m-d H:i:s",time());
                $res=M("news_person")->add($data);
            }
           if($res){
                $this->ajaxReturn(array("status"=>1,"info"=>" 操作成功","url"=>U('admin/news/personlist')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"操作失败"));
            }
       }

       $id=I("get.id");
       if(!empty($id)){
          $data=M("news_person")->find($id);
           if(empty($data)){
               $this->error("不存在该内容!");
           }
           $this->assign("cache",$data);
       }
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
   public function changestatu(){
      $id  = I("post.id");
      $hot = M("news_person")->where(array("id"=>$id))->getField("status");
      $hot = $hot?0:1;
      $res = M("news_person")->where(array("id"=>$id))->setField(array("status"=>$hot));
      if($res){
        $this->ajaxReturn(array('status'=>$hot?1:2,'info'=>'修改成功！'));
      }else{
        $this->ajaxReturn(array('status'=>0,'info'=>'修改失败！'));
      }
   }

   public function delnews(){
       $id=I("post.id");
       $res=M('News')->where(array('id'=>$id))->delete();
       if($res){
           $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功"));
           return;
       }else{
           $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
           return;
       }
   }
   public function delpersonnews(){
       if(IS_AJAX){
            $id = I('post.id');
            $arr = explode('_',$id);
            $arr = implode(',',$arr);
            $arr =  rtrim($arr,',');
            $data['id'] = array('in',$arr);
            $data['is_del']=1;
            $del = M('news_person')->save($data);
            if($del){
                $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
            }
        }
   }

   //今日头条列表 dyh
   public function todaynews(){

       $where=array();
       $news_title=I("get.news_title");
       if($news_title!=null){
          $where['news_title']=array("like","%$news_title%");
          $this->assign("news_title",$news_title);
       }
       
       $count=M("jinri")->where(array("is_del"=>0))->where($where)->count();
       $p=getpage($count,10);
       $page=$p->show();
       $list=M("jinri")->where(array("is_del"=>0))->where($where)->limit($p->firstRow,$p->listRows)->order("sort desc,release_time desc,id desc")->select();

       $this->assign("list",$list);
       $this->assign("page",$page);
       $this->display();
   }
   //今日头条详情 dyh
   public function todaycontent(){
       if(IS_AJAX){
            $data=I("post.");
            if($data['id']>0){
                $data['edit_time']=date("Y-m-d H:i:s",time());
                $res=M("jinri")->save($data);
            }else{
                $data['add_time']=date("Y-m-d H:i:s",time());
                $res=M("jinri")->add($data);
            }
           if($res){
                $this->ajaxReturn(array("status"=>1,"info"=>" 操作成功","url"=>U('admin/news/todaynews')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"操作失败"));
            }
       }

       $id=I("get.id");
       if(!empty($id)){
          $data=M("jinri")->find($id);
           if(empty($data)){
               $this->error("不存在该内容!");
           }
           $this->assign("cache",$data);
       }
       $this->display();
   }

   public function deltoday(){
      if(IS_AJAX){
            $id = I('post.id');
            $arr = explode('_',$id);
            $arr = implode(',',$arr);
            $arr =  rtrim($arr,',');
            $data['id'] = array('in',$arr);
            $data['is_del']=1;
            $del = M('jinri')->save($data);
            if($del){
                $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('admin/news/todaynews')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
            }
        }
   }

   public function statustoday(){
         if(IS_AJAX){
                $id = I("id");
                $m = M("jinri");
                $res = $m->where("id=$id")->field("id,status")->find();
                if($res){
                    $res['status'] = $res['status']==1?0:1;
                    $res2 = $m->save($res);
                    if($res2){
                        $arr = array("启用","禁用");
                        $return = array(
                            "status" => 1,
                            "info" => $arr[$res['status']]
                        );
                   

                    }else{
                        $return = array(
                            "status" => 0
                        );
                   
                    }
                }else{
                    $return = array(
                        "status" => 2
                    );
                }
               $this->ajaxReturn($return);
            }
   }

   public function delgonglue(){
      if(IS_AJAX){
            $id = I('post.id');
            $arr = explode('_',$id);
            $arr = implode(',',$arr);
            $arr =  rtrim($arr,',');
            $data['id'] = array('in',$arr);
            $data['is_del']=1;
            $del = M('gonglue')->save($data);
            if($del){
                $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('admin/news/gongluenews')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
            }
        }
   }

   public function statusgonglue(){
         if(IS_AJAX){
                $id = I("id");
                $m = M("gonglue");
                $res = $m->where("id=$id")->field("id,status")->find();
                if($res){
                    $res['status'] = $res['status']==1?0:1;
                    $res2 = $m->save($res);
                    if($res2){
                        $arr = array("启用","禁用");
                        $return = array(
                            "status" => 1,
                            "info" => $arr[$res['status']]
                        );
                    }else{
                        $return = array(
                            "status" => 0
                        );
                   
                    }
                }else{
                    $return = array(
                        "status" => 2
                    );
                }
               $this->ajaxReturn($return);
            }
   }

   public function gongluenews(){
      $where=array();
      $where['cate_id']=1;
      $where['is_del']=0;
      $cate_id=I("get.cate_id");
      if($cate_id!=null){
          $where['cate_id']=$cate_id;
      }

       $news_title=I("get.news_title");
       if($news_title!=null){
          $where['news_title']=array("like","%$news_title%");
          $this->assign("news_title",$news_title);
       }

      $count=M("gonglue")->where($where)->count();
      $p=getpage($count,10);
      $page=$p->show();
      $list=M("gonglue")->where($where)->limit($p->firstRow,$p->listRows)->order("sort desc,release_time desc,id desc")->select();
       $this->assign("cate_id",$where['cate_id']);
       $this->assign("list",$list);
       $this->assign("page",$page);
       $this->display();
   }

   public function gongluecontent(){
     if(IS_AJAX){
            $data=I("post.");
            if($data['id']>0){
                $data['edit_time']=date("Y-m-d H:i:s",time());
                $res=M("gonglue")->save($data);
            }else{
                $data['add_time']=date("Y-m-d H:i:s",time());
                $res=M("gonglue")->add($data);
            }
           if($res){
                $this->ajaxReturn(array("status"=>1,"info"=>" 操作成功","url"=>U('admin/news/gongluenews')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"操作失败"));
            }
       }

       $id=I("get.id");
       if(!empty($id)){
          $data=M("gonglue")->find($id);
           if(empty($data)){
               $this->error("不存在该内容!");
           }
           $this->assign("cache",$data);
       }
       $this->display();
   }


   public function personList(){
        // $title    = I("get.name");
        // // $cate   = M("news_cate")->where(array('pid'=>0,'sort'=>1))->find();
        // $pid=59;
        // $this->assign("pid",$pid);
        // $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        // // dump($sel);
        // // die;
        // $map['cate_id'] = $pid;
        // $this->assign('sel',$sel);
        // if($title){
        //   $map['news_title'] = array('like','%'.$title.'%');
        //   $this->assign('title',$title);
        // }
        // $count  = M('News')->where($map)->count();
        // $Page   = getpage($count, 30);
        // $show   = $Page->show();//分页显示输出
        // $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $map=array();
        $map['is_del']=0;
        $title    = I("get.name");
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
       
        $count  = M('news_person')->where($map)->count();
        $Page   = getpage($count, 10);
        $show   = $Page->show();//分页显示输出
        $List   = M('news_person')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort desc,release_time desc,id desc')->select();
    
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',$cate['classname']);
        $this->display();
    }


   public function houseList(){
        $title    = I("get.name");
        $classname  = I('get.classname');
        $pid=29;
        $this->assign("pid",$pid);
        $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        // dump($sel);
        // die;
        $map['cate_id'] = $pid;
        $this->assign('sel',$sel);
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }

        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 10);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"房贷资讯");
        $this->display();
    }



   public function carList(){
        $title    = I("get.name");
        $classname  = I('get.classname');
        $pid=63;
        $this->assign("pid",$pid);
        $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        // dump($sel);
        // die;
        $map['cate_id'] = $pid;
        $this->assign('sel',$sel);
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }
        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 30);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"车贷资讯");
        $this->display();
    }

    //信用卡资讯
    public function creditList(){
       $title    = I("get.name");
        $classname  = I('get.classname');
        $pid=66;
        $this->assign("pid",$pid);
        $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        // dump($sel);
        // die;
        $map['cate_id'] = $pid;
        $this->assign('sel',$sel);
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }

        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 10);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);


        $this->assign('classname',"信用卡资讯");

        $this->display();
    }


    public function baikeList(){
        $title    = I("get.name");
        $classname  = I('get.classname');
        $pid=64;
        $this->assign("pid",$pid);
        $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        $map['cate_id'] = $pid;
        $this->assign('sel',$sel);
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }
        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 30);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"百科资讯");
        $this->display();
    }

    //信用生活
    public function lifeList(){
       $title    = I("get.name");
        $classname  = I('get.classname');
        $pid=66;
        $this->assign("pid",$pid);
        $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        // dump($sel);
        // die;
        $map['cate_id'] = $pid;
        $this->assign('sel',$sel);
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }

        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 30);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);


        $this->assign('classname',"信用生活资讯");

        $this->display();
    }
    //问答
    public function wendaList(){
       $title    = I("get.name");
        $classname  = I('get.classname');
        $cate   = M("news_cate")->where(array('pid'=>0,'sort'=>7))->find();
        $sel    = M("news_cate")->where(array('pid'=>$cate['id']))->select();
        $map['cate_id'] = $cate['id'];
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }
        $this->assign('sel',$sel);

        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 10);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',$cate['classname']);

        $this->display();
    }

    //理财资讯
    public function licaiList(){
       $title    = I("get.name");
        $classname  = I('get.classname');
        $pid=62;
        $this->assign("pid",$pid);
        $sel    = M("news_cate")->where(array('pid'=>$pid))->select();
        $map['cate_id'] = $pid;
        $this->assign('sel',$sel);
        if($title){
          $map['news_title'] = array('like','%'.$title.'%');
          $this->assign('title',$title);
        }
        if($classname){
          $map['cate_id|cate_pid'] = $classname;
          $this->assign('cate_id',$classname);
        }
        $count  = M('News')->where($map)->count();
        $Page   = getpage($count, 30);
        $show   = $Page->show();//分页显示输出
        $List   = M('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort asc,add_time desc')->select();
        $this->assign('cache',$List);
        $this->assign('title',$title);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('classname',"理财资讯");
        $this->display();
    }


}
