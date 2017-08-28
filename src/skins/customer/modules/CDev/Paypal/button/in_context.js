/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Paypal In-Context checkout
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

jQuery(function () {
  var merchantId = core.getCommentedData(jQuery('body'), 'PayPalMerchantId');
  var environment = core.getCommentedData(jQuery('body'), 'PayPalEnvironment');

  window.paypalCheckoutReady = function () {
    paypal.checkout.setup(merchantId, {
      environment: environment,
      button: ['ec_minicart', 'ppc_minicart'],
      click: function () {
      }
    });
  };
});

var initiateToken = function () {
  var postOptions = {
    target: 'checkout',
    action: 'startExpressCheckout',
    inContext: true
  };
  postOptions[xliteConfig.form_id_name] = xliteConfig.form_id;
  core.post(URLHandler.buildURL(postOptions));
};

var expressCheckoutAdd2CartFlag = false;

var paypalExpressCheckout = function (element, isAdd2Cart, url) {
  if (isAdd2Cart) {
    showAdd2CartPopup = false;
    expressCheckoutAdd2CartFlag = true;
    element.commonController.backgroundSubmit = true;
    element['expressCheckout'].value = 1;

    var form = $(element);

    if (form) {
      form.submit();
    }

    element['expressCheckout'].value = 0;
  } else {
    paypal.checkout.initXO();
    popup.close();

    expressCheckoutAdd2CartFlag = false;
    setTimeout(function () {
      element.target = "PPFrame";
      initiateToken();

      core.bind('paypaltoken', function (event, result) {
        if (result.token) {
          paypal.checkout.startFlow(result.token);
        } else {
          paypal.checkout.closeFlow();
        }
      });
    }, 0);

    setTimeout(function () {
      element.target = "";
    }, 500);
  }

  if (window.event) {
    window.event.preventDefault();
  }

  return false;
};

jQuery(function () {
  decorate(
    'ProductDetailsView',
    'postprocessAdd2Cart',
    function (event, data) {
      arguments.callee.previousMethod.apply(this, arguments);

      if (expressCheckoutAdd2CartFlag) {
        paypal.checkout.initXO();
        popup.close();

        setTimeout(function () {
          initiateToken();

          core.bind('paypaltoken', function (event, result) {
            if (result.token) {
              paypal.checkout.startFlow(result.token);
            } else {
              paypal.checkout.closeFlow();
            }
          });
        }, 0);

        expressCheckoutAdd2CartFlag = false;
      }
    }
  );
});


