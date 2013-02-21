// JavaScript Document
/*
HSV色彩模型(Hue   色度,   Saturation	饱和度,	Value			纯度) 
HLS色彩模型(Hue   色度,   Lightness	亮度,	Saturation		饱和度)
H和S都是相同的，关键是L和V 
好多地方都说是亮度，如果是这样，那HSV和HLS有什么区别？

 
两者的意义大体一样，只是在饱和度，亮度的取值轴有不同的意义。
比如，HSV方式中，各个颜色要想达到最亮，就要把Value设置为100%，
同时Saturation下降时，颜色也慢慢衰退。
而HLS方式中，当把亮度调为100%时，就没有颜色了，成为白色。
而各个纯颜色的表现实在亮度为50%的时候，
同时Saturation如果减少的话，颜色会慢慢变透彻。
*/




;(function(){

jQuery.Color = function (color){
	var __color = new Object();
	__color.r = 0;
	__color.g = 0;
	__color.b = 0;
	//alert(HextoRGB)
	color = String(color);
	//alert(color)
	if(color.charAt(0) == "#"){
		HextoRGB(color);
	}else if(color.indexOf('rgb')!=-1) {
		color = color.slice(color.indexOf("(")+1, -1).split(',');
		__color.r = parseInt(color[0]);
		__color.g = parseInt(color[1]);
		__color.b = parseInt(color[2]);
	}
	this.RGB = function (){ return __color;};
	this.RGBHEX = function (){
		return RGBtoHex();
	};
	this.RGB.R = function (num){
		num = parseInt(num);
		if(num>-1 && num<256){
			__color.r = num;
		}
		return __color.r;
	};
	this.RGB.G = function (num){
		num = parseInt(num);
		if(num>-1 && num<256){
			__color.g = num;
		}
		return __color.g;
	};
	this.RGB.B = function (num){
		num = parseInt(num);
		if(num>-1 && num<256){
			__color.b = num;
		}
		return __color.b;
	};
	this.HLS = function (hls){
		if(hls){
			try{
				HLStoRGB(hls.h, hls.l, hls.s);
			}catch(e){
				
			}
		}
		return RGBtoHLS(__color.r, __color.g, __color.b);
	};
	this.HLS.H = function (h){
		if(h){
			try{
				var ss = this();
				ss.h = h;
				this(ss);
				//alert(this())
			}catch(e){
				
			}
		}
		return this().h;
	};
	this.HLS.L = function (l){
		if(l){
			try{
				var ss = this();
				ss.l = l;
				this(ss);
				//alert(this())
			}catch(e){
				
			}
		}
		return this().l;
		
	};
	this.HLS.S = function (s){
		if(s){
			try{
				var ss = this();
				ss.s = s;
				this(ss);
				//alert(this())
			}catch(e){
				
			}
		}
		return this().s;
		
	};
	this.HSV = function (hsv){
		if(hsv){
			try{
				HSVtoRGB(hsv.h, hsv.s, hsv.v);
			}catch(e){
				
			}
		}
		return RGBtoHSV(__color.r, __color.g, __color.b);
	};
	this.HSV.H = function (h){
		if(h){
			try{
				var ss = this();
				ss.h = h;
				this(ss);
				//alert(this())
			}catch(e){
				
			}
		}
		return this().h;
		
	};
	this.HSV.S = function (s){
		if(s){
			try{
				var ss = this();
				ss.s = s;
				this(ss);
				//alert(this())
			}catch(e){
				
			}
		}
		return this().s;
		
	};
	this.HSV.V = function (v){
		if(v){
			try{
				var ss = this();
				ss.v = v;
				this(ss);
				//alert(this())
			}catch(e){
				
			}
		}
		return this().v;
		
	};
	this.CMYK = function (){
		
	};
	this.CMYK.C = function (){
		
	};
	this.CMYK.M = function (){
		
	};
	this.CMYK.Y = function (){
		
	};
	this.CMYK.K = function (){
		
	};
	function HextoRGB(hex) {
		hex = hex.toUpperCase();
		if (hex.charAt(0) == "#") {
			hex = hex.substring(1, hex.length);
		}
		var _color = new Object;
		_color.r = hex.substring(0, 2);
		_color.g = hex.substring(2, 4);
		_color.b = hex.substring(4, 6);
		__color.r = parseInt(_color.r, 16);
		__color.g = parseInt(_color.g, 16);
		__color.b = parseInt(_color.b, 16);
		if (isNaN(__color.r)) {
			__color.r = 0;
		}
		if (isNaN(__color.g)) {
			__color.g = 0;
		}
		if (isNaN(__color.b)) {
			__color.b = 0;
		}
		//return __color;
	};
	function RGBtoHex() {
		function DectoHex(num) {
			var i = 0;
			var j = 20;
			var str = "#";
			while (j >= 0) {
				i = (num >> j) % 16;
				str += "0123456789ABCDEF".charAt(i);
				j -= 4;
			}
			return str;
		}
		var n = Math.round(__color.b);
		n += Math.round(__color.g) << 8;
		n += Math.round(__color.r) << 16;
		return DectoHex(n);
	};
	function RGBtoHLS(R, G, B) {
		R /= 255;
		G /= 255;
		B /= 255;
		var max, min, diff, r_dist, g_dist, b_dist;
		var hls = new Array(3);
		max = MAX(R, G, B);
		min = MIN(R, G, B);
		diff = max - min;
		hls.l = (max + min) / 2;
		if (diff == 0) {
			hls.h = 0;
			hls.s = 0;
		}else {
			if (hls.l < 0.5) {
				hls.s = diff  / (max + min);
			}else {
				hls.s = diff  / (2 - max - min);
			}
			r_dist = (max - R) / diff;
			g_dist = (max - G) / diff;
			b_dist = (max - B) / diff;
			if (R == max) {
				hls.h = b_dist - g_dist;
			}else if (G == max) {
				hls.h = 2 + r_dist - b_dist;
			}else if (B == max) {
				hls.h = 4 + g_dist - r_dist;
			}
			hls.h *= 60;
			if (hls.h < 0) {
				hls.h += 360;
			}
			if (hls.h >= 360) {
				hls.h -= 360;
			}
		}
		return hls;
	};
	function HLStoRGB(H, L, S) {
		function RGB(q1, q2, hue) {
			if (hue > 360) {
				hue = hue - 360;
			}
			if (hue < 0) {
				hue = hue + 360;
			}
			if (hue < 60) {
				return (q1 + (q2 - q1) * hue  / 60);
			}else if (hue < 180) {
				return (q2);
			}else if (hue < 240) {
				return (q1 + (q2 - q1) * (240 - hue)  / 60);
			}else {
				return (q1);
			}
		}
		var p1, p2;
		var rgb = new Array(3);
		if (L <= 0.5) {
			p2 = L * (1 + S);
		}else {
			p2 = L + S - (L * S);
		}
		p1 = 2 * L - p2;
		if (S == 0) {
			rgb.r = L;
			rgb.g = L;
			rgb.b = L;
		}else {
			rgb.r = RGB(p1, p2, H + 120);
			rgb.g = RGB(p1, p2, H);
			rgb.b = RGB(p1, p2, H - 120);
		}
		__color.r = rgb.r*255;
		__color.g = rgb.g*255;
		__color.b = rgb.g*255;
		return __color;
	};
	function RGBtoHSV(r, g, b) {
		r /= 255;
		g /= 255;
		b /= 255;
		var min, max, delta;
		var hsv = new Array(3);
		min = MIN(r, g, b);
		max = MAX(r, g, b);
		hsv.v = max;
		delta = max - min;
		if (max != 0) {
			hsv.s = delta  / max;
		} else {
			hsv.s = .005;
			hsv.h = 0;
			return hsv;
		}
		if (delta == 0) {
			hsv.s = .005;
			hsv.h = 0;
			return hsv;
		}
		if (r == max) {
			hsv.h = (g - b)  / delta;
		}else if (g == max) {
			hsv.h = 2 + (b - r)  / delta;
		}else {
			hsv.h = 4 + (r - g)  / delta;
		}
		hsv.h *= 60;
		if (hsv.h < 0) {
			hsv.h += 360;
		}
		if (hsv.h >= 360) {
			hsv.h -= 360;
		}
		return hsv;
	};
	function HSVtoRGB(h, s, v) {
		var rgb = new Array(3);
		var i;
		var f, p, q, t;
		if (s == 0) {
			rgb.r = rgb.g = rgb.b = v * 255;
			return rgb;
		}
		h /= 60;
		i = Math.floor(h);
		f = h - i;
		p = v * (1 - s);
		q = v * (1 - s * f);
		t = v * (1 - s * (1 - f));
		switch (i) {
			case 0:
				rgb.r = v;
				rgb.g = t;
				rgb.b = p;
				break;
			case 1:
				rgb.r = q;
				rgb.g = v;
				rgb.b = p;
				break;
			case 2:
				rgb.r = p;
				rgb.g = v;
				rgb.b = t;
				break;
			case 3:
				rgb.r = p;
				rgb.g = q;
				rgb.b = v;
				break;
			case 4:
				rgb.r = t;
				rgb.g = p;
				rgb.b = v;
				break;
			default:
				rgb.r = v;
				rgb.g = p;
				rgb.b = q;
				break;
		}
		__color.r = rgb.r*255;
		__color.g = rgb.g*255;
		__color.b = rgb.g*255;
		return __color;
	};
	function MIN() {
		var min = 255;
		for (var i = 0; i < arguments.length; i++) {
			if (arguments[i] < min) {
				min = arguments[i];
			}
		}
		return min;;
	};
	function MAX() {
		var max = 0;
		for (var i = 0; i < arguments.length; i++) {
			if (arguments[i] > max) {
				max = arguments[i];
			}
		}
		return max;;
	};
	//return __color;
	function RGBtoCMYK(r, g, b) {
		// doesn't distort! not really usable...
		r /= 255;
		g /= 255;
		b /= 255;
		var cmyk = new Array(4);
		cmyk.c = Math.pow(1 - r, .45);
		cmyk.m = Math.pow(1 - g, .45);
		cmyk.y = Math.pow(1 - b, .45);
		cmyk.k = MIN(cmyk.c, cmyk.y, cmyk.m);
		cmyk.c -= cmyk.k;
		cmyk.m -= cmyk.k;
		cmyk.y -= cmyk.k;
		return cmyk;
	};
	return this;
};


})();
/*RGB 0~255
H 0~359
S、L、V 0~100
要注意的是 HLS和HSV模式转RGB的函数，输入的 L\S或者S\V值要预先除以100
找了很久才找到的，总会有用到的时候吧（我现在就正好要用到）
另外提供WEB216安全色的算法（这个其实很简单-_-bbb 附赠品 :p）
输入的值是字符串   #3E6A95 这样的*/
function snaptoSafe(c) {
	var safe = new Array;
	safe[0] = parseInt(c.substr(1, 2), 16);
	safe[1] = parseInt(c.substr(3, 2), 16);
	safe[2] = parseInt(c.substr(5, 2), 16);
	var i;
	for (i = 0; i < safe.length; i++) {
		safe[i] = Math.round(safe[i] / 51) * 51;
		safe[i] = safe[i].toString(16);
		if (safe[i].length < 2) {
			safe[i] = '0' + safe[i];
		}
	}
	var thecolor = '#' + safe[0] + safe[1] + safe[2];
	return thecolor;
}
//返回离该色彩最近的安全色