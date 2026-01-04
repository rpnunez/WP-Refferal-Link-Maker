/**
 * Analytics tracking script
 *
 * @package WP_Referral_Link_Maker
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Track clicks on referral links with data-ref-link-id attribute
        $(document).on('click', 'a[data-ref-link-id]', function(e) {
            var $link = $(this);
            var referralLinkId = $link.data('ref-link-id');
            var postId = $link.data('post-id') || 0;
            var referrerUrl = window.location.href;

            var data = {
                action: 'wp_rlm_track_click',
                nonce: wpRlmAnalytics.nonce,
                referral_link_id: referralLinkId,
                post_id: postId,
                referrer_url: referrerUrl
            };

            // Use sendBeacon if available for better reliability
            if (navigator.sendBeacon) {
                var formData = new FormData();
                for (var key in data) {
                    formData.append(key, data[key]);
                }
                navigator.sendBeacon(wpRlmAnalytics.ajaxUrl, formData);
            } else {
                // Fallback to AJAX with async
                $.ajax({
                    url: wpRlmAnalytics.ajaxUrl,
                    type: 'POST',
                    data: data,
                    async: true
                });
            }
        });
    });

})(jQuery);
