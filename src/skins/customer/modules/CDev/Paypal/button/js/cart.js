/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Paypal In-Context cart
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

define('paypal_ec_checkout_cart_button_processor', ['paypal_ec_button_processors'], function (Processors) {
  Processors.push(function (element, state) {
    if (element.is('.pp-funding-credit')) {
      if (!state.funding) {
        state.funding = {};
      }

      state.funding.allowed = [ paypal.FUNDING.CREDIT ];
      state.tagline = true;
      state.size = 'responsive';
      state.tagline = true;
    }
  })
});