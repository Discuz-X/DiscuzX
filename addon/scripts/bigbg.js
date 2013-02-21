// JavaScript Document
jQuery(document).ready(function($){
	$('.wp,#diy-tg,#scrolltop').css({'z-index':'2','position':'relative'});
	$('#scrolltop').css({'position':'fixed'});
	
	
	var fm = document.createDocumentFragment();
	var d = document.createElement("DIV");
	//alert(parseInt($('ft').offsetTop))
	$(d).css({
		width:"100%",
		height:parseInt(EleId('ft').offsetTop)+parseInt(EleId('ft').offsetHeight)+"px",
		backgroundColor:"#666666",
		position:"absolute",
		top:'0',
		left:'0',
		zIndex:'1',
		overflow:'hidden'
	})
	d.id = "sbig_bg";
	d.align = 'center';
	d = _appendChild(d,fm);
	
	
	d = _appendChild(document.createElement("DIV"),d);
	//d.prototype.EleId = d.prototype.getElementById;
	//alert(Element.prototype.getElementById)
	//alert(CSSStyleDeclaration)
	d.style.width = '10px';
	d.style.position = 'relative';
	_appendChild(EleTN('STYLE', EleId('hidebg'))[0],d);
	d = _appendChild(EleTN('TABLE', EleId('hidebg'))[0],d);
	//alert("-"+d.clientWidth/2+"px")
	
	//d.innerHTML+='<table class="bgimg" style="display: inline-table;position:relative;margin-left:-1042px;" border="0" cellpadding="0" cellspacing="0" width="2083"><tbody></tbody></table>';
	_appendChild(fm, document.body)
	
	var a = EleTN("LINK");
	a = a[a.length-1].href
	a = a.slice(0, a.lastIndexOf('/'));
	function placeBG(){
		EleId('sbig_bg').style.height = parseInt(EleId('ft').offsetTop)+parseInt(EleId('ft').offsetHeight)+"px";
		//alert(parseInt(document.body.getBoundingClientRect().top))
		/*while(parseInt(d.offsetHeight)<parseInt(EleId('sbig_bg').offsetHeight) && EleTN('TR', EleId('hidebg'))[0]){
			var tr = _appendChild(EleTN('TR', EleId('hidebg'))[0], EleTN('TBODY',d)[0]);
			window.backgroundIndex.push(tr);
		};*/
		var sawPlace = -(parseInt(document.body.getBoundingClientRect().top)-window.sawPlaceTopLine)+jQuery.GetPageSize().windowHeight;
		//EleId('testmsg').innerHTML = sawPlace+' '+document.documentElement.clientWidth;
		if(sawPlace>window.sawPlace && window.backgroundIndex[0]) {
			//Array();
			//alert(window.backgroundIndex[0].offsetTop);
			function srcplace(obj){
				obj.src = a+"/images/"+obj.getAttribute('_src');
			}
			while(Math.min(sawPlace,parseInt(window.backgroundIndex[0].offsetTop))!=sawPlace){
				var tr = window.backgroundIndex.shift();
				srcplace(EleTN('IMG', tr)[1]);
				if(document.documentElement.clientWidth>960){
					srcplace(EleTN('IMG', tr)[0]);
					srcplace(EleTN('IMG', tr)[2]);
					
				}
			};
			window.sawPlace = sawPlace;
		};
	};
	window.sawPlaceTopLine = parseInt(document.body.getBoundingClientRect().top);
	window.sawPlace = 0;
	window.backgroundIndex = [];
	var n = 0;
	while(EleTN('TR', d)[n]){
		window.backgroundIndex.push(EleTN('TR', d)[n]);
		n+=1;
	};
	delete n;
	d.style.position = 'absolute';
	d.style.top = 0;
	d.style.right = "-"+(d.clientWidth/2-5)+"px";
	/*var ts = _appendChild(document.createElement("DIV"),document.body);
	with(ts.style){
		position = 'fixed';
		zIndex = '3';
		top = '0';
		width = '180px';
		height = '20px';
		backgroundColor = '#FAFAFA';
		left = '600px';
	}
	ts.id = 'testmsg';*/
	placeBG();
	$(window).bind('scroll resize', function() {
		placeBG();
	});
	
});/**/