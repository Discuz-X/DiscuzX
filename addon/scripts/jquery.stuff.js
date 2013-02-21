// JavaScript Document


(function($){
$.fn.seekMaxHeight = function () {
	//element.style.height = '1px';
	this.each(function(){
		var orgHei = parseInt(this.parentNode.offsetHeight);
		var time = 0;
		//alert(orgHei);
		do {
			this.style.height = (this.offsetHeight+216)+"px";
			time++;
		} while(this.parentNode.offsetHeight == orgHei);
		do {
			this.style.height = (this.offsetHeight-36)+"px";
			time++;
		} while(this.parentNode.offsetHeight != orgHei);
		do {
			this.style.height = (this.offsetHeight+6)+"px";
			time++;
		} while(this.parentNode.offsetHeight == orgHei);
		do {
			this.style.height = (this.offsetHeight-1)+"px";
			time++;
		} while(this.parentNode.offsetHeight != orgHei);
		//alert(this.offsetHeight);
	});
	
	/*
	//alert(time);
	*/
};

function seekMaxWidth(element) {
	element.style.width = '1px';
	var orgTop = parseInt(element.offsetTop);
	var time = 0;
	do {
		element.style.width = (element.offsetWidth+216)+"px";
		time++;
	} while(element.offsetTop == orgTop);
	do {
		element.style.width = (element.offsetWidth-36)+"px";
		time++;
	} while(element.offsetTop != orgTop);
	do {
		element.style.width = (element.offsetWidth+6)+"px";
		time++;
	} while(element.offsetTop == orgTop);
	do {
		element.style.width = (element.offsetWidth-1)+"px";
		time++;
	} while(element.offsetTop != orgTop);
	//alert(time);
};




$.GetPageSize = function (){
    var xScroll, yScroll;
    if (window.innerHeight  &&  window.scrollMaxY) { 
        xScroll = document.body.scrollWidth;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight){
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    } else {
        xScroll = document.body.offsetWidth;
        yScroll = document.body.offsetHeight;
    }
    var windowWidth, windowHeight;

	windowWidth = (self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
	windowHeight = (self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight);

    if(yScroll < windowHeight){
        pageHeight = windowHeight;
    } else { 
        pageHeight = yScroll;
    }
    if(xScroll < windowWidth){ 
        pageWidth = windowWidth;
    } else {
        pageWidth = xScroll;
    }
    arrayPageSize = new Object();
	arrayPageSize.pageWidth = pageWidth;
	arrayPageSize.pageHeight = pageHeight;
	arrayPageSize.windowWidth = windowWidth;
	arrayPageSize.windowHeight = windowHeight; 
    return arrayPageSize;
};



})(jQuery);




