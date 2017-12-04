/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Paypal initialize Express Checkout on click 'Place order'
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

core.bind([
  'checkout.main.ready',
  'checkout.common.anyChange',
  'checkout.sections.payment.persist',
  'checkout.paymentTpl.loaded',
  'checkout.common.state.ready'
], function (event, controller) {
  if ($('.paypal-checkout-box').length > 0) {
    $('.review-step form.place .button-row').hide();
    $('form.place .paypal-ec-checkout').show();
    $('form.place .paypal-ec-checkout-credit').hide();

    //fastlane
    $('.checkout_fastlane_section-buttons form.place .checkout_fastlane_section-place_order').hide();
  } else if ($('.paypal-checkout-credit-box').length > 0) {
    $('.review-step form.place .button-row').hide();
    $('form.place .paypal-ec-checkout').hide();
    $('form.place .paypal-ec-checkout-credit').show();

    //fastlane
    $('.checkout_fastlane_section-buttons form.place .checkout_fastlane_section-place_order').hide();
  } else {
    $('.review-step form.place .button-row').show();
    $('form.place .paypal-ec-checkout').hide();
    $('form.place .paypal-ec-checkout-credit').hide();

    //fastlane
    $('.checkout_fastlane_section-buttons form.place .checkout_fastlane_section-place_order').show();
  }
});

core.bind('checkout.common.state.nonready', function (state) {
  $('form.place .paypal-ec-checkout').addClass('nonready');
});

core.bind('checkout.common.state.ready', function (state) {
  $('form.place .paypal-ec-checkout').removeClass('nonready');
});

