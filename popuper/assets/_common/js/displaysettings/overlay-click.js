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

    overlayClickable: true,

    /**
     * @see _common/js/popup-object.js:75
     */
    onInitOverlay: function onInitOverlay() {
        var containerElement = $PopupsManager.getContainerElement();

        $utils.addEventHandler(containerElement, 'click',
            function(event) {
                if (event.target === this) {
                    event.preventDefault();

                    $Popup.close();

                    return false;
                }

                return true;
            }
        );
    }

});