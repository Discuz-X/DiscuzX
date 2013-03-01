<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="Addon/Scripts/jQuery.js"></script>
<script type="text/javascript" src="Addon/Scripts/jquery.flash.js"></script>
<style>
body{
	margin:0;
}
#te{
	width:960px;
	border-bottom:1px solid #0F0;
}
#te:after{
content:'.';
visibility:hidden;
	display:block;
	clear:both;
	height:0;
}
#CA1,
#CA2{ background-color: #2F146B; }
#CA3,
#CA4{ background-color: #1E31E1; }
#CA5,
#CA6{ background-color: #17E875; }
#CA7,
#CA8{ background-color: #A6FF00; }
#CA9,
#CA10{
background:yellow;
}
#CA11,
#CA12{
background:#FC0;
}
#CA13,
#CA14{
background:orange;
}
#CA15{ background-color: #E9168F; }
#te>div{
	width:64px;
	height:800px;
}
</style>
<title>代码测试页</title>
</head>

<body>
	<div id="te">
	
	</div>
	<script type="text/javascript">
	for(var i=0;i<15;i++){
		$("#te").append($("<div id='CA"+(i+1)+"'>"+i+"</div>"));
	}
	$('#te>div:nth-child(2n)').css({'float':'right'});
	$('#te>div:nth-child(2n+1)').css({'float':'left'});
	//$("#te").after('<p style="display:block;clear:both;background-color:#888">.</p>')
	</script>
</body>
</html>