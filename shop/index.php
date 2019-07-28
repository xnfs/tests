<?php
//判断php版本
if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');

header('X-Powered-By: oscshop2');//这个值的意义用于告知网站是用何种语言或框架编写的
//设置网站字符集
header("Content-Type:text/html; charset=utf-8");
//版本号
define('OSCSHOP_VERSION', '2.0');
//根目录，物理路径
define('ROOT_PATH',str_replace('\\','/',dirname(__FILE__)) . '/'); //当前文件的目录为c:\xampp\htdocs\oscshop2
//图片上传目录
define('DIR_IMAGE',ROOT_PATH.'public/uploads/');
//类库包
define('EXTEND_PATH','./extend/');//自动注册命名空间就是设置对应的常量，比如在extend目录下，就只需要从他的子目录开始写起
//扩展类库包
define('VENDOR_PATH','./vendor/');
// 定义应用目录
define('APP_PATH','./oscshop/');
//应用命名空间
define('APP_NAMESPACE','osc');
// 加载框架引导文件
require './thinkphp/start.php';
//https://www.kancloud.cn/manual/thinkphp5/118137上面的系统常量参考
