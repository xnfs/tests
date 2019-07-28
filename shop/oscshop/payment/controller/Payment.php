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
 * 电脑版本
 */
 
namespace osc\payment\controller;
use osc\common\controller\Base;
use think\Db;
class Payment extends Base{

	
	function pay_api(){
		if(request()->isPost()){
		
			$type=session('payment_method');
			
			$class = '\\osc\\payment\\controller\\' . ucwords($type);
				
			$payment= new $class();
			
			storage_user_action(member('uid'),member('username'),config('FRONTEND_USER'),'下了订单，未支付');	
			
			$url=$payment->process();
			
			return $url;
		
		}
	}
	
	function choice_payment_type(){//第一步：选择付款的方式
		
		$map['order_id']=['eq',(int)input('param.order_id')];
		$map['uid']=['eq',member('uid')];
		
		if(!$order=Db::name('order')->where($map)->find()){
			$this->error('订单不存在！！');
		}
		
		session('re_pay_order_id',$order['order_id']);//订单号存入session
		
		$this->assign('list',osc_service('payment','service')->get_available_payment_list());//返回的支付方式:支付宝或者是微信或者是银行
		
		return $this->fetch('payment_list');//将数据返回到当前指定的页面 
	}
	function re_pay(){//第二步实例化自己相关业务的支付类，重组需要的数组数据，为支付的url做准备（查询订单表）
		if(request()->isPost()){
		
			$type=input('param.type');//用户提交的哪种支付方式默认是alipay，若是其他的支付方式则实例化对应的类
			
			$class = '\\osc\\payment\\controller\\' . ucwords($type);//ucwords将单词的第一个字母大写
				
			$payment= new $class();
			
			$return=$payment->re_pay((int)session('re_pay_order_id'));
			
			storage_user_action(member('uid'),member('username'),config('FRONTEND_USER'),'点击了去支付');
			
			return ['type'=>$return['type'],'pay_url'=>$return['pay_url']];//['pay_url']是支付地址
		
		}
	}
}
