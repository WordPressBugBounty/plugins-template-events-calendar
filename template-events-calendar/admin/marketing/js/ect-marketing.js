jQuery(document).ready(function ($) {

    /**
     * Dismiss Notice
     */
    $(document).on('click', '.notice.is-dismissible .notice-dismiss', function (e) {
        e.preventDefault();

        var $notice = $(this).closest('.notice'); // Get the clicked notice
        var nonce   = $notice.data('nonce');
        var notice  = $notice.data('notice');

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
    $(document).on('click', '.ect-tec-notice-divi .ect-install-plugin', function (e) {
        e.preventDefault();

        let button = $(this);
        let plugin = button.data('plugin');
        let nonce = button.data('nonce');
        
        if (!plugin) return;

        button.text('Installing...').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'ect_install_plugin',
            slug: plugin,
            _wpnonce: nonce
        }, function (response) {
            if (response.success) {
                button.text('Activated')
                    .addClass('disabled')
                    .prop('disabled', true);
    
                $('.ect-tec-notice-divi .ect-notice-widget')
                    .text('Events Modules for Divi is now active! Design your Events page with Divi.');
            } else {
                button.text('Failed! Try Again').prop('disabled', false);
            }
        });
    });

});
