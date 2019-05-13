/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Facebook Pixel event script
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

define('facebookPixel/addToCart', ['facebookPixel/event'], function (Event) {
  FacebookPixelAddToCart = Event.extend({
    processReady: function () {
      var self = this;

      core.bind('addToCartViaClick', function (event, params) {
        var productId = params.productId;
        var sku = $('.facebook-pixel-sku-' + productId).attr('value')
        self.registerAddedToCart(sku);
      });

      core.bind('addToCartViaDrop', function (event, params) {
        var productId = params.productId;
        var sku = $('.facebook-pixel-sku-' + productId).attr('value')
        self.registerAddedToCart(sku);
      });

      decorate(
        'ProductDetailsView',
        'postprocessAdd2Cart',
        function (event, data) {
          arguments.callee.previousMethod.apply(this, arguments);
          var sku = this.base.find('form.product-details').get(0).facebook_pixel_sku.value
          self.registerAddedToCart(sku);
        }
      );
    },

    registerAddedToCart: function (productId) {
      if (productId) {
        this.sendEvent('AddToCart', {
          content_ids: [productId],
          content_type: 'product'
        });
      } else {
        this.sendEvent('AddToCart');
      }
    }
  });

  FacebookPixelAddToCart.instance = new FacebookPixelAddToCart();

  return FacebookPixelAddToCart;
});
