<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;
use think\Cookie;
use think\Session;

class Indexbase extends Controller
{
	public $controller;
	public $module;
	public $action;

	public function __construct()
	{
		parent::__construct();
		$request=  \think\Request::instance();
		$this->controller=$request->controller();
		$this->module=$request->module();
		$this->action=$request->action();
		$this->assign('c',$this->controller);

		$data=array('htitle' => '特派员');
        $this->assign('headdata',$data);
        // Session::set('userinfo','');
        if(Session::get('userinfo'))
        {
        	$this->assign('userinfo',Session::get('userinfo'));
        }elseif(Cookie::get('userinfo')){
        	$userinfo=Cookie::get('userinfo');
        	Session::set('userinfo',$userinfo);
        	$this->assign('userinfo',$userinfo);
        }else{
        	$this->assign('userinfo',['address'=>'','uid'=>'']);
        }

		//对数据进行验证
		$data=array();
		$valiname='';
		if(request()->ispost() && input('valiname'))
		{
			$valiname=input('valiname');
			$data=$_POST;
		}

		if(!empty($data))
		{
			$validate=Loader::validate($valiname);
			if(!$validate->check($_POST)) {
				$this->error($validate->getError());
			}else{
				unset($_POST['valiname']);
			}
		}
		// exit();
	}
	//获取模板
	public function TempStr($list,$tempname,$parameter=array())
	{
	    $str='';
	    $this->assign('list', $list);
	    $this->assign('empty',"<div class='empty'>暂无数据</div>");
	    $this->assign('parameter',$parameter);
	    $str = $this->fetch('pub:'.$tempname);
	    return $str;
	}

}
