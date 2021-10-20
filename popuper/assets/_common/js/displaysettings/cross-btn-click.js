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
Object.assign($Popup, {

    closeButtonElement: null,

    closeButtonSelector: '#popup-close-button',

    /**
     *
     */
    onInitDisplaySettings: function onInitDisplaySettings() {
        var containerElement = $PopupsManager.getContainerElement();

        $Popup.closeButtonElement = containerElement.querySelector($Popup.closeButtonSelector);

        if ($Popup.closeButtonElement) {
            $utils.addEventHandler($Popup.closeButtonElement, 'click',
                function(event) {
                    event.preventDefault();

                    $Popup.close();

                    return false;
                }
            );
        }
    }

});