//<!-- Add jQuery library -->
//<script type="text/javascript" src="data/asset/js/fancybox/lib/jquery-1.10.2.min.js"></script>
//<!-- Add mousewheel plugin (this is optional) -->
//<script type="text/javascript" src="data/asset/js/fancybox/lib/jquery.mousewheel.pack.js?v=3.1.3"></script>
//<!-- Add fancyBox main JS and CSS files -->
//<script type="text/javascript" src="data/asset/js/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>
//<link rel="stylesheet" type="text/css" href="data/asset/js/fancybox/source/jquery.fancybox.css?v=2.1.5" media="screen" />
//<!-- Add Button helper (this is optional) -->
//<link rel="stylesheet" type="text/css" href="data/asset/js/fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" />
//<script type="text/javascript" src="data/asset/js/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
//<!-- Add Thumbnail helper (this is optional) -->
//<link rel="stylesheet" type="text/css" href="data/asset/js/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />
//<script type="text/javascript" src="data/asset/js/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
//<!-- Add Media helper (this is optional) -->
//<script type="text/javascript" src="data/asset/js/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
//<script type="text/javascript">
//$(document).ready(function() {
/*
 *  Simple image gallery. Uses default settings
 */
//    $('.fancybox').fancybox();
//    });
//</script>
var path = ""; //path to clear.gif
(function() {
    var scripts = document.getElementsByTagName("SCRIPT"),
        src = (scripts[scripts.length - 1].src);
    path = src.slice(0, src.lastIndexOf('/') + 1);
})();

//<!-- Add jQuery library -->
//document.writeln('<script type="text/javascript" src="' + path + 'lib/jquery-1.10.2.min.js"></script>');
//<!-- Add mousewheel plugin (this is optional) -->
document.writeln('<script type="text/javascript" src="' + path + 'lib/jquery.mousewheel.pack.js?v=3.1.3"></script>');
//<!-- Add fancyBox main JS and CSS files -->
document.writeln('<script type="text/javascript" src="' + path + 'source/jquery.fancybox.pack.js?v=2.1.5"></script>');
document.writeln('<link rel="stylesheet" type="text/css" href="' + path + 'source/jquery.fancybox.css?v=2.1.5" media="screen" />');
//<!-- Add Button helper (this is optional) -->
document.writeln('<link rel="stylesheet" type="text/css" href="' + path + 'source/helpers/jquery.fancybox-buttons.css?v=1.0.5" />');
document.writeln('<script type="text/javascript" src="' + path + 'source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>');
//<!-- Add Thumbnail helper (this is optional) -->
document.writeln('<link rel="stylesheet" type="text/css" href="' + path + 'source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />');
document.writeln('<script type="text/javascript" src="' + path + 'source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>');
//<!-- Add Media helper (this is optional) -->
document.writeln('<script type="text/javascript" src="' + path + 'source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>');
//<script type="text/javascript">
//$(document).ready(function() {
/*
 *  Simple image gallery. Uses default settings
 */
//    $('.fancybox').fancybox();
//    });
//</script>