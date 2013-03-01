<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript">
//<![CDATA[
//alert(parent.parent)
if(self.frameElement != null && (self.frameElement.tagName.toUpperCase() == "IFRAME")){
	//alert("OK")
	top.location.href = "http://bbs.cn-stylelife.net/frame.php";
	//parent.parent.location = "login.jsp";
}
window.jQuery = null;
function j($){
	if(window.jQuery==null){
		window.jQuery = $;
	//alert(window.jQuery)
		$(window).on("hashchange",function(){
			//alert();
			//src();
			if(BA().contentDocument.URL.replace(baseURI, '')!=getHash())
				src(getHash());
		});
	}
}
var baseURI = top.location.protocol+"//"+top.location.host;
function QRonload(){
	//alert('QRonload:  '+EleId('BABEL').contentWindow.location.substr(21));
	//alert(document.getElementById('BABEL').contentWindow.jQuery)
	//window.jQuery = document.getElementById('BABEL').contentDocument.jQuery;
	document.title=BA().contentDocument.title;
	location.hash="!"+BA().contentWindow.location.href.replace(baseURI,'');//$('#BABEL').contents().attr('location');
};
function BA(){
	return document.getElementById('BABEL');
}
function src(u){
	if(u)BA().src = u;
	return BA().src;
}
function getHash(){
	var src=top.location.hash;
	src=src.replace(/^#\!/, '');
	return src;
};
//]]>
</script>
<style type="text/css">
/**/html, body {height: 100%; margin:0; padding:0; border:none;overflow:hidden;}
#div {height: 100%; background: #aaa;}
</style>
</head>

<body>

<!------------------------------------------------------------------------------->
<iframe id="BABEL" style="width:100%;height:100%;margin:0;padding:0;border:0;overflow:auto;" src="#"></iframe>
<!-------------------------------------------------------------------------------->

<script type="text/javascript">

//<![CDATA[
if(getHash()==""){
	top.location.hash="#!/";
}
src(baseURI+getHash());

/**/
//]]>
</script>



</body>
</html>