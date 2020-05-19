/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Onboarding setup tiles controller
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

jQuery(function () {
    var checkTilesVisibility = function() {
        let availableTiles = [
            '.onboarding-setup-tile[data-tile-type="payment_methods"]',
            '.onboarding-setup-tile[data-tile-type="shipping_methods"]',
            '.onboarding-setup-tile[data-tile-type="domain_name"]',
            '.onboarding-setup-tile[data-tile-type="templates_ad"]',
            '.onboarding-setup-tile[data-tile-type="addons_ad"]'
        ];

        let tilesCount = jQuery(availableTiles.join()).length;
        if (availableTiles.length - tilesCount >= 1) {
            jQuery('.onboarding-setup-tile[data-tile-type="templates_ad"]').removeClass('onboarding-tile-hidden');
        }
        if (availableTiles.length - tilesCount >= 2) {
            jQuery('.onboarding-setup-tile[data-tile-type="addons_ad"]').removeClass('onboarding-tile-hidden');
        }
        if (tilesCount === 0) {
            jQuery('.onboarding-setup-tiles').hide();
        }
    };

    checkTilesVisibility();

    jQuery('.onboarding-setup-tile .setup-tile-close').click(function () {
        let parent = jQuery(this).closest('.onboarding-setup-tile').hide();

        jQuery.cookie(parent.data('tile-type') + '_tileClosed', 1);

        setTimeout(function () {
            parent.remove();
            checkTilesVisibility();
        }, 0);
    });

    jQuery('.onboarding-setup-tile .setup-tile-button button').click(function () {
        let parent = jQuery(this).closest('.onboarding-setup-tile');
        jQuery.cookie(parent.data('tile-type') + '_tileClosed', 1);
    });
});
