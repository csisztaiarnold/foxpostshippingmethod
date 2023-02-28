<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\ScopeInterface;
use Oander\FoxPostShippingMethod\Helper\Data;

class ShippingMethodConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Resolver
     */
    protected Resolver $store;

    /**
     * CustomConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Resolver             $store
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Resolver             $store
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $config['foxpost_data']['iframeurl'] = $this->scopeConfig->getValue(
            Data::SHIPPING_METHOD_PATH . Data::IFRAME_URL,
            ScopeInterface::SCOPE_STORE
        );
        return $config;
    }
}
