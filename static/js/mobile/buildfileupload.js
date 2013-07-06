jQuery.extend({
	buildfileupload: function(s) {
		try {
			var reader = new FileReader();
			var canvaszoom = false;
			if(1 || (s.maxfilesize && s.files[0].size > s.maxfilesize * 1024)) {
				canvaszoom = true;
			}

			var picupload = function(picdata) {

				if(!XMLHttpRequest.prototype.sendAsBinary){
					XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
						function byteValue(x) {
							return x.charCodeAt(0) & 0xff;
						}
						var ords = Array.prototype.map.call(datastr, byteValue);
						var ui8a = new Uint8Array(ords);
						this.send(ui8a.buffer);
					}
				}

				var xhr = new XMLHttpRequest(),
					file = s.files[0],
					index = 0,
					start_time = new Date().getTime(),
					boundary = '------multipartformboundary' + (new Date).getTime(),
					builder;

				builder = jQuery.getbuilder(s, file.name, picdata, boundary);

				if(s.uploadpercent) {
					xhr.upload.onprogress = function(e) {
						if(e.lengthComputable) {
							var percent = Math.ceil((e.loaded / e.total) * 100);
							$('#' + s.uploadpercent).html(percent + '%');
						}
					};
				}

				xhr.open("POST", s.uploadurl, true);
				xhr.setRequestHeader('content-type', 'multipart/form-data; boundary='
					+ boundary);

				xhr.sendAsBinary(builder);

				xhr.onerror = function() {
					s.error();
				};
				xhr.onabort = function() {
					s.error();
				};
				xhr.ontimeout = function() {
					s.error();
				};
				xhr.onload = function() {
					if(xhr.responseText) {
						s.success(xhr.responseText);
					}
				};
			};

			var detectsubsampling = function(img, imgwidth, imgheight) {
				if(imgheight * imgwidth > 1024 * 1024) {
					var tmpcanvas = document.createElement('canvas');
					tmpcanvas.width = tmpcanvas.height = 1;
					var tmpctx = tmpcanvas.getContext('2d');
					ctx.drawImage(img, -imgwidth + 1, 0);
					return ctx.getImageData(0, 0, 1, 1).data[3] === 0;
				} else {
					return false;
				}
			};
			var detectverticalsquash = function(img, imgheight) {
				var tmpcanvas = document.createElement('canvas');
				tmpcanvas.width = 1;
				tmpcanvas.height = imgheight;
				var tmpctx = tmpcanvas.getContext('2d');
				tmpctx.drawImage(img, 0, 0);
				var data = tmpctx.getImageData(0, 0, 1, imgheight).data;
				var sy = 0;
				var ey = imgheight;
				var py = imgheight;
				while(py > sy) {
					var alpha = data[(py - 1) * 4 + 3];
					if(alpha === 0) {
						ey = py;
					} else {
						sy = py;
					}
					py = (ey + sy) >> 1;
				}
				var ratio = py / imgheight;
				return (ratio === 0) ? 1 : ratio;
			};

			var maxheight = 500;
			var maxwidth = 500;
			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d');

			var img = document.createElement('img');
			img.onload = function() {
				$this = $(this);
				var imgwidth = $this.width();
				var imgheight = $this.height();

				var canvaswidth = maxwidth;
				var canvasheight = maxheight;
				var newwidth = imgwidth;
				var newheight = imgheight;
				if(imgwidth/imgheight <= canvaswidth/canvasheight && imgheight >= canvasheight) {
					newheight = canvasheight;
					newwidth = Math.ceil(canvasheight/imgheight*imgwidth);
				} else if(imgwidth/imgheight > canvaswidth/canvasheight && imgwidth >= canvaswidth) {
					newwidth = canvaswidth;
					newheight = Math.ceil(canvaswidth/imgwidth*imgheight);
				}

				ctx.save();
				if(detectsubsampling(this, imgwidth, imgheight)) {
					imgheight = imgheight / 2;
					imgwidth = imgwidth / 2;
				}
				var vertsquashratio = detectverticalsquash(this, imgheight);

				canvas.height = newheight;
				canvas.width = newwidth;
				ctx.drawImage(this, 0, 0, imgwidth, imgheight, 0, 0, newwidth, newheight/vertsquashratio);
				ctx.restore();

				var newdataurl = canvas.toDataURL(s.files[0].type).replace(/data:.+;base64,/, '');

				if(typeof atob == 'function') {
					picupload(atob(newdataurl));
				} else {
					picupload(jQuery.base64decode(newdataurl));
				}
			};
			$('body').append(img);
			$(img).css('display', 'none');

			reader.index = 0;
			reader.onloadend = function(e) {
				if(canvaszoom) {
					img.src = e.target.result;
				} else {
					picupload(e.target.result);
				}
				return;
			};
			if(canvaszoom) {
				reader.readAsDataURL(s.files[0]);
			} else {
				reader.readAsBinaryString(s.files[0]);
			}
		} catch(err) {
			return s.error();
		}
		return;
    },
	getbuilder: function(s, filename, filedata, boundary) {
		var dashdash = '--',
			crlf = '\r\n',
			builder = '';

		for(var i in s.uploadformdata) {
			builder += dashdash;
			builder += boundary;
			builder += crlf;
			builder += 'Content-Disposition: form-data; name="' + i + '"';
			builder += crlf;
			builder += crlf;
			builder += s.uploadformdata[i];
			builder += crlf;
		}

		builder += dashdash;
		builder += boundary;
		builder += crlf;
		builder += 'Content-Disposition: form-data; name="' + s.uploadinputname + '"';
		builder += '; filename="' + filename + '"';
		builder += crlf;

		builder += 'Content-Type: application/octet-stream';
		builder += crlf;
		builder += crlf;

		builder += filedata;
		builder += crlf;

		builder += dashdash;
		builder += boundary;
		builder += dashdash;
		builder += crlf;
		return builder;
	}
});

jQuery.extend({
	base64encode: function(input) {
		var output = '';
		var chr1, chr2, chr3 = '';
		var enc1, enc2, enc3, enc4 = '';
		var i = 0;
		do {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)){
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)){
				enc4 = 64;
			}
			output = output+this._keys.charAt(enc1)+this._keys.charAt(enc2)+this._keys.charAt(enc3)+this._keys.charAt(enc4);
			chr1 = chr2 = chr3 = '';
			enc1 = enc2 = enc3 = enc4 = '';
		} while (i < input.length);
		return output;
	},
	base64decode: function(input) {
		var output = '';
		var chr1, chr2, chr3 = '';
		var enc1, enc2, enc3, enc4 = '';
		var i = 0;
		if (input.length%4!=0){
			return '';
		}
		var base64test = /[^A-Za-z0-9\+\/\=]/g;
		if (base64test.exec(input)){
			return '';
		}
		do {
			enc1 = this._keys.indexOf(input.charAt(i++));
			enc2 = this._keys.indexOf(input.charAt(i++));
			enc3 = this._keys.indexOf(input.charAt(i++));
			enc4 = this._keys.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64){
				output+=String.fromCharCode(chr2);
			}
			if (enc4 != 64){
				output+=String.fromCharCode(chr3);
			}
			chr1 = chr2 = chr3 = '';
			enc1 = enc2 = enc3 = enc4 = '';
		} while (i < input.length);
		return output;
	},
	_keys: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
});