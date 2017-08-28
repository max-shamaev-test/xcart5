/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Sticky panel controller
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

decorate(
  'StickyPanelModelList',
  'handleAnyItemsSelected',
  function (selector) {
    arguments.callee.previousMethod.apply(this, arguments);

    this.base.find('button.action-enable-sale span:last').text(core.t('Put up for sale'));
    this.base.find('button.action-disable-sale span:last').text(core.t('Cancel sale'));
  }
);


decorate(
  'StickyPanelModelList',
  'handleNoSelectedItems',
  function (selector) {
    arguments.callee.previousMethod.apply(this, arguments);

    this.base.find('button.action-enable-sale span:last').text(core.t('Put all for sale'));
    this.base.find('button.action-disable-sale span:last').text(core.t('Cancel sale for all'));
  }
);
