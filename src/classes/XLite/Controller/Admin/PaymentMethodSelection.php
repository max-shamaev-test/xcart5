<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Controller\Admin;

use Includes\Utils\Module\Manager;

/**
 * Payment method selection  controller
 */
class PaymentMethodSelection extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('search'));
    }

    /**
     * Constructor
     *
     * @param array $params Constructor parameters
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }

    /**
     * Get session cell name for pager widget
     *
     * @return string
     */
    public function getPagerSessionCell()
    {
        return parent::getPagerSessionCell() . '_' . md5(microtime(true));
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        switch ($this->getPaymentType()) {
            case \XLite\Model\Payment\Method::TYPE_ALTERNATIVE:
                $result = static::t('Add alternative payment method');
                break;

            case \XLite\Model\Payment\Method::TYPE_OFFLINE:
                $result = static::t('Add offline payment method');
                break;

            default:
                $result = '';
                break;
        }

        return $result;
    }

    /**
     * Return payment methods type which is provided to the widget
     *
     * @return string
     */
    protected function getPaymentType()
    {
        return \XLite\Core\Request::getInstance()->{\XLite\View\Button\Payment\AddMethod::PARAM_PAYMENT_METHOD_TYPE};
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        $searchParams = parent::getSearchParams();

        $searchParams[\XLite\View\Pager\APager::PARAM_PAGE_ID] = 1;

        return $searchParams;
    }

    /**
     * Return true if 'Install' link should be displayed
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function isDisplayInstallModuleLink(\XLite\Model\Payment\Method $method)
    {
        return $method->getModuleName()
            && !$this->isModuleEnabled($method)
            && !$this->isDisplayInstallModuleButton($method);
    }

    /**
     * Return true if payment method's module is enabled
     *
     * @param \XLite\Model\Payment\Method $method Payment method model object
     *
     * @return boolean
     */
    protected function isModuleEnabled(\XLite\Model\Payment\Method $method)
    {
        return (bool) $method->getProcessor();
    }

    /**
     * Return true if 'Install' button should be displayed
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function isDisplayInstallModuleButton(\XLite\Model\Payment\Method $method)
    {
        $result = false;
        // todo: get this info from BUS

        return $result;
    }

    /**
     * Returns URL to payment module
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getPaymentModuleURL(\XLite\Model\Payment\Method $method)
    {
        list($author, $name) = explode('_', $method->getModuleName());

        return \Includes\Utils\Module\Manager::getRegistry()->getModuleServiceURL($author, $name);
    }
}
