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

        sendDataUrl: GLOBAL.mainSecureProtocol
            + '://' + GLOBAL.popuperHost
            + '/ajax/how-to-trade',

        /**
         * this - context of button element
         *
         *@see _common/js/popup-object.js:168
         */
        onAcceptButtonClick: function onAcceptButtonClick(event) {
            event.preventDefault();
            event.stopPropagation();

            $Popup.sendData($Popup.sendDataUrl, {value: 1}, this);
        },

        /**
         * this - context of button element
         *
         *@see _common/js/popup-object.js:168
         */
        onNeutralButtonClick: function onNeutralButtonClick(event) {
            event.preventDefault();
            event.stopPropagation();

            $Popup.sendData($Popup.sendDataUrl, {value: 0}, this);
        }

    }
);