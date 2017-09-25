/* vim: set ts=4 sw=4 sts=4 et: */

/**
 * Braintree widget for OPC
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

core.bind(
    'load',
    function() {

        core.bind(
            'checkout.paymentTpl.postprocess',
            braintreePayment.init.bind(braintreePayment) 
        );

        core.bind(
            'resources.ready',
            braintreePayment.init()
        );

        braintreePayment.init();
    }
);

