<?php
/**
 * oscshop2 B2C电子商务系统
 *
 * ==========================================================================
 * @link      http://www.oscshop.cn/
 * @copyright Copyright (c) 2015-2016 oscshop.cn. 
 * @license   http://www.oscshop.cn/license.html License
 * ==========================================================================
 *
 * @author    李梓钿
 *
 */
namespace osc\common\controller;
use think\Controller;//引入需要的第三方类
class Base extends controller{
	
	protected function _initialize() {	//控制器的初始化方法，在方法调用前首先执行	
		
		if (!is_file(APP_PATH.'database.php')) {
			header('Location:'.request()->domain().'/install');//domain获取域名（http://www.tp5.com）；install方法只是判断网站有没有安装index模块
			die();
		}				
		
		$module=request()->module();//module获取模块
		
		if(!is_module_install($module)){
			die('该模块未安装');
		}
		
		$config =   cache('db_config_data');
		
        if(!$config){        	
            $config =   load_config();					
            cache('db_config_data',$config);
        }
		
        config($config); //config函数是tp矿建里面读取配置文件的内置函数
	}
	
}
