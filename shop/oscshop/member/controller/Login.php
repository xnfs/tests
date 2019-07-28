<?php
/**
 * @author    李梓钿
 *会员登录注册相关
 */
namespace osc\member\controller;
use osc\common\controller\Base;
use osc\common\validate\Member;
use think\Db;
use think\captcha\Captcha;
class Login extends Base{

	//保存会员信息
	private function refresh_member($auth){
		
		if(empty($auth)&&!is_array($auth)){
			return;	
		}
		
		if('session'==config('member_login_type')){
		 	session('member_user_auth', $auth);
			session('member_user_auth_sign',data_auth_sign($auth));
		 }elseif('cookie'==config('member_login_type')){		
		 	cookie('member_user_auth',$auth,3600*7);
			cookie('member_user_auth_sign',data_auth_sign($auth),3600*7);
		 }
	}

	//登录验证
	public function validate_login($data){
		
			if(empty($data['username'])){
				return ['error'=>'用户名必填'];
			}elseif(empty($data['password'])){
				return ['error'=>'密码必填'];
			}
			
			if(1==config('use_captcha_id')){				
				if(!check_verify($data['captcha'])){
					return ['error'=>'验证码错误'];
				}
			}
			
			$user=Db::name('member')->where('username',$data['username'])->find();
			
			if(!$user){
				return ['error'=>'账号不存在！！'];
			}elseif(($user['checked']==0)&&(1==config('reg_check'))){//需要审核
				return ['error'=>'该账号未审核通过！！'];
			}
			
			if(think_ucenter_encrypt($data['password'],config('PWD_KEY'))==$user['password']){
		
				$auth = array(
		            'uid'             => $user['uid'],
		            'username'        => $user['username'],
		            'nickname'        => $user['nickname'],
		            'group_id'		  => $user['groupid'],		                     
				 );			
								
				$this->refresh_member($auth);//存储会话信息，用于登录
				
				$login['lastdate']=time();
				$login['loginnum']		=	array('exp','loginnum+1');
				$login['lastip']	=	get_client_ip();
				
				DB::name('member')->where('uid',$user['uid'])->update($login);//更新数据库里面的登录状态信息
				
				storage_user_action($user['uid'],$user['username'],config('FRONTEND_USER'),'登录了网站');
				
				return ['success'=>'登录成功','total'=>osc_cart()->count_cart_total($user['uid'])];
			}else{
				return ['error'=>'密码错误'];
			}
	}
	
	//注册：
	public function reg()
    {
    	if(request()->isPost()){//判断请求类型是否为post
    		
    		$data=input('post.');//获取所有的post变量的值					
			if(1==config('use_captcha_id')){				
				if(!check_verify($data['captcha'])){
					return ['error'=>'验证码错误'];
				}
			} 
			 	
			$validate=new Member();//注册验证类
				
			if(!$validate->check($data)){	//若有错误信息返回false			
			    return ['error'=>$validate->getError()];				
			}						
						
			$member['username']=$data['username'];
			$member['password']=think_ucenter_encrypt($data['password'],config('PWD_KEY'));
			$member['email']=$data['email'];
			$member['groupid']=config('default_group_id');
			$member['reg_type']='pc';
			$member['regdate']=time();
			$member['lastdate']=time();
			
			if(empty($data['nickname'])){
				$member['nickname']=$data['username'];//没有设置昵称时则默认用户名为昵称
			}else{
				$member['nickname']=$data['nickname'];
			}
			
			if(1==config('reg_check')){//需要审核或者验证
				$member['checked']=0;
			}else{
				$member['checked']=1;
			}
			
			$uid=Db::name('member')->insert($member,false,true);
			
			if($uid){//判断是否插入成功
				
				//写入用户权限表
				Db::name('member_auth_group_access')->insert(['uid'=>$uid,'group_id'=>$member['groupid']],false,true);				 		
				
				if(1==config('reg_check')){//需要审核
					return ['success'=>'注册成功，请等待管理员审核','check'=>1];
				}else{//不需要审核
					$auth = array(
		            'uid'             => $uid,
		            'username'        => $member['username'],
		            'nickname'        => $member['nickname'],
		            'group_id'		  => $member['groupid']	,
		          	            
					 );	
					 
					$this->refresh_member($auth);
					
					storage_user_action($uid,$member['username'],config('FRONTEND_USER'),'注册成为会员');
					
					return ['success'=>'注册成功','check'=>0];
				}
				
			}else{
				return ['error'=>'注册失败'];
			}
			
    	}
		
		if(osc_service('member','user')->is_login()){
			die('您已经登录了账号！！');
		}
		  
		return $this->fetch();   
    }
	
	//获取地区
    function getarea(){
        $where['area_parent_id']=input('param.areaId');
      
        return Db::name('area')->where($where)->select();
    }

	
	//登录
	public function login(){
		
		if(request()->isPost()){
			
			$data=input('post.');//获取过滤掉的post数据
			
			$r=$this->validate_login($data);
			
			if(isset($r['error'])){
				return $r;
			}elseif($r['success']){
				session('total',$r['total']);
				return ['success'=>true,'total'=>$r['total']];
			}
			
		}
		
		if(osc_service('member','user')->is_login()){
			die('您已经登录了账号！！');
		}
		  
		return $this->fetch();  
	}
	function logout(){
		osc_service('member','user')->logout();		
		$this->redirect('/');//redirect路径重定向
	}
	 public function verify(){	//验证码的自定义用法
		$captcha = new Captcha((array)Config('captcha'));
		return $captcha->entry(1);	 	
    }
}
