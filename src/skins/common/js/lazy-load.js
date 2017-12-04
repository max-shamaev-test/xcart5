/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Lazy load
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

(function () {
  function processLazyLoadImages() {
    var layoutOptions = window.core.getLayoutOptions();

    $('.lazy-load:not(.lazy-load-bound)').each(function () {
      var wrapper = $(this);
      wrapper.addClass('lazy-load-bound');
      var image = wrapper.find('img:first');

      if (!image.css('background-image') || (layoutOptions.lazy_load === false)) {
        wrapper.removeClass('lazy-load lazy-load-bound');
        return;
      }

      image.bind('load', function () {
        if (wrapper.hasClass('lazy-load')) {
          wrapper.addClass('lazy-load-transition lazy-load-loaded');
          setTimeout(function () {
            wrapper.addClass('lazy-load-out');
          }, 100);
          wrapper.on('transitionend webkitTransitionEnd oTransitionEnd', function () {
            $(this).removeClass('lazy-load lazy-load-transition lazy-load-out');
          });
        }
      }).each(function () {
        if (this.complete) $(this).load();
      });
    });
  }

  core.bind('load', processLazyLoadImages);
  core.bind('loader.loaded', processLazyLoadImages);
  core.bind('afterPopupPlace', processLazyLoadImages);
})(jQuery);
