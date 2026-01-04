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

            // Send tracking data via AJAX
            $.ajax({
                url: wpRlmAnalytics.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_rlm_track_click',
                    nonce: wpRlmAnalytics.nonce,
                    referral_link_id: referralLinkId,
                    post_id: postId,
                    referrer_url: referrerUrl
                },
                async: true // Don't block navigation
            });
        });
    });

})(jQuery);
