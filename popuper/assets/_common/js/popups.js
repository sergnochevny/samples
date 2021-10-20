'use strict';

//<<<<<<<<< polyfills IE
// @formatter:off
Array.from||(Array.from=function(){var t=Object.prototype.toString,e=function(e){return"function"==typeof e||"[object Function]"===t.call(e)},n=Math.pow(2,53)-1,r=function(t){var e=function(t){var e=Number(t);return isNaN(e)?0:0!==e&&isFinite(e)?(e>0?1:-1)*Math.floor(Math.abs(e)):e}(t);return Math.min(Math.max(e,0),n)};return function(t){var n=Object(t);if(null==t)throw new TypeError("Array.from requires an array-like object - not null or undefined");var o,i=arguments.length>1?arguments[1]:void 0;if(void 0!==i){if(!e(i))throw new TypeError("Array.from: when provided, the second argument must be a function");arguments.length>2&&(o=arguments[2])}for(var a,s=r(n.length),u=e(this)?Object(new this(s)):new Array(s),c=0;c<s;)a=n[c],u[c]=i?void 0===o?i(a,c):i.call(o,a,c):a,c+=1;return u.length=s,u}}()),Array.prototype.includes||Object.defineProperty(Array.prototype,"includes",{value:function(t,e){if(null==this)throw new TypeError('"this" is null or not defined');var n=Object(this),r=n.length>>>0;if(0===r)return!1;var o,i,a=0|e,s=Math.max(a>=0?a:r-Math.abs(a),0);for(;s<r;){if((o=n[s])===(i=t)||"number"==typeof o&&"number"==typeof i&&isNaN(o)&&isNaN(i))return!0;s++}return!1}}),String.prototype.includes||(String.prototype.includes=function(t,e){return"number"!=typeof e&&(e=0),!(e+t.length>this.length)&&-1!==this.indexOf(t,e)}),Object.assign||Object.defineProperty(Object,"assign",{enumerable:!1,configurable:!0,writable:!0,value:function(t,e){if(null==t)throw new TypeError("Cannot convert first argument to object");for(var n=Object(t),r=1;r<arguments.length;r++){var o=arguments[r];if(null!=o)for(var i=Object.keys(Object(o)),a=0,s=i.length;a<s;a++){var u=i[a],c=Object.getOwnPropertyDescriptor(o,u);void 0!==c&&c.enumerable&&(n[u]=o[u])}}return n}}),function(t){"window"in t&&"document"in t&&(t.XMLHttpRequest=t.XMLHttpRequest||function(){try{return new ActiveXObject("Msxml2.XMLHTTP.6.0")}catch(t){}try{return new ActiveXObject("Msxml2.XMLHTTP.3.0")}catch(t){}try{return new ActiveXObject("Msxml2.XMLHTTP")}catch(t){}throw Error("This browser does not support XMLHttpRequest.")},[["UNSENT",0],["OPENED",1],["HEADERS_RECEIVED",2],["LOADING",3],["DONE",4]].forEach(function(e){e[0]in t.XMLHttpRequest||(t.XMLHttpRequest[e[0]]=e[1])}),function(){if(!("FormData"in t)){n.prototype={append:function(e,n){if("Blob"in t&&n instanceof t.Blob)throw TypeError("Blob not supported");e=String(e),this._data.push([e,n])},toString:function(){return this._data.map(function(t){return encodeURIComponent(t[0])+"="+encodeURIComponent(t[1])}).join("&")}},t.FormData=n;var e=t.XMLHttpRequest.prototype.send;t.XMLHttpRequest.prototype.send=function(t){return t instanceof n&&(this.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),arguments[0]=t.toString()),e.apply(this,arguments)}}function n(t){if(this._data=[],t)for(var e=0;e<t.elements.length;++e){var n=t.elements[e];""!==n.name&&this.append(n.name,n.value)}}}())}(self),"".trim||(String.prototype.trim=function(){return this.replace(/^[\s﻿]+|[\s﻿]+$/g,"")}),function(t){t.DOMException||((DOMException=function(t){this.message=t}).prototype=new Error);var e,n,r=/[\11\12\14\15\40]/,o=0,i=function(t,e){if(""===e)throw new DOMException("Failed to execute '"+t+"' on 'DOMTokenList': The token provided must not be empty.");if(-1!==(o=e.search(r)))throw new DOMException("Failed to execute '"+t+"' on 'DOMTokenList': The token provided ('"+e[o]+"') contains HTML space characters, which are not valid in tokens.")};"function"!=typeof DOMTokenList&&function(t){var e=t.document,n=t.Object,o=n.prototype.hasOwnProperty,a=n.defineProperty,s=0,u=0;function c(){if(!s)throw TypeError("Illegal constructor")}function l(){var e=t.event,n=e.propertyName;if(!u&&("className"===n||"classList"===n&&!a)){var o=e.srcElement,i=o[" uCLp"],s=""+o[n],c=s.trim().split(r),l=o["classList"===n?" uCL":"classList"],h=i.length;t:for(var p=0,f=i.length=c.length,v=0;p!==f;++p){for(var d=0;d!==p;++d)if(c[d]===c[p]){v++;continue t}l[p-v]=c[p]}for(var m=f-v;m<h;++m)delete l[m];if("classList"!==n)return;u=1,o.classList=l,o.className=s,u=0,l.length=c.length-v}}function h(t){if(!(t&&"innerHTML"in t))throw TypeError("Illegal invocation");function e(){}t.detachEvent("onpropertychange",l),s=1;try{e.prototype=new c}finally{s=0}var n=e.prototype,o=new e;t:for(var i=t.className.trim().split(r),h=0,p=i.length,f=0;h!==p;++h){for(var v=0;v!==h;++v)if(i[v]===i[h]){f++;continue t}this[h-f]=i[h]}n.length=p-f,n.value=t.className,n[" uCL"]=t,a?(a(t,"classList",{enumerable:1,get:function(){return o},configurable:0,set:function(e){u=1,t.className=n.value=e+="",u=0;var i=e.trim().split(r),a=n.length;t:for(var s=0,c=n.length=i.length,l=0;s!==c;++s){for(var h=0;h!==s;++h)if(i[h]===i[s]){l++;continue t}o[s-l]=i[s]}for(var p=c-l;p<a;++p)delete o[p]}}),a(t," uCLp",{enumerable:0,configurable:0,writeable:0,value:e.prototype}),a(n," uCL",{enumerable:0,configurable:0,writeable:0,value:t})):(t.classList=o,t[" uCL"]=o,t[" uCLp"]=e.prototype),t.attachEvent("onpropertychange",l)}c.prototype.toString=c.prototype.toLocaleString=function(){return this.value},c.prototype.add=function(){t:for(var t=0,e=arguments.length,n="",r=this[" uCL"],o=r[" uCLp"];t!==e;++t){n=arguments[t]+"",i("add",n);for(var a=0,s=o.length,c=n;a!==s;++a){if(this[a]===n)continue t;c+=" "+this[a]}this[s]=n,o.length+=1,o.value=c}u=1,r.className=o.value,u=0},c.prototype.remove=function(){for(var t=0,e=arguments.length,n="",r=this[" uCL"],o=r[" uCLp"];t!==e;++t){n=arguments[t]+"",i("remove",n);for(var a=0,s=o.length,c="",l=0;a!==s;++a)l?this[a-1]=this[a]:this[a]!==n?c+=this[a]+" ":l=1;l&&(delete this[s],o.length-=1,o.value=c)}u=1,r.className=o.value,u=0},t.DOMTokenList=c;try{t.Object.defineProperty(t.Element.prototype,"classList",{enumerable:1,get:function(t){return o.call(this,"classList")||h(this),this.classList},configurable:0,set:function(t){this.className=t}})}catch(n){t[" uCL"]=h,e.documentElement.firstChild.appendChild(e.createElement("style")).styleSheet.cssText='_*{x-uCLp:expression(!this.hasOwnProperty("classList")&&window[" uCL"](this))}[class]{x-uCLp/**/:expression(!this.hasOwnProperty("classList")&&window[" uCL"](this))}'}}(t),e=t.DOMTokenList.prototype,n=t.document.createElement("div").classList,e.item||(e.item=function(t){return void 0===(e=this[t])?null:e;var e}),e.toggle&&!1===n.toggle("a",0)||(e.toggle=function(t){if(arguments.length>1)return this[arguments[1]?"add":"remove"](t),!!arguments[1];var e=this.value;return this.remove(e),e===this.value&&(this.add(t),!0)}),e.replace&&"boolean"==typeof n.replace("a","b")||(e.replace=function(t,e){i("replace",t),i("replace",e);var n=this.value;return this.remove(t),this.value!==n&&(this.add(e),!0)}),e.contains||(e.contains=function(t){for(var e=0,n=this.length;e!==n;++e)if(this[e]===t)return!0;return!1}),e.forEach||(e.forEach=function(t){if(1===arguments.length)for(var e=0,n=this.length;e!==n;++e)t(this[e],e,this);else{e=0,n=this.length;for(var r=arguments[1];e!==n;++e)t.call(r,this[e],e,this)}}),e.entries||(e.entries=function(){var t=this;return{next:function(){return 0<t.length?{value:[0,t[0]],done:!1}:{done:!0}}}}),e.values||(e.values=function(){var t=this;return{next:function(){return 0<t.length?{value:t[0],done:!1}:{done:!0}}}}),e.keys||(e.keys=function(){var t=this;return{next:function(){return 0<t.length?{value:0,done:!1}:{done:!0}}}})}(window),[Element.prototype,Document.prototype,DocumentFragment.prototype].forEach(function(t){t.hasOwnProperty("prepend")||Object.defineProperty(t,"prepend",{configurable:!0,enumerable:!0,writable:!0,value:function(){var t=Array.prototype.slice.call(arguments),e=document.createDocumentFragment();t.forEach(function(t){var n=t instanceof Node;e.appendChild(n?t:document.createTextNode(String(t)))}),this.insertBefore(e,this.firstChild)}})});
// @formatter:on
//>>>>>>>>> polyfills IE

