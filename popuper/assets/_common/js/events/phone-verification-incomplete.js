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
      canSend: true,

      notify: {
        selector: '.input-errors',
        element: null
      },

      timer: null,

      resendDuration: 60,

      closeButtonSelector: '#popup-close-button',
      codeInputSelector: '#phone-verification-code-input',
      resendButtonSelector: '#phone-verification-resend-btn',
      nonMobileButtonSelector: '#phone-verification-non-mobile-btn',

      closeButtonElement: null,
      codeInputElement: null,
      resendButtonElement: null,
      nonMobileButtonElement: null,

      submitResendUrl: GLOBAL.mainSecureProtocol +
          '://' + GLOBAL.popuperHost +
          '/ajax/phone-verification-resend',
      submitCloseUrl: GLOBAL.mainSecureProtocol +
          '://' + GLOBAL.popuperHost +
          '/ajax/phone-verification-close',
      submitNonMobileUrl: GLOBAL.mainSecureProtocol +
          '://' + GLOBAL.popuperHost +
          '/ajax/phone-verification-non-mobile',
      submitFormUrl: GLOBAL.mainSecureProtocol +
          '://' + GLOBAL.popuperHost +
          '/ajax/phone-verification-check',

      /**
       * @see _common/js/popup-object.js:76
       */
      onInit: function onInit() {
        let containerElement = $PopupsManager.getContainerElement();

        $Popup.closeButtonElement = containerElement.querySelector(
            $Popup.closeButtonSelector);
        $Popup.codeInputElement = containerElement.querySelector(
            $Popup.codeInputSelector);
        $Popup.resendButtonElement = containerElement.querySelector(
            $Popup.resendButtonSelector);
        $Popup.nonMobileButtonElement = containerElement.querySelector(
            $Popup.nonMobileButtonSelector);

        if ($Popup.closeButtonElement) {
          $utils.dropEventHandlers($Popup.closeButtonElement, 'click');
          $utils.addEventHandler($Popup.closeButtonElement, 'click', function(event) {
            event.preventDefault();

            $Popup.sendData($Popup.submitCloseUrl, {}, this);
            $Popup.close();

            return false;
          });
        }

        if ($Popup.codeInputElement) {
          $utils.addEventHandler($Popup.codeInputElement, 'keydown', function(event) {
            return /^\d|.{2,}$/.test(event.key) || event.ctrlKey || event.metaKey;
          });
          $utils.addEventHandler($Popup.codeInputElement, 'keyup', function(event) {
            // Ctrl+V or Cmd+V
            if ((event.ctrlKey || event.metaKey) && event.keyCode === 86) {
              $Popup.codeInputElement.value = $Popup.codeInputElement.value.replace(new RegExp(/[^\d]/,'g'), '');
            }
              // The "Enter" key on the keyboard
              if (event.keyCode === 13) {
                  event.preventDefault();
                  $PopupsManager.getContainerElement().querySelector($Popup.buttonTypes.accept.selector).click();
              }
          });
        }

        if ($Popup.resendButtonElement) {
          $Popup.resendButtonElement.setAttribute('data-btn-title', $Popup.resendButtonElement.textContent);

          $utils.addEventHandler($Popup.resendButtonElement, 'click', function(event) {
            event.preventDefault();

            $Popup.hideNotifyBlock();
            $Popup.clearNotifyBlock();

            $Popup.sendData($Popup.submitResendUrl, {}, this);

            startCountdown($Popup.resendDuration, $Popup.resendButtonElement);
            function startCountdown(duration, element) {
              element.setAttribute('disabled', 'disabled');
              let timer = duration, minutes, seconds;

              $Popup.timer = setInterval(function() {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;

                element.textContent = '(' + minutes + ':' + seconds + ')';

                if (--timer < 0) {
                  element.textContent = element.getAttribute('data-btn-title');
                  element.removeAttribute('disabled');
                  clearInterval($Popup.timer);
                }
              }, 1000);
            }

            return false;
          });
        }

        if ($Popup.nonMobileButtonElement) {
          $utils.addEventHandler($Popup.nonMobileButtonElement, 'click', function(event) {
            event.preventDefault();

            $Popup.hideNotifyBlock();
            $Popup.clearNotifyBlock();

            $Popup.sendData($Popup.submitNonMobileUrl,{}, this);

            return false;
          });
        }
      },

      /**
       * @returns {boolean}
       * @see _common/js/popup-object.js:486
       */
      canSendData: function canSendData() {
        return !$Popup.preview && $Popup.canSend;
      },

      /**
       * @see _common/js/popup-object.js:461
       *
       * @param {{}}response
       * @param buttonElement
       */
      onSendDataComplete: function onSendDataComplete(response, buttonElement) {
        let submitBtn = $PopupsManager.getContainerElement().querySelector($Popup.buttonTypes.accept.selector);

        if (response.disableButtons) {
          $Popup.canSend = false;

          clearInterval($Popup.timer);
          $Popup.resendButtonElement.textContent = $Popup.resendButtonElement.getAttribute(
              'data-btn-title');
          $Popup.resendButtonElement.setAttribute('disabled', 'disabled');

          if ($Popup.nonMobileButtonElement) {
            $Popup.nonMobileButtonElement.setAttribute('disabled', 'disabled');
          }

          submitBtn.setAttribute('disabled', 'disabled');
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
        $Popup.hideNotifyBlock();
        if (!/^\d{4}$/.test($Popup.codeInputElement.value)) {
          $Popup.notifyInputField($Popup.codeInputElement, false);
          $Popup.showNotifyInputField($Popup.codeInputElement);
          return false;
        }

        return true;
      }
    },
);