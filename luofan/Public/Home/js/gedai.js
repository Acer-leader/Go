$(document).ready(
    function() {
         var interval;
        	interval = setInterval(function() {
           		$(".next").triggerHandler("click");
	       		$(".no4").find(".info").fadeIn(1000).find("input[type='text']").val("");
	        }, 3000);

	        $("#adviser").hover(function(){
	        	clearInterval(interval);
	        } , function(){
		        	interval = setInterval(function() {
	           		$(".next").triggerHandler("click");
		       		$(".no4").find(".info").fadeIn(1000).find("input[type='text']").val("");
		        }, 3000);
	        });

        var json0 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": -130
        }
        var json1 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 0
        }
        var json2 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 130
        }
        var json3 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 260
        }
        var json4 = {
            "width": 420,
            "height": 260,
            "top": 0,
            "left": 390
        }
        var json5 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 820
        }
        var json6 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 950
        }
        var json7 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 1080
        }
        var json8 = {
            "width": 120,
            "height": 180,
            "top": 45,
            "left": 1210
        }
        $(".next").click(
            function() {
            	
                if (!$("#adviser li").is(":animated")) {
                    //先交换位置
                    $(".no1").animate(json0, 400);
                    $(".no2").animate(json1, 400);
                    $(".no3").animate(json2, 400);
                    $(".no4").animate(json3, 400 );
                    $(".no5").animate(json4, 400);
                    $(".no6").animate(json5, 400);
                    $(".no7").animate(json6, 400);
                    $(".no8").animate(json7, 400);
                    $(".no0").css(json6);
                    //再交换身份
                    $(".no0").attr("class", "wait");
                    $(".no1").attr("class", "no0").find(".info").hide();
                    $(".no2").attr("class", "no1").find(".info").hide();
                    $(".no3").attr("class", "no2").find(".info").hide();
                    $(".no4").attr("class", "no3").find(".info").hide();
                    $(".no5").attr("class", "no4").find(".info").fadeIn(1000).find("input[type='text']").val("");
                    $(".no6").attr("class", "no5").find(".info").hide();
                    $(".no7").attr("class", "no6").find(".info").hide();
                    $(".no8").attr("class", "no7").find(".info").hide();
                    //$(".no3").next().find(".info").fadeIn(20000).find("input[type='text']").val("");
                    //上面的交换身份，把no0搞没了！所以，我们让no1前面那个人改名为no0
                    if ($(".no7").next().length != 0) {
                        //如果no5后面有人，那么改变这个人的姓名为no6
                        $(".no7").next().attr("class", "no8");
                    } else {
                        //no5前面没人，那么改变此时队列最开头的那个人的名字为no0
                        $("#adviser li:first").attr("class", "no8");
                    }
                    //发现写完上面的程序之后，no6的行内样式还是json0的位置，所以强制：
                    $(".no8").css(json8);
                }
            }
        );
        $(".prev").click(
            function() {
                if (!$("#adviser li").is(":animated")) {
                    $(".no0").animate(json1, 400);
                    $(".no1").animate(json2, 400);
                    $(".no2").animate(json3, 400);
                    $(".no3").animate(json4, 400);
                    $(".no4").animate(json5, 400);
                    $(".no5").animate(json6, 400);
                    $(".no6").animate(json7, 400);
                    $(".no7").animate(json8, 400);
                    $(".no8").css(json0);
                    $(".no8").attr("class", "wait");
                    $(".no7").attr("class", "no8").find(".info").hide();
                    $(".no6").attr("class", "no7").find(".info").hide();
                    $(".no5").attr("class", "no6").find(".info").hide();
                    $(".no4").attr("class", "no5").find(".info").hide();
                    $(".no3").attr("class", "no4").find(".info").fadeIn(1000).find("input[type='text']").val("");
                    $(".no2").attr("class", "no3").find(".info").hide();
                    $(".no1").attr("class", "no2").find(".info").hide();
                    $(".no0").attr("class", "no1").find(".info").hide();
                    //上面的交换身份，把no0搞没了！所以，我们让no1前面那个人改名为no0
                    if ($(".no1").prev().length != 0) {
                        //如果no1前面有人，那么改变这个人的姓名为no0
                        $(".no1").prev().attr("class", "no0");
                    } else {
                        //no1前面没人，那么改变此时队列最后的那个人的名字为no0
                        $("#adviser li:last").attr("class", "no0");
                    }
                    $(".no0").css(json0);
                }
            }
        );
    }
);