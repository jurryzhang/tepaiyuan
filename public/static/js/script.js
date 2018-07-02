$("#jiedan label input").on("click",function  () {
	var index=$(this);
	var url=index.attr('gurl');
	var curtype=$('.dingdannav').attr('curtype');
	if (index.is(':checked')) {
	    curstatus=0;
	}else{
		curstatus=1;
	}
	$.post(url, { "curstatus": curstatus,"curtype":curtype },function(result){
		if(result.code==1)
		{
			$('#list_'+curtype).html(result.temp);
		}
	}, "json");
})
$(".dingdannav li").on("click",function  () {
	var index = $(this).index();
	var type=$(this).attr('type');
	var url=$(this).parent().attr('gurl');
	$(this).parent().attr('curtype',type);
	$(this).addClass("active").siblings().removeClass("active");
	
	$.post(url, { "type": type },function(result){
		$('#list_'+type).html(result.temp);
		if(result.code==0)
		{
			alert(result.msg);
		}else{
			$(".ddlist").eq(index).show().siblings().hide()
		}
	}, "json");
})

//返回
$(".back").click(function  () {
	window.location.go(-1);
})


/******* 供应商 js start *********/
$(".foodTitle ul li").on("click",function  () {
	var index = $(this).index();
	$(this).addClass("curLi").siblings().removeClass("curLi");
	$(".foodText").css("display","none")
	$(".foodText").eq(index).css("display","block")
})
/******* 供应商 js end *********/



//点击自动跳转
$(".redirect").click(function  () {
	var url=$(this).attr('redUrl');
	// alert(url);
	window.location=url;
})

$('.imgup_show').on("change",function(){
	var index=$(this);
	if (!$(this).val().match(/.jpg|.jpeg|.gif|.png|.bmp/i)){
		return alert("图片"+$(this).val()+"上传格式不正确，请重新选择");
	}
	file =this.files[0];
	var objUrl = getObjectURL(file) ; //获取图片的路径，该路径不是图片在本地的路径
	if (objUrl) {
		index.parent().css({"background":"url('"+objUrl+"')",'background-size':'1rem'}) ; //将图片路径存入src中，显示出图片
	}
})

//图片上传（单图片）
$(".imgupload").on("change",function(){
	if (!$(this).val().match(/.jpg|.jpeg|.gif|.png|.bmp/i)){
		return alert("图片"+$(this).val()+"上传格式不正确，请重新选择");
	}
	var index=$(this);
	var lookid=index.attr('lookid');
	var shujubiao=index.attr('shujubiao');
	var url=index.attr('saveurl');
	var ziduan=index.attr('ziduan');
	// alert(lookid+'--'+shujubiao);
	file =this.files[0];
	var fd = new FormData();
	fd.append("uploadimg", file);
	fd.append("lookid", lookid);
	fd.append("shujubiao", shujubiao);
	fd.append("ziduan", ziduan);

	// var objUrl = getObjectURL(file) ; //获取图片的路径，该路径不是图片在本地的路径
	// if (objUrl) {
	// 	$(".uploadimg_show").attr("src", objUrl) ; //将图片路径存入src中，显示出图片
	// }
	$(".submit").html('正在上传图片');
	$(".submit").attr('disabled',true);

	$.ajax({    
        url : url,    
        type : 'POST',    
        data : fd,
		cache: false,  
        processData: false,  
        contentType: false,		
        success : function(result){
        	alert(result.msg);
        	if(result['code']==1)
        	{
        		window.location.reload();
        	}
        	$(".submit").html('确定');
        	$(".submit").removeAttr('disabled');
        },
		dataType:"json",
    })

});

//发送手机验证码
$(".getcode").on("click",function  () {
	var num=60;
	var index=$(this);
	var mobile=$('#telphone').val();
	var url=index.attr('sendurl');
	var cishu=index.attr('cishu');
	var telcode=$('#telcode');
	cishu++;
	if(mobile=='')
	{
		alert('请输入手机号');
	}else{
		$.post(url, { "mobile": mobile },function(result){
			// alert(result.code);
			if (result.code==2){
				index.attr('cishu',cishu);
				alert('手机验证码已经成功发送到您的手机');
				if(cishu==1)
				{
					RemainTime(index,num);
				}
			}else{
				if(result.code==3)
				{
					index.attr('cishu',0);
					alert(result.msg);
				}else if(result.code==5)
				{
					telcode.val(getrand(6));
				}else{
					if(result.msg){
						alert(result.msg);
					}else{
						alert('手机验证码发送失败');
					}
				}
				
			}
		}, "json");
	}

	
})

//建立一个可存取到该file的url
function getObjectURL(file) {
	var url = null ;
	if (window.createObjectURL!=undefined) { // basic
		url = window.createObjectURL(file) ;
	} else if (window.URL!=undefined) { // mozilla(firefox)
		url = window.URL.createObjectURL(file) ;
	} else if (window.webkitURL!=undefined) { // webkit or chrome
		url = window.webkitURL.createObjectURL(file) ;
	}
	return url ;
}

