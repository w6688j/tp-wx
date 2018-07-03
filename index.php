<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用入口文件
	// die(":kk");
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	header("Content-type: text/html; charset=utf-8");
	die('PHP环境不支持，使用本系统需要 PHP > 5.3.0 版本才可以~ !');
}

//当前目录路径
define('SITE_PATH', getcwd() . '/');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

//生成目录
//define('BIND_MODULE', 'Xyft');

// 定义应用目录
define('APP_PATH','./Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';