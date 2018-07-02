<?php
namespace app\gys\validate;
use think\Validate;

class Freight extends Validate
{
   protected $rule = [
        'name'=>'require|length:6,12',
        'telphone'=>'require|length:11|Unique:freight',
        'status'=>'eq:on',
    ];
	
	protected $message=[
		'name.require'=>'车主姓名不能为空',
		'name.length'=>'请填写真实姓名',
		'telphone.require'=>'手机号不能为空',
		'telphone.length'=>'手机号长度只能是11位',
		'telphone.Unique'=>'手机号已注册，请选择其他的手机号',
		'status'=>'请先接受协议',
		'email.require'=>'邮箱地址不能为空',
	];
	protected $scene=[
		'edit_ziliao'=>[
			'email'=>'require',
		],
	];
}