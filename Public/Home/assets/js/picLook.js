(function($) {
	$.fn.picLook = function(options) {
		var _this = this,
			index = 0;
		defaultVal = {
			speed: 800
		};
		var opt = $.extend(defaultVal, options);
		var plImgshow= _this.find(".picLookshow .plImg");
		var len=plImgshow.length;
		var showimg=_this.find(".picLook");
		var imghtml=_this.find(".picLookshow").html();
		var plImg;		
		plImgshow.click(function(){
		   $("html,body").css("overflow","hidden");
		   index=$(this).index();
		   showimg.prepend(imghtml);
		    plImg = showimg.find(".plImg");
		    plImg.eq(index).css("opacity", "1");
		    showimg.animate({
					top: "0"
				});
				
		  });
		  var plNext = showimg.find(".plNext");
		  var plClose = showimg.find(".plClose");  

		 if(len>1){
		 
		       				
			plNext.click(function() {
				if (!plImg.is(":animated")) {
					plImg.eq(index).css("z-index", "200").animate({
						top: "100%"
					}, opt.speed, function() {
						$(this).css({
							"top": "0",
							"opacity": "0",
							"z-index": "100"
						});

					});
					if (index == plImg.length - 1) {
						plImg.eq(0).css({
							"opacity": "1",
							"z-index": "80"
						});
						index = 0;
					} else {
						plImg.eq((index + 1)).css({
							"opacity": "1",
							"z-index": "80"
						});
						index = index + 1;
					}
				}

			});
			var browser = window.navigator.userAgent.toLowerCase().indexOf('firefox');

			if (browser != -1) {
				//处理火狐滚轮事件
				showimg[0].addEventListener('DOMMouseScroll', function(ev) {
					var oEvent = ev || event;				
					if (oEvent.detail > 0) { //向上滚动
				       plNext.click();
					} 
				});
			} else {				
				//其他浏览器
					showimg[0].onmousewheel = function(ev) {
					var oEvent = ev || event;					//上下滚轮动作判断
					if (oEvent.wheelDelta < 0) { //向下滚动
						plNext.click();
					} 
				}
			}
	   }
          plClose.click(function() {
              
				showimg.css({
				  "top":"-150%"				   
				});
				plImg.remove();
				$("html,body").css("overflow","visible");
			});
		
	}
})(jQuery)