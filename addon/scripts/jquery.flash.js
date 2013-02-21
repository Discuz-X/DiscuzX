/**
 * Flash (http://jquery.lukelutman.com/plugins/flash)
 * A jQuery plugin for embedding Flash movies.
 * 
 * Version 2.0
 * May 17th, 2012
 *
 * Copyright (c) 2012 Luke Lutman (http://www.lukelutman.com)
 * Dual licensed under the MIT and GPL licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/gpl-license.php
 * 
 * Inspired by:
 * SWFObject (http://blog.deconcept.com/swfobject/)
 * UFO (http://www.bobbyvandersluis.com/ufo/)
 * sIFR (http://www.mikeindustries.com/sifr/)
 * 
 * IMPORTANT: 
 * The packed version of jQuery breaks ActiveX control
 * activation in Internet Explorer. Use JSMin to minifiy
 * jQuery (see: http://jquery.lukelutman.com/plugins/flash#activex).
 *
 **/ ;

//document.getElementById("gad").innerHTML = posterbottomad240;





var posterbottomad600 = '<scr'+'ipt type="text/javascript"><!--\r\ngoogle_ad_client = "ca-pub-1689530283436837";/* 发帖者信息下方大 */google_ad_slot = "1845439149";google_ad_width = 120;google_ad_height = 600;//-->\r\n</scr'+'ipt><scr'+'ipt type="text/javascript"src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></scr'+'ipt>';
;(function($){
	
var $$, $$$;




/**
 * 
 * @desc Replace matching elements with a flash movie.
 * @author Luke Lutman
 * @version 1.0.1
 *
 * @name flash
 * @param Hash htmlOptions Options for the embed/object tag.
 * @param Hash pluginOptions Options for detecting/updating the Flash plugin (optional).
 * @param Function replace Custom block called for each matched element if flash is installed (optional).
 * @param Function update Custom block called for each matched if flash isn't installed (optional).
 * @type jQuery
 *
 * @cat plugins/flash
 * 
 * @example $('#hello').flash({ src: 'hello.swf' });
 * @desc Embed a Flash movie.
 *
 * @example $('#hello').flash({ src: 'hello.swf' }, { version: 8 });
 * @desc Embed a Flash 8 movie.
 *
 * @example $('#hello').flash({ src: 'hello.swf' }, { expressInstall: true });
 * @desc Embed a Flash movie using Express Install if flash isn't installed.
 *
 * @example $('#hello').flash({ src: 'hello.swf' }, { update: false });
 * @desc Embed a Flash movie, don't show an update message if Flash isn't installed.
 *
**/
$$ = $.flash = function(htmlOptions, pluginOptions, replace, update){
	var __tmp = document.createDocumentFragment();
	return $(__tmp).flash(htmlOptions, pluginOptions, replace, update);
};



$.fn.flash = function(htmlOptions, pluginOptions, replace, update) {
	//jQuery.flash
	// Set the default block.
	var block = replace || $$.replace;
	
	// Merge the default and passed plugin options.
	pluginOptions = $$.copy($$.pluginOptions, pluginOptions);
	
	
	// Detect Flash.
	if(!$$.hasFlash(pluginOptions.version)) {
		// Use Express Install (if specified and Flash plugin 6,0,65 or higher is installed).
		if(pluginOptions.expressInstall && $$.hasFlash(10,0,0)) {
			// Add the necessary flashvars (merged later).
			var expressInstallOptions = {
				flashvars: {
					MMredirectURL: location,
					MMplayerType: 'PlugIn',
					MMdoctitle: $('title').text() 
				}
			};
		// Ask the user to update (if specified).
		} else if (pluginOptions.update) {
			// Change the block to insert the update message instead of the flash movie.
			block = update || $$.update;
		// Fail
		} else {
			// The required version of flash isn't installed.
			// Express Install is turned off, or flash 6,0,65 isn't installed.
			// Update is turned off.
			// Return without doing anything.
			return this;
		}
	}
	
	// Merge the default, express install and passed html options.
	htmlOptions = $$.copy(new $$.htmlOptions(), expressInstallOptions, htmlOptions);
	
	// Invoke $block (with a copy of the merged html options) for each element.
	return this.each(function(){
		block.call(this, htmlOptions);
	});

	
};
/**
 *
 * @name flash.copy
 * @desc Copy an arbitrary number of objects into a new object.
 * @type Object
 * 
 * @example $$.copy({ foo: 1 }, { bar: 2 });
 * @result { foo: 1, bar: 2 };
 *
**/
$$.copy = function() {
	//var options = {}, flashvars = {};
	for(var i = 1; i < arguments.length; i++) {
		var arg = arguments[i];
		if(arg == undefined) continue;
		arguments[0].ex(arg);
		// don't clobber one flash vars object with another
		// merge them instead
		if(arg.flashvars == undefined) continue;
		arguments[0].ex.call(arguments[0].flashvars, arg.flashvars);
	};
	//options.flashvars = flashvars;
	return arguments[0];
};
/*
 * @name flash.hasFlash
 * @desc Check if a specific version of the Flash plugin is installed
 * @type Boolean
 *
**/
$$.hasFlash = function() {
	// look for a flag in the query string to bypass flash detection
	if(/hasFlash\=true/.test(location)) return true;
	if(/hasFlash\=false/.test(location)) return false;
	var pv = $$.hasFlash.playerVersion().match(/\d+/g);
	var rv = String([arguments[0], arguments[1], arguments[2]]).match(/\d+/g) || String($$.pluginOptions.version).match(/\d+/g);
	for(var i = 0; i < 3; i++) {
		pv[i] = parseInt(pv[i] || 0);
		rv[i] = parseInt(rv[i] || 0);
		// player is less than required
		if(pv[i] < rv[i]) return false;
		// player is greater than required
		if(pv[i] > rv[i]) return true;
	}
	// major version, minor version and revision match exactly
	return true;
};
/**
 *
 * @name flash.hasFlash.playerVersion
 * @desc Get the version of the installed Flash plugin.
 * @type String
 *
**/
$$.hasFlash.playerVersion = function() {
	// ie
	try {
		try {
			// avoid fp6 minor version lookup issues
			// see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
			var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
			try { axo.AllowScriptAccess = 'always';	} 
			catch(e) { return '6,0,0'; }				
		} catch(e) {}
		return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
	// other browsers
	} catch(e) {
		try {
			if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
				return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
			}
		} catch(e) {}		
	}
	return '0,0,0';
};
/**
 *
 * @name flash.htmlOptions
 * @desc The default set of options for the object or embed tag.
 *
**/
$$.htmlOptions = function(){
/*	,
	toString:function(){
		if(ActiveXObject){
			
		}else{
			var s = '';
			for(var key in this)
				if(typeof this[key] != 'function')
					s += key+'="'+this[key]+'" ';
			alert(s);
			return s;	
		}
	}
	flashvars: {
		toString:function(){
			var s = '';
			for(var key in this)
				if(typeof this[key] != 'function')
					s += key+'='+encodeURIComponent(this[key])+'&';
			return s.replace(/&$/, '');	
		}
	},*/
	
	$$$.call(this, 'src', '?', 'Movie', 'src', true);
	$$$.call(this, 'Menu', false, null, '', true);
	$$$.call(this, 'height', '2', null, null, false);
	
	$$$.call(this, 'type', 'application/x-shockwave-flash', null, null, false);
	$$$.call(this, 'width', '2', null, null, false);
	$$$.call(this, 'wmode', 'transparent', null, null);
	$$$.call(this, 'allowScriptAccess', 'sameDomain', null, null);
	$$$.call(this, 'quality', 'high', null, null, true);
	
	$$$.call(this, 'allowFullScreen', 'false', null, null);
	$$$.call(this, 'id', '', null, null, false);
	$$$.call(this, 'name', '', null, null, false);
	$$$.call(this, 'style', 'position:relative;', null, null, false);
	
	this.flashvars= {
		toString:function(){
			var s = '';
			for(var key in this)
				if(typeof this[key] != 'function')
					s += key+'='+encodeURIComponent(this[key])+'&';
			return s.replace(/&$/, '');	
		}
	};
	this.ex = function (ob){
		for(var key in ob)
			if(typeof ob[key] != 'function')
				this.htmlOptionsVariable(key, ob[key]);
	};
	this.objectNode = function (){
		try{
			if(window.ActiveXObject){
				var ob = $('<object></object>');
				var ds = document.createDocumentFragment();
				var obje = document.createElement('object');
				var par = document.createElement('param');
				par.name = "Movie";
				par.value = this.Movie;
				_appendChild(par, obje);
				obje.type = 'application/x-shockwave-flash';
				//alert(obje.Movie);
				//obje.Movie = this.Movie;
				//_appendChild(dd, ds)
				//ob.appendTo(ds);
				//alert(ds.firstChild.childNodes.length)
				//alert(ob.attr('childNodes'))
				for(var key in this){
					if(typeof this[key] != 'function'){
						//alert(key + ' ' +typeof this[key] + ' '+ this[key].objParam)
						//if(1===2 && this[key].objParam == undefined || this[key].objParam){
							//ob.append(jQuery(document.createElement('param')).attr('name', (this[key].objMarkup || key)).attr('value', this[key]));
						//	var par = document.createElement('param');
						//	par.name = (this[key].objMarkup || key);
						//	par.value = this[key];
						//	_appendChild(par, obje);
						//	alert(obje.childNodes.length)
						//}else{
							//ob.attr((this[key].objMarkup || key), this[key]);
							if(key == 'type' || key == 'Movie' || key == 'src')continue;
							if(key == 'style'){
								obje.style.cssText = this[key];
							} else {
								//jQuery(obje).attr((this[key].objMarkup || key), this[key])
								obje[(this[key].objMarkup || key)] = this[key];
							}
							//alert(key)
						//}
					}
				}
				//alert(obje.outerHTML)
				return obje;
			}else{
				
				
				var em = $(document.createElement('embed'));
				var s = '';
				for(var key in this)
					if(typeof this[key] != 'function'){
						em.attr((this[key].embMarkup || key), this[key]);
					}
				//alert(s);
				//alert(em.attr('width'))
				return em;	
			}
		} catch(e){
			alert(e.message);
		}
	};
	this.toString=function(){
		if(window.ActiveXObject){
			var fff=' ',fgh = '';
			for(var key in this){
				if(typeof this[key] != 'function'){
					//alert(key + ' ' +typeof this[key] + ' '+ this[key].objParam)
					if(this[key].objParam == undefined || this[key].objParam){
						fgh += '<param name="'+(this[key].objMarkup || key)+'" value="'+this[key]+'" />';
					}else{
						fff += (this[key].objMarkup || key)+'="'+this[key]+'" ';
					}
				}
			}
			return '<object'+ fff+'>'+
				fgh+
				'</object>';
		}else{
			var s = '';
			for(var key in this)
				if(typeof this[key] != 'function'){
					s += (this[key].embMarkup || key)+'="'+this[key]+'" ';
				}
			//alert(s);
			return '<embed ' + s + ' />';	
		}
	};
	this.htmlOptionsVariable = $$$;
};



$$$ = function (variableName, defaultValue, objMarkup, embMarkup, objParam) {
	try{
		//alert(Boolean(''))
		//alert($$.htmlOptions[variableName])
		//variableName = variableName.toLowerCase();
		switch (arguments.length){
			case 4:
				objParam = true;
			case 5:
				//if(objMarkup) objMarkup = objMarkup.toLowerCase();
				//if(embMarkup) embMarkup = embMarkup.toLowerCase();
				//alert(objParam)
				this[variableName] = {
					objMarkup:objMarkup || variableName,
					embMarkup:embMarkup || variableName,
					objParam:!!objParam
				};
				//alert(arguments.callee)
				//alert(this[variableName].objParam)
				this[this[variableName].embMarkup] = 
				this[this[variableName].objMarkup] = this[variableName];
			case 2:
				//alert(variableName +' '+ (this[variableName] == undefined ))
				//alert(variableName+'  '+this[variableName].objParam)
				if(this[variableName] == undefined || this[variableName].objParam == undefined){
					arguments.callee.call(this, variableName, defaultValue, null, null);
					break;
				}
				this[variableName].toString = function(){return defaultValue;};
				break;
			default:
				//
				//
				break;
		}
	}catch(e){
		alert(e.message);
	}
	
	//alert(variableName+'  '+$$.htmlOptions[variableName].objMarkup);
};



/**
 *
 * @name flash.pluginOptions
 * @desc The default set of options for checking/updating the flash Plugin.
 *
**/
$$.pluginOptions = {
	expressInstall: false,
	update: true,
	version: '10.0.0'
};
/**
 *
 * @name flash.replace
 * @desc The default method for replacing an element with a Flash movie.
 *
**/
$$.replace = function(htmlOptions) {
	//this.innerHTML = '<div class="alt">'+this.innerHTML+'</div>';
	
	
	
	//alert(jQuery(this).context.nodeName)
	//this.flashObject = new FlashObject();
	//jQuery(this).attr('flashObject', new FlashObject());
	try{
	var dd = new $$.htmlOptions;
	//alert(dd.htmlOptionsVariable)
	}catch(e){
		alert(e.message);
	}//alert(jQuery(this).context.id)
	//alert(htmlOptions.objectNode())
	$(this)
		.addClass('flash-replaced')
		.prepend(htmlOptions.objectNode());
};
/**
 *
 * @name flash.update
 * @desc The default method for replacing an element with an update message.
 *
**/
$$.update = function(htmlOptions) {
	var url = String(location).split('?');
	url.splice(1,0,'?hasFlash=true&');
	url = url.join('');
	var msg = '<p>This content requires the Flash Player. <a href="http://www.adobe.com/go/getflashplayer">Download Flash Player</a>. Already have Flash Player? <a href="'+url+'">Click here.</a></p>';
	this.innerHTML = '<span class="alt">'+this.innerHTML+'</span>';
	$(this)
		.addClass('flash-update')
		.prepend(msg);
};


/**
 *
 * Flash Player 9 Fix (http://blog.deconcept.com/2006/07/28/swfobject-143-released/)
 *
**/
if (window.attachEvent) {
	window.attachEvent("onbeforeunload", function(){
		__flash_unloadHandler = function() {};
		__flash_savedUnloadHandler = function() {};
	});
}
	
})(jQuery);






