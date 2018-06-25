<?php
return array(
    'MODULE_ALLOW_LIST' => array('Admin'),
    'DEFAULT_MODULE'     => 'Admin', //默认模块
    'URL_MODEL'          => '2', //URL模式
    'SESSION_AUTO_START' => true, //是否开启session
    'SHOW_PAGE_TRACE'        =>true,   //调试模式


    'TMPL_PARSE_STRING'  =>array(
        //'__JS__' => '/website/Public/Admin/Js',
        //'__CSS__' => '/website/Public/Admin/Css',
        // '__IMAGES__' => '/website/Public/Admin/Images',
        // '__UPLOADS__' => '/website/Public/Admin/Uploads',
        // '__UEDITOR__' => '/website/Public/Admin/Ueditor',
        // '__SWF__' => '/website/Public/Admin/Swf',
        // '__LHG__' => '/website/Public/Admin/lhgcalendar',
		'__JS__' => '/Public/Admin/Js',
        '__CSS__' => '/Public/Admin/Css',
        '__IMAGES__' => '/Public/Admin/Images',
        '__UPLOADS__' => '/Public/Admin/Uploads',
        '__UEDITOR__' => '/Public/Admin/Ueditor',
        '__SWF__' => '/Public/Admin/Swf',
        '__LHG__' => '/Public/Admin/lhgcalendar',
        '__JqColor__' => '/Public/Admin/JqColor',

    ),

 /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
    'mimes'    => '', //允许上传的文件MiMe类型
    'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
    'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,xls,xlsx', //允许上传的文件后缀
    'autoSub'  => true, //自动子目录保存文件
    'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    'rootPath' => './Uploads/Download/', //保存根路径
    'savePath' => '', //保存路径
    'saveName' => array('ydid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
    'saveExt'  => '', //文件保存后缀，空则使用原后缀
    'replace'  => false, //存在同名是否覆盖
    'hash'     => true, //是否生成hash编码
    'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
), //下载模型上传配置（文件上传类配置）



    'URL_HTML_SUFFIX'=>'',//去掉后缀

        'TMPL_ACTION_ERROR'     =>  THINK_PATH . 'Tpl/dispatch_jump.tpl', //   设置默认错误页面
        // 'TMPL_ACTION_ERROR'     =>  'Public:error', //   设置默认错误页面
        'TMPL_ACTION_SUCCESS' =>    THINK_PATH . 'Tpl/dispatch_jump2.tpl',
        'TMPL_EXCEPTION_FILE'   =>  THINK_PATH.'Tpl/think_exception.tpl',

);