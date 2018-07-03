<?php
//技术胡 18659218384
#!/usr/bin/env php
define('APP_PATH',dirname(__FILE__).'/Application/');

//绑定控制器
define('BIND_CONTROLLER', 'Worker');

define('BIND_MODULE', 'Home');
// 加载框架引导文件
require dirname(__FILE__).'/ThinkPHP/ThinkPHP.php';
?>