//定时时间设置
function RemainTime(index,num){
	var dingshi=setInterval(function(){ 
		if(num==1)
		{
			index.html('重新获取');
			index.attr('cishu',0);
			clearInterval(dingshi);
		}else{
			num--;
			index.html(num+'s');
		}
	}, 1000);
}

//生成随机字符
function getrand(n)
{
	var str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	var s = "";
	for(var i = 0; i < n; i++){
	    var rand = Math.floor(Math.random() * str.length);
	    s += str.charAt(rand);
	}
	return s;
}

//提示函数
function msgTishi(msg)
{
	alert(msg);
}

//页面刷新
function CurRefresh()
{
	location.reload();
}

/**
 * js验证函数
 */
//供应商注册验证
function regCheckgys()
{
	var true_name=$('#true_name').val();
	var person_charge=$('#person_charge').val();
	var mobile=$('#mobile').val();
	var phone=$('#phone').val();
	var tax_number=$('#tax_number').val();
	var stall_number=$('#stall_number').val();
	var scope_business=$('#scope_business').val();
	var status=$('#status');

	if(checkFun(true_name,'请输入真实姓名')){return false;}
	if(checkFun(person_charge,'负责人不能为空')){return false;}
	if(checkFun(mobile,'手机号不能为空')){return false;}
	if(checkFun(mobile,'请填写有效的手机号',4)){return false;}
	if(checkFun(phone,'座机号不能为空')){return false;}
	if(checkFun(tax_number,'税号不能为空')){return false;}
	if(checkFun(tax_number,'税号格式有误',5)){return false;}
	if(checkFun(stall_number,'摊位号不能为空')){return false;}
	if(checkFun(scope_business,'经营范围不能为空')){return false;}
	if(checkFun(status,'请接受协议',6)){return false;}

	return true;
}

// 修改密码验证
function restpwdCheck()
{
	var oldpwd=$('#oldpwd').val();
	var newpwd=$('#newpwd').val();
	var renewpwd=$('#renewpwd').val();
	if(checkFun(oldpwd,'请输入旧密码')){return false;}
	if(checkFun(newpwd,'请输入新密码')){return false;}
	if(checkFun(newpwd,'密码长度为6位字符',2)){return false;}
	if(checkFun(renewpwd,'请输入确认密码',2)){return false;}
	if(checkFun(newpwd,'新密码与确认密码有误',3,renewpwd)){return false;}
	return true;
}

// 忘记密码检测1
function forgetpwdCheck()
{
	var telphone=$('#telphone').val();
	var telcode=$('#telcode').val();
	if(checkFun(telphone,'请输入手机号')){return false;}
	if(checkFun(telcode,'请输入核验码')){return false;}
	return true;
}

// 忘记密码检测2
function forgetpwdCheck2()
{
	var newpwd=$('#newpwd').val();
	var renewpwd=$('#renewpwd').val();
	if(checkFun(newpwd,'请输入新密码')){return false;}
	if(checkFun(newpwd,'密码长度为6位字符',2)){return false;}
	if(checkFun(renewpwd,'请输入确认密码')){return false;}
	if(checkFun(newpwd,'新密码与确认密码有误',3,renewpwd)){return false;}
	return true;
}

/**
 * [checkFun 验证方法]
 * @param  {[type]} val  [需要验证的值]
 * @param  {[type]} msg  [提示]
 * @param  {String} type [类型 1空验证 2长度验证 3是否相等 4手机号验证 5税号验证 6checked验证]
 * @return {[type]}      [description]
 */
function checkFun(val,msg,type=1,other=6)
{
	if(type==1)
	{
		if(!val || val=='')
		{
			alert(msg);return true;
		}
	}
	if(type==2)
	{
		if(val.length<other)
		{
			alert(msg);return true;
		}
	}
	if(type==3)
	{
		if(val!=other)
		{
			alert(msg);return true;
		}
	}
	if(type==4)
	{
		//手机号验证
		var regularC = /^(0|86|17951)?(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/;
		if(!regularC.test(val))
		{
			alert(msg);
			return true;
		}
	}
	if(type==5)
	{
		//税号验证
		var regularC = /^[A-Z0-9]{15}$|^[A-Z0-9]{18}$/;
		if(!regularC.test(val))
		{
			alert(msg);
			return true;
		}
	}
	if(type==6)
	{
		//checked验证
		if(!val.is(':checked'))
		{
			alert(msg);
			return true;
		}
	}

	return false;
}

//邮箱验证
function checkEmail(email)
{
	email = email || '';
	return (email.length > 3 && email.indexOf('@') > -1);
}