/**
 * popups.js
 *
 * main Loader
 */
(function() {
    var assetsCommonPrefix = 'assets-common-popuper.';
    var assetsPopuperPrefix = 'assets-popuper.';
    var popuperDomainPrefix = 'popuper.';
    var urlParamLanguageKey = 'lang';

    var popupsManagerScript = '/popups-manager.js';
    var utilsScript = '/js/core/utils.js?v=20201150820';

    var scriptSrc;

    /**
     * @param url
     * @returns {boolean}
     */
    function isRelativeUrl(url) {
        return (typeof url === 'string' || url instanceof String) &&
            !new RegExp('^(?:[a-z]+:)?//', 'i').test(url);
    }

    /**
     * @param url
     * @returns {boolean}
     */
    function scriptExists(url) {
        var allScripts = document.scripts;

        if (isRelativeUrl(url)) {
            url = document.location.protocol + url;
        }

        for (var scriptKey in allScripts) {
            var script = allScripts[scriptKey];

            if (typeof script.src !== 'undefined' && script.src.includes(url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param url
     * @param callback
     * @param waitForObj
     * @param timestamp
     */
    function loadScript(url, callback, waitForObj, timestamp) {
        if ((typeof url === 'string' || url instanceof String) && url.length) {
            if (!scriptExists(url)) {
                var scriptElement = document.createElement('script');

                if (timestamp) {
                    url = url + (/\?/.test(url) ? '&' : '?') + 'ts=' + new Date().getTime();
                }

                scriptElement.src = url;
                scriptElement.async = false;
                scriptElement.type = 'text/javascript';

                if (typeof callback === 'function') {
                    var _event, _context, _loaded;

                    var applyCallBack = function applyCallBack() {
                        if (waitForObj === false || window[waitForObj] !== undefined) {
                            callback.call(_context, _event);
                        } else {
                            setTimeout(applyCallBack, 50);
                        }
                    };

                    scriptElement.onreadystatechange = function(event) {
                        if (this.readyState === 'loaded' || this.readyState === 'complete') {
                            if (!_loaded) {
                                _loaded = true;

                                _context = this;
                                _event = event;

                                scriptElement.onreadystatechange = null;
                                applyCallBack();
                            }
                        }
                    };

                    scriptElement.onload = function(event) {
                        if (!_loaded) {
                            _loaded = true;

                            _context = this;
                            _event = event;

                            scriptElement.onload = null;
                            applyCallBack();
                        }
                    };
                }

                document.head.appendChild(scriptElement);
            } else if (typeof callback === 'function') {
                callback.call();
            }
        }
    }

    /**
     * determine language by script source
     * @returns {string}
     */
    function getLanguageFromUrl(){
        var scriptSource = getScriptSource();
        var url = new URL(scriptSource);

        return url.searchParams.get(urlParamLanguageKey);
    }

    /**
     * determine main host
     * @returns {string}
     */
    function getMainHost() {
        var scriptSource = getScriptSource();

        if (isRelativeUrl(scriptSource)) {
            throw new Error('Can not initialize main domain.');
        }

        var matches = scriptSource.replace(/^(?:[a-z]+:)?\/\//i, '').
            replace(assetsCommonPrefix, '').
            replace(assetsPopuperPrefix, '').
            replace(popuperDomainPrefix, '').
            match(/^([^/]+)/i);

        if (null === matches) {
            throw new Error('Can not initialize main domain.');
        }

        return matches[1];
    }

    /**
     * @returns {string|*}
     */
    function getScriptSource() {
        if(scriptSrc){
            return scriptSrc;
        }

        if (typeof document.currentScript !== 'undefined' && document.currentScript) {
            return scriptSrc = document.currentScript.src;
        }

        var script = document.querySelector('script[src*="' + assetsCommonPrefix + '"]');
        if (script === null || typeof script === 'undefined') {
            var scripts = document.getElementsByTagName('script');
            script = scripts[scripts.length - 1];
        }

        if (typeof script.getAttribute.length === 'undefined' || script.getAttribute.length <= 1) {
            return scriptSrc = script.src;
        }

        scriptSrc = script.getAttribute('src', 2);
        if (isRelativeUrl(scriptSrc)) {
            scriptSrc = script.getAttribute('src', -1);
        }

        return scriptSrc;
    }

    /**
     * @returns {string}
     */
    function getManagerAddress() {
        var url = '//' + window.GLOBAL.mainPopuperDomain + popupsManagerScript;

        return $utils.addGLOBALParamsToUrl(url);
    }

    ////////Run from here///////////////////////////////////////////////////////////
    window.GLOBAL = window.GLOBAL || {};
    window.GLOBAL.language = getLanguageFromUrl() || window.GLOBAL.language;
    window.GLOBAL.mainDomain = getMainHost() || window.GLOBAL.mainDomain || '';
    window.GLOBAL.mainSecureProtocol = window.location.protocol.replace(':', '');
    window.GLOBAL.mainPopuperDomain = window.GLOBAL.mainPopuperDomain ||
        popuperDomainPrefix + window.GLOBAL.mainDomain;
    window.GLOBAL.assetsCommonPopuperDomain = window.GLOBAL.assetsCommonPopuperDomain ||
        assetsCommonPrefix + window.GLOBAL.mainDomain;
    window.GLOBAL.assetsPopuperDomain = window.GLOBAL.assetsPopuperDomain ||
        assetsPopuperPrefix + window.GLOBAL.mainDomain;

    window.addEventListener("DOMContentLoaded", function(event) {
        loadScript('//' + window.GLOBAL.assetsCommonPopuperDomain + utilsScript, function() {
            loadScript(getManagerAddress(), function(event) {
                if (!event && $PopupsManager) {
                    $PopupsManager.fetchPopup();
                }
            }, '$PopupsManager', true);
        }, '$utils');
    });
})();