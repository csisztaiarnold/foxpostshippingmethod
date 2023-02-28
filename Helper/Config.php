<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const FOXPOST_SETTINGS_PATH = 'carriers/foxpost_api/';
    const FOXPOST_API_BASE_URL = 'https://webapi.foxpost.hu/api/';
    const FOXPOST_LABEL_FOLDER = 'foxpost_labels';

    /**
     * Is the module enabled?
     *
     * @return bool
     */
    public function isFoxpostApiEnabled(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::FOXPOST_SETTINGS_PATH . 'enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the FoxPost API Basic Auth Username.
     *
     * @return string
     */
    public function getFoxpostApiUsername(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::FOXPOST_SETTINGS_PATH . 'username',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the FoxPost API Basic Auth Password.
     *
     * @return string
     */
    public function getFoxpostApiPassword(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::FOXPOST_SETTINGS_PATH . 'password',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the FoxPost API Key.
     *
     * @return string
     */
    public function getFoxpostApiKey(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::FOXPOST_SETTINGS_PATH . 'api_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the label size.
     *
     * @return string
     */
    public function getLabelSize(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::FOXPOST_SETTINGS_PATH . 'label_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the base URL for the API.
     *
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        return self::FOXPOST_API_BASE_URL;
    }

    /**
     * Get the base URL for the API.
     *
     * @return string
     */
    public function getFoxpostLabelFolder(): string
    {
        return self::FOXPOST_LABEL_FOLDER;
    }
}
