/**
 * Denne klasse indeholder metoder som kan bruges til at vise notifikationer på siden
 * Indeholder success, fejl og advarsel
 */
var NotificationControl = {
    _successNotification: window.createNotification({
        closeOnClick: true,
        displayCloseButton: false,

        // nfc-top-left
        // nfc-bottom-right
        // nfc-bottom-left
        positionClass: 'nfc-bottom-left',

        // callback
        onclick: false,

        showDuration: 3500,
        // success, info, warning, error, and none
        theme: 'success'
    }),
    _warningNotification: window.createNotification({
        closeOnClick: true,
        displayCloseButton: false,

        // nfc-top-left
        // nfc-bottom-right
        // nfc-bottom-left
        positionClass: 'nfc-bottom-left',

        // callback
        onclick: false,

        showDuration: 3500,
        // success, info, warning, error, and none
        theme: 'warning'
    }),
    _errorNotification: window.createNotification({
        closeOnClick: true,
        displayCloseButton: false,

        // nfc-top-left
        // nfc-bottom-right
        // nfc-bottom-left
        positionClass: 'nfc-bottom-left',

        // callback
        onclick: false,

        showDuration: 3500,
        // success, info, warning, error, and none
        theme: 'error'
    }),

    /**
     * Viser en succes besked
     * @param title
     * @param message
     */
    success: function (title, message) {
        this._successNotification({title, message});
    },

    /**
     * Viser en advarsel besked
     * @param title
     * @param message
     */
    warning: function (title, message) {
        this._warningNotification({title, message});
    },

    /**
     * Viser en fejl besked
     * @param title
     * @param message
     */
    error: function (title, message) {
        this._errorNotification({title, message});
    }
};

// Kunne desværre ikke bruge JavaScript ES6 klasser, det virker ikke på iPhone

// class NotificationControl {
//     static successNotification = window.createNotification({
//         closeOnClick: true,
//         displayCloseButton: false,
//
//         // nfc-top-left
//         // nfc-bottom-right
//         // nfc-bottom-left
//         positionClass: 'nfc-bottom-left',
//
//         // callback
//         onclick: false,
//
//         showDuration: 3500,
//         // success, info, warning, error, and none
//         theme: 'success'
//     });
//
//     static warningNotification = window.createNotification({
//         closeOnClick: true,
//         displayCloseButton: false,
//
//         // nfc-top-left
//         // nfc-bottom-right
//         // nfc-bottom-left
//         positionClass: 'nfc-bottom-left',
//
//         // callback
//         onclick: false,
//
//         showDuration: 3500,
//         // success, info, warning, error, and none
//         theme: 'warning'
//     });
//
//     static errorNotification = window.createNotification({
//         closeOnClick: true,
//         displayCloseButton: false,
//
//         // nfc-top-left
//         // nfc-bottom-right
//         // nfc-bottom-left
//         positionClass: 'nfc-bottom-left',
//
//         // callback
//         onclick: false,
//
//         showDuration: 3500,
//         // success, info, warning, error, and none
//         theme: 'error'
//     });
//
//     /**
//      * Viser en succes besked
//      * @param title
//      * @param message
//      */
//     static success(title, message) {
//         this.successNotification({title, message});
//     }
//
//     /**
//      * Viser en advarsel besked
//      * @param title
//      * @param message
//      */
//     static warning(title, message) {
//         this.warningNotification({title, message});
//     }
//
//     /**
//      * Viser en fejl besked
//      * @param title
//      * @param message
//      */
//     static error(title, message) {
//         this.errorNotification({title, message});
//     }
// }