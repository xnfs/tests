<?php
return [	
	
	//默认错误跳转对应的模板文件
	'dispatch_error_tmpl' => APP_PATH.'mobile/view/public/error.tpl',
	//默认成功跳转对应的模板文件
	'dispatch_success_tmpl' => APP_PATH.'mobile/view/public/success.tpl',		
		
	'captcha'=>[
		'useNoise'=>false,
		'length'=>4,
		'fontSize'=>18,
		'imageH'=>'53',
		'imageW'=>'130'
	],

];
