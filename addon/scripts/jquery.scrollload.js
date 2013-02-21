// JavaScript Document


;(function($){
	
$.fn.ScrollLoad = function (callback) {
	this.each(function(){
		
		//callback.call(this);
		var offsetTop=getPosition(this);
		
		
	});
};

function getPosition(target) {
	var left = 0, top = 0;
	
	do {
		left += target.offsetLeft || 0;
		top += target.offsetTop || 0;
	} while(target = target.offsetParent);
	
	return {left:left,top:top};
}
function onScroll(){
	EleId('sbig_bg').style.height = parseInt(EleId('ft').offsetTop)+parseInt(EleId('ft').offsetHeight)+"px";

	var sawPlace = -(parseInt(document.documentElement.getBoundingClientRect().top)-window.sawPlaceTopLine)+jQuery.GetPageSize().windowHeight;

	if(sawPlace>window.sawPlace && window.verticalPixelIndex[0]) {
		while(Math.min(sawPlace,getPosition(window.verticalPixelIndex[0]).top)!=sawPlace){
			var tr = window.verticalPixelIndex.shift();
		};
		window.sawPlace = sawPlace;
	};
};
window.sawPlaceTopLine = parseInt(document.documentElement.getBoundingClientRect().top);
window.sawPlace = 0;
window.verticalPixelIndex = [];
$(document).ready(function(){
	onScroll();
});
$(window).scroll(onScroll);
})(jQuery);