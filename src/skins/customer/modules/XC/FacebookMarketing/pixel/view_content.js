/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Facebook Pixel event script
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

define('facebookPixel/viewContent', ['facebookPixel/event'], function (Event) {
  FacebookPixelViewContent = Event.extend({
    processReady: function() {
      this.registerView();
    },

    registerView: function() {
      this.sendEvent('ViewContent');
    }
  });

  FacebookPixelViewContent.instance = new FacebookPixelViewContent();

  return FacebookPixelViewContent;
});
