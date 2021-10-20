'use strict';

/**
 *  Popup Object Class
 */
window.$Popup = {
    Id: null,

    eventTypeId: null,
    htmlId: null,
    contentElement: null,
    submitFormUrl: null,
    preview: null,

    leadRequestUrl: GLOBAL.mainSecureProtocol
        + '://' + GLOBAL.popuperHost
        + '/ajax/lead-request',

    errorClass: 'error',
    successClass: 'success',

    notify: {
        selector: '.popup-errors',
        element: null
    },

    form: {
        selector: 'form',
        element: null
    },

    buttonTypes: {
        accept: {
            selector: 'button[data-button-accept]',
            click: 'onAcceptButtonClick',
            submit: true
        },
        decline: {
            selector: 'button[data-button-decline]',
            click: 'onDeclineButtonClick',
            submit: false
        },
        neutral: {
            selector: 'button[data-button-neutral]',
            click: 'onNeutralButtonClick',
            submit: false
        }
    },

    buttonActions:{
        handle: function handle(buttonElement){
            $PopupsManager.showPreloader();

            this.collectForButton(buttonElement).reduce(
                /**
                 * @param {Promise} promise
                 * @param {function} f
                 * @returns {Promise}
                 */
                function(promise, f) {
                    return promise.then(
                        function() {return new Promise(f);}
                    );
                },
                Promise.resolve(),
            ).then(
                function() {
                    $PopupsManager.hidePreloader();
                },
            ).catch(
                function(e) {
                    console.log(e);
                },
            );
        },

        /**
         * @param buttonElement
         * @returns {[]}
         */
        collectForButton: function collectForButton(buttonElement) {
            var actions = [];

            for(var attribute in this.actionHandlers) {
                if (buttonElement.hasAttribute(attribute)) {
                    actions.push(this.actionHandlers[attribute].bind(buttonElement));
                }
            }

            return actions;
        },

        /**
         * {{actionAttribute: actionHandler(resolve, reject)}}
         */
        actionHandlers:{
            /**
             * @format attribute: function of Promise by type:
             * @function(resolve, reject)
             *      call reject to stop further handling another actions
             *      or resolve to next
             *
             * all handlers will bind to buttonElement (as context)
             * so it be available as this inside
             * @see popup-object.js:84
             */

            /**
             * !!! ORDER OF ACTIONS IS IMPORTANT
             */

             /**
             * Lead request action handler
             *
             * @param {function} resolve
             * @param {function} reject
             */
            'data-lead-request': function leadRequestAction(resolve, reject) {
                var buttonElement = this;
                var leadRequestId = buttonElement.getAttribute('data-lead-request');

                $Popup.postAjaxRequest(
                    $Popup.leadRequestUrl,
                    {
                        requestTypeId: leadRequestId,
                        popupId: $Popup.Id,
                        address: window.location.href,

                    },
                ).then(
                    function(response) {
                        if (
                            $Popup.hasOwnProperty('onSendLeadRequestComplete')
                            && $utils.isFunction($Popup.onSendLeadRequestComplete)
                        ) {
                            $Popup.onSendLeadRequestComplete(response, buttonElement);
                        }

                        if(response.success) {
                            return resolve();
                        }

                        reject(response);
                    },
                ).catch(
                    function(e) {
                        reject(e);
                    },
                );
            },

            /**
             * Redirect action handler
             *
             * @param {function} resolve
             * @param {function} reject
             */
            'data-redirect': function redirectAction(resolve, reject) {
                var buttonElement = this;
                var buttonLink = buttonElement.getAttribute('data-redirect');

                if (buttonLink && $utils.isValidRedirectUrl(buttonLink)) {
                    window.location.href = buttonLink;

                    return reject();
                }

                return resolve();
            },

            /**
             * Popup close action handler
             *
             * @param {function} resolve
             * @param {function} reject
             */
            'data-popup-close': function closeAction(resolve, reject) {
                $Popup.close();

                return reject();
            },
        },
    },

    request: {
        readyState: 4
    },

    /**
     * @param settings
     */
    init: function init(settings) {
        this.Id = settings.popupId;
        this.eventTypeId = settings.typeId;
        this.preview = settings.preview;

        if ($utils.isString(settings.htmlId) && settings.htmlId.length) {
            this.htmlId = settings.htmlId;
        }

        this.initContentElement();
        this.initNotify();
        this.initForm();
        this.initButtons();

        if (this.hasOwnProperty('onInitDisplaySettings') && $utils.isFunction(this.onInitDisplaySettings)) {
            this.onInitDisplaySettings();
        }

        if (this.hasOwnProperty('onInitOverlay') && $utils.isFunction(this.onInitOverlay)) {
            this.onInitOverlay();
        }

        if (this.hasOwnProperty('onInit') && $utils.isFunction(this.onInit)) {
            this.onInit();
        }

        $PopupsManager.showPopup();
    },

    /**
     *
     */
    initContentElement: function initContentElement() {
        this.contentElement = $PopupsManager.getContentElement();

        if ($utils.isString(this.htmlId) && this.htmlId.length) {
            this.contentElement.id = this.htmlId;
        }
    },

    /**
     *
     */
    initNotify: function initNotify() {
        this.notify.element = this.contentElement.querySelector(this.notify.selector);
    },

    /**
     *
     */
    initForm: function initForm() {
        this.form.element = this.contentElement.querySelector(this.form.selector);

        if (this.form.element) {
            var eventHandler = function eventHandler(event) {
                event.preventDefault();
                event.stopPropagation();

                return false;
            };

            $utils.addEventHandler(this.form.element, 'submit', eventHandler);
        }
    },

    /**
     *
     */
    initButtons: function initButtons() {
        /**
         * @param {{submit: boolean, selector: string, click: string}} buttonType
         */
        var buttonType;

        for (buttonType in this.buttonTypes) {
            this.initButtonElement(this.buttonTypes[buttonType]);
        }
    },

    /**
     * @param {{submit: boolean, link: *, selector: string, click: string, element: *}} buttonType
     */
    initButtonElement: function initButtonElement(buttonType) {
        var buttonElements = this.contentElement.querySelectorAll(buttonType.selector);

        if (buttonElements && buttonElements.length) {
            for (var _i = 0; _i < buttonElements.length; _i++) {
                var buttonElement = buttonElements[_i];
                this.initButtonHandler(buttonType, buttonElement);
            }
        }
    },

    /**
     * @param {{submit: boolean, link: *, selector: string, click: string, element: *}} buttonType
     * @param buttonElement
     */
    initButtonHandler: function initButtonHandler(buttonType, buttonElement) {
        if (buttonElement) {
            if (this.form.element && buttonType.submit && this.submitFormUrl) {
                $utils.addEventHandler(buttonElement, 'click',
                    function(event) {
                        event.preventDefault();
                        event.stopPropagation();

                        $Popup.submitForm(this);

                        return false;
                    }
                );
            } else {
                $utils.addEventHandler(buttonElement, 'click',
                    function(event) {
                        if (
                            $Popup.hasOwnProperty(buttonType.click)
                            && $utils.isFunction($Popup[buttonType.click])
                        ) {
                            $Popup[buttonType.click].call(this, event);
                        }

                        if (!event.defaultPrevented) {
                            event.preventDefault();
                            event.stopPropagation();

                            $Popup.handleMainButtonActions(this);
                        }

                        return false;
                    }
                );
            }
        }
    },

    /** hide messages */
    hideNotifyBlock: function hideNotifyBlock() {
        if (this.notify.element) {
            $PopupsManager.hideElement(this.notify.element);
        }

        var inputFields = this.contentElement.querySelectorAll('input[data-notify]');

        if (inputFields && inputFields.length) {
            for (var _i = 0; _i < inputFields.length; _i++) {
                var inputField = inputFields[_i];

                if (inputField.hasAttribute('data-notify')) {
                    var _notify = inputField.getAttribute('data-notify');

                    if (_notify.trim().length) {
                        inputField.classList.remove(_notify.trim());
                    }
                }
            }
        }

        if (this.hasOwnProperty('onHideNotifyMessages') && $utils.isFunction(this.onHideNotifyMessages)) {
            this.onHideNotifyMessages();
        }
    },

    /**
     * clear messages
     */
    clearNotifyBlock: function clearNotifyBlock() {
        if (this.notify.element) {
            this.notify.element.innerHTML = '';
        }

        var inputFields = this.contentElement.querySelectorAll('input[data-notify]');

        if (inputFields && inputFields.length) {
            for (var _i = 0; _i < inputFields.length; _i++) {
                var inputField = inputFields[_i];
                this.clearNotifyInputField(inputField);
            }
        }
    },

    /**
     * @param inputField
     */
    clearNotifyInputField: function clearNotifyInputField(inputField) {
        if (inputField && inputField.hasAttribute('data-notify')) {
            var _notify = inputField.getAttribute('data-notify');

            if (_notify.trim().length) {
                inputField.classList.remove(_notify.trim());
            }

            inputField.removeAttribute('data-notify');
        }
    },

    /**
     * show messages
     */
    showNotifyBlock: function showNotifyBlock() {
        if (this.notify.element && this.notify.element.innerHTML.trim().length) {
            $PopupsManager.showElement(this.notify.element);
        }

        var inputFields = this.contentElement.querySelectorAll('input[data-notify]');

        if (inputFields && inputFields.length) {
            for (var _i = 0; _i < inputFields.length; _i++) {
                var inputField = inputFields[_i];

                this.showNotifyInputField(inputField);
            }
        }
    },

    /**
     * @param inputField
     */
    showNotifyInputField: function showNotifyInputField(inputField) {
        if (inputField && inputField.hasAttribute('data-notify')) {
            var _notify = inputField.getAttribute('data-notify');

            if (_notify.trim().length) {
                inputField.classList.add(_notify.trim());
            }
        }
    },

    /**
     * add error message
     *
     * @param fieldName
     * @param message
     * @param success
     */
    putNotifyMessage: function putNotifyMessage(fieldName, message, success) {
        if (this.notify.element && $utils.isString(message) && message.length) {
            var notifySpan = document.createElement('span');

            notifySpan.classList.add(success ? this.successClass : this.errorClass);
            notifySpan.innerHTML = message;

            this.notify.element.appendChild(notifySpan);

            if ($utils.isString(fieldName) && fieldName.length) {
                var inputField = this.contentElement.querySelector('input[name=' + fieldName + ']');

                this.notifyInputField(inputField, success);
            }
        }
    },

    /**
     * @param inputField
     * @param success
     */
    notifyInputField: function notifyInputField(inputField, success) {
        if (inputField) {
            inputField.setAttribute('data-notify', success ? this.successClass : this.errorClass);
        }
    },

    /**
     * before ajax send request
     */
    beforeSend: function beforeSend() {
        this.hideNotifyBlock();
    },

    /**
     * @param {{}} messages
     * @param success
     */
    handleResponseMessages: function handleResponseMessages(messages, success) {
        this.clearNotifyBlock();

        if ($utils.isObject(messages)) {
            for (var fieldName in messages) {
                if (!success && this.hasOwnProperty('handleErrorsOnSubmit') &&
                    $utils.isFunction(this.handleErrorsOnSubmit)) {
                    this.handleErrorsOnSubmit(fieldName, messages[fieldName]);
                }

                this.putNotifyMessage(fieldName, messages[fieldName], success);
            }
        } else if (Array.isArray(messages) && messages.length) {
            for (var _i = 0; _i < messages.length; _i++) {
                var message = messages[_i];

                this.putNotifyMessage(null, message, success);
            }
        }

        this.showNotifyBlock();
    },

    /**
     * @param buttonElement
     */
    handleMainButtonActions: function handleMainButtonActions(buttonElement) {
        this.buttonActions.handle(buttonElement);
    },

    /**
     * @param {string} url
     * @param {{}} data
     * @returns Promise
     */
    postAjaxRequest: function postAjaxRequest(url, data) {
        /** set ajax loader */
        $PopupsManager.showPreloader();

        data = $utils.objectToFormData(data);

        if (this.request && this.request.readyState !== 4) {
            this.request.abort();
        }

        return new Promise(
            function(resolve, reject) {
                $Popup.request = $utils.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    crossDomain: true,
                    withCredentials: true,

                    complete: function complete(xhr) {
                        try {
                            if (xhr.readyState === xhr.DONE) {
                                var response = JSON.parse(xhr.responseText);
                            }
                        } catch (e) {
                            console.log(e);
                        }

                        if(!response) {
                            $PopupsManager.hidePreloader();

                            return reject(xhr);
                        }

                        if(!response.success){
                            $PopupsManager.hidePreloader();
                        }

                        return resolve(response);
                    },
                });
            },
        );
    },

    /**
     * @returns {boolean}
     */
    canSendData: function canSendData() {
        return true;
    },

    /**
     * @param {string} url
     * @param {{}} data
     * @param {HTMLButtonElement} buttonElement
     * @returns {boolean}
     */
    sendData: function sendData(url, data, buttonElement) {
        if (!this.canSendData()) {
            return false;
        }

        this.beforeSend();

        this.postAjaxRequest(url, data).then(
            function(response) {
                if ($Popup.hasOwnProperty('onSendDataComplete') && $utils.isFunction($Popup.onSendDataComplete)) {
                    $Popup.onSendDataComplete(response, buttonElement);
                }

                if (response.messages) {
                    $Popup.handleResponseMessages(response.messages, response.success);
                }

                if (response.success === true && buttonElement) {
                    $Popup.handleMainButtonActions(buttonElement);
                }
            },
        ).catch(
            function(e) {
                console.log(e);
            },
        );

        return false;
    },

    /**
     * @param buttonElement
     */
    submitForm: function submitForm(buttonElement) {
        if (this.submitFormUrl && this.form.element) {
            var formData = new FormData(this.form.element);

            if (this.hasOwnProperty('onSubmitForm') && $utils.isFunction(this.onSubmitForm)) {
                this.onSubmitForm(formData);
            }

            if (this.validateForm(formData)) {
                this.sendData(this.submitFormUrl, formData, buttonElement);
            }
        }
    },

    /**
     * @param {FormData} formData
     * @returns {boolean}
     */
    validateForm: function validateForm(formData) {
        if (this.hasOwnProperty('formDataValidate') && $utils.isFunction(this.formDataValidate)) {
            return this.formDataValidate(formData);
        }

        return true;
    },

    /**
     *
     */
    close: function close() {
        if (this.hasOwnProperty('beforeClose') && $utils.isFunction(this.beforeClose)) {
            this.beforeClose();
        }

        $PopupsManager.closePopup();
    }
};