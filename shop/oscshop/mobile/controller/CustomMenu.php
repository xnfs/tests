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
use osc\common\controller\AdminBase;
use think\Db;
class CustomMenu extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','自定义菜单');	
	}
	
	private function menu_type(){
		
		return array(
			1=>array(
				'name'=>'点击跳转到链接',				
				'type'=>'view' 
			),		
			2=>array(
				'name'=>'点击获取最新图文',
				'key'=>'get_last_news', //key的值是自定义的
				'type'=>'click' 
			),			
		);
		
	}
	
	public function index(){
		
		$menu=wechat()->getMenu();

		$this->assign('menu',$menu['menu']['button']);
		
		$this->assign('menu_type',$this->menu_type());
		
		return $this->fetch('index');
	}
	
	function create_menu(){
		
		if(request()->isPost()){
			$data=input('post.');
			wechat()->deleteMenu();//不管之前有没有设置菜单，都先删除确保万无一失
			
			$menu_type=$this->menu_type();
			foreach ($data['type'] as $k => $v) {	
				//有子菜单
				if(count($v)>1){//count(array,mode)
    // 0 - 默认。不对多维数组中的所有元素进行计数
    // 1 - 递归地计数数组中元素的数目（计算多维数组中的所有元素）

					// 子菜单的数据格式
					 //   "name":"菜单",
			      //      "sub_button":[
			      //      {    
			      //          "type":"view",
			      //          "name":"搜索",
			      //          "url":"http://www.soso.com/"
			      //       },

					$menu[$k]['name']=$data['name'][$k][0];
					unset($v[0]);//就是删除'name'：'菜单'
					foreach ($v as $k1 => $v1) {						
						$key=$k1-1;						
						//点击跳转到链接
						if($v1[0]==1){
							$menu[$k]['sub_button'][$key]['type']='view';
							$menu[$k]['sub_button'][$key]['name']=trim($data['name'][$k][$k1]);
							$menu[$k]['sub_button'][$key]['url']=trim($data['content'][$k][$k1]);
							
						//点击获取最新图文	
						}elseif($v1[0]==2){
							$menu[$k]['sub_button'][$key]['type']='click';
							$menu[$k]['sub_button'][$key]['name']=trim($data['name'][$k][$k1]);
							$menu[$k]['sub_button'][$key]['key']=$menu_type[2]['key'];
						}
					}
				//没有子菜单	
				}else{
					//点击跳转到链接
					if($v[0]==1){
						$menu[$k]['type']='view';
						$menu[$k]['name']=trim($data['name'][$k][0]);
						$menu[$k]['url']=trim($data['content'][$k][0]);
						
					//点击获取最新图文	
					}elseif($v[0]==2){
						$menu[$k]['type']='click';
						$menu[$k]['name']=trim($data['name'][$k][0]);
						$menu[$k]['key']=$menu_type[2]['key'];
					}
				}
			}
			$menuPostString =array (
      	    	'button' => $menu
			);
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'生成自定义菜单');	
			wechat()->createMenu($menuPostString);
		}
		return $this->index();
	}
	
	function delete_menu(){
		storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'删除自定义菜单');			
		wechat()->deleteMenu();
		return $this->index();
	}
}
?>