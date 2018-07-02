$("#jiedan p").on("click",function  () {
	$(this).hide().siblings().show();
})


$(".dingdannav li").on("click",function  () {
	var index = $(this).index();
	$(this).addClass("active").siblings().removeClass("active");
	$(".ddlist").eq(index).show().siblings().hide()
})

$(".back").click(function  () {
	window.location.go(-1);
})


$(".foodTitle ul li").on("click",function  () {
	var index = $(this).index();
	$(this).addClass("curLi").siblings().removeClass("curLi");
	$(".foodText").css("display","none")
	$(".foodText").eq(index).css("display","block")
})

$('.imgup_show').on("change", function () {
  var index = $(this);
  if (!$(this).val().match(/.jpg|.jpeg|.gif|.png|.bmp/i)) {
    return alert("图片" + $(this).val() + "上传格式不正确，请重新选择");
  }
  file = this.files[0];
  var objUrl = getObjectURL(file); //获取图片的路径，该路径不是图片在本地的路径
  if (objUrl) {
    index.parent().css({"background": "url('" + objUrl + "')", 'background-size': '1rem'}); //将图片路径存入src中，显示出图片
  }
})

//图片上传（单图片）
$(".imgupload").on("change", function () {
  if (!$(this).val().match(/.jpg|.jpeg|.gif|.png|.bmp/i)) {
    return alert("图片" + $(this).val() + "上传格式不正确，请重新选择");
  }
  var index = $(this);
  var lookid = index.attr('lookid');
  var shujubiao = index.attr('shujubiao');
  var url = index.attr('saveurl');
  var ziduan = index.attr('ziduan');
  // alert(lookid+'--'+shujubiao);
  file = this.files[0];
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
  $(".submit").attr('disabled', true);

  $.ajax({
    url: url,
    type: 'POST',
    data: fd,
    cache: false,
    processData: false,
    contentType: false,
    success: function (result) {
      alert(result.msg);
      if (result['code'] == 1) {
        window.location.reload();
      }
      $(".submit").html('确定');
      $(".submit").removeAttr('disabled');
    },
    dataType: "json",
  })

});

//建立一个可存取到该file的url
function getObjectURL(file) {
  var url = null;
  if (window.createObjectURL != undefined) { // basic
    url = window.createObjectURL(file);
  } else if (window.URL != undefined) { // mozilla(firefox)
    url = window.URL.createObjectURL(file);
  } else if (window.webkitURL != undefined) { // webkit or chrome
    url = window.webkitURL.createObjectURL(file);
  }
  return url;
}

//发送手机验证码
$(".getcode").on("click", function () {
  var num = 60;
  var index = $(this);
  var mobile = $('#telphone').val();
  var url = index.attr('sendurl');
  var cishu = index.attr('cishu');
  var telcode = $('#telcode');
  cishu++;
  if (mobile == '') {
    alert('请输入手机号');
  } else {
    $.post(url, {"mobile": mobile}, function (result) {
      // alert(result.code);
      if (result.code == 2) {
        index.attr('cishu', cishu);
        alert('手机验证码已经成功发送到您的手机');
        if (cishu == 1) {
          RemainTime(index, num);
        }
      } else {
        if (result.code == 3) {
          index.attr('cishu', 0);
          alert(result.msg);
        } else if (result.code == 5) {
          telcode.val(getrand(6));
        } else {
          if (result.msg) {
            alert(result.msg);
          } else {
            alert('手机验证码发送失败');
          }
        }

      }
    }, "json");
  }

})

//定时时间设置
function RemainTime(index, num) {
  var dingshi = setInterval(function () {
    if (num == 1) {
      index.html('重新获取');
      index.attr('cishu', 0);
      clearInterval(dingshi);
    } else {
      num--;
      index.html(num + 's');
    }
  }, 1000);
}

//生成随机字符
function getrand(n) {
  var str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  var s = "";
  for (var i = 0; i < n; i++) {
    var rand = Math.floor(Math.random() * str.length);
    s += str.charAt(rand);
  }
  return s;
}

//点击自动跳转
$(".redirect").click(function () {
  var url = $(this).attr('redUrl');
  // alert(url);
  window.location = url;
})

