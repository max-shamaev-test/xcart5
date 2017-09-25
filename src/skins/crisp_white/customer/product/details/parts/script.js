/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Single language selector
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */


jQuery(function() {
  core.bind('block.product.details.postprocess', function() {
    $('.product-details-tabs ul.tabs').tabCollapse({
      tabsClass: 'hidden-xs',
      accordionClass: 'visible-xs'
    });
  });
});