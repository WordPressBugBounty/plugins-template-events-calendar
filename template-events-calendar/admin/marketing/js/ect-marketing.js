(function ($) {

    /**
     * Dismiss Notice
     */
    $(document).on('click', '.notice.is-dismissible .notice-dismiss', function (e) {
        e.preventDefault();

        var $notice = $(this).closest('.notice'); // Get the clicked notice
        var nonce = $notice.data('nonce');
        var notice = $notice.data('notice');

        if (!nonce || !notice) {
            return;
        }

        $.post(ajaxurl, {
            action: 'ect_dismiss_notice',
            nonce: nonce,
            notice: notice
        }, function (response) {
            if (response.success) {
                $notice.fadeOut();
            }
        });
    });


    /**
     * Install Elementor + Divi Plugins separately
     */
    function installPlugin(button, slug) {
        var $button = $(button);
        var nonce = $button.data('nonce');

        if (!slug || !nonce) {
            return;
        }

        $button.text('Installing...').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'ect_install_plugin',
            slug: slug,
            _wpnonce: nonce
        }, function (response) {
            if (response.success) {
                $button.text('Activated')
                    .addClass('disabled')
                    .prop('disabled', true);

                $('.ect-tec-notice-divi .ect-notice-widget')
                    .text('Events Modules for Divi is now active! Design your Events page with Divi.');
            } else {
                $button.text('Failed! Try Again').prop('disabled', false);
            }
        });
    }

    function getPluginSlug(plugin) {
        const slugs = {
            'events-calendar-modules-for-divi': 'events-calendar-modules-for-divi'
        };

        return slugs[plugin] || plugin;
    }


    if (typeof elementor !== 'undefined' && elementor) {
        var twaeControlDone = false;

        function runTwaeElementorInit() {
            if (twaeControlDone) return;
            if (!elementor.addControlView || !elementor.modules || !elementor.modules.controls) return;
            twaeControlDone = true;
            console.log('elementor:init');
            var callbackfunction = elementor.modules.controls.BaseData.extend({
                onRender: function (data) {
                    if (!data.el) return;
                    var customNotice = data.el.querySelector('.ect-tec-notice-divi');
                    if (!customNotice) return;
                    var installBtns = customNotice.querySelectorAll('button.ect-install-plugin');
                    if (installBtns.length === 0) return;
                    installBtns.forEach(function (btn) {
                        var installSlug = btn.getAttribute('data-plugin');
                        var pluginSlug = getPluginSlug(installSlug);

                        btn.addEventListener('click', function (event) {
                            event.preventDefault();
                            installPlugin(jQuery(btn), pluginSlug);
                        });
                    });
                },
            });
            elementor.addControlView('raw_html', callbackfunction);
        }
        $(window).on('elementor:init', runTwaeElementorInit);
        if (typeof window.addEventListener === 'function') {
            window.addEventListener('elementor/init', runTwaeElementorInit);
        }
        if (elementor.addControlView && elementor.modules && elementor.modules.controls) {
            setTimeout(runTwaeElementorInit, 0);
        }
    } else {
        $(document).ready(function ($) {
            const customNotice = $('.ect-tec-notice-divi');
            if (customNotice.length === 0) return;

            const installBtns = customNotice.find('button.ect-install-plugin');
            if (installBtns.length === 0) return;

            installBtns.each(function () {
                const btn = this;
                const installSlug = btn.getAttribute('data-plugin');
                const pluginSlug = getPluginSlug(installSlug);

                $(btn).on('click', function (e) {
                    e.preventDefault();
                    if (pluginSlug) {
                        installPlugin($(btn), pluginSlug);
                    }
                });
            });
        });
    }

})(jQuery);