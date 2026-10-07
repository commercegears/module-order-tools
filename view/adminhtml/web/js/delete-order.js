/**
 * Softaware Order Tools: "Delete Permanently" on the order page. Asks for confirmation in Magento's confirm dialog,
 * then sends a POST with the form key.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/dataPost'
], function ($, confirm, dataPost) {
    'use strict';

    return function (config, element) {
        $(element).on('click', function (event) {
            event.preventDefault();
            confirm({
                title: config.title,
                content: config.message,
                buttons: [{
                    text: config.cancelLabel,
                    class: 'action-secondary action-dismiss',
                    click: function (e) {
                        this.closeModal(e);
                    }
                }, {
                    text: config.confirmLabel,
                    class: 'action-primary action-accept',
                    click: function (e) {
                        this.closeModal(e, true);
                    }
                }],
                actions: {
                    confirm: function () {
                        dataPost().postData({action: config.url, data: {'order_id': config.orderId}});
                    }
                }
            });
        });
    };
});
