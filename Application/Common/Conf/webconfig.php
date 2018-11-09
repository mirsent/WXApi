<?php
return array(

    /*状态*/
    'STATUS_N' => 0, // 删除状态
    'STATUS_Y' => 1, // 正常状态
    'STATUS_B' => 2, // 禁用状态

    /*微信支付参数*/
    'WX_TEST_CONFIG' => array(
        'APPID'      => 'wx4af7990f8faed2af', // 微信支付APPID
        'APPSECRET'  => 'bf678846c87c828fe51f008ffd716baa', // secert
        'MCHID'      => '', // 微信支付MCHID 商户收款账号
        'KEY'        => '',  // 微信支付KEY
        'NOTIFY_URL' => '', // 接收支付状态的连接
        'money'      => 1 // 支付金额
    ),

    'WX_DS_CONFIG' => array(
        'APPID'      => 'wxc4979c48c7688375',
        'APPSECRET'  => '7ae96678b1d9d9dde23b6514d95b749e',
        'MCHID'      => '',
        'KEY'        => '',
        'NOTIFY_URL' => '',
        'money'      => 1
    ),

    /*漫画状态*/
    'C_SERIAL_L' => 1, // 连载中
    'C_SERIAL_W' => 2, // 已完结

    'C_FEE_Y' => 1, // 收费
    'C_FEE_N' => 2, // 免费

    'C_TARGET_M' => 1, // 男频
    'C_TARGET_F' => 2, // 女频

    'C_SPACE_C' => 1, // 长篇
    'C_SPACE_D' => 2, // 短篇

    'C_STATUS_U' => 1, // 上架
    'C_STATUS_D' => 2, // 下架
);
