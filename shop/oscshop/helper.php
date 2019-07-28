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
 * 调用php内置库或者是第三方没有使用命名空间时最好加上\;
 */
use \osc\common\service\Goods;
use \osc\common\service\System;
use \osc\common\service\Transport;
use \osc\common\service\Order;
use \oscshop\Hashids;
use think\exception\ClassNotFoundException;//异常抛出错误类
use \oscshop\Weight;

if (!function_exists('osc_goods')) {//首先检查这个类是否存在，不存在就实例化一个
    /**
     * 商品相关数据助手函数
     */
    function osc_goods()
    {
        return Goods::getInstance();//  /common/service
    }
}

if (!function_exists('osc_system')) {
    /**
     * 系统相关数据助手函数
     */
    function osc_system()
    {
        return System::getInstance();
    }
}
if (!function_exists('osc_model')) {
    /**
     * osc模型实例化助手函数
	 * 
     */
    function osc_model($module_name,$controller_name)
    {
    	$class = '\\osc\\'.$module_name.'\\model\\' . ucwords($controller_name);	
        
		if (class_exists($class)) {
               return new $class();
        } else {
                throw new ClassNotFoundException('class not exists:' . $class, $class);
        }
    }
}
if (!function_exists('osc_service')) {
    /**
     * osc service助手函数
	 * 
     */
    function osc_service($module_name,$service_name)
    {
    	$class = '\\osc\\'.$module_name.'\\service\\' . ucwords($service_name);	//windows系统上的路径类似为\xampp\htdocs;ucwords函数是将每个单词的首字母大写；lcfirst函数是将每个单词的首字母小写
        
		if (class_exists($class)) {
               return new $class();
        } else {
                throw new ClassNotFoundException('class not exists:' . $class, $class);//第一个参数为错误信息，第二个参数为类名
        }
    }
}
if (!function_exists('osc_cart')) {
    /**
     * osc购物车助手函数
	 * 
     */
    function osc_cart()
    {    	
        return new \oscshop\Cart();//第三方类库前面需要加\符号        
    }
}
if (!function_exists('osc_weight')) {
    /**
     * osc重量相关助手函数
	 * 
     */
    function osc_weight()
    {    	
        return Weight::getInstance();       
    }
}
if (!function_exists('osc_transport')) {
    /**
     * osc运费相关助手函数
	 * 
     */
    function osc_transport()
    {    	
        return Transport::getInstance();       
    }
}
if (!function_exists('osc_order')) {
    /**
     * osc订单相关助手函数
	 * 
     */
    function osc_order()
    {    	
        return new Order();       
    }
}
if (!function_exists('hashids')) {
    /**
     * 数字id加密
     */
    function hashids()
    {
    	return new Hashids(config('PWD_KEY'),10);
    }
}
?>