<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class ProblemController extends CommonController {
    public function problem(){
        $title = I('post.title');
        $like['title'] = array('like','%'.$title.'%');
        $problem = M('problem')
                ->alias('a')
                ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                ->field('a.id,a.user_id,a.title,a.add_time,a.browse,a.reply,a.is_help,a.is_show,a.sort,b.person_name')
                ->where($like)
                ->order('sort desc')
                ->select();
        $data['reply'] = array('gt',0);
        $countB = M('Problem')->where($data)->count();
        $countA = M('Problem')->count();
        $countC = $countA - $countB;
        $this->assign('title',$title);
        $this->assign('countA',$countA);
        $this->assign('countB',$countB);
        $this->assign('countC',$countC);
        $this->assign('problem',$problem);
        $this->display();
    }
    public function del(){
        if(IS_GET){
            $id = I('get.id');
            $res = M('Problem')->delete($id);
            $ress = M('Answer')->where(array('p_id'=>$id))->delete();
            if($res){
                $this->redirect('Admin/Problem/Index');
            }else{
                $this->success('删除失败',U('Problem/Index/index'));
            }
        }
    }
    public function edit(){
        if(IS_POST){
            $id = I('post.id');
            $data['sort'] = I('post.sort');
            $data['is_show'] = I('post.is_show');
            $res = M('Problem')->where(array('id'=>$id))->save($data);
            if($res){
                $this->redirect('Admin/Problem/index');
            }else{
                $this->success('修改失败！',U('Admin/Problem/edit',array('id'=>$id)));
            }
        }else{
            $id =   I('get.id');
            $res = M('Problem')
                    ->alias('a')
                    ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                    ->field('a.title,a.id,a.add_time,a.browse,a.reply,a.is_help,a.is_show,b.username')
                    ->where(array('a.id'=>$id))
                    ->find();
            $answer = M('Answer')->where(array('p_id'=>$id))->select();
        }
        $this->assign('answer',$answer);
        $this->assign('res',$res);
        $this->display();
    }
    public function keyword(){
        $res = M('Keyword')->select();
        $this->assign('res',$res);
        $this->display();
    }
    public function setKey(){
        $id = I('get.id');
        if(IS_POST){
            $data['title'] = I('post.keyword');
            $data['sort'] = I('post.sort');
            $data['is_show'] = I('post.is_show');
            $data['add_time'] = date('Y-m-d H:i:s',time());
            if($id){
                $res = M('Keyword')->where(array('id'=>$id))->save($data);
            }else{
                $res = M('Keyword')->add($data);
            }
            
            if($res){
                $this->redirect('Admin/Problem/Keyword');
            }else{
                $this->error('设置失败',U('Admin/Problem/setKey'));
            }
        }else{
            $res = M('Keyword')->find($id);
            $this->assign('res',$res);
        }
       $this->display();
    }
    public function delKey(){
        if(IS_GET){
            $id = I('get.id');
            $res = M('Keyword')->delete($id);
            if($res){
                $this->redirect('Admin/Problem/keyword');
            }else{
                $this->success('删除失败',U('Admin/Problem/keyword'));
            }
        }
    }
}
