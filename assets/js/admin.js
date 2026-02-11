/**
 * Ethileo Events Pro - Admin JavaScript
 *
 * @package Ethileo\EventsPro
 */

(function ($) {
    'use strict';

    const EthileoAdmin = {
        init() {
            this.bindEvents();
            this.initDatePicker();
        },

        bindEvents() {
            $(document).on('click', '.ethileo-delete-btn', this.handleDelete.bind(this));
            $(document).on('submit', '.ethileo-form', this.handleFormSubmit.bind(this));
        },

        handleDelete(e) {
            e.preventDefault();
            
            if (!confirm(ethileoEventsProAdmin.i18n.confirmDelete)) {
                return;
            }

            const $btn = $(e.currentTarget);
            const id = $btn.data('id');
            const type = $btn.data('type');

            $.ajax({
                url: ethileoEventsProAdmin.ajaxUrl,
                method: 'POST',
                data: {
                    action: `ethileo_delete_${type}`,
                    id: id,
                    nonce: ethileoEventsProAdmin.nonce
                },
                success(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut();
                    } else {
                        alert(ethileoEventsProAdmin.i18n.error);
                    }
                }
            });
        },

        handleFormSubmit(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const formData = new FormData($form[0]);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect;
                    }
                }
            });
        },

        initDatePicker() {
            if ($.fn.datepicker) {
                $('.ethileo-datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: 0
                });
            }
        }
    };

    $(document).ready(() => EthileoAdmin.init());

})(jQuery);
