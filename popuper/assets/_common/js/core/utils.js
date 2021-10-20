'use strict';

/**
 * Utils Object that is used in all libraries
 */

var _bind = Function.prototype.bind;

/**
 * @param arr
 * @returns {any[]|unknown[]}
 * @private
 */
function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];
        return arr2;
    } else {
        return Array.from(arr);
    }
}

var $utils = {
    /**
     * for console out
     */
    debug: false,

    /**
     * debounce default delay
     */
    debounceDefaultDelay: 300,

    /**
     * @type {{elementsWithHandlers: [], eventHandlers: {}, loadedOuterJS: {}, loadedInnerJS: {}}}
     */
    Cache: {
        elementsWithHandlers: [],
        eventHandlers: {},
        loadedOuterJS: {},
        loadedInnerJS: {}
    },

    /**
     * check & load some outer lib (like jquery) if need
     */
    outerJSLoader: {
        /**
         * @param url
         * @param callBack
         */
        load: function load(url, callBack) {
            if ($utils.isString(url) && url.length) {
                if (!$utils.Cache.loadedOuterJS.hasOwnProperty(url)) {
                    var scriptElement = document.createElement('script');

                    scriptElement.src = url;
                    scriptElement.async = false;

                    if ($utils.isFunction(callBack)) {
                        $utils.addEventHandler(scriptElement, 'load', callBack);
                    }

                    document.head.appendChild(scriptElement);
                    $utils.Cache.loadedOuterJS[url] = scriptElement;
                } else if ($utils.isFunction(callBack)) {
                    callBack.call();
                }
            }
        }
    },

    /**
     * check & load some js dynamically if need
     */
    innerJSLoader: {
        /**
         * @param script
         * @param initJsArgs
         * @returns {null|Function}
         */
        initJs: function initJs(script, initJsArgs) {
            if ($utils.isString(script) && script.length) {
                initJsArgs = initJsArgs || {};

                var args = Object.keys(initJsArgs);

                if (args.length) {
                    return new (_bind.apply(Function, [null].concat(_toConsumableArray(args), [script])))();
                } else {
                    return new Function(script);
                }
            }

            return null;
        },

        /**
         * @param callJsFn
         * @param initJsArgs
         * @param callback
         */
        call: function call(callJsFn, initJsArgs, callback) {
            initJsArgs = initJsArgs || {};

            if ($utils.isFunction(callJsFn)) {
                var args = Object.keys(initJsArgs);

                if (args.length) {
                    callJsFn.apply($utils, Object.values(initJsArgs));
                } else {
                    callJsFn.call($utils);
                }
            }

            if ($utils.isFunction(callback)) {
                callback.call($utils);
            }
        },

        /**
         * @param url
         * @param withCredentials
         * @param initJsArgs
         * @param callback
         */
        load: function load(url, withCredentials, initJsArgs, callback) {
            if ($utils.isString(url) && url.length) {
                if (url in $utils.Cache.loadedInnerJS) {
                    $utils.innerJSLoader.call($utils.Cache.loadedInnerJS[url], initJsArgs, callback);
                } else {
                    $utils.ajax({
                        type: 'GET',
                        url: url,
                        crossDomain: true,
                        withCredentials: withCredentials,
                        success: function success(xhr) {
                            if (xhr.readyState === xhr.DONE) {
                                var callJsFn = $utils.innerJSLoader.initJs(xhr.responseText, initJsArgs);
                                if ($utils.isFunction(callJsFn)) {
                                    $utils.Cache.loadedInnerJS[url] = callJsFn;
                                }
                                $utils.innerJSLoader.call(callJsFn, initJsArgs, callback);
                            }
                        },
                        error: function error() {
                            $utils.innerJSLoader.call(null, initJsArgs, callback);
                        }
                    });
                }
            }
        }
    },

    /**
     * check & load some css if need
     */
    styleLoader: {
        /**
         * @param link
         * @returns {boolean}
         */
        exists: function exists(link) {
            if ($utils.isRelativeUrl(link)) {
                link = document.location.protocol + link;
            }
            return document.styleSheets.length && Array.from(document.styleSheets).some(function(styleSheet) {
                return styleSheet.href && styleSheet.href.includes(link);
            });
        },

        /**
         * @param url
         */
        load: function load(url) {
            if ($utils.isString(url) && url.length && !this.exists(url)) {
                var link = document.createElement('link');

                link.type = 'text/css';
                link.href = url;
                link.rel = 'stylesheet';
                link.media = 'all';

                document.head.appendChild(link);
            }
        }
    },

    /**
     * @param url
     * @returns {*|boolean}
     */
    isValidRedirectUrl: function isValidRedirectUrl(url) {
        return $utils.isString(url) && url.length && window.location.href !== url;
    },

    /**
     * @param something
     */
    consoleLog: function consoleLog(something) {
        $utils.debug && console.log(something);
    },

    /**
     * @param arg
     * @returns {boolean}
     */
    isNumber: function isNumber(arg) {
        return typeof arg === 'number';
    },

    /**
     * @param arg
     * @returns {boolean}
     */
    isPrimitive: function isPrimitive(arg) {
        return arg === null || typeof arg !== 'object' && typeof arg !== 'function';
    },

    /**
     * @param value
     * @returns {*|boolean}
     */
    isObject: function isObject(value) {
        return !!value && typeof value === 'object' && value.constructor === Object;
    },

    /**
     * @param value
     * @returns {boolean}
     */
    isFunction: function isFunction(value) {
        return typeof value === 'function';
    },

    /**
     * @param value
     * @returns {boolean}
     */
    isBoolean: function isBoolean(value) {
        return typeof value === 'boolean';
    },

    /**
     * @param value
     * @returns {boolean}
     */
    isString: function isString(value) {
        return typeof value === 'string' || value instanceof String;
    },

    /**
     * @param url
     * @returns {boolean}
     */
    isRelativeUrl: function isRelativeUrl(url) {
        return $utils.isString(url) && !new RegExp('^(?:[a-z]+:)?//', 'i').test(url);
    },

    /**
     * @param name
     * @param value
     * @param options
     * @returns {null}
     */
    cookie: function cookie(name, value, options) {
        if (typeof value !== 'undefined') {
            options = options || {};

            if (value === null) {
                value = '';
                options.expires = -1;
            }

            var expires = '';

            if (options.expires && ($utils.isNumber(options.expires) || options.expires.toUTCString)) {
                var date;

                if ($utils.isNumber(options.expires)) {
                    date = new Date();
                    date.setTime(date.getTime() + options.expires * 24 * 60 * 60 * 1000);
                } else {
                    date = options.expires;
                }

                expires = '; expires=' + date.toUTCString();
            }

            var secure = options.secure ? '; secure' : '';
            var sameSite = '';

            if (document.location.protocol === 'https:') {
                sameSite = '; SameSite=None';
                secure = '; secure';
            }

            document.cookie = [
                name,
                '=',
                encodeURIComponent(value),
                expires,
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                sameSite,
                secure
            ].join('');
        } else {
            var cookieValue = null;

            if ($utils.isString(document.cookie) && document.cookie !== '') {
                var cookies = document.cookie.split(';');

                for (var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i].trim();

                    if (cookie.substring(0, name.length + 1) === name + '=') {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }

            return cookieValue;
        }
    },

    /**
     * Add cross browser event
     * @param element
     * @param event
     * @param fn
     */
    addEventHandler: function addEventHandler(element, event, fn) {
        if (!element) {
            return false;
        }

        /**
         * @param event
         * @returns {boolean}
         */
        function listenHandler(event) {
            if (false === fn.apply(this, arguments)) {
                event.stopPropagation();
                event.preventDefault();

                return false;
            }

            return true;
        }

        var elementIdx = $utils.Cache.elementsWithHandlers.indexOf(element);

        if (!$utils.Cache.elementsWithHandlers.includes(element)) {
            $utils.Cache.elementsWithHandlers.push(element);
            elementIdx = $utils.Cache.elementsWithHandlers.indexOf(element);
            $utils.Cache.eventHandlers[elementIdx] = {};
        }

        if (!(event in $utils.Cache.eventHandlers[elementIdx])) {
            $utils.Cache.eventHandlers[elementIdx][event] = [];
        }

        $utils.Cache.eventHandlers[elementIdx][event].push(listenHandler);

        element.addEventListener(event, listenHandler, false);
    },

    /**
     * @param element
     * @param event
     */
    hasEventHandler: function hasEventHandler(element, event){
        if ($utils.Cache.elementsWithHandlers.includes(element)) {
            var elementIdx = $utils.Cache.elementsWithHandlers.indexOf(element);

            if (event in $utils.Cache.eventHandlers[elementIdx]) {
                return true;
            }

        }

        return false;
    },

    /**
     * Drop cross browser event handlers
     *
     * @param element
     * @param event
     */
    dropEventHandlers: function dropEventHandlers(element, event) {
        if (!element) {
            return false;
        }

        if ($utils.Cache.elementsWithHandlers.includes(element)) {
            var elementIdx = $utils.Cache.elementsWithHandlers.indexOf(element);
            var elementHandlers = $utils.Cache.eventHandlers[elementIdx];

            if (event in elementHandlers) {
                var eventHandlers = elementHandlers[event];

                for (var i = eventHandlers.length; i--;) {
                    var handler = eventHandlers[i];

                    element.removeEventListener(event, handler);

                    delete eventHandlers[i];
                }
                delete eventHandlers[event];
            }
            delete $utils.Cache.eventHandlers[elementIdx];

            $utils.Cache.elementsWithHandlers[elementIdx] = null;
        }
    },

    /**
     * Drop all events handlers
     */
    dropAllEventsHandlers: function dropAllEventsHandlers() {
        $utils.Cache.elementsWithHandlers.forEach(function(element, elementIdx) {
            var event;
            var eventHandlers;
            var elementHandlers = $utils.Cache.eventHandlers[elementIdx];

            for (event in elementHandlers) {
                eventHandlers = elementHandlers[event];
                for (var i = eventHandlers.length; i--;) {
                    var handler = eventHandlers[i];

                    element.removeEventListener(event, handler);

                    delete eventHandlers[i];
                }
                delete elementHandlers[event];
            }
            delete $utils.Cache.eventHandlers[elementIdx];
        });

        $utils.Cache.elementsWithHandlers = [];
    },

    /**
     * @param url
     * @returns {string}
     */
    addGLOBALParamsToUrl: function addGLOBALParamsToUrl(url) {
        if (url) {
            var additionalParams = [];

            if ($utils.isObject(GLOBAL.pageInfo)) {
                for (var _i = 0, _a = Object.keys(GLOBAL.pageInfo); _i < _a.length; _i++) {
                    var key = _a[_i];
                    additionalParams.push(
                        'page[' + encodeURIComponent(key) + ']' + '=' + encodeURIComponent(GLOBAL.pageInfo[key]));
                }
            }

            if (GLOBAL.language) {
                additionalParams.push(
                    'lang=' +  encodeURIComponent(GLOBAL.language)
                );
            }

            if (additionalParams.length) {
                url = url + (/\?/.test(url) ? '&' : '?') + additionalParams.join('&');
            }
        }

        return url;
    },

    /**
     * @param url
     * @returns {string}
     */
    timeStampToUrl: function timeStampToUrl(url) {
        if (url) {
            url = url + (/\?/.test(url) ? '&' : '?') + 'ts=' + new Date().getTime();
        }

        return url;
    },

    /**
     * @param data
     * @returns {FormData|*}
     */
    objectToFormData: function objectToFormData(data) {
        if (data instanceof FormData) {
            return data;
        }

        if ($utils.isObject(data)) {
            var formData = new FormData();

            for (var property in data) {
                Array.isArray(data[property]) ? data[property].forEach(function(value) {
                    formData.append(property + '[]', value);
                }) : formData.append(property, data[property]);
            }

            return formData;
        }

        return data;
    },

    /**
     * @param {{}} options
     * @returns {XMLHttpRequest}
     */
    ajax: function ajax(options) {
        /**
         * @var {{data: *, withCredentials: boolean, success: function(), error: function(), complete: function(), dynamicVersion: boolean, crossDomain: boolean, type: string, url: *}} options
         */
        options = options || {};

        var xhr = new XMLHttpRequest();
        var method = $utils.isString(options.type) ? options.type.toUpperCase() : 'GET';
        var url = options.url;
        var withCredentials = $utils.isBoolean(options.withCredentials) && options.withCredentials;
        var dynamicVersion = $utils.isBoolean(options.dynamicVersion) && options.dynamicVersion;

        if (dynamicVersion || withCredentials) {
            url = $utils.timeStampToUrl(url);
        }

        xhr.open(method, url, true);

        if (withCredentials) {
            xhr.withCredentials = options.withCredentials;
        }

        if ($utils.isFunction(options.success)) {
            xhr.onload = function() {
                options.success.call(null, xhr);
            };
        }

        if ($utils.isFunction(options.error)) {
            xhr.onerror = function() {
                options.error.call(null, xhr);
            };
        }

        if ($utils.isFunction(options.complete)) {
            xhr.onloadend = function() {
                options.complete.call(null, xhr);
            };
        }

        if (method === 'GET') {
            xhr.setRequestHeader('Content-Type', 'text/plain; charset=x-user-defined');
        } else if (method === 'POST') {
            xhr.setRequestHeader('Accept', 'application/json, text/javascript, */*; q=0.01');
            if (!(options.data instanceof FormData)) {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            }
        }

        if (!$utils.isBoolean(options.crossDomain) || !options.crossDomain) {
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        }

        xhr.send(options.data ? options.data : null);

        return xhr;
    },

    /**
     * @param handler
     * @param delay
     * @returns {function(): *}
     */
    debounce: function debounce(handler, delay) {
        if (handler) {
            var timeout, args, context, timestamp, result;

            if (!delay) {
                delay = $utils.debounceDefaultDelay;
            }

            var later = function later() {
                var last = Date.now() - timestamp;
                if (last < delay && last >= 0) {
                    timeout = setTimeout(later, delay - last);
                } else {
                    result = handler.apply(context, args);
                    timeout = context = args = null;
                }
            };

            return function() {
                context = this;
                args = arguments;
                timestamp = Date.now();

                if (!timeout) {
                    timeout = setTimeout(later, delay);
                }

                return result;
            };
        }
    }
};