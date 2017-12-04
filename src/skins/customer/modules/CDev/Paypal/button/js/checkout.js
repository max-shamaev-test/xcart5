/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Paypal In-Context checkout
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

define('paypal_ec_checkout_button_processor', ['paypal_ec_button_processors'], function (Processors) {
  Processors.push(function (element, state) {
    if (element.is('.paypal-ec-checkout')) {
      state.size = 'medium';

      state.additionalUrlParams.ignoreCheckout = true;

      state.payment = function () {
        var dfr = $.Deferred();

        var form = element.closest('form').get(0);
        var actionElement = jQuery(form).find('input[name="action"]');
        var oldAction = actionElement.val();
        actionElement.val('setOrderNote');

        var notes = $('textarea[name="notes"]').clone();
        notes.appendTo(form);

        form.submitBackground();
        actionElement.val(oldAction);
        notes.remove();
        paypal.request.post(getInitiateTokenUrl(state.additionalUrlParams)).then(function (data) {
          if (data.token) {
            dfr.resolve(data);
          } else {
            core.trigger('message', {'type': 'error', 'message': data.error});
            dfr.reject(data.error);
          }
        });

        return dfr.then(function (data) {
          return data.token;
        });
      };
    }
  })
});