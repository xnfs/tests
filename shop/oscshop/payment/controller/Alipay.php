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
 
namespace osc\payment\controller;
use osc\common\controller\Base;
use think\Db;
class Alipay extends Base{
	
	//下单处理
	public function process(){
		
		return ['url'=>$this->alipay_url(osc_order()->add_order('alipay'))];
	}
	public function alipay_url($order,$type=''){//第三步是实例化支付宝的sdk类，重装能够有效传入sdk类的数组数据		
		
		if($order['order_id']){
			
			$payment=payment_config('alipay');//读取支付配置文件的信息
			
			$payment['notify_url']=request()->domain().url('payment/alipay/alipay_notify');//notify是来验证支付请求信息是否是支付宝发出的					
					
			$payment['return_url']=request()->domain().url('payment/alipay/alipay_return');//同步通知
			$payment['order_type']='goods_buy';
			$payment['subject']=$order['subject'];
			$payment['name']=$order['name'];
			$payment['pay_order_no']=$order['pay_order_no'];
			$payment['pay_total']=$order['pay_total'];					

			$alipay= new \payment\alipay\Alipay($payment);//支付宝支付的sdk接口类
			
			$url= $alipay->get_payurl();
			
			if($type=='re_pay'){//取消支付则清除存储在session里面的order_id
				session('re_pay_order_id',null);
			}else{//支付后则需要清空购物车
				osc_order()->clear_cart($order['uid']);//删除cart数据表的对应数据而且清空session里面的值
			}
			
			return $url;
		}
		
		
	}
	public function re_pay($order_id){

		$order=Db::name('order')->where('order_id',(int)$order_id)->find();
		
		if($order&&($order['order_status_id']!=config('paid_order_status_id'))){//订单存在及还没有支付的状态
			$url=$this->alipay_url([
				'order_id'=>$order['order_id'],
				'subject'=>$order['pay_subject'],
				'name'=>$order['name'],
				'pay_order_no'=>$order['order_num_alias'],
				'pay_total'=>$order['total'],
				'uid'=>$order['uid'],
			],'re_pay'
			);
		}					
		return ['type'=>'alipay','pay_url'=>$url];
	}
	
	//异步通知
	public function alipay_notify(){
	
		
		$alipay= new \payment\alipay\Alipay(payment_config('alipay'));	
		
		$verify_result = $alipay->verifyNotify();
		
		if($verify_result) {		
			
			$post=input('post.');
			
			$order=Db::name('order')->where('order_num_alias',$post['out_trade_no'])->find();
			
			if($post['trade_status'] == 'TRADE_FINISHED') {				
				
		    }
		    elseif($post['trade_status'] == 'TRADE_SUCCESS') {		
				
				if($order&&($order['order_status_id']!=config('paid_order_status_id'))){
										
					osc_order()->update_order($order['order_id']);
					
					echo "success";		
									
				}else{
					echo "fail";
				}		        
				
		    }			
			
		}else{
			
			echo "fail";
		}
	}
	//同步通知
	public function alipay_return(){
		
		$alipay= new \payment\alipay\Alipay(payment_config('alipay'));		
		//对进入的参数进行远程数据判断
		$verify = $alipay->return_verify();
	
		if($verify){
		
			$get=input('param.');
			
			$order=Db::name('order')->where('order_num_alias',$get['out_trade_no'])->find();
			
			if($order['order_status_id']==config('paid_order_status_id')){
				@header("Location: ".url('/pay_success'));	
				die;
			}
			
			if($order&&($order['order_status_id']!=config('paid_order_status_id'))){
				//支付完成
				if($get['trade_status']=='TRADE_SUCCESS'){					
					
					osc_order()->update_order($order['order_id']);
					
					@header("Location: ".url('/pay_success'));	
				}						
			}else{
				die('订单不存在');
			}
			
		}else{
			die('支付失败');
		}	
	}
	
}
