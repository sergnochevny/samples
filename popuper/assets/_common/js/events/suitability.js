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

        sendDataUrl: null,

        /**
         * this - context of button element
         *
         *@see _common/js/popup-object.js:168
         */
        onAcceptButtonClick: function onAcceptButtonClick(event) {
            if ($utils.isString($Popup.sendDataUrl) && $Popup.sendDataUrl.length) {
                event.preventDefault();
                event.stopPropagation();

                $Popup.sendData($Popup.sendDataUrl, {value: 1}, this);
            }
        },

        /**
         * this - context of button element
         *
         *@see _common/js/popup-object.js:168
         */
        onDeclineButtonClick: function onDeclineButtonClick(event) {
            if ($utils.isString($Popup.sendDataUrl) && $Popup.sendDataUrl.length) {
                event.preventDefault();
                event.stopPropagation();

                $Popup.sendData($Popup.sendDataUrl, {value: -1}, this);
            }
        }

    }
);