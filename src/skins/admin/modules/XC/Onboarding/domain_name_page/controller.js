/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Onboarding Domain name page controller
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

jQuery(function () {
    let form = jQuery('.domain-name-page .send-form form').eq(0);
    form.bind(
        'state-changed',
        function () {
            jQuery(this).find('button.send-btn').commonController('enable');
        }
    );
    form.bind(
        'state-initial',
        function () {
            jQuery(this).find('button.send-btn').commonController('disable');
        }
    );
});
