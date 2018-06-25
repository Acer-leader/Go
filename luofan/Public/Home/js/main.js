	$(".ui-select").bind("mouseenter",function(){ 
	var input = $("input[type=hidden]",this),
	    sleHd = $(".select-value",this),
	    list = $(".select-list",this);
	$(".select-value").bind("click",function(){
		$(this).parent().parent().css("zIndex","9");
		$(this).parent().addClass("active")
		$(this).siblings('.select-list').css('display')=="none" ? $(this).siblings('.select-list').show() : !0;
	}),
	$(".select-list li").bind("click",function(){
		$(this).parent().parent().hide();
	})
})
$(".ui-select").bind("mouseleave",function(){
	$(this).parent().css("zIndex","");
	$(this).removeClass("active");
	$(".ui-select").find('.select-list').hide();
})


$(".ban_btn").click(function () {
            $(".ban_btn").removeClass("btn_ts");
            $(this).addClass("btn_ts");
        });
        $(".wysq_dk").hover(function(){
            $(".wysq_dk").removeClass("wid50");
            $(this).addClass("wid50");
        });

        $(".wysq_dk1").hover(function () {
            $(".sqing_dk_form").css({"display":"none","z-index":"-1"});
            $(".wyao_dk_form").css({"display":"block","z-index":"66"});
        });
        $(".wysq_dk2").hover(function () {
            $(".wyao_dk_form").css({"display":"none","z-index":"-1"});
            $(".sqing_dk_form").css({"display":"block","z-index":"66"});
        });


        $(".dklx_list ul li").click(function(){
            var xz_value = $(this).html();
            var this_xz = $(this).parent().parent().parent().find(".input_leixing span").empty();
            this_xz.append(xz_value);
        });

		
	$('.return').click(function(){
		$('html,body').animate({'scrollTop':'0'},1000)
	});
	
	$('.header_last').hover(function(){
		$('.manag_box').stop().slideDown();
	},function(){
		$('.manag_box').stop().slideUp();
	})








