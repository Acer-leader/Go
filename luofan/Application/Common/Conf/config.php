<?php
//return array(
//	//'配置项'=>'配置值'
//);
function J($str){
    return str_replace('./', '', str_replace('//', '/', $str));
}
return array(
    'MODULE_ALLOW_LIST' => array('Home','Admin','Supplier','Iphone'),
    'DEFAULT_MODULE'     => 'Home', //默认模块
    'URL_MODEL'          => '2', //URL模式
    'SESSION_AUTO_START' => true, //是否开启session
    //更多配置参数


    // 多个伪静态后缀设置 用|分割
//    'URL_HTML_SUFFIX' => 'html|shtml|xml',




    //数据库配置
    /* 数据库设置 */
    'DB_TYPE'               => 'mysql',     // 数据库类型
//    'DB_HOST'               => '127.0.0.1', // 服务器地址
//    'DB_NAME'               => 'luofan',      // 数据库名
//    'DB_USER'               => 'root',      // 用户名
//    'DB_PWD'                => 'QVZXZQvZxz5g3b7Zd',      // 密码

    'DB_HOST'               => '127.0.0.1', // 服务器地址
    'DB_NAME'               => 'luofan',      // 数据库名
    'DB_USER'               => 'root',      // 用户名
    'DB_PWD'                => 'root123',      // 密码

    'DB_PORT'               => '3306',        // 端口
    'DB_PREFIX'             => 'app_',    // 数据库表前缀
    'DB_FIELDTYPE_CHECK'    => false,       // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       => false,        // 启用字段缓存
    'DB_CHARSET'            => 'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        => false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         => 1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           => '', // 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    => false, // 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE'    => 'file',   // SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH'   => 20000, // SQL缓存的队列长度
    'DB_SQL_LOG'            => false, // SQL执行日志记录


    "over_time"             => 120, // 验证码过期时间
    "PASS_KEY"				=> 'unohachahangzhouwangzz',

    // 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.163.com',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'nuihh19@163.com',//你的邮箱名
    'MAIL_FROM' =>'nuihh19@163.com',//发件人地址
    'MAIL_FROMNAME'=>'洛凡金融',//发件人姓名
    'MAIL_PASSWORD' =>'luofan2017',//邮箱密码
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件
    'MAIL_CONTENT' => '【洛凡金融】尊贵的小贷公司，您在牛轰轰发布的贷款产品有了新的申请人，点击链接查看：http://luofan.unohacha.com/Supplier/User/login.html',


);