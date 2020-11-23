class NotificationControl {
    static successNotification = window.createNotification({
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
    });

    static warningNotification = window.createNotification({
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
    });

    static errorNotification = window.createNotification({
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
    });

    static success(title, message) {
        this.successNotification({title, message});
    }

    static warning(title, message) {
        this.warningNotification({title, message});
    }

    static error(title, message) {
        this.errorNotification({title, message});
    }
}