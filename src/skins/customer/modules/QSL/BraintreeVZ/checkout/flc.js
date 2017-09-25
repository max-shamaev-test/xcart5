/* vim: set ts=4 sw=4 sts=4 et: */

/**
 * Braintree widget for FLC 
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

core.bind(
    'load',
    function() {

        core.bind(
            'checkout.paymentTpl.postprocess',
            braintreePayment.init()
        );

        core.bind(
            'resources.ready',
            braintreePayment.init()
        );

        core.bind(
            'checkout.common.ready',
            function(event, state) {

                state.state = braintreePayment.submitForm(event);
            }
        );

        core.bind(
            'fastlane_section_switched',
            function (event, data) {

                if (
                    'undefined' != typeof data.newSection
                    && 'payment' == data.newSection.name
                ) {
                    braintreePayment.init();
                }
            }
        );
    }
);

