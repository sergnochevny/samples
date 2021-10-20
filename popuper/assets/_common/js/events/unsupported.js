'use strict';

/**
 * __________________JS template for popup__________________
 *
 * It will be wrapped with an anonymous function with
 * that is called after loading this file
 */

/**
 * It overloads and extends the instance of parent PopupObject
 *
 * @see _common/js/popup-object.js
 */
Object.assign(
    $Popup, {

        clickHereLinkSelector: '#click-here-link',

        clickHereLinkElement: null,

        /**
         * @see _common/js/popup-object.js:79
         */
        onInit: function onInit() {
            $Popup.clickHereLinkElement = $Popup.contentElement.querySelector($Popup.clickHereLinkSelector);

            if ($Popup.clickHereLinkElement) {
                $utils.addEventHandler($Popup.clickHereLinkElement, 'click', function(event) {
                    $Popup.markAsShowed();

                    if ($Popup.clickHereLinkElement.href === document.location.href) {
                        $Popup.close();

                        event.preventDefault();

                        return false;
                    }

                    return true;
                });
            }
        },

        /**
         * @see _common/js/popup-object.js:168
         *
         * this - context of button element
         */
        onAcceptButtonClick: function onAcceptButtonClick(event) {
            $Popup.markAsShowed();
        },

        /**
         *
         */
        markAsShowed: function markAsShowed() {
            if (
                $utils.isString(window.GLOBAL.countryByIP)
                && window.GLOBAL.countryByIP.length
            ) {
                $utils.cookie(
                    'lastNotifiedUnsupportedCountry',
                    window.GLOBAL.countryByIP,
                    {
                        'domain': window.GLOBAL.mainDomain || document.domain,
                        'path': '/'
                    }
                );
            }

            $utils.consoleLog(
                'lastNotifiedUnsupportedCountry = ' +
                $utils.cookie('lastNotifiedUnsupportedCountry')
            );
        }

    }
);