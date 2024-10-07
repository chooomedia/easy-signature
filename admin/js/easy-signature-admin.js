(function( $ ) {
    'use strict';

    $(function() {
        // Initialisierung des Easy Signature Admin-Bereichs
        var EasySignatureAdmin = {
            init: function() {
                this.bindEvents();
                this.initColorPicker();
                this.initMediaUploader();
                this.initSortableFields();
                this.initPreviewGenerator();
            },

            bindEvents: function() {
                $('#easy-signature-form').on('submit', this.handleFormSubmit);
                $('.easy-signature-toggle').on('click', this.handleToggle);
                $('#add-social-field').on('click', this.addSocialField);
            },

            initColorPicker: function() {
                $('.easy-signature-color-picker').wpColorPicker();
            },

            initMediaUploader: function() {
                var file_frame;
                $('.upload-image-button').on('click', function(event) {
                    event.preventDefault();
                    var button = $(this);

                    if (file_frame) {
                        file_frame.open();
                        return;
                    }

                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: 'Wählen Sie ein Bild',
                        button: {
                            text: 'Bild verwenden'
                        },
                        multiple: false
                    });

                    file_frame.on('select', function() {
                        var attachment = file_frame.state().get('selection').first().toJSON();
                        button.siblings('.image-preview').attr('src', attachment.url);
                        button.siblings('.image-id').val(attachment.id);
                    });

                    file_frame.open();
                });
            },

            initSortableFields: function() {
                $('.easy-signature-sortable').sortable({
                    handle: '.easy-signature-sort-handle',
                    update: function(event, ui) {
                        EasySignatureAdmin.updateFieldOrder();
                    }
                });
            },

            initPreviewGenerator: function() {
                $('#generate-preview').on('click', function(e) {
                    e.preventDefault();
                    var formData = $('#easy-signature-form').serialize();
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'easy_signature_generate_preview',
                            form_data: formData
                        },
                        success: function(response) {
                            $('#signature-preview').html(response);
                        }
                    });
                });
            },

            handleFormSubmit: function(e) {
                e.preventDefault();
                var form = $(this);
                var submitButton = form.find('input[type="submit"]');
                
                submitButton.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            EasySignatureAdmin.showNotice('Einstellungen erfolgreich gespeichert.', 'success');
                        } else {
                            EasySignatureAdmin.showNotice('Fehler beim Speichern der Einstellungen.', 'error');
                        }
                    },
                    error: function() {
                        EasySignatureAdmin.showNotice('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.', 'error');
                    },
                    complete: function() {
                        submitButton.prop('disabled', false);
                    }
                });
            },

            handleToggle: function() {
                var target = $(this).data('target');
                $('#' + target).slideToggle();
            },

            addSocialField: function(e) {
                e.preventDefault();
                var template = $('#social-field-template').html();
                var newField = $(template);
                newField.find('.easy-signature-color-picker').wpColorPicker();
                $('.social-fields-container').append(newField);
                EasySignatureAdmin.updateFieldOrder();
            },

            updateFieldOrder: function() {
                $('.easy-signature-sortable .easy-signature-field').each(function(index) {
                    $(this).find('.field-order').val(index + 1);
                });
            },

            showNotice: function(message, type) {
                var noticeClass = 'notice ';
                noticeClass += (type === 'error') ? 'notice-error' : 'notice-success';
                
                var notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');
                $('.easy-signature-notices').html(notice).show();
                
                setTimeout(function() {
                    notice.fadeOut();
                }, 3000);
            }
        };

        // Initialisierung
        EasySignatureAdmin.init();
    });

})( jQuery );