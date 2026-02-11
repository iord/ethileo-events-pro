/**
 * Ethileo Events Pro - Frontend JavaScript
 *
 * @package Ethileo\EventsPro
 */

(function ($) {
    'use strict';

    const EthileoFrontend = {
        init() {
            this.bindEvents();
            this.initPhotoUpload();
        },

        bindEvents() {
            $(document).on('submit', '.ethileo-rsvp-form', this.handleRSVP.bind(this));
        },

        handleRSVP(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const formData = new FormData($form[0]);

            $.ajax({
                url: ethileoEventsPro.restUrl + '/rsvp',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ethileoEventsPro.nonce);
                },
                success(response) {
                    if (response.success) {
                        $form.html('<div class="ethileo-success">Thank you for your RSVP!</div>');
                    }
                }
            });
        },

        initPhotoUpload() {
            // Photo upload functionality
            if (typeof Dropzone !== 'undefined') {
                new Dropzone('.ethileo-photo-upload', {
                    url: ethileoEventsPro.restUrl + '/photos',
                    headers: {
                        'X-WP-Nonce': ethileoEventsPro.nonce
                    }
                });
            }
        }
    };

    $(document).ready(() => EthileoFrontend.init());

})(jQuery);
