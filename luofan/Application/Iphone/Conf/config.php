<?php
return array(
    //'配置项'=>'配置值'

        'MODULE_ALLOW_LIST' => array('Iphone'),
        'DEFAULT_MODULE'     => 'Iphone', //默认模块
        'URL_MODEL'          => '2', //URL模式
        'SESSION_AUTO_START' => true, //是否开启session
        // 'TMPL_CACHE_ON'      => true,

        'TMPL_PARSE_STRING'  =>array(
            '__JS__' => '/Public/Iphone/Js',
            '__CSS__' => '/Public/Iphone/Css',
            '__IMAGES__' => '/Public/Iphone/Images',
            '__IMAGES1__' => '/Public/Iphone/Images1',
            '__ASSETS__' => '/Public/Iphone/Assets',
            '__SWF__' => '/Public/Iphone/Swf',
            '__FONTS__' => '/Public/Iphone/Fonts',
            '__UCSS__' => '/Public/Iphone/unohacha/css',
            '__UNO__' => '/Public/Iphone/unohacha',
            '__BIG__' => '/Public/Iphone/big',
            '__LHG__' => '/Public/Admin/lhgcalendar',
			'__HOST__' => '/Iphone',

        ),
    );
