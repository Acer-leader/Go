<?php
namespace Common\Controller;
use Think\Controller;
class CommonController extends Controller {
    public function _initialize(){
        //判断用户是否已经登录
        $uid =$_SESSION['admin_id'] ;
        if(!$uid){
            $this->redirect('/Admin/User/login');
        }
        $this->assign('urlname', strtolower(ACTION_NAME));
        $_SESSION['access'] = $this->getAccess($_SESSION['admin_id']);
        $this->checkAuth($_SESSION['admin_id']);
        $this->showNodeList();
        $this->assign("munetype", CONTROLLER_NAME);
    }
    
    

    public function del() {
        $src=str_replace(__ROOT__.'/', '', str_replace('//', '/', $_GET['src']));
        if (file_exists($src)){
            unlink($src);
        }
        print_r($_GET['src']);
        exit();
    }
    public function  GetInfo($id){
        $action=D('Member');
        $returninfo=$action->GetInfomation($id);
        return $returninfo;
    }


    public function addImage(){
        $data = $this->uploadImg();
        $this->ajaxReturn($data);
    }
	
	public function uploadImg() {

        $upload = new \Think\UploadFile;
        $upload->maxSize  = 3145728 ;// 设置附件上传大小
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg','svg');// 设置附件上传类型
        $savepath='./Uploads/Picture/'.date('Ymd').'/';
        if (!file_exists($savepath)){
            mkdir($savepath);
        }
        $upload->savePath =  $savepath;// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
        }
        return $info;
    }
	
//===============================权限节点 1======================================================
	/**wzz 20170415
	 * 获取当前用户的权限节点
	 * @param  $uid     管理员ID
	 * @return $module  权限节点ID
	 */
    public function getAccess($uid){
        $cate = M("user")->where(array('id'=>$uid))->getField('cate');
        return M('admin_cate')->where(array('id'=>$cate))->getField('module');
    }

	
	/**wzz 20170415
	 * 获取头部导航/左侧导航栏/
	 */	
	 public function showNodeList(){
        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name     = strtolower(ACTION_NAME);
        // 输出顶部
        $access = $_SESSION['access'];
        $m = M("admin_node");
		$map = array(
			'level' => 1,
			'id'    => array("in", $access),
		);
        $node_head_list = $m->where($map)->order("sort asc")->select();
        $this->assign("node_head_list", $node_head_list);
        // 输出左侧导航栏
        $map = array(
			'controller' => $controller_name,
			'action'     => $action_name,
		);
		//找到当期url最后一级的内容节点
        $q = $m->where($map)->order("level desc")->find();	
        //获取左侧导航标签
        $map = array(
        	'id' => array("in",$access)
		);
        switch($q['level']){
            case "1":
                $map["pid"] = $q['id'];
            break;
            case "3":
                $map["pid"] = $q['pid2'];
            break;
            case "4":
                $map["pid"] = $q['pid3'];
            break;
            default:
                die("no access");
        }
        $left = $m->where($map)->order("sort asc")->select();	
		
        foreach($left as $k=>$v){
        	 $map_res = array(
        		'id'  => array("in",$access),
        		'pid' => $v['id'],
			);
            $left[$k]['nodes'] = $m->where($map_res)->order("sort asc")->select();
        }

        $this->assign("node_left_list", $left);
        // 输出头部的序号
        $sort = $m->where(array('id'=>$map['pid']))->getField('sort');
		
        $this->assign("head_munetype", $sort);
    }

	/*wzz 20170415
	 * 检查管理员操作权限，由当前控制器和方法 输出左侧的urlname
	 */
    public function checkAuth($uid){
    	$m = M("admin_node");
        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name     = strtolower(ACTION_NAME);
        $access = $_SESSION['access'];
        $map["level"] = 4;
        $map["controller"] = $controller_name;
        $map["action"]     = $action_name;
        $map["id"]         = array("in", $access);
        $res = $m->where($map)->order("level desc")->find();
        if(!$res){
            if(IS_AJAX){
                $this->ajaxReturn(array('status'=>0,"info"=>"您没有此操作权限"));
            }else{
                die("no access!");
            }
        }
        // 输出左侧的urlname
		$action = $m->where(array('id'=>$res['pid']))->getField('action');
		$actionArr = $m->where(array('pid'=>$res['pid2']))->getField('action',true);
		if(in_array($action_name,$actionArr)){  //缓存相应导航栏
			$_SESSION['action'] = $action;
		}
		$action = $_SESSION['action'];  //取出上次缓存的导航栏
		$this->assign('left_urlname', $action);
    }
//===============================权限节点 2======================================================	

    /**
     * 发送系统通知的方法
     * @param int     $userid    接受消息者的id
     * @param string  $msg       需要推送的消息
     * @param array   $data 	 需要修改的参数
     */
    public function sendSystemMessage($userid,$title, $msg, $data=array()){
        $data["title"]=$title;
        $data["msg"]       = $msg;
        $data['user_id']   = $userid;
        $data['create_at'] = time();
        $res = M("systemMessage")->add($data);
        if($res){
            return true;
        }else{
            return false;
        }
    }
	
	
	
}