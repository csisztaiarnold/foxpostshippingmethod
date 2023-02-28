<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const SHIPPING_METHOD_PATH = 'carriers/foxpost/';
    const ENABLED = 'active';
    const TITLE = 'title';
    const METHOD_NAME = 'name';
    const SHIPPING_FEE = 'shipping_fee';
    const FREE_SHIPPING_THRESHOLD = 'active_free_shipping_threshold';
    const SHIPPING_FEE_THRESHOLD = 'shipping_fee_threshold';
    const ERROR_MESSAGE = 'error_message';
    const IFRAME_URL = 'iframeurl';
    const SORT_ORDER = 'sort_order';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context              $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @param  $storeid
     * @return bool
     */
    public function getShippingMethodEnabled($storeid = null): bool
    {
        return (bool)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeid
        ) ?? false;
    }

    /**
     * @param  $websiteId
     * @return string
     */
    public function getShippingMethodTitle($websiteId = null): string
    {
        return (string)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::TITLE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        ) ?? '';
    }

    /**
     * @param  $websiteId
     * @return string
     */
    public function getShippingMethodName($websiteId = null): string
    {
        return (string)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::METHOD_NAME,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        ) ?? '';
    }

    /**
     * @param  $storeId
     * @return int
     */
    public function getShippingMethodFee($storeId = null): int
    {
        return (int)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::SHIPPING_FEE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? 0;
    }

    /**
     * @param  $storeId
     * @return string
     */
    public function getErrorMessage($storeId = null): string
    {
        return (string)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? '';
    }

    /**
     * @param  $storeId
     * @return int
     */
    public function getShippingMethodFeeThreshold($storeId = null): int
    {
        return (int)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::SHIPPING_FEE_THRESHOLD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? 0;
    }

    /**
     * @param  $storeId
     * @return bool
     */
    public function getShippingFreeThreshold($storeId = null): bool
    {
        return (bool)$value = $this->scopeConfig->getValue(
            self::SHIPPING_METHOD_PATH . self::FREE_SHIPPING_THRESHOLD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? false;
    }
}
