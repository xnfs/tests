<?php
/**
 *
 * @author    李梓钿
 *会员中心
 */
namespace osc\common\controller;
use think\Db;
class HomeBase extends Base{	
	
	protected function _initialize() {
		parent::_initialize();//继承父类的方法	
		
		if(request()->isMobile()&&('mobile'!=request()->module())){//判断是否为移动端
			header('Location:'.request()->domain().'/mobile/');
			die();
		}
		
		$this->assign('top_nav',osc_goods()->get_goods_category());//这里是pc端的数据，页面的导航栏在初始化的时候就获取数据，此基类被其他的所有模板继承
		
	}


	
}
