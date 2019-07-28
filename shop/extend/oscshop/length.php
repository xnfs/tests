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
namespace oscshop;//此目录是extend的子目录，而extend目录又是自动注册命名空间的
use think\Db;//tp框架的根命令空间：think：thinkphp/library/think；traits：thinkphp/library/traits；app：application
class Length {
	
	private $lengths = array();
	
	private static $instance;
	
	private function __construct() {//初始化也就是构造函数
		
		$length_class_query = Db::name('length_class')->select(); 		
		foreach ($length_class_query as $result) {
			$this->lengths[$result['length_class_id']] = array(
				'length_class_id' => $result['length_class_id'],
				'title'           => $result['title'],
				'unit'            => $result['unit'],
				'value'           => $result['value']
			);
		}
	}
	//单例模式	
	public static function getInstance(){    
        if (!(self::$instance instanceof self))  //instanceof是判断一个对象是否属于一个类（参考http://www.jb51.net/article/74409.htm）
        {  
            self::$instance = new self();  
        }  
        return self::$instance;  
    }
	//禁克隆
	private function __clone(){} 
	
	public function convert($value, $from, $to) {
		if ($from == $to) {
			return $value;
		}

		if (isset($this->lengths[$from])) {
			$from = $this->lengths[$from]['value'];
		} else {
			$from = 1;
		}

		if (isset($this->lengths[$to])) {
			$to = $this->lengths[$to]['value'];
		} else {
			$to = 1;
		}

		return $value * ($to / $from);
	}

	public function format($value, $length_class_id, $decimal_point = '.', $thousand_point = ',') {
		if (isset($this->lengths[$length_class_id])) {
			return number_format($value, 2, $decimal_point, $thousand_point) . $this->lengths[$length_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}

	public function getUnit($length_class_id) {
		if (isset($this->lengths[$length_class_id])) {
			return $this->lengths[$length_class_id]['unit'];
		} else {
			return '';
		}
	}
}

?>