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





