(function(NS, CFG){

/**
 * @class XWBcontrol
 */

NS = window[NS] = {};

/**
 * @class XWBcontrol.util
 * Core API
 */
    var document = window.document,
        String = window.String,
        view = document.defaultView,
        ua = navigator.userAgent.toLowerCase(),
        undefined,
        trimReg = new RegExp("(?:^\\s*)|(?:\\s*$)", "g"),
        strict = document.compatMode === "CSS1Compat",
        opera = ua.indexOf("opera") > -1,
        safari = (/webkit|khtml/).test(ua),
        ie = !opera && ua.indexOf("msie") > -1,
        gecko = !safari && ua.indexOf("gecko") > -1;

/**
 * @class XWBcontrol.util
 */

NS.util = {
            /**@type Boolean*/
            opera : opera,
            /**@type Boolean*/
            ie : ie,
            /**@type Boolean*/
            safari : safari,
            /**@type Boolean*/
            gecko:gecko,
            /**@type Boolean*/
            strict:strict,

            /**
             * 根据结点ID值返回该DOM结点.
             * 该遍历为广度优先
             * 如果只有一个参数,返回id相同的结点(只一个).
             * @param {String|DOMElement} id 结点ID,直接一个DOM也没关系
             * @param {DOMElement} [ancestorNode] 父结点,如果该值指定,将在该结点下查找
             * @return {DOMElement} 对应ID的结点,如果不存在返回null
             * @member XWBcontrol.util
             * @method $
             */
            $: function (a, b) {
               var iss = typeof a === "string";

               if (iss && !b)
                    return document.getElementById(a);

               if (!iss)
                    return a;

               if (b.id == a)
                    return b;

               var child = b.firstChild,
                   tmp = [];
               while (child) {
                  if (child.id == a) {
                     return child;
                  }
                  //
                  if (child.firstChild)
                    tmp.push(child);
                  child = child.nextSibling;
                  if (!child) {
                     child = tmp.pop();
                     if (child)
                        child = child.firstChild;
                  }
               }
               return null;
            },
            /**
             * @param {Object} target 目标对象,可为空
             * @param {Object} source 源对象
             * @param {Boolean} [override]
             * @return {Object} target
             */
            extend : function(target, src, override){
              if(!target)
                target = {};
              if(src){
                for(var i in src)
                    if(!target[i] || override)
                        target[i] = src[i];
              }
              return target;
            },

            /*
             * 设置结点风格，key必须符合JS属性名称语法,该方法目前未支持设置透明度.
             */
            setStyle : function (el, key, value) {
                // TODO:this.setOpacity(value)
                var st = el.style;
                st[
                    key === 'float' || key === 'cssFloat' ? ( st.styleFloat === undefined ? ( 'cssFloat' ) : ( 'styleFloat' ) ) : key
                ] = value;
                return this;
            },


            /*获得view结点风格,,该方法目前未支持获得透明度.<br>
             * getStyle('position');<br>
             */
            getStyle : function(){
                return view && view.getComputedStyle ?
                    function(el, prop){
                        var v, cs;
                        if(prop == 'float')
                            prop = "cssFloat";

                        if(v = el.style[prop])
                            return v;
                        if(cs = view.getComputedStyle(el, ""))
                            return cs[camel];
                        return null;
                    } :
                    function(el, prop){
                        var v, cs;
                        if(prop == 'float')
                            prop = "styleFloat";

                        if(v = el.style[prop])
                            return v;

                        if(cs = el.currentStyle)
                            return cs[prop];
                        return null;
                    };
            }(),

            /**
             * @param {HTMLElement} element
             * @return {left:scrollLeft, top:scrollTop}
             */
            getScroll : function(d){
                var doc = document;
                if(d == doc || d == doc.body){
                    var l, t;
                    if(this.ie && this.strict){
                        l = doc.documentElement.scrollLeft || (doc.body.scrollLeft || 0);
                        t = doc.documentElement.scrollTop || (doc.body.scrollTop || 0);
                    }else{
                        l = window.pageXOffset || (doc.body.scrollLeft || 0);
                        t = window.pageYOffset || (doc.body.scrollTop || 0);
                    }
                    return {left: l, top: t};
                }else{
                    return {left: d.scrollLeft, top: d.scrollTop};
                }
            },
            /**
             * @param {String} string
             * @return {String}
             */
            trim : function(s){
                return s.replace(trimReg, "");
            },
            /**
             * @param {HTMLElement} element
             * @param {String} css
             */
            addClass: function(o, s) {
                var ss = this.trim(o.className.replace(s, ''));
                ss += ' ' + s;
                o.className = ss;
            }
            ,
            /**
             * @param {HTMLElement} element
             * @param {String} css
             */
            addClassIf: function(o, s) {
                if(!this.hasClass(o,s)){
                    var ss = this.trim(o.className.replace(s, ''));
                    ss += ' ' + s;
                    o.className = ss;
                }
            },
            /**
             * @param {HTMLElement} element
             * @param {String} css
             */
            delClass: function(o, s) {
                o.className = this.trim(o.className.replace(s, ""));
            },
            /**
             * @param {HTMLElement} element
             * @param {String} css
             */
            hasClass : function(o, s) {
                return s && (' ' + o.className + ' ').indexOf(' ' + s + ' ') != -1;
            },

            /**
             * @param {String} html
             * @return {String}
             */
            escapeHtml : function(html) {
            	if( null == html || '' == html ){
            		return '';
            	}else{
            		return html.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            	}
        	},

        /**
         * 得到相对页面x,y坐标值.
         * @return {Array} [x, y]
         */
            absoluteXY: function(el) {
                    var p, b, scroll, bd = (document.body || document.documentElement);

                    if(el == bd )
                        return [0, 0];

                    if (el.getBoundingClientRect) {
                        b = el.getBoundingClientRect();
                        scroll = this.getScroll(document);
                        return [b.left + scroll.left, b.top + scroll.top];
                    }

                    var x = 0, y = 0;

                    p = el;

                    var hasAbsolute = this.getStyle(el, "position") == "absolute";

                    while (p) {

                        x += p.offsetLeft;
                        y += p.offsetTop;
                        //f.view = p;
                        if (!hasAbsolute && this.getStyle(p, "position") == "absolute") {
                            hasAbsolute = true;
                        }

                        if (this.gecko) {
                            var bt = parseInt(this.getStyle(p, "borderTopWidth"), 10) || 0;
                            var bl = parseInt(this.getStyle(p, "borderLeftWidth"), 10) || 0;
                            x += bl;
                            y += bt;
                            if (p != el && this.getStyle(p, 'overflow') != 'visible') {
                                x += bl;
                                y += bt;
                            }
                        }
                        p = p.offsetParent;
                    }

                    if (this.safari && hasAbsolute) {
                        x -= bd.offsetLeft;
                        y -= bd.offsetTop;
                    }

                    if (this.gecko && !hasAbsolute) {
                        x += parseInt(this.getStyle(bd, "borderLeftWidth"), 10) || 0;
                        y += parseInt(this.getStyle(bd, "borderTopWidth"), 10) || 0;
                    }

                    p = el.parentNode;
                    while (p && p != bd) {
                        if (!this.opera || (p.tagName != 'TR' && this.getStyle(p, "display") != "inline")) {
                            x -= p.scrollLeft;
                            y -= p.scrollTop;
                        }
                        p = p.parentNode;
                    }

                    return [x, y];
            },

        /**
         * 发起一个ajax请求.
         * @param {String} url
         * @param {Object} param
         */
            connect : function(url, param){
                var ajax;

                if (window.XMLHttpRequest) {
                    ajax = new XMLHttpRequest();
                } else {
                    if (window.ActiveXObject) {
                        try {
                            ajax = new ActiveXObject("Msxml2.XMLHTTP");
                        } catch (e) {
                            try {
                                ajax = new ActiveXObject("Microsoft.XMLHTTP");
                            } catch (e) { }
                            }
                        }
                }


                if(ajax){
                    param.method = param.method ? param.method.toUpperCase() : 'GET';
                    ajax.open(param.method, url, true);
                    if (param.method === 'POST')
                        ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset='+(param.encoding?param.encoding:''));
                    ajax.onreadystatechange = function(){
                        if (ajax.readyState === 4) {
                            var ok = (ajax.status === 200);
                            if(ok && param.success){
                                try{
                                    var data = (!param.dt || param.dt === 'json') ? eval("("+ajax.responseText+");") : ajax.responseText;
                                }catch(e){
                                    ok = false;
                                }
                                param.success.call(param.scope||this, data);
                            }

                            if(!ok && param.failure){
                                param.failure.call(param.scope||this, ajax.responseText);
                            }
                        }
                    };
                    
                    if("POST" === param.method){
                    	ajax.send(param.data);
                    }else{
                    	ajax.send();
                    }
                }
            },

            jsonp : function(url, param){
                var fn = 'jsonp_' + (+new Date()),
                    doc = param.doc || document,
                    script = doc.createElement('script'),
                    hd = doc.getElementsByTagName("head")[0],
                    success;

                if(typeof param == 'function'){
                    success = param;
                    param = {};
                }else success = param.success;


                script.type = 'text/javascript';
                param.charset && (script.charset = param.charset);
                param.deffer  && (script.deffer  = param.deffer);

                url = url + ( url.indexOf('?')>=0 ? '&jsonp='+fn : '?jsonp='+fn);

                script.src = url;

                var cleaned = false;

                function clean(){
                    if(!cleaned){
                        try {
                            delete window[fn];
                            script.parentNode.removeChild(script);
                            script = null;
                        }catch(e){}
                        cleaned = true;
                    }
                }

                window[fn] = function(){
                    clean();
                    if(success)
                      success.apply(param.scope||this, arguments);
                };

                script.onreadystatechange = script.onload = function(){
                    var rs = this.readyState;
                    //
                    if( !cleaned && (!rs || rs === 'loaded' || rs === 'complete') ){
                        clean();
                        if(param.failure)
                            param.failure.call(param.scope||this);
                    }
                };

                hd.appendChild(script);
            },

       /**
       * 获得文档内容区域高度.
       * @return {Number}
       */
       getDocumentHeight: function() {
           return Math.max(
             !strict ? document.body.scrollHeight : document.documentElement.scrollHeight ,
             this.getViewportHeight()
           );
       },

       /**
       * 获得文档内容区域宽度.
       * @return {Number}
       */
       getDocumentWidth: function() {
           return Math.max(
             !strict ? document.body.scrollWidth : document.documentElement.scrollWidth,
             this.getViewportWidth()
           );
       },

       /**
       * 获得视图可见区域域高度.
       * @return {Number}
       */
       getViewportHeight: function(){
         return ie ? strict ? document.documentElement.clientHeight : document.body.clientHeight :
                self.innerHeight;
       },

       /**
       * 获得视图可见区域域宽度.
       * @return {Number}
       */
       getViewportWidth: function() {
          return ie ? strict ? document.documentElement.clientWidth : document.body.clientWidth :
                 self.innerWidth;
       },

       /***/
       getViewport : function(){
         return {width:this.getViewportWidth(), height:this.getViewportHeight()};
       },

   /**
    * 应用一段CSS样式文本.
    * <pre><code>
      loadStyle('customCS', '.g-custom {background-color:#DDD;}');
      //在元素中应用新增样式类
      &lt;div class=&quot;g-custom&quot;&gt;动态加载样式&lt;/div&gt;
      </code></pre>
    * @param {String} id 生成的样式style结点ID\
    * @param {String} 样式文本内容
    */
       loadStyle: function(ss) {
         var styleEl = this._styleEl;
         if(!styleEl){
           styleEl = this._styleEl = document.createElement("style");
           styleEl.type = 'text/css';
           document.getElementsByTagName('head')[0].appendChild(styleEl);
         }
         styleEl.styleSheet && (styleEl.styleSheet.cssText += ss) || styleEl.appendChild(document.createTextNode(ss));
         return styleEl;
       },

       /**
        * 添加元素事件监听
        * @param {HTMLElement} element
        * @param {String} event
        * @param {Function} handler
        * @param {Boolean} [remove] 是否移除事件监听
        */
       domEvent : function(el, evt, handler, remove){
         if(!remove){
           if (el.addEventListener) {
             el.addEventListener(evt, handler, false);
           } else if (el.attachEvent) {
             el.attachEvent('on' + evt, handler);
           }
         }else {
           if (el.removeEventListener) {
             el.removeEventListener(evt, handler, false);
           } else if (el.detachEvent) {
             el.detachEvent('on' + evt, handler);
           }
         }
         return this;
       },
       /***/
       stopEvent : function(ev){
         if (ev.preventDefault)
             ev.preventDefault();

         if(ev.stopPropagation)
             ev.stopPropagation();

         if(ie){
             ev.returnValue = false;
             ev.cancelBubble = true;
         }
       },

       /**
        * @param {String} templateString
        * @param {Object} dataMap
        * @param {Boolean} [urlencode] encodeURIComponent for value
        example:
        <pre>
            <code>
                var name = templ('My name is {name}', {name:'xweibo'});
                var url  = templ('http://www.server.com/getName?name={name}', {name:'微博'}, true);
            </code>
        </pre>
        */
       templ : function(str, map, urlencode){
            return str.replace(/\{([\w_$]+)\}/g, function(s, s1){
                var v = map[s1];
                if(v === undefined || v === null)
                    return '';
                return urlencode?encodeURIComponent(v) : v;
            });
       },

       /**
        * 根据html模板返回HTML结点
        * @param {String} htmls
        * @param {Object} map
        * @param {Boolean} [urlencode]
        example:
        <pre>
            <code>
                var iframeElement = forNode(
                  '&lt;{tag} class="{cls}" frameBorder="no" scrolling="auto" hideFocus=""></iframe&gt;',
                  {tag:'iframe', cls:'ui-frame'}
                );
            </code>
        </pre>
        */
       forNode : function(htmls, map, urlencode){
         if(map)
            htmls = this.templ(htmls, map, urlencode);

         var div = document.createElement('DIV');
         div.innerHTML = htmls;
         var nd = div.removeChild(div.firstChild);
         div = null;
         return nd;
       },
       /***/
       arrayRemove : function(arr, idx){
         arr.splice(idx, 1)[0];
       }
};

/**
 * @namespace XWBcontrol.ui
 */
NS.ui = {};

/**
 * @class XWBcontrol.ui.FloatTip
 * 浮动面板类。<br>
  example :
  <pre><code>
    &lt;img title="已绑定新浪微博"
         src="icon_on.gif"
         onmouseover="XWBcontrol.TipPanel.showLayer(this, '11014');"
         onmouseout="XWBcontrol.TipPanel.setHideTimer();"&gt;</code>
 </pre>
 * @abstract
 */

NS.ui.FloatTip = function(cfg){
    for(var i in cfg) this[i] = cfg[i];
};

NS.ui.FloatTip.prototype = {
/**
 * @cfg {Number} timeoutHide defaults to 500 ms
 */
    timeoutHide : 500,
/**
 * @cfg {Number} offX 面板定位时往瞄点X方向的偏移增量，默认25
 */
    offX : 25,
/**
 * @cfg {Number} offX 面板定位时往瞄点Y方向的偏移增量，默认-10
 */
    offY : -10,

/**
 * @cfg {Function} beforeShow
 */

/**
 * @cfg {Function} afterShow
 */

/**
 * @cfg {Function} createView 这是个抽象方法,必须实现,以生成html结点
 */

/**
 * @cfg {String} focus 窗口显示时聚焦到指定ID的按钮
 */

/**
 * 清除超时隐藏
 */
    clearHideTimer : function(){
        if(this.hideTimerId){
            clearTimeout(this.hideTimerId);
            this.hideTimerId = false;
        }
    },

/**
 * 开始超时隐藏,在指定时间内隐藏
 */
    setHideTimer : function(){
        this.clearHideTimer();
        this.hideTimerId = setTimeout(this._getHideTimerCall(), this.timeoutHide);
    },

/**
 * 显示浮层,浮层每次显示前会先清除计时.
 * @param {HTMLElement|Array} anchorElement element OR [x, y]
 * @param {Object} [extra] 其它信息，可在显示前或后通过beforeShow, afterShow等方法应用这些信息。
 */
    showLayer : function(anchor, param){
        this.clearHideTimer();
        var layer = this.getLayer();

        if(!this.beforeShow || this.beforeShow(anchor, param) !== false){
            if(anchor){
                if(anchor.tagName){
                    anchor = NS.util.absoluteXY(anchor);
                    anchor[0] += this.offX;
                    anchor[1] += this.offY;
                }
                layer.style.left = anchor[0] + 'px';
                layer.style.top  = anchor[1] + 'px';
            }
            layer.style.display = '';

            if(this.afterShow)
                this.afterShow();
        }
    },

/**
 * 立即隐藏层
 */
    hideLayer : function(){
        this.getLayer().style.display = 'none';
    },



/**
 * 获得层结点,未创建时创建并初始化相关事件.
 */
    getLayer : function(){
        var layer = this.layer;
        if(!layer){
            var self = this;
            layer = this.layer = this.createView();
            layer.onmouseover = function(){
                self.clearHideTimer();
            };
            layer.onmouseout  = function(){
                self.setHideTimer();
            };
        }
        return layer;
    },

    _onTimerHide : function(){
        this.hideLayer();
    },

    _getHideTimerCall : function(){
        if(!this._onHideTimer){
            var self = this;
            this._onHideTimer = function(){
                self._onTimerHide();
                self.clearHideTimer();
            };
        }
        return this._onHideTimer;
    }
};

/**
 * @class XWBcontrol.ui.Popup
 example:<br>
 <pre><code>
        new Popup({
            title  : '用户注册',
            width  : 400,
            height : 360,
            contentType:'iframe',
            url    : 'http://www.google.cn',
            onclose : function (){
              if(notClose)
                return false;
              alert('close me!');
            },
            onContentLoad : function(){
                alert('iframe is loaded!');
            },
            onok : function(){alert('ok clicked!');},
            buttons:['ok','cancel']
	   });
 </code></pre>
 */
NS.ui.Popup = function (opt){
  if(opt){
    for(var k in opt)
      this[k] = opt[k];
  }

  if(this.title){
    this.setTitle(this.title);
  }

  if(this.width && this.height){
    var w = this.width, h = this.height;
    this.setSize(w, h);
    delete this.width;
    delete this.height;
  }

  if(this.left && this.top){
    var l = this.left, t = this.top;
    this.setXY(l, t);
    delete this.left;
    delete this.top;
  }

  if(this.overflow){
    this.getView();
    this.ctEl.style.overflow = this.overflow;
    delete this.overflow;
  }


  if(this.url){
    var ct =this.contentType;
    delete this.contentType;
    this.setUrl(this.url, ct);
  }

  if(!this.hidden){
    delete this.hidden;
    this.display(true);
  }

  if(this.html){
      // for fast rendering, fix some ie rending bug
      this.setHtml(this.html, true);
      delete this.html;
  }
};

var currentZ = 99998;

NS.ui.Popup.prototype = {

/**
 * @cfg {Function} onContentLoad 设置内容加载完成后的回调方法.
 */
   onContentLoad : false,

/**
 * @property hidden
 * 当前窗口状态,开始时为隐藏状态
 * @type Boolean
 */
    hidden : true,

/**
 * @cfg {Boolean} destoryOnClose 关闭窗口时是否销毁，默认直接销毁。
 */
    destoryOnClose : true,

/**
 * @cfg {Boolean} autoCenter 是否自动居中,默认true
 */
    autoCenter : true,

/**
 * @cfg {Boolean} autoH 是否自动适应内容高度,对内容为IFRAME的窗口无效,默认false
 */
/**
 * @cfg {Boolean} mask 设置为false时不应用遮罩层。
 */
/**
 * @cfg {Boolean} trackZ 是否自动更新窗口zIndex,默认为true
 */
    trackZ : true,
/**
 * @cfg {String} contentType 设置内容类型，可选有html, iframe,默认为html
 */
    contentType : 'html',

/**
 * @cfg {Function} handler 当存在buttons时，button点击后的回调方法，传递按钮id作为参数。
 */

/**
 * 显示或隐藏窗口。
 * @param {Boolean} show
 */
    display : function(b){

      if((!this.hidden) !== b){
        var v = this.getView(), self = this;
        if(b) {
          if(this.autoCenter)
             v.style.visibility = 'hidden';

          v.style.display = 'block';

          if(this.autoCenter){
             this.center();
             v.style.visibility = '';
          }
          this.trackZIndex();

          // capture focus on button
          if(this.focus){
            NS.util.$('_xwb_btn_' + this.focus, this.view).focus();
          }

        } else {
          v.style.display = 'none';
        }

        this.domEvent(window, 'resize', function(){self._onWinResized();}, !b);


        this.hidden = !b;
        if(this.mask !== false)
            this._applyMask(b);
      }
    },

/**
 * 窗口居中。
 * @return this
 */
    center : function(){
      var sz  = NS.util.getViewport(),
          dsz = this.getSize(),
          off = (sz.height - dsz.height) / 2 | 0;
      this.setXY( Math.max((((sz.width - dsz.width) / 2) | 0), 0), Math.max(off - off/2|0, 0)+NS.util.getScroll(document).top);
      return this;
    },
/**
 * 自适应内容高度(对内容为iframe的无效).
 */
    autoHeight : function(autoW){
        var pre = this.view.style.visibility;
        this.view.style.visibility = 'hidden';
        var ct = this.ctEl;
        // clip
        ct.style.overflow = 'hidden';
        if(autoW)
          ct.style.width  = '1px';
        ct.style.height = '1px';
        var self = this;

        setTimeout(function(){
          var wr = self._getWrapperInsets();
          self.setSize(autoW?parseInt(ct.scrollWidth, 10) + wr[0] : self.width, parseInt(ct.scrollHeight, 10) + wr[0]);
          self.view.style.visibility = pre;
        }, 0);

    },

/**
 * 设置长宽.
 * @param {Number} width
 * @param {Number} height
 * @return this
 */
    setSize : function(w, h){
      var st = this.getView().style;
      if(typeof w === 'number')
        st.width = w + 'px';
      if(typeof h === 'number')
        st.height = h + 'px';
      this._onBoxResized(w, h);
      return this;
    },

/**
 * 获得宽高 {width:width, height:height}
 */
    getSize : function(){
      return {width : parseInt(this.getView().style.width) || 0, height : parseInt(this.getView().style.height)||0};
    },

/**
 * 获得内容容器宽高.
 */
    getCtSize : function(){
      var sz = this.getSize(),
          insets = this._getWrapperInsets();
      sz.width  -= insets[1];
      sz.height -= insets[0];
      return sz;
    },

/**
 * 设置窗口位置。
 * @param {Number} x
 * @param {Number} y
 * @return this
 */
    setXY : function(x, y){
      var v = this.getView();
      v.style.left = x + 'px';
      v.style.top  = y + 'px';
      return this;
    },

/**
 * @param {String} title
 * @return this
 */
    setTitle : function(title){
      this.getView();
      this.titleEl.innerHTML = title;
      return this
    },

/**
 * 获得窗口视图DOM结点。
 * @return {HTMLElement}
 */
    getView : function(){
      var v = this.view;
      if(!v)
        v = this._createView();

      return v;
    },

/**
 * 获得iframe dom结点
 * @return {HTMLElement}
 */
    getFrameEl : function(){
      var f = this.frameEl;
      if(!f)
        f = this.frameEl = this._createFrame();

      return f;
    },

/**
 * 获得iframe的window.document结点
 * @return {DocumentObjectOfIFrame}
 */
    getFrameDoc : function(){
      var f = this.getFrameEl();
      return f.contentWindow ? f.contentWindow.document:f.contentDocument;
    },

/**
 * 获得iframe页面中的html元素.
 * @param {String} childId
 * @return {HTMLElementInIFrame}
 */
    getChild : function(id){
      switch(this.contentType){
        case 'iframe' :
          return this.getFrameDoc().getElementById(id);
        default :
          return NS.util.$(id, this.ctEl);
      }
    },

/**
 * 设置窗口iframe的src
 * @param {String} src
 * @return this
 */
    setSrc : function(src, title){
      this.setUrl(src, 'iframe');
      if(title !== undefined)
        this.setTitle(title);
      return this;
    },

/**
 * 设置加载内容的url,设置后将立即更新内容。
 * @TODO:预留以后可ajax加载等等
 */
    setUrl : function(url, ctype){
      this.setContentType(ctype);
      switch(ctype){
        case 'iframe':
          this.getFrameEl().src = url;
          this._onFrameOpen();
          break;
      }
      this.url = url;
      return this;
    },

/**
 * 设置窗口内容
 * @param {String} html
 * @return this
 */
    setHtml : function(html, inner){
      this.getView();
      this.setContentType('html');
      if(!inner)
        this.ctEl.innerHTML = html;

      this.onContentLoad && this.onContentLoad();

      if(this.autoH)
        this.autoHeight();
    },

/**
 * 设置窗口内容类型,iframe或html
 */
    setContentType : function(type){
      if(this.contentType !== type) {
        // some clean
        if(this.frameEl && this.frameEl.parentNode == this.ctEl)
          this.ctEl.removeChild(this.frameEl);

        switch(type){
          case 'iframe':
            var f = this.getFrameEl();
            this.ctEl.innerHTML = '';
            this.ctEl.appendChild(f);
            //
            break;
          case 'html' :
            break;
        }
        this.contentType = type;
      }
    },

/**
 * 手动更新窗口zindex，默认是自动更新。
 * 当同时显示多个窗口时,调用该方法可使窗口置顶。
 */
    trackZIndex : function(){
      if(this.z !== currentZ){
         currentZ += 2;

        if(this.maskEl)
          this.maskEl.style.zIndex = currentZ - 1;
        this.getView().style.zIndex = currentZ;
        this.z = currentZ;
      }
    },

/**
 * 监听窗口元素DOM事件.
 */
    domEvent : function(el, evt, handler, remove){

      if(typeof el === 'string')
         el = NS.util.$(el, this.getView());

      if(!this._observers)
        this._observers = [];

      if(!remove) {
        this._observers.push([el, evt, handler]);
      } else {
        for(var i=0,obs = this._observers,len=obs.length;i<len;i++){
          var item = obs[i];
          if(item[0] === el && item[1] === evt && item[2] === handler){
            NS.util.arrayRemove(obs, i);
            break;
          }
        }
      }
      NS.util.domEvent(el, evt, handler, remove);
    },


/**
 * 关闭窗口
 */
    close : function(){
      if(!this.onclose || this.onclose() !== false){
        if(this.destoryOnClose)
          this.destory();
        else this.display(false);
      }
    },

/**
 * 销毁窗口。
 */
    destory : function(){
      if(!this.hidden)
        this.display(false);
      if(this._observers){
        for(var i=0,obs = this._observers,len=obs.length;i<len;i++){
          var item = obs[i];
          NS.util.domEvent(item[0], item[1], item[2], true);
        }
        this._observers = null;
      }

      if(this.view){
        this.view.parentNode.removeChild(this.view);
        this.frameEl = null;
        this.view = null;
      }
    },

    onok : function(){
      this.close();
    },

    oncancel : function(){
      this.close();
    },

    _createView : function(){
      this._loadStyle();
      var v = this.view = NS.util.forNode([
        '<div class="xwb_ui_dlg xwb_ui_focus" style="display:none;">',
          //'<div class="ui_main_wrap">',
          '<table class="ui_boxinner"><tbody>',
            '<tr>',
              '<td class="ui_border r0d0" id="_xwb_dlg_xwn"></td>',
              '<td class="ui_border r0d1" id="_xwb_dlg_xn"></td>',
              '<td class="ui_border r0d2" id="_xwb_dlg_xne"></td>',
            '</tr>',
            '<tr>',
              '<td class="ui_border r1d0" id="_xwb_dlg_xw"></td>',
              '<td class="r1d1">',
                '<table class="xwb_ui_dlg_main"><tbody>',
                  // titlebar template
                  '<tr id="_xwb_dlg_tb"><td class="ui_title_wrap">',
                        '<div class="ui_title" id="_xwb_dlg_tbct">',
                           '<div class="ui_title_text">',
                             '<span class="ui_title_icon"></span><span id="_xwb_load_tip" class="ui_title_tip" style="display:none;">正在加载 </span><span id="_xwb_dlg_tle">提示</span>',
                           '</div>',
                           '<a class="ui_close" title="关闭" id="_xwb_dlg_cls" href="javascript:void(0)">×</a>',
                        '</div>',
                   '</td></tr>',
                  // content
                  '<tr><td class="ui_content_wrap"><div id="_xwb_dlg_wrap" class="ui_ctx">'+(this.html?this.html:'')+'</div></td></tr>',
                  // bottom
                  this.buttons ? '<tr><td class="ui_bottom_wrap"><div class="ui_bottom"><div class="ui_btns" id="_xwb_dlg_buttons"></div><div class="ui_resize"></div></div></td></tr>':'',
                 '</tbody></table>',
              '</td>',
              '<td class="ui_border r1d2" id="_xwb_dlg_xe"></td></tr>',
              // resizers
              '<tr><td class="ui_border r2d0" id="_xwb_dlg_xsw"></td><td class="ui_border r2d1" id="_xwb_dlg_xs"></td><td class="ui_border r2d2" id="_xwb_dlg_xes"></td>',
          '</tr></tbody></table></div>'
       ].join(''));

       // find nodes and bind events
       var self = this;

       this.ctEl    = NS.util.$('_xwb_dlg_wrap', v);
       this.titleEl = NS.util.$('_xwb_dlg_tle', v);

       this.domEvent(NS.util.$('_xwb_dlg_cls', v), 'click', function(e){
         e = e || window.event;
         NS.util.stopEvent(e);
         self.oncancel();
       });

       if(this.buttons){
         this.buildButtons(this.buttons);
       }

       document.body.appendChild(v);
       div = null;
       return v;
    },

    buildButtons : function(btns){
      var btnLayer = NS.util.$('_xwb_dlg_buttons', this.getView());
      btnLayer.innerHTML = '';
      for(var i=0, btns=this.buttons,len=btns.length;i<len;i++){
        var btn = btns[i];
            btn = this._createBtn(btn);
            btn.onclick = this._getBtnClickHandler();
            btnLayer.appendChild(btn);
      }

      this.buttons = btns;
    },

    _createBtn : function(cfg){
      if(typeof cfg === 'string'){
        switch(cfg){
          case 'ok' :
          cfg = {title:'确定', id:'ok'};
            break;
            case 'cancel':
            cfg = {title:'取消', id:'cancel'};
              break;
              case 'yes' :
              cfg = {title:' 是 ', id:'ok'};
              break;
              case 'no'  :
              cfg = {title:' 否 ', id:'cancel'};
              break;
        }
      }
      var tpl = cfg.html || [
      '<span',
      cfg.cls ? ' class="'+cfg.cls+'"':'',
      '><button id="'+ ( cfg.id?'_xwb_btn_'+cfg.id:'' )+'">'+(cfg.title||'')+'</button></span>'].join('');

      var nd = NS.util.forNode(tpl, cfg);

      if(cfg.id && cfg.html)
        nd.id = '_xwb_btn_' + cfg.id;

      return nd;
    },

    _getBtnClickHandler : function(){
      if(!this._btnClickHandler){
         var self = this;
         this._btnClickHandler =
          (function onBtnClick(e){
            e = e || window.event;
            var src = e.target || e.srcElement;
            if(src.id && src.id.indexOf('_xwb_btn_')<0)
                src = this;
            if(src.id){
              var code = src.id.substr('_xwb_btn_'.length);
              self._onDlgResponse(code, e);
            }
          });
      }

      return this._btnClickHandler;
    },

    _onDlgResponse : function(code, e){
       if(!this.handler || this.handler(code, e) !== false)
         this['on'+code] && this['on'+code](e);
    },

    _getWrapperInsets : function(){
      return !this.buttons ? [52,16] : [92,16];
    },

    _createFrame : function(){
      var f = NS.util.forNode('<iframe class="ui-iframe" frameBorder="no" scrolling="auto" hideFocus=""></iframe>'),
          self = this;
      var sz = this.getCtSize();
      f.style.width  = sz.width + 'px';
      f.style.height = sz.height + 'px';
      this.domEvent(f, ie?'readystatechange':'load', function(evt){
         evt = evt || window.event;
         var status = (evt.srcElement || evt.target).readyState || evt.type;
         switch(status){
           case 'loading':  //IE  has several readystate transitions
           break;
           //
           //当用户手动刷新FRAME时该事件也会发送
           //case 'interactive': //IE
           case 'load': //Gecko, Opera
           case 'complete': //IE
             if(f.src)
               self._onFrameLoad(evt);
             break;
         }
      });

      return f;
    },

    _onFrameOpen : function(){
      NS.util.$('_xwb_load_tip', this.getView()).style.display = '';
    },

    _onFrameLoad : function(e){
      NS.util.$('_xwb_load_tip', this.getView()).style.display = 'none';

      if(this.onContentLoad)
        this.onContentLoad(e);
    },

    _onBoxResized : function(w, h){
      var rects = this._getWrapperInsets();
      var h = (h - rects[0]) + 'px';
      var w = (w - rects[1]) + 'px';
      var f = this.getFrameEl();
      this.ctEl.style.width  = w;
      this.ctEl.style.height = h;

      if(this.contentType == 'iframe') {
        f.style.width = w;
        f.style.height = h;
      }

      if(this.autoCenter)
         this.center();
    },

    _loadStyle : function(){
      NS.util.loadStyle([
        //'.xwb_ui_dlg .ui_main_wrap{border:1px solid #000!important;}',
        '.xwb_ui_dlg .ui_title_wrap, .xwb_ui_dlg .ui_bottom{font-family:\'\u5FAE\u8F6F\u96C5\u9ED1\',\'Arial\'}',
        '.xwb_ui_dlg .ui_border{background-color:#000; filter:alpha(opacity=30); opacity:0.3}',
        '.xwb_ui_focus .ui_border{filter:alpha(opacity=50); opacity:0.5}',
        '.xwb_ui_dlg .ui_move .ui_border{filter:alpha(opacity=60); opacity:0.6}',
        '.xwb_ui_dlg .r0d0, .xwb_ui_dlg .r0d2, .xwb_ui_dlg .r2d0, .xwb_ui_dlg .r2d2{height:8px; width:8px}',
        '.xwb_ui_overlay{background:#CCC;opacity:0.3;filter:alpha(opacity=30);}',
        '.xwb_ui_dlg .xwb_ui_dlg_main{border:1px solid #000!important}',
        '.xwb_ui_focus .xwb_ui_dlg_main{box-shadow:0 0 3px rgba(0,0,0,0.7); moz-box-shadow:0 0 3px rgba(0,0,0,0.7); webkit-box-shadow:0 0 3px rgba(0,0,0,0.7)}',
        '.xwb_ui_focus .r2d1{box-shadow:0 5px 5px rgba(0,0,0,0.3); moz-box-shadow:0 5px 5px rgba(0,0,0,0.3); webkit-box-shadow:0 5px 5px rgba(0,0,0,0.3)}',
        '.xwb_ui_dlg .ui_move .r2d1{box-shadow:0 5px 5px rgba(0,0,0,0.5); moz-box-shadow:0 5px 5px rgba(0,0,0,0.5); webkit-box-shadow:0 5px 5px rgba(0,0,0,0.5)}',
        '.xwb_ui_dlg .xwb_ui_dlg_main{background-color:#FFF}',
        '.xwb_ui_dlg .ui_content_wrap{_width:15em; min-width:15em}',
        '.xwb_ui_dlg .ui_btns{background-color:#F6F6F6; border-bottom:1px solid #CCC; border-top:1px solid #EBEBEB; box-shadow:inset 0 -2px 2px rgba(204,204,204,0.3); moz-box-shadow:inset 0 -2px 2px rgba(204,204,204,0.3); webkit-box-shadow:inset 0 -2px 2px rgba(204,204,204,0.3)}',
        '.xwb_ui_dlg .ui_btns button{letter-spacing:2px; padding:2px 4px}',
        '.xwb_ui_dlg .ui_title{border-bottom:2px solid #EBEBEB; height:100%; position:relative}',
        '.xwb_ui_dlg .ui_title_text{background-color:#3A6EA5; border:1px solid #4E84C0; border-bottom-color:#0D1D3C; box-shadow:inset 0 -2px 2px rgba(0,0,0,0.2); color:#EBEBEB; font-weight:700; height:30px; line-height:30px; moz-box-shadow:inset 0 -2px 2px rgba(0,0,0,0.2); padding:0 30px 0 10px; text-shadow:0 1px 0 #000; webkit-box-shadow:inset 0 -2px 2px rgba(0,0,0,0.2)}',
        '.xwb_ui_dlg .ui_move .ui_title_text{color:#FFF}',
        '.xwb_ui_focus .ui_title_text{background-color:#214FA3}',
        '.xwb_ui_dlg .ui_close{border:1px solid #3A6EA5; color:#FFF!important; height:13px; line-height:13px; padding:0; right:5px; text-align:center; text-decoration:none; top:5px; width:13px}',
        '.xwb_ui_focus .ui_close{border:1px solid #214FA3}',
        '.xwb_ui_dlg .ui_close:hover{background-color:#771C35; border:1px solid #000; cursor:pointer; text-decoration:none}',
        '.xwb_ui_dlg .ui_close:active{background-color:#A80000}',
        '.xwb_ui_dlg .ui_resize{height:8px; width:8px}',
        '.xwb_ui_dlg .ui_loading_tip{color:#808080; font-size:9px}',
        '.xwb_ui_dlg .ui_title_tip {font-size:12px}',
        '.xwb_ui_dlg .ui_ctx{overflow-y:auto; position:relative}',
        '.xwb_ui_dlg .ui_title_icon, .xwb_ui_dlg .ui_content, .xwb_ui_dlg .xwb_ui_dlg_icon, .xwb_ui_dlg .ui_btns span{display:inline; display:inline-block; zoom:1}',
        '.xwb_ui_dlg{_overflow:hidden; position:absolute; text-align:left; top:0}',
        '.xwb_ui_dlg table{border:0; border-collapse:collapse; margin:0}',
        '.xwb_ui_dlg td{padding:0}',
        '.xwb_ui_dlg .ui_title_icon, .xwb_ui_dlg .xwb_ui_dlg_icon{_font-size:0; vertical-align:middle}',
        '.xwb_ui_overlay{left:0; position:absolute; top:0; width:100%; z-index:99999}',
        '.xwb_ui_dlg .ui_title_text{cursor:default; overflow:hidden}',
        '.xwb_ui_dlg .ui_close{display:block; outline:none; position:absolute}',
        '.xwb_ui_dlg .ui_content{margin:10px}',
        '.xwb_ui_dlg .ui_content.ui_iframe{display:block; height:100%; margin:0; padding:0; position:relative}',
        '.xwb_ui_dlg .ui_iframe{border:none; height:100%; overflow:auto; width:100%}',
        '.xwb_ui_dlg .ui_bottom{position:relative}',
        '.xwb_ui_dlg .ui_resize{_font-size:0; bottom:0; cursor:nw-resize; position:absolute; right:0; z-index:1}',
        '.xwb_ui_dlg .ui_btns{text-align:right; white-space:nowrap}',
        '.xwb_ui_dlg .ui_btns span{margin:5px 10px 5px 5px;}',
        '.xwb_ui_dlg .ui_btns button{cursor:pointer}',
        '* .xwb_ui_dlg .ui_ie6_select_mask{height:99999em; left:0; position:absolute; top:0; width:99999em; z-index:-1}'
        // ie6 && strict ? '.xwb_ui_dlg .ui_main_wrap {border:none !important;}':''
        ].join('')
      , null, 'css_artdlg');
    },

    _applyMask : function(b){
      var mask = this.maskEl;
      if(!mask)
        mask = this.maskEl = NS.util.forNode('<div class="xwb_ui_overlay"></div>');

      if(b){
        mask.style.height  = NS.util.getDocumentHeight()  + 'px';
        document.body.appendChild(mask);
      }else {
        document.body.removeChild(mask);
      }
    },

    _onWinResized : function(){
      var mask = this.maskEl;
      if(mask && mask.parentNode === document.body)
        mask.style.height  = NS.util.getDocumentHeight()  + 'px';

      if(this.autoCenter)
        this.center();
    }
};





////////////////////////////////////////////////
///   应用入口 , 请在每个函数,成员变量前写上注释
////////////////////////////////////////////////
/**
 * @class XWBcontrol
 * XWBcontrol只放全局可用的方法,与具体页面无关,页面相关的API请创建相关页面对象(如profile),放到相关对象里。<br>
 * 应用入口 , 请在每个公开函数,成员变量前写上JS-DOC注释，在private变量写上//注释.<br>
 */
NS.util.extend(NS, {

		isEndReg:false,

		win	: {},

		openReg : function (){
			var me = this;
			this.win['reg'] = new NS.ui.Popup({
					title  : '用户注册',
					width  : 400,
					height : 480,
					hidden : false,
					contentType:'iframe',
					url    : CFG.regUrl,
					onclose : function (){me.ck.del('xwb_tips_type');window.top.location='index.php';}
				});
		},

        /**
		 * 为DX设定横版提示浮层
		 */
        openReg4dx : function (){
			var me = this;
			this.win['reg'] = new NS.ui.Popup({
					title  : '用户登录',
					width  : 600,
					height : 345,
					hidden : false,
					contentType:'iframe',
					url    : CFG.regUrl,
					onclose : function (){me.ck.del('xwb_tips_type');window.top.location='index.php';}
				});
		},

		/**
		 * 打开设置"新浪微博签名"对话框
		 */
		openSigner : function (){
			//未绑定用户
			if (CFG.sina_uid==''){
				this.bind();
				return;
			}
			var me = this;
			this.win['signer'] = new NS.ui.Popup({
					title  : '使用新浪微博签名',
					width  : 550,
					height : 360,
					hidden : false,
					contentType:'iframe',
					url    : CFG.signerUrl
		    });
		},

		alert : function (p){
			if (Object.prototype.toString.call(p) == '[object String]') {
				p = {'msg':p};
			}
			if (p.msg == undefined) {
				alert('请设置"msg"属性');
			}
			this.win['alert'] = new NS.ui.Popup({
					title  : p.title || '提示',
					width  : p.width || 400,
					height : p.height || 350,
					hidden : p.hidden == undefined ? false :true,
					html    : '<div>' + p.msg + '</div>',
					onclose : p.onclose || false,
					buttons:['ok']
				});
		},

		confirm : function (p){
			if (Object.prototype.toString.call(p) == '[object String]') {
				p = {'msg':p};
			}
			if (p.msg == undefined) {
				alert('请设置"msg"属性');
			}
			this.win['confirm'] = new NS.ui.Popup({
					title  : p.title || '提示',
					width  : p.width || 250,
					height : p.height || 150,
					hidden : p.hidden == undefined ? false :true,
					html    : '<div class="tips-text">' + p.msg + '</div>',
					onclose : p.onclose || false,
					onok : p.onok || false,
					buttons:['ok','cancel']
				});
		},

		hasBind : function (){
			this.win['hasBind'] = new NS.ui.Popup({
					title  : '当前SINA帐号已经绑定过',
					width  : 350,
					height : 200,
					left:20,
					top:10,
					destoryOnClose : true,
					hidden : false,
					html    : '<div class="unrebind"><strong>帐号不能重复绑定</strong><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您当前的新浪微博账号已经绑定了本站的账号，你可以解除绑定或者 <a href="xwb.php?m=xwbAuth.login">换一个账号</a>。</p></div>',
					onclose : function (){
						window.top.location='index.php';
					}
			});
			this.ck.del('xwb_tips_type');
		},

		bind: function() {
			 this.win['bind'] = new NS.ui.Popup({
					title  : '用户绑定与设置',
					width  : 710,
					height : 290,
					hidden : false,
					contentType:'iframe',
					destoryOnClose : true,
					url    : CFG.bindUrl + '&r=' + Math.random()
			 });
			this.ck.del('xwb_tips_type');
		},

		close:function (key){
			this.win[key].close();
		},

		ck : {
			set : function (name, value){
				var Days = 30; //此 cookie 将被保存 30 天
				var exp  = new Date();
				exp.setTime(exp.getTime() + Days*24*60*60*1000);
				document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
			} ,
			get : function (name){
				var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
				if(arr != null) return unescape(arr[2]);return null;

			},
			del : function (name){
				var exp = new Date();
				exp.setTime(exp.getTime() - 100);
				var cval=this.get(name);
				if(cval!=null) document.cookie= name + "="+''+";expires="+exp.toGMTString();
			}
		},

		bindSettingTips: function(xwb_user_is_bind) {
			if (xwb_user_is_bind == '1') {
				return;
			}
			var d = new Date();
			var cookie_index = 'bind_tips_' + sina_uid + d.getFullYear() + d.getMonth() + d.getDate();
			var cookie_msg = document.cookie;
			cookie_msg = cookie_msg.split(';');
			for (var i=0; i<cookie_msg.length; i++) {
				if (cookie_msg[i].indexOf(cookie_index) >= 0) {
					return;
				}
			}
			// show tips
			var div = document.getElementById('xwb_bind_set_tips_window');
			div.style.display = 'block';
			// set cookie
			var t = d.getTime() + 3600 * 24 * 100;
			d.setTime(t);
			document.cookie = cookie_index +'=1;expire=' + d.toGMTString() + ';path=/';
		},


		isShowBind : function(targetUid){
		    if(targetUid == CFG.site_uid){
		        XWBcontrol.bind();
		    }
		},

		////////////////////////////////////////////////
		//
		//  注意:本空间只放全局可用的方法,与具体页面无关,页面相关的请放到NS.xxx相关对象里
		//
		///////////////////////////////////////////////

        /**
         * @property cfg
         * 全局配置信息
         * @type Object
         */
        cfg : CFG,

        /**
         * 获得当前用户新浪微博ID,如果用户未绑定,返回空.
         * @return {String}
         */
        getSinaId : function(){return CFG.sina_uid;},

        /**
         * 获得当前站点版本.
         * @return {String}
         */
        getSiteVer : function() {return CFG.siteVer;},

        /**
         * 检测是否为discuz指定版本
         * @param {Number} version
         */
        isV : function(ver){
            return parseInt(this.getSiteVer().charAt(0),10) === ver;
        },

        /**
         * 获得当前用户站内ID
         * @return {String}
         */
        getSiteId : function(){return CFG.site_uid;},

		/**
		 * 检测传入的sinaUID是否当前登录用户.
		 * @param {String} sinaUID
		 */
		isCurrent : function(sinaUid){return this.getSinaId() == sinaUid;},

		/**
		 * 检测当前页面是否指定页面.
		 * @param {String} pageId
         * @return {Boolean}
		 */
		isPage : function(page) {return CFG.pName === page;},
		
		/**
		 * 获取新浪微博或者xweibo的个人主页link
		 * @param {String} sina_uid
		 * @return {String}
		 */
		getWeiboProfileLink : function(sina_uid){
			if(1 == CFG.switchToStd){
				return CFG.baseurlToStd + 'index.php?m=ta&id=' + sina_uid;
			}else{
				return 'http://weibo.com/' + sina_uid;
			}
		},

		/**
		 * 添加关注链接点击前检测是否已关注，
		 * 方法可用在添加关注的超链接上<a href="xx" onclick="XWBcontrol.onAddFriendClick(event)">
		 * @param {DOMEvent} event
		 */
		onAddFriendClick : function(evt, sid){
		   // clear cache
		   NS.TipPanel.userData(sid, false);
		   if(!CFG.sina_uid){
		        if(evt)
		            NS.util.stopEvent(evt);
		        XWBcontrol.bind();
		      return false;
		   }
		},


		/**
		 * 获得用户微博相关信息, callback(data, isOk);
           截止目前data的数据如下<br>
           <pre>
         {
           "sina_uid": "1622368815",
           "sina_name": "\u6e90irock",
           "location": "\u5e7f\u4e1c \u5e7f\u5dde",
           "isFriend": 0,
           "gender": "m",
           "profile_image_url": "http:\/\/tp4.sinaimg.cn\/1622368815\/50\/1280302818",
           "followers_count": 16,
           "friends_count": 42,
           "statuses_count": 15,
           "description": "\u8e29\u7740\u65f6\u95f4\u7684\u65c5\u884c",
           "last_blog": "sss",
           "last_blog_id": "1677668228"
        }
          </pre>

         example:
         <pre>
           getUserData(function(data, isOk){
                if(isOk !== false) {
                  var uid = data['sina_uid'];
                }
           });
         </pre>
         @param {String} sinaUID
         @param {Function} callback such as callback(data, isOk)
         @param {Object} [scope] scope of callback
		 */
		getUserData : function(sinaId, callback, scope){
            NS.util.connect(CFG.getTipsUrl + '&view_id='+sinaId, {
                success : function(j){
                    if(j[0]){
                        callback.call(this, j[0], false);
                    }else {
                        callback.call(this, j[1]);
                    }
                },

                failure : function(){
                    callback.call(this, 'Request error!', false);
                },
                scope : scope
            });
		},

		/**
		 * 创建预览微博IFRAME
		 * @param {Object} cfg
		  <pre>
		  cfg = {
		    url:string,
		    appendTo:HTMLElement,
		    sinaUid : suid,
		  };
		  </pre>
		 * @return {IFrame} iframeElement
		 */
		buildPreviewFrame : function(cfg) {
		    var v,
		        width,
		        height,
		        p = NS.util.$(cfg.appendTo);

		    // 在url中位置可能不同，分开获得
		    v = cfg.url.match(/[\?&]width=(\d+)/i);
		    width = v && v[1]  || 180;
		    v = cfg.url.match(/[\?&]height=(\d+)/i);
		    height = v && v[1] || 480;

		    //alert(cfg.url);

		    // 适应iframe高度
            var maxH = Math.min(p.offsetHeight - 32, height, 500);
            cfg.url = cfg.url.replace(/([\?&]height=)(\d+)(?=&?)/i, '$1'+maxH);

            // 设置uid
            cfg.url = cfg.url.replace(/([\?&]uname=)([^&]*)(?=&?)/i, '$1'+ encodeURIComponent( cfg.sinaUid ));

		    var div = NS.util.forNode(
		        '<div class="xwb-show-plugins4dx">' +
                    '<iframe width="'+width+'px" height="'+maxH+'px" frameBorder="no" scrolling="auto" hideFocus="" src="{url}"></iframe>'+
                '<span class="xwb-del-btn" title="关闭"></span>' +
                '</div>', cfg);

            div.lastChild.onclick = function(){
                this.parentNode.parentNode.removeChild(this.parentNode);
            };

            p.lastChild.style.height = 'auto';
            p.insertBefore(div, p.lastChild.nextSibling);
		    //p.insertBefore(div, p.firstChild);
		}
});


/**
 * @class XWBcontrol.TipPanel
 * 用户微博浮动信息栏，已实例化的类，可在任何地方显示。
  example :
  <pre><code>
    &lt;img title="已绑定新浪微博"
         src="icon_on.gif"
         onmouseover="XWBcontrol.TipPanel.showLayer(this, '11014');"
         onmouseout="XWBcontrol.TipPanel.setHideTimer();"&gt;</code>
 </pre>

 * @extends XWBcontrol.ui.FloatTip
 * @singleton
 */
NS.TipPanel = new NS.ui.FloatTip({
    /**
     * @cfg {Number} expire 用户数据缓存时间,单位:秒,默认两分钟内不会刷新.
     */
    expire : 120,
    createView : function(){
        var el = NS.util.forNode('<div class="xwb-info-card"><div class="inner-info-card"><div id="xwb_info_card_inner" class="hidden"></div><div id="xwb_info_card_load" class="info-card-load">正在读取，请稍微...</div></div></div>');
        this.layerCt = NS.util.$('xwb_info_card_inner', el);
        this.loadEl  = NS.util.$('xwb_info_card_load', el);
        document.body.appendChild(el);
        return el;
    },

    userData : function(key, v){
        if(!this.cache)
            this.cache = {};
        if(v === undefined){
            var c = this.cache[key];
            if(c){
                var t = c.t;
                if((new Date() - t)/1000 < this.expire)
                    return c.d;
            }
            return null;
        }
        this.cache[key] = {d:v,t:+new Date()};
    },

    beforeShow : function(anchor, sinaId){
        if(sinaId != this.reqSinaId)
            this.reqSinaId = sinaId;

        var data = this.userData(this.reqSinaId);
        if(!data){
            this.loadContent();
        }else {
            this.updateContent(data);
        }
    },

    loadContent : function(){
            NS.util.addClass(this.layerCt, 'hidden');
            NS.util.delClass(this.loadEl , 'hidden');
            NS.getUserData(this.reqSinaId, this._onConnectResponse, this);
    },

/**
 * 获得内容存放的结点
 * @return {HTMLElement}
 */
    getCt : function(){
        if(!this.layer)
            this.getLayer();
        return this.layerCt;
    },

    _onConnectResponse : function(data, isOk){
        if(isOk !== false){
            this.userData(this.reqSinaId, data);
            this.updateContent(data);
//            由于用户资料页的微博标识和勋章资料页使用了同一个缓存域
//            当刷新一个页面后，若先访问用户资料页，再访问勋章资料页
//            则在访问勋章资料页时，因缓存域非空，不再经loadContent进入_onConnectResponse
//            而是直接调用updateContent方法，所以勋章资料页所调用的隐藏类样式没有发生变化
//            从而无法显示正确的资料，而是一直显示为loading。
//            解决方法：
//            1. 将隐藏类样式的变换动作放入_onConnectResponse末尾执行。修改简单，但由于
//            _onConnectResponse的调用比loadContent频繁，因此会频繁执行类替换的动作
//            2. 将用户资料页的微博标识的数据放入新的缓存域。无须修改xwbjs，但在xwbtgc.js
//            中的代码量会增加，并且将鼠标移到微博标识和勋章上会分别发起两次请求。
//            2011-01-20 junxiong
//            NS.util.delClass(this.layerCt, 'hidden');
//            NS.util.addClass(this.loadEl , 'hidden');
        }
    },

    updateContent : function(data) {
        this.getLayer();
        data.attentionUrl = CFG.attentionUrl + '&att_id='+data.sina_uid;
        data.weiboProfileLink = NS.getWeiboProfileLink(data.sina_uid);
        this.layerCt.innerHTML = NS.util.templ([
                            '<div class="info-card-pic">',
                                '<a target="_blank" href="',
                                data.weiboProfileLink,
                            	'"><img width="50" height="50" src="{profile_image_url}" /></a>',
                                (NS.getSiteId() > 0 && !NS.isCurrent(data.sina_uid)) ? /* 增加登录判定 2010-12-28 */
                                    data.isFriend ? '<span class="followed">已关注</span>':
                                                    '<a class="add-follow" target="_blank" onclick="XWBcontrol.onAddFriendClick(event, \'{sina_uid}\')" href="{attentionUrl}">加关注</a>':'',
                            '</div>',
                            '<div class="info-coard-main">',
                                '<a class="xwb-username" target="_blank" href="',
                                data.weiboProfileLink,
                            	'">{sina_name}</a>',
                                '<div class="xwb-'+(data.gender==='m'?'male':'female')+'">{location}<span>粉丝{followers_count}人</span></div>',
                                '<p>'+NS.util.escapeHtml(data.last_blog)+'</p>',
                            '</div>'
         ].join(''), data);
         NS.util.delClass(this.layerCt, 'hidden');
         NS.util.addClass(this.loadEl , 'hidden');
    }
});



/////////////////////////////////////////
////
//// 零散的页面初始化,根据不同页面实现不同的功能.
////
/////////////////////////////////////////

if (window.xwb_sidebar_binded)
    NS.bindSettingTips(xwb_sidebar_binded);

if( CFG ) {

NS.util.domEvent(window, 'load', function(){

    //-----------------------------------------------------------------------
    switch (CFG.tipsType) {
    	case 'reg':
    		NS.openReg4dx();
    	break;

    	case 'hasBinded':
    		NS.hasBind();
    	break;

    	case 'set':
    		NS.setBind();
    	break;
    	case 'bind':
    		NS.bind();
    	break;
    }

});

    //-----------------------------------------------------------------------
    //
    //
    //-----------------------------------------------------------------------
    if(CFG.tipsType === 'reg') {
        NS.util.domEvent(document, 'keydown', function(e){
            e = e || window.event;
            if(e.keyCode === 13){
    			if (NS.isEndReg) {
    				NS.close('reg');
    				window.top.location='index.php';
    			}
            }
        });
    }


    //-----------------------------------------------------------------------------------
    // 个性页面
    //-----------------------------------------------------------------------------------
    if( NS.isPage('home_spacecp') ){
        /**
         * @class XWBcontrol.profile
         * 个性设置页面
         */

        NS.profile = {
                /**
                 * @property signatureMsgAreaId
                 * 个性设置中签名textarea元素的id
                 * @type String
                 */
            	signatureMsgAreaId : 'sightmlmessage',

            	/**
            	 * 获得个性设置签名textarea元素
            	 * @private
            	 */
            	getSignerArea   : function(){
            	    return NS.util.$(this.signatureMsgAreaId);
            	},

                /**
                 * 设置个性设置中签名文本值。
                 * @param {String} value
                 * @param {Number} callback(previousValue), scope = this
                 */
            	setSignerBox  : function(callback){
            	    var textarea = this.getSignerArea();
                    textarea.value = callback.call(this, textarea.value);
                    textarea.focus();
            	},

                // 用于识别新浪签名TAG
                SIGNER_REG : /<-sina_sign,\d+?,[a-z0-9]+?,(\d+?)->/i,

                /**
                 * 在个性设置页中,设置新浪微博签名
                 * <-sina_sign,100000000,1->
                 * @param {String} uid 用户ID
                 * @param {String} skinNumber 皮肤下标
                 */
            	setXwbSigner : function(uid, skinNum, keyStr){
            	  var tpl = this.getSignerSkinTpl(uid, skinNum, keyStr);
            	  this.setSignerBox(function(pre){
                   	    if(!pre)
                   	        return tpl;

                   	    var reg = this.SIGNER_REG;
                   	    // 追加到开头
                   	    if(!reg.test(pre))
                   	        return tpl + pre;

                   	    return pre.replace(reg, function(){return tpl;});
            	  });
            	},

                /**
                 * 获得微博签名discuz模板。
                 * @param {String} uid 用户ID
                 * @param {String} skinNumber 皮肤下标
                 */
                getSignerSkinTpl : function(uid, skinNum, keyStr){
                    return '<-sina_sign,'+uid+','+keyStr+','+skinNum+'->';
                },

        		/**
        		 * 在个性设置页中,获得当前皮肤idx值,无返回false
        		 * @return {Number|false}
        		 */
        		getSignerSkinNum : function(){
        		  var v = this.getSignerArea().value;
        		  var re = this.SIGNER_REG;
        		  if(re.test(v)){
        		    return parseInt(re.exec(v)[1]);
        		  }
        		  return false;
        		},

        		/**
        		 * 根据用户ID和皮肤下标获得微博皮肤完整的URL，已作忽略缓存处理。
        		 * @param {String} uid
        		 * @param {Number} skinNum
        		 * @param {String} checkCode
        		 */
        		getSignerSkinUrl : function(uid, skinNum, code){
        		  return 'http://service.t.sina.com.cn/widget/qmd/'+uid+'/'+code+'/'+skinNum+'.png?rnd='+(+new Date());
        		},

        		/**
        		 * 签名设置对话框确定后调用,以向页面设置已选定的签名风格
        		 */
            	onSignerDlgOk : function(uid, skinNum, keyStr){
            	  this.setXwbSigner(uid, skinNum, keyStr);
            	  NS.close('signer');
            	}
        };
    }

    //-----------------------------------------------------------------------------------
    // 个人设置页面
    //-----------------------------------------------------------------------------------
    if( NS.isPage('home_space') ){
    	//alert('ok0');
        if(NS.isV(1)){
        	//alert('ok');
            // 生成预览微博IFRAME
        	/*
            if(window._xwb_site_view_uid && window._xwb_is_wbx_display === '1'){
            	//alert('ok1');
                NS.getUserData(_xwb_site_view_uid, function(data, status){
                    if(status !== false){
                        NS.buildPreviewFrame({
                            url : CFG.wbxUrl,
                            appendTo:'ct',
                            sinaUid : data['sina_name']
                        });
                    }
                });
            }
            */
        }
    }

    //-----------------------------------------------------------------------------------
    // 管理员面板首页
    //-----------------------------------------------------------------------------------
    if( NS.isPage('admin_home') && !NS.ck.get('xwb_update_tip') ){

        NS.util.domEvent(window, 'load', function(){
            NS.util.jsonp(CFG.updateApi, function(j){
                if( j.ver.toString() != CFG.xwb_Version.toString() ){
                  new NS.ui.Popup({
                        width:420,
                        //mask : false,
                        title : '新浪微博插件升级提示',
                        // 自动适应内容高度
                        autoH:true,
                        destoryOnClose : true,
                        hidden : false,
                        focus : 'ok',
                        html:[
                            '<div style="padding:20px 15px;">',
                            '<img width="64px" height="64px" src="'+j.img+'" style="float:left;">',
                            '<div style="float:left;padding:0 15px;width:280px;">',
                              j.text||'',
                            '</div>',
                            '<div style="clear:both;"></div>',
                            '</div>'
                            ].join(''),

                        buttons : [
                           {
                            title:'',
                            id:'uptip',
                            html:'<span style="float:left;"><input style="border:none !important;" id="xwb_upd_tip" type="checkbox"><label for="xwb_upd_tip">下次不再提示</label></span>'
                           },
                           'cancel',{title:'立即升级', id:'ok'}
                        ],

                        onok : function(){
                            this.close();
                            window.open(j.downurl||'');
                        },

                        onuptip : function(e){
                            e = e || window.event;
                            var src = e.target || e.srcElement;
                            if(src.type == 'checkbox'){
                               NS.ck.set('xwb_update_tip', src.checked?'on':'');
                            }
                        }
                  });
                }
            });
        });
    }
}

})('XWBcontrol', window._xwb_cfg_data);