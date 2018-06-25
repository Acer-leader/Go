<?php
return array(
    //'配置项'=>'配置值'

        'MODULE_ALLOW_LIST' => array('Home'),
        'DEFAULT_MODULE'     => 'Home', //默认模块
        'URL_MODEL'          => '2', //URL模式
        'SESSION_AUTO_START' => true, //是否开启session
        // 'TMPL_CACHE_ON'      => true,

        'TMPL_PARSE_STRING'  =>array(
            '__JS__' => '/Public/Home/Js',
            '__CSS__' => '/Public/Home/Css',
            '__IMAGES__' => '/Public/Home/Images',
            '__IMAGES1__' => '/Public/Home/Images1',
            '__ASSETS__' => '/Public/Home/Assets',
            '__SWF__' => '/Public/Home/Swf',
            '__FONTS__' => '/Public/Home/Fonts',
            '__UCSS__' => '/Public/Home/unohacha/css',
            '__UNO__' => '/Public/Home/unohacha',
            '__BIG__' => '/Public/Home/big',
            '__LHG__' => '/Public/Admin/lhgcalendar',
			'__HOST__' => '',

        ),
    );
