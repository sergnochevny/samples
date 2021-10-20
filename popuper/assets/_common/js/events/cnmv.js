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

        copyBtnSelector: '#cnmv-copy-btn',
        copyTextSelector: '#cnmv-copy-text',
        dataInputSelector: '#cnmv-confirmation-input',

        copyBtnElement: null,
        copyTextElement: null,
        dataInputElement: null,

        submitFormUrl: GLOBAL.mainSecureProtocol
            + '://' + GLOBAL.popuperHost
            + '/ajax/cnmv-acknowledgement',

        /**
         * @see _common/js/popup-object.js:79
         */
        onInit: function onInit() {
            $Popup.copyBtnElement = $Popup.contentElement.querySelector($Popup.copyBtnSelector);
            $Popup.copyTextElement = $Popup.contentElement.querySelector($Popup.copyTextSelector);
            $Popup.dataInputElement = $Popup.contentElement.querySelector($Popup.dataInputSelector);

            if ($Popup.copyBtnElement && $Popup.copyTextElement && $Popup.dataInputElement) {
                $utils.addEventHandler($Popup.copyBtnElement, 'click',
                    function(event) {
                        event.preventDefault();
                        event.stopPropagation();

                        $Popup.dataInputElement.value = $Popup.copyTextElement.innerHTML;

                        $Popup.hideNotifyBlock();
                    }
                );
            }
        },

        /**
         * @see _common/js/popup-object.js:546
         *
         /**
         * @param {FormData} formData
         * @returns {boolean}
         */
        formDataValidate: function formDataValidate(formData) {
            if ($Popup.dataInputElement) {
                var sendValue = $Popup.dataInputElement.value;

                if (sendValue.length > 0 && sendValue.length < 256) {
                    return true;
                }

                $Popup.notifyInputField($Popup.dataInputElement, false);
                $Popup.showNotifyInputField($Popup.dataInputElement);
            }

            return false;
        }

    }
);