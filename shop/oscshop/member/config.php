<?php
return [
	'use_captcha_id'=>1,	//默认的配置方式（一维数组）;模板里面可以这样访问：{:config('use_captcha_id')}，本项目不能用use_captcha设置关联数组的key
	'captcha'=>[
		'useNoise'=>true,
		'length'=>4,
		'fontSize'=>18,
		'imageH'=>'50',
		// 'useImgBg'=>true,
		'imageW'=>'150'
	],
];
