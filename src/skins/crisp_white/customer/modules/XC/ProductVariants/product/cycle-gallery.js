/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Product details controller
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

decorate(
  'ProductDetailsView',
  'handleLoadVariantsImages',
  function (XMLHttpRequest, textStatus, data) {
    arguments.callee.previousMethod.apply(this, arguments);

    core.trigger('init-cycle-gallery');
  }
);

decorate(
  'ProductDetailsView',
  'loadVariantsImages',
  function (XMLHttpRequest, textStatus, data) {
    arguments.callee.previousMethod.apply(this, arguments);

    if (this.base.data('variants-has-images')) {
      $('.cycle-slideshow').each(function () {
        if ($(this).find('.cycle-carousel-wrap').length > 0) {
          $(this).cycle('destroy');
        }
      });
    }
  }
);

decorate(
  'ProductQuickLookVariantView',
  'processVariantImageAsGallery',
  function (XMLHttpRequest, textStatus, data) {
    $('.cycle-slideshow').cycle('destroy');
    arguments.callee.previousMethod.apply(this, arguments);
    core.trigger('init-cycle-gallery');
  }
);