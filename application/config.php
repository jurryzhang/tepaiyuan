<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
	// +----------------------------------------------------------------------
	// | 应用设置
	// +----------------------------------------------------------------------
	'goods_no_pre'           => 'SD',

	// 应用命名空间
	'app_namespace'          => 'app',
	// 应用调试模式
	'app_debug'              => TRUE,
	// 应用Trace
	'app_trace'              => FALSE,
	// 应用模式状态
	'app_status'             => '',
	// 是否支持多模块
	'app_multi_module'       => TRUE,
	// 入口自动绑定模块
	'auto_bind_module'       => FALSE,
	// 注册的根命名空间
	'root_namespace'         => [],
	// 扩展函数文件
	'extra_file_list'        => [THINK_PATH.'helper'.EXT],
	// 默认输出类型
	'default_return_type'    => 'html',
	// 默认AJAX 数据返回格式,可选json xml ...
	'default_ajax_return'    => 'json',
	// 默认JSONP格式返回的处理方法
	'default_jsonp_handler'  => 'jsonpReturn',
	// 默认JSONP处理方法
	'var_jsonp_handler'      => 'callback',
	// 默认时区
	'default_timezone'       => 'PRC',
	// 是否开启多语言
	'lang_switch_on'         => FALSE,
	// 默认全局过滤方法 用逗号分隔多个
	'default_filter'         => '',
	// 默认语言
	'default_lang'           => 'zh-cn',
	// 应用类库后缀
	'class_suffix'           => FALSE,
	// 控制器类后缀
	'controller_suffix'      => FALSE,

	// +----------------------------------------------------------------------
	// | 模块设置
	// +----------------------------------------------------------------------

	// 默认模块名
	'default_module'         => 'index',
	// 禁止访问模块
	'deny_module_list'       => ['common'],
	// 默认控制器名
	'default_controller'     => 'Index',
	// 默认操作名
	'default_action'         => 'index',
	// 默认验证器
	'default_validate'       => '',
	// 默认的空控制器名
	'empty_controller'       => 'Error',
	// 操作方法后缀
	'action_suffix'          => '',
	// 自动搜索控制器
	'controller_auto_search' => FALSE,

	// +----------------------------------------------------------------------
	// | URL设置
	// +----------------------------------------------------------------------

	// PATHINFO变量名 用于兼容模式
	'var_pathinfo'           => 's',
	// 兼容PATH_INFO获取
	'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
	// pathinfo分隔符
	'pathinfo_depr'          => '/',
	// URL伪静态后缀
	'url_html_suffix'        => 'html',
	// URL普通方式参数 用于自动生成
	'url_common_param'       => FALSE,
	// URL参数方式 0 按名称成对解析 1 按顺序解析
	'url_param_type'         => 0,
	// 是否开启路由
	'url_route_on'           => TRUE,
	// 路由使用完整匹配
	'route_complete_match'   => FALSE,
	// 路由配置文件（支持配置多个）
	'route_config_file'      => ['route'],
	// 是否强制使用路由
	'url_route_must'         => FALSE,
	// 域名部署
	'url_domain_deploy'      => FALSE,
	// 域名根，如thinkphp.cn
	'url_domain_root'        => '',
	// 是否自动转换URL中的控制器和操作名
	'url_convert'            => TRUE,
	// 默认的访问控制器层
	'url_controller_layer'   => 'controller',
	// 表单请求类型伪装变量
	'var_method'             => '_method',
	// 表单ajax伪装变量
	'var_ajax'               => '_ajax',
	// 表单pjax伪装变量
	'var_pjax'               => '_pjax',
	// 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
	'request_cache'          => FALSE,
	// 请求缓存有效期
	'request_cache_expire'   => NULL,
	// 全局请求缓存排除规则
	'request_cache_except'   => [],

	// +----------------------------------------------------------------------
	// | 模板设置
	// +----------------------------------------------------------------------

	'template'              => [
		// 模板引擎类型 支持 php think 支持扩展
		'type'         => 'Think',
		// 模板路径
		'view_path'    => '',
		// 模板后缀
		'view_suffix'  => 'html',
		// 模板文件名分隔符
		'view_depr'    => DS,
		// 模板引擎普通标签开始标记
		'tpl_begin'    => '{',
		// 模板引擎普通标签结束标记
		'tpl_end'      => '}',
		// 标签库标签开始标记
		'taglib_begin' => '{',
		// 标签库标签结束标记
		'taglib_end'   => '}',
	],

	// 默认跳转页面对应的模板文件
	// 'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
	// 'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
	'dispatch_success_tmpl' => 'pub:msg',
	'dispatch_error_tmpl'   => 'pub:msg',

	// +----------------------------------------------------------------------
	// | 异常及错误设置
	// +----------------------------------------------------------------------

	// 异常页面的模板文件
	'exception_tmpl'        => THINK_PATH.'tpl'.DS.'think_exception.tpl',

	// 错误显示信息,非调试模式有效
	'error_message'         => '页面错误！请稍后再试～',
	// 显示错误信息
	'show_error_msg'        => FALSE,
	// 异常处理handle类 留空使用 \think\exception\Handle
	'exception_handle'      => '',

	// +----------------------------------------------------------------------
	// | 日志设置
	// +----------------------------------------------------------------------

	'log'   => [
		// 日志记录方式，内置 file socket 支持扩展
		'type'  => 'File',
		// 日志保存目录
		'path'  => LOG_PATH,
		// 日志记录级别
		'level' => [],
	],

	// +----------------------------------------------------------------------
	// | Trace设置 开启 app_trace 后 有效
	// +----------------------------------------------------------------------
	'trace' => [
		// 内置Html Console 支持扩展
		'type' => 'Html',
	],

	// +----------------------------------------------------------------------
	// | 缓存设置
	// +----------------------------------------------------------------------

	'cache' => [
		// 驱动方式
		'type'   => 'File',
		// 缓存保存目录
		'path'   => CACHE_PATH,
		// 缓存前缀
		'prefix' => '',
		// 缓存有效期 0表示永久缓存
		'expire' => 0,
	],

	// +----------------------------------------------------------------------
	// | 会话设置
	// +----------------------------------------------------------------------

	'session'      => [
		'id'             => '',
		// SESSION_ID的提交变量,解决flash上传跨域
		'var_session_id' => '',
		// SESSION 前缀
		'prefix'         => 'think',
		// 驱动方式 支持redis memcache memcached
		'type'           => '',
		// 是否自动开启 SESSION
		'auto_start'     => TRUE,
		//保存时间
		'expire'         => 3600 * 7,
	],

	// +----------------------------------------------------------------------
	// | Cookie设置
	// +----------------------------------------------------------------------
	'cookie'       => [
		// cookie 名称前缀
		'prefix'    => '',
		// cookie 保存时间
		'expire'    => 3600,
		// cookie 保存路径
		'path'      => '/',
		// cookie 有效域名
		'domain'    => '',
		//  cookie 启用安全传输
		'secure'    => FALSE,
		// httponly设置
		'httponly'  => '',
		// 是否使用 setcookie
		'setcookie' => TRUE,
	],

	//分页配置
	'paginate'     => [
		'type'      => 'bootstrap',
		'var_page'  => 'page',
		'list_rows' => 15,
	],

	//图片上传配置
	'imguploads'   => [
		'exts'     => ['jpg', 'gif', 'png', 'jpeg'],
		'rootPath' => 'upload',
	],
	'img_pathtype'=> [
  //       '0' => 'http://www.mytest.com/',//主站
		// '1' => 'http://www.pt2.com/',//副站
        '0' => 'http://116.196.105.18:800/',//主站
		'1' => 'http://116.196.105.18:805/',//副站
    ],


	//其他设置项
	'other'        => [
		'pwd_prefix'   => '123',//密码前缀
		'zhuce_yajin'  => 1500,//注册押金
		'mobile_sms'   => '[特派员]',//短信发送结尾提示
		'smsyz_status' => '0',//是否开启短信验证
		'areas_city'   => [110000, 310000, 120000, 500000],//直辖市
		'article_id'   => 1,//关于我们分类文章
		'article_cid'  => 1,//常见文章id
		'imgurl'       => ''//图片链接前缀
	],
	//     北京  110000
	// 上海  310000
	// 天津  120000
	// 重庆  500000

	//特派员押金余额状态
	'paylogstatus' => [
		'1' => ['0' => '审核中', '1' => '支付押金成功', '2' => '申请未通过'],//1押金
		'2' => ['0' => '审核中', '1' => '退押金成功', '2' => '申请未通过'],//2退押金
		'3' => ['0' => '审核中', '1' => '提现成功', '2' => '申请未通过'],//3提现
		'4' => ['0' => '正在充值', '1' => '充值成功', '2' => '充值失败'],//4充值
		'5' => ['0' => '正在变更', '1' => '增加成功', '2' => '变更失败'],//6平台增加
		'6' => ['0' => '正在变更', '1' => '减少成功', '2' => '变更失败'],//6平台减少
	],

	//短信宝
	'smsbao'       => [
		'sms_ecmoban_user'     => 'cgw1688',
		'sms_ecmoban_password' => 'zhifeng1688',
		'smsapi'               => 'http://api.smsbao.com/',
	],
	//微信支付
	'wxapi'        => [
		'appid'      => 'wx229cd21c928ffa66',
		'mch_id'     => '1484355072',
		'appsecret'  => '809e8cbbad1ccf0324170a7be648935f',
		'key'        => 'bcda89d37d9b6356b3f6b5be9bcd1ec8',
		'notify_url' => 'http://pay.yuyoupay.com/index/pay/wxnotify',
	],
];
