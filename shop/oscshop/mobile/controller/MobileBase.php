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
namespace osc\mobile\controller;
use osc\common\controller\Base;
class MobileBase extends Base{

    protected function _initialize() {
		parent::_initialize();		
		if(!request()->isMobile()&&(false==config('app_debug'))){//条件成立意味着是移动端而且是线上环境
			$this->error('请使用移动端打开');
		}
		//微信中获取用户信息自动注册	
		if(in_wechat()){//判断是否在微信公众号里面的打开的网站
			wechat()->wechatAutoReg(wechat()->getOpenId());				
			
			if(!session('mobile_total')){
				session('mobile_total',osc_cart()->count_cart_total(user('uid')));
			}
				
		}
	}


}
?>