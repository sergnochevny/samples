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

    contentClickable: true,

    /**
     *
     */
    onInitDisplaySettings: function onInitDisplaySettings() {
        $utils.addEventHandler($Popup.contentElement, 'click',
            function(event) {
                if (!$utils.hasEventHandler(event.target, 'click')) {
                    event.preventDefault();

                    $Popup.close();

                    return false;
                }

                return true;
            }
        );
    }

});