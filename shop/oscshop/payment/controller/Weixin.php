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
 * 扫码支付
 */
namespace osc\payment\controller;
use osc\common\controller\Base;
use payment\weixin\WxPayApi;
use payment\weixin\WxPayConfig;
use payment\weixin\WxPayUnifiedOrder;
use payment\weixin\WxPayNotifyCallBack;
use think\Db;

class Weixin extends Base{
	
	function process(){
		return ['type'=>'wx_pay','url'=>url('/wxpay')];
	}
	
	public function re_pay($order_id){
		return ['type'=>'wx_pay','pay_url'=>url('payment/weixin/re_pay_code',array('order_id'=>$order_id))];
	}
	
	function code(){
		
		$order=osc_order()->add_order('weixin');
		
		if($order['order_id']){
		
			$config=payment_config('weixin');
			
			$cfg = array(
			    'APPID'     => $config['appid'],
			    'MCHID'     => $config['weixin_partner'],
			    'KEY'       => $config['partnerkey'],
			    'APPSECRET' => $config['appsecret'],
			    'NOTIFY_URL' =>request()->domain().url('payment/weixin/weixin_notify')
		    );
		    WxPayConfig::setConfig($cfg);     
	        //②、统一下单
	        $input = new WxPayUnifiedOrder();           
	  
	        $input->SetBody($order['subject']);
	        $input->SetAttach('附加数据');
	        $input->SetOut_trade_no($order['pay_order_no']);
			
	        $input->SetTotal_fee((float)$order['pay_total']*100);
			
	        $input->SetTime_start(date("YmdHis"));
	        $input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetTrade_type('NATIVE');
	
			$input->SetProduct_id(time());
			
			$wxapi=new WxPayApi();
			
		    $url= $wxapi->unifiedOrder($input);	
		
			osc_order()->clear_cart($order['uid']);
			
			$this->assign('url',$url['code_url']);
			
			$this->assign('trade_no',$order['pay_order_no']);
			
		}
		
		return $this->fetch(); 
	}
	//会员中心去支付
	public function re_pay_code(){
		
		$order_id=(int)input('order_id');
		
		$order=Db::name('order')->where('order_id',$order_id)->find();
		
		if($order&&($order['order_status_id']!=config('paid_order_status_id'))){//判断是否已经付款
		
			$config=payment_config('weixin');//微信需要的一些配置项目
	
			$cfg = array(
			    'APPID'     => $config['appid'],
			    'MCHID'     => $config['weixin_partner'],
			    'KEY'       => $config['partnerkey'],//秘钥
			    'APPSECRET' => $config['appsecret'],
			    'NOTIFY_URL' =>request()->domain().url('payment/weixin/weixin_notify')
		    );
			//重新生成trade_no，即唯一订单号
			$trade_no=build_order_no();
			
			Db::name('order')->where('order_id',$order['order_id'])->update(array('order_num_alias'=>$trade_no,'payment_code'=>'weixin'));//将刚生成的订单号写入数据库还有支付方式	
			
		    WxPayConfig::setConfig($cfg);   //微信支付配置文件类 ，何用？ 
	        //②、统一下单
	        $input = new WxPayUnifiedOrder();  //微信支付的统一订单类,设置微信支付的相关参数        
	  
	        $input->SetBody($order['pay_subject']);//支付单的简要描述
	        $input->SetAttach('附加数据');//在查询API和支付通知中原样返回
	        $input->SetOut_trade_no($trade_no);//设置商户系统内部的订单号
			
	        $input->SetTotal_fee((float)$order['total']*100);//设置订单总金额，只能为整数
			
	        $input->SetTime_start(date("YmdHis"));//设置订单生成时间
	        $input->SetTime_expire(date("YmdHis", time() + 600));//设置订单失效时间
			$input->SetTrade_type('NATIVE');//交易类型
	
			$input->SetProduct_id(time());//若trade_typr设置为NATIVE时此参数必设置。此prodect_id为二维码中包含的商品ID，商户自行定义
			
			$wxapi=new WxPayApi();//微信支付的ipa接口类
			
		    $url= $wxapi->unifiedOrder($input);				
			
			$this->assign('url',$url['code_url']);
			
			$this->assign('trade_no',$trade_no);
			
			$this->assign('order_id',$order_id);
			
			return $this->fetch('recode'); 
		}
	}
	public function get_order_status(){
		
		$data=input('post.');
		
		$order=Db::name('order')->where('order_num_alias',$data['out_trade_no'])->find();	
		
		if($order['order_status_id']==config('paid_order_status_id')){
			die('pay_suc');
		}else{
			die('no_pay');
		}
	}
	//异步通知
	public function weixin_notify(){	
		
		$config=payment_config('weixin');
		
		$notify_url=request()->domain().url('payment/weixin/weixin_notify');
		
		$cfg = array(
			'APPID'     => $config['appid'],
			'MCHID'     => $config['weixin_partner'],
			'KEY'       => $config['partnerkey'],
			'APPSECRET' => $config['appsecret'],
			'NOTIFY_URL' => $notify_url,
		);
		WxPayConfig::setConfig($cfg); 	

		$call_back=new WxPayNotifyCallBack();
		
		$data=$call_back->Handle(false);//回调入口，是否需要签名输出
		
		if($data&&$data['result_code']=='SUCCESS'){
			
			$order=Db::name('order')->where('order_num_alias',$data['out_trade_no'])->find();		
			
			if($order&&($order['order_status_id']==config('default_order_status_id'))){				
				osc_order()->update_order($order['order_id']);					
			}
			
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
		
	}
}
