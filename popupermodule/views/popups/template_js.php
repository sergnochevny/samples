<?php

use Popuper\Renderer\RenderingData;

/**
 * @var RenderingData $renderingData
 * @var string        $currentHost
 * @var string        $countryByIP
 * @var string        $ipAddress
 * @var string        $popupsLang
 * @var string        $ip
 */

/** JUST TO DECEIVE IDE  */
if (false) { ?>
<script>
    <?php } ?>

    'use strict';

    (function(settings) {
        /**
         * GLOBALS
         */
        window.GLOBAL.popuperHost = '<?php echo $currentHost; ?>';
        window.GLOBAL.countryByIP = '<?php echo $countryByIP; ?>';
        window.GLOBAL.ipAddress = '<?php echo $ipAddress; ?>';
        window.GLOBAL.popupsLang = '<?php echo $popupsLang; ?>';
        /**
         * Popup Manager engine
         */
        var $manager = {
            blockingLayer: null,

            blockingLayerClass: 'blocking-layer',
            preloaderClass: 'ajax-loader',
            containerElementId: 'popuper-main',
            contentSelector: '.popup-content',

            addressFetchPopup: window.GLOBAL.mainSecureProtocol +
                '://' + window.GLOBAL.popuperHost + '/ajax/getNext',

            addressPopupObject: window.GLOBAL.mainSecureProtocol +
                '://' + window.GLOBAL.assetsCommonPopuperDomain +
                '/js/core/popup-object.js?v=6',

            emptyPopupSettings: {
                htmlId: null,
                htmlContent: '',
                outerScripts: [],
                innerScripts: [],
                styles: [],
                typeId: 0,
                popupId: 0,
                settings: {},
                hidden: 0
            },

            popupSettings: {},

            loadedPerPage: 0,

            logByType: [],

            overlay: {
                color: '',
                opacity: 0,
                onclick: null
            },

            request: {
                readyState: 4
            },

            containerElementClasses: [],
            defaultContainerClasses: ['popuper-modal', 'overlay'],

            // milliseconds delayed after page load to call one more time
            autoloadDelay: 5000,

            //is second call was already initiated
            autoloadStarted: false,

            /**
             * @param settings
             */
            loadSettings: function loadSettings(settings) {
                $manager.overlay.color = settings.color || '';
                $manager.overlay.opacity = settings.opacity > 0 ?
                    settings.opacity > 1 ? settings.opacity / 100 : settings.opacity : 0;

                $manager.containerElementClasses = $manager.defaultContainerClasses.slice(0);

                if ($utils.isString(settings['class']) && settings['class'].length > 0) {
                    $manager.containerElementClasses.push(settings['class']);
                }
            },

            /**
             * @param url
             */
            loadPopup: function loadPopup(url) {
                var additionalValues = {
                    log: $manager.logByType
                };

                url = $utils.addGLOBALParamsToUrl(url);

                if ($manager.request && $manager.request.readyState !== 4) {
                    $manager.request.abort();
                }

                $manager.request = $utils.ajax({
                    type: 'POST',
                    url: url,
                    data: $utils.objectToFormData(additionalValues),
                    crossDomain: true,
                    withCredentials: true,

                    success: function success(xhr) {
                        if (xhr.readyState === xhr.DONE) {
                            var _popupSettings = JSON.parse(xhr.responseText);
                            $manager.initPopup(_popupSettings);
                            $manager.loadedPerPage++;
                        }
                    }
                });
            },

            /**
             * @returns {Element | HTMLElement}
             */
            findContainerElement: function findContainerElement() {
                return document.getElementById($manager.containerElementId);
            },

            /**
             * @returns {(Element | any)|null}
             */
            getContentElement: function getContentElement() {
                var containerElement = $manager.findContainerElement();

                if (!containerElement) {
                    return null;
                }

                return containerElement.querySelector($manager.contentSelector);
            },

            /**
             * @param content
             * @param force
             * @returns {boolean}
             */
            setContent: function setContent(content, force) {
                force = force || false;

                if (!content && !force) {
                    return false;
                }

                var containerElement = $manager.getContainerElement();

                if (containerElement) {
                    $manager.containerElementClasses.forEach(function(item) {
                        if ($utils.isString(item) && item.length > 0) {
                            containerElement.classList.add(item);
                        }
                    });

                    if ($manager.overlay.color && $manager.overlay.opacity) {
                        containerElement.style.background = $manager.getOverlayBackgroundRule();
                    } else {
                        containerElement.style.background = 'none';
                        containerElement.style.pointerEvents = 'none';
                    }

                    containerElement.innerHTML = content;
                }

                return true;
            },

            /**
             * @returns {string}
             */
            getOverlayBackgroundRule: function getOverlayBackgroundRule() {
                var red = parseInt($manager.overlay.color.substring(0, 2), 16);
                var green = parseInt($manager.overlay.color.substring(2, 4), 16);
                var blue = parseInt($manager.overlay.color.substring(4, 6), 16);

                return 'rgba(' + red + ', ' + green + ', ' + blue + ', ' + $manager.overlay.opacity + ')';
            },

            /**
             * @returns {*}
             */
            createContainerElement: function createContainerElement() {
                var containerElement = document.createElement('div');

                containerElement.id = $manager.containerElementId;
                $manager.hideElement(containerElement);
                document.body.appendChild(containerElement);

                return containerElement;
            },

            /**
             * @returns {*}
             */
            getContainerElement: function getContainerElement() {
                var containerElement = $manager.findContainerElement();

                if (containerElement) {
                    return containerElement;
                }

                return $manager.createContainerElement();
            },

            /**
             * @returns {boolean}
             */
            removeContainerElement: function removeContainerElement() {
                var containerElement = $manager.findContainerElement();

                if (!containerElement) {
                    return false;
                }

                document.body.removeChild(containerElement);

                return true;
            },
            /**
             *
             */
            addBlockingLayer: function addBlockingLayer() {
                $manager.blockingLayer = document.createElement('div');
                $manager.blockingLayer.classList.add($manager.blockingLayerClass);

                $utils.addEventHandler($manager.blockingLayer, 'click',
                    function(event) {
                        event.preventDefault();
                        if (!$manager.isExistContent()) {
                            $manager.closePopup();
                        }

                        return false;
                    }
                );

                document.body.prepend($manager.blockingLayer);

                $manager.showElement($manager.blockingLayer);
            },

            /**
             *
             */
            removeBlockingLayer: function removeBlockingLayer() {
                if ($manager.blockingLayer) {
                    $utils.dropEventHandlers($manager.blockingLayer);

                    document.body.removeChild($manager.blockingLayer);

                    $manager.blockingLayer = null;
                }
            },

            /**
             * @returns {*|string|boolean}
             */
            isExistContent: function isExistContent() {
                var contentElement = $manager.getContentElement();

                return contentElement && contentElement.hasChildNodes();
            },

            /**
             * @param settings
             * @returns {$manager.emptyPopupSettings|{settings, hidden, styles, typeId, htmlId, outerScripts, innerScripts, htmlContent}}
             */
            sanitizeSettings: function sanitizeSettings(settings) {
                if (!$utils.isObject(settings)) {
                    settings = $manager.emptyPopupSettings;
                }

                return Object.assign($manager.emptyPopupSettings, settings);
            },

            /**
             * @param scriptLinks
             * @param callback
             */
            loadInnerScripts: function loadInnerScripts(scriptLinks, callback) {
                var links;

                if ($utils.isString(scriptLinks) && scriptLinks.length) {
                    links = JSON.parse(scriptLinks);
                } else {
                    links = scriptLinks;
                }
                if (Array.isArray(links) && links.length) {
                    $utils.innerJSLoader.load(links.shift(), false, null, function() {
                        $manager.loadInnerScripts(links, callback);
                    });
                } else if ($utils.isFunction(callback)) {
                    callback.call();
                }
            },

            /**
             * @param scriptUrls
             * @param callback
             */
            loadOuterScripts: function loadOuterScripts(scriptUrls, callback) {
                var links;

                if ($utils.isString(scriptUrls) && scriptUrls.length) {
                    links = JSON.parse(scriptUrls);
                } else {
                    links = scriptUrls;
                }
                if (Array.isArray(links) && links.length) {
                    $utils.outerJSLoader.load(links.shift(), function() {
                        $manager.loadOuterScripts(links, callback);
                    });
                } else if ($utils.isFunction(callback)) {
                    callback.call();
                }
            },

            /**
             * @param stylesLinks
             */
            loadStyles: function loadStyles(stylesLinks) {
                var links;
                if ($utils.isString(stylesLinks) && stylesLinks.length) {
                    links = JSON.parse(stylesLinks);
                } else {
                    links = stylesLinks;
                }
                if (Array.isArray(links) && links.length) {
                    links.forEach(function(url) {
                        $utils.styleLoader.load(url);
                    });
                }
            },

            /**
             * @param settings
             */
            initPopup: function initPopup(settings) {
                $utils.consoleLog(settings);

                $manager.popupSettings = $manager.sanitizeSettings(settings);

                $manager.removePopup();
                $manager.loadSettings($manager.popupSettings.settings);
                $manager.loadStyles($manager.popupSettings.styles);
                $manager.setContent($manager.popupSettings.htmlContent);

                $manager.loadInnerScripts([$manager.addressPopupObject], function() {
                    $manager.loadOuterScripts($manager.popupSettings.outerScripts, function() {
                        $manager.loadInnerScripts($manager.popupSettings.innerScripts, function() {
                            if (
                                typeof $Popup !== 'undefined'
                                && $utils.isObject($Popup)
                                && $utils.isFunction($Popup.init)
                                && $manager.isExistContent()
                                && (!$manager.popupSettings.hidden || $manager.loadedPerPage)
                            ) {
                                $Popup.init($manager.popupSettings);
                            }
                        });
                    });
                });
                $manager.autoloadInit();
            },

            /**
             * Hide element
             * @param element
             *
             * element should be instance of DOMElement
             * that got with document.getElementById or other func
             */
            hideElement: function hideElement(element) {
                element.style.display = 'none';
            },

            /**
             * Show DOMElement
             * @param element
             *
             * element should be instance of DOMElement
             * that got with document.getElementById or other func
             */
            showElement: function showElement(element) {
                element.style.display = 'flex';
            },

            /**
             *
             */
            hidePopup: function hidePopup() {
                var containerElement = $manager.findContainerElement();

                if (containerElement) {
                    $manager.hideElement(containerElement);
                }
            },

            /**
             *
             */
            removePopup: function removePopup() {
                $utils.dropAllEventsHandlers();
                $manager.removeBlockingLayer();
                $manager.removeContainerElement();
            },

            /**
             * @returns {boolean}
             */
            closePopup: function closePopup() {
                $manager.hidePopup();
                $manager.removePopup();
                $manager.fetchPopup();

                return true;
            },

            /**
             *
             */
            showPopup: function showPopup() {
                var containerElement = $manager.findContainerElement();

                if (containerElement) {
                    $manager.showElement(containerElement);
                    if ($manager.popupSettings.typeId) {
                        $manager.logByType.push($manager.popupSettings.typeId);
                    }
                }
            },

            /**
             * @param cleanUp
             */
            fetchPopup: function fetchPopup(cleanUp) {
                cleanUp = cleanUp || false;

                if ($manager.isExistContent()) {
                    return;
                }

                if (cleanUp) {
                    $manager.logByType = [];
                }

                $manager.loadPopup($manager.addressFetchPopup);
            },

            autoloadInit: function () {
                if  ($manager.autoloadStarted) {
                    return;
                }
                $manager.autoloadStarted = true;
                setTimeout(
                    $manager.fetchPopup,
                    $manager.autoloadDelay
                );
            },

            /** set loader */
            showPreloader: function showPreloader() {
                var containerElement = $manager.findContainerElement();

                if (containerElement) {
                    containerElement.classList.add($manager.preloaderClass);
                }
            },

            /** remove loader */
            hidePreloader: function hidePreloader() {
                var containerElement = $manager.findContainerElement();

                if (containerElement) {
                    containerElement.classList.remove($manager.preloaderClass);
                }
            }
        };

        ////////////Run from Here////////////////
        window.$PopupsManager = $manager;
        $manager.initPopup(settings);

    })({
        htmlId: '<?php echo $renderingData->htmlId;?>',
        htmlContent: '<?php
            echo addslashes(
                str_replace(
                    ["\r\n", "\r", "\n",],
                    '',
                    $renderingData->htmlContent
                )
            );
            ?>',
        outerScripts: <?php echo json_encode($renderingData->outerScripts);?>,
        innerScripts: <?php echo json_encode($renderingData->innerScripts);?>,
        styles: <?php echo json_encode($renderingData->styles);?>,
        typeId: <?php echo $renderingData->typeId;?>,
        popupId: <?php echo $renderingData->popupId;?>,
        settings: <?php echo json_encode($renderingData->settings, JSON_FORCE_OBJECT); ?>,
        preview: <?php echo $renderingData->preview ? 'true' : 'false'; ?>,
    });
