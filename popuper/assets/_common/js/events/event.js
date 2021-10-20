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

        submitFormUrl: null,

        /**
         * @see _common/js/popup-object.js:79
         */
        onInit: function onInit() {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:71
         */
        onInitDisplaySettings: function onInitDisplaySettings() {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:75
         */
        onInitOverlay: function onInitOverlay() {
            /**
             * some own code here
             */
        },

        /**
         * this - context of button element
         *
         *!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * You must use event.preventDefault
         * when using sendData
         *!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         *
         * @see _common/js/popup-object.js:168
         */
        onAcceptButtonClick: function onAcceptButtonClick(event) {
            /**
             * some own code here
             */
        },

        /**
         * this - context of button element
         *
         *!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * You must use event.preventDefault
         * when using sendData
         *!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         *
         * @see _common/js/popup-object.js:168
         */
        onDeclineButtonClick: function onDeclineButtonClick(event) {
            /**
             * some own code here
             */
        },

        /**
         * this - context of button element
         *
         *!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * You must use event.preventDefault
         * when using sendData
         *!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         *
         *@see _common/js/popup-object.js:168
         */
        onNeutralButtonClick: function onNeutralButtonClick(event) {
            /**
             * some own code here
             */
        },

        /**
         *@see _common/js/popup-object.js:210
         */
        onHideNotifyMessages: function onHideNotifyMessages() {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:461
         *
         * @param {{}}response
         * @param buttonElement
         */
        onSendDataComplete: function onSendDataComplete(response, buttonElement) {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:412
         *
         * @param {{}}response
         * @param buttonElement
         */
        onSendLeadRequestComplete: function onSendLeadRequestComplete(response, buttonElement) {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:531
         *
         * @param {FormData} formData
         */
        onSubmitForm: function onSubmitForm(formData) {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:546
         *
         /**
         * @param {FormData} formData
         * @returns {boolean}
         */
        formDataValidate: function formDataValidate(formData) {
            /**
             * some own code here with return boolean
             */
            return true;
        },

        /**
         * @see _common/js/popup-object.js:330
         *
         * @param fieldName
         * @param message
         */
        handleErrorsOnSubmit: function handleErrorsOnSubmit(fieldName, message) {
            /**
             * some own code here
             */
        },

        /**
         * @see _common/js/popup-object.js:557
         */
        beforeClose: function beforeClose() {
            /**
             * some own code here
             */
        }
    }
);