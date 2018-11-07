<?php
return array(

    /*附加设置*/
    'LOAD_EXT_CONFIG'   => 'db,webconfig',                  // 加载扩展配置文件
    'TMPL_PARSE_STRING' => array(                           // 模板相关配置
        '__PUBLIC__'    => __ROOT__.'/Public',
        '__BOWER__'     => __ROOT__.'/Public/bower_components',
        '__STATICS__'   => __ROOT__.'/Public/statics',

        '__Home_CSS__'  => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/css',
        '__Home_JS__'   => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/js',
        '__Home_IMG__'  => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/img',
        '__Admin_CSS__' => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/css',
        '__Admin_JS__'  => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/js',
        '__Admin_IMG__' => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/img',
    ),

    /*auth设置*/
    'AUTH_CONFIG'       => array(
        'AUTH_USER'     => 'user'                           // 用户信息表
    ),

);
