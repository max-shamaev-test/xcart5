/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Facebook Pixel event script
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

define('facebookPixel/search', ['facebookPixel/event'], function (Event) {
  FacebookPixelSearch = Event.extend({
    processReady: function() {
      var searchTextInput = jQuery(".search-product-form input[name='substring']");

      if (searchTextInput.length) {
        if (searchTextInput.val()) {
          this.registerSearchSubstring(searchTextInput.val());
        }

        jQuery(".search-product-form button[type='submit']").click(_.bind(function (event) {
          this.registerSearchSubstring(searchTextInput.val());
        }, this));
      } else if (core.getURLParam('substring').length) {
        this.registerSearchSubstring(core.getURLParam('substring'));
      }
    },

    registerSearchSubstring: function(substring) {
      if (substring) {
        this.sendEvent('Search', {
          search_string: substring
        });
      } else {
        this.sendEvent('Search');
      }
    }
  });

  FacebookPixelSearch.instance = new FacebookPixelSearch();

  return FacebookPixelSearch;
});
