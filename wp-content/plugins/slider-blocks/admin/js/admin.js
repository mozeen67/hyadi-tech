/* eslint-disable no-undef */
/**
 * GutSlider Admin JavaScript
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Handle block toggle
        $('.block-toggle').on('change', function () {
            const $toggle = $(this);
            const blockKey = $toggle.data('key');
            // const blockName = $toggle.data('block');
            const isChecked = $toggle.is(':checked');

            // Disable toggle during request
            $toggle.prop('disabled', true);

            // Show loading indicator
            const $card = $toggle.closest('.gutslider-block-card');
            $card.css('opacity', '0.6');

            // Send AJAX request
            $.ajax({
                url: gutslider.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gutslider_toggle_block',
                    nonce: gutslider.nonce,
                    block_key: blockKey,
                    status: isChecked
                },
                success(response) {
                    if (response.success) {
                        // Show success notification
                        showNotification('success', response.data.message);
                    } else {
                        // Revert toggle on error
                        $toggle.prop('checked', !isChecked);
                        showNotification('error', response.data.message);
                    }
                },
                error() {
                    // Revert toggle on error
                    $toggle.prop('checked', !isChecked);
                    showNotification('error', 'An error occurred. Please try again.');
                },
                complete() {
                    // Re-enable toggle and remove loading state
                    $toggle.prop('disabled', false);
                    $card.css('opacity', '1');
                }
            });
        });

        // Show notification function
        function showNotification(type, message) {
            const notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notification = $('<div>', {
                class: `notice ${notificationClass} is-dismissible gutslider-notification`,
                html: `<p>${message}</p>`
            });

            // Add close button functionality
            $notification.append(
                '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>'
            );

            // Insert notification
            if ($('.gutslider-admin-wrap').length) {
                $('.gutslider-admin-wrap').prepend($notification);
            } else {
                $('.wrap').prepend($notification);
            }

            // Handle dismiss button
            $notification.find('.notice-dismiss').on('click', function () {
                $notification.fadeOut(300, function () {
                    $(this).remove();
                });
            });

            // Auto remove after 3 seconds
            setTimeout(function () {
                $notification.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 1000);
        }

        // Handle pro block clicks
        $('.pro-block .toggle-switch input:disabled').on('click', function (e) {
            e.preventDefault();
            showProUpgradeNotice();
        });

        $('.pro-block').on('click', function (e) {
            if ($(e.target).closest('.toggle-switch, .demo-link').length === 0) {
                showProUpgradeNotice();
            }
        });

        // Show pro upgrade notice
        function showProUpgradeNotice() {
            if (!gutslider.isPro) {
                const upgradeUrl = 'https://gutslider.com/pricing/';
                const message = `This is a Pro feature. <a href="${upgradeUrl}" target="_blank" rel="noopener noreferrer">Upgrade to Pro</a> to unlock this block.`;
                showNotification('error', message);
            }
        }
    });
})(jQuery);
