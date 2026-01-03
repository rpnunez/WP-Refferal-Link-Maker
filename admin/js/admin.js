/**
 * Admin-specific JavaScript for WP Referral Link Maker
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/admin/js
 */

(function( $ ) {
    'use strict';

    $(function() {
        // Admin initialization code here
        
        // Example: Add confirmation for enabling auto-updates
        $('input[name="wp_referral_link_maker_settings[auto_update_enabled]"]').on('change', function() {
            if ($(this).is(':checked')) {
                var confirmed = confirm('Enabling auto-updates will automatically process posts with referral links. Are you sure?');
                if (!confirmed) {
                    $(this).prop('checked', false);
                }
            }
        });

        // Example: Validate API key format
        $('input[name="wp_referral_link_maker_settings[api_key]"]').on('blur', function() {
            var apiKey = $(this).val().trim();
            if (apiKey.length > 0 && apiKey.length < 10) {
                alert('API key seems too short. Please verify your API key.');
            }
        });
    });

})( jQuery );
