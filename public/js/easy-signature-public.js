(function($) {
    'use strict';

    // Öffentliche JavaScript-Funktionen hier

})(jQuery);(function($) {
    'use strict';

    // Easy Signature Public Object
    var EasySignaturePublic = {
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initCopyToClipboard();
            this.initLazyLoading();
        },

        cacheDom: function() {
            this.$document = $(document);
            this.$window = $(window);
            this.$signatureContainers = $('.easy-signature-container');
        },

        bindEvents: function() {
            this.$document.on('click', '.easy-signature-toggle', this.toggleSignature);
            this.$window.on('resize', this.handleResize.bind(this));
            this.$document.on('click', '.easy-signature-social a', this.trackSocialClick);
        },

        initCopyToClipboard: function() {
            if (typeof ClipboardJS !== 'undefined') {
                new ClipboardJS('.easy-signature-copy-btn', {
                    text: function(trigger) {
                        return $(trigger).siblings('.easy-signature-container').text();
                    }
                }).on('success', function(e) {
                    EasySignaturePublic.showNotification('Signatur in die Zwischenablage kopiert!');
                    e.clearSelection();
                });
            }
        },

        initLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove("lazy");
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });

                let lazyImages = document.querySelectorAll("img.lazy");
                lazyImages.forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            }
        },

        toggleSignature: function(e) {
            e.preventDefault();
            var $target = $($(this).data('target'));
            $target.slideToggle();
        },

        handleResize: function() {
            // Hier können Sie Logik für responsive Anpassungen hinzufügen
            this.adjustSignatureSizes();
        },

        adjustSignatureSizes: function() {
            this.$signatureContainers.each(function() {
                var $container = $(this);
                if ($container.width() < 300) {
                    $container.addClass('easy-signature-compact');
                } else {
                    $container.removeClass('easy-signature-compact');
                }
            });
        },

        trackSocialClick: function(e) {
            if (typeof ga !== 'undefined') {
                ga('send', 'event', 'Easy Signature', 'Social Click', $(this).attr('aria-label'));
            }
        },

        showNotification: function(message) {
            var $notification = $('<div class="easy-signature-notification">' + message + '</div>');
            $('body').append($notification);
            setTimeout(function() {
                $notification.addClass('show');
                setTimeout(function() {
                    $notification.removeClass('show');
                    setTimeout(function() {
                        $notification.remove();
                    }, 300);
                }, 2000);
            }, 100);
        },

        loadSignature: function(userId, containerId) {
            $.ajax({
                url: easy_signature_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'load_signature',
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        $('#' + containerId).html(response.data.html);
                        EasySignaturePublic.initLazyLoading();
                    } else {
                        console.error('Failed to load signature:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax request failed:', error);
                }
            });
        }
    };

    // Initialize on document ready
    $(function() {
        EasySignaturePublic.init();
    });

    // Make loadSignature method available globally
    window.EasySignaturePublic = EasySignaturePublic;

})(jQuery);