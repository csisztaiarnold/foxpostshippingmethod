<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Controller\Adminhtml\Test;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Oander\FoxPostShippingMethod\Helper\Api;
use Oander\FoxPostShippingMethod\Helper\Config;
use Zend_Http_Client_Exception;

class Connection extends Action
{
    /**
     * @var Api
     */
    private Api $apiHelper;

    /**
     * @var Config
     */
    protected Config $configHelper;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonResultFactory;

    /**
     * @param Api         $apiHelper
     * @param Context     $context
     * @param Config      $configHelper
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Api         $apiHelper,
        Context     $context,
        Config      $configHelper,
        JsonFactory $jsonResultFactory
    ) {
        $this->apiHelper = $apiHelper;
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Test the connection in the Foxpost API config.
     *
     * @return Json
     * @throws Zend_Http_Client_Exception
     */
    public function execute()
    {
        if ($this->configHelper->isFoxpostApiEnabled() === true) {
            $response = $this->apiHelper->request('address', 'GET');

            if (isset($response['data']['error'])) {
                $data = [
                    'success' => 0,
                    'message' => $response['data']['error'],
                ];
            } else {
                $data = [
                    'success' => 1,
                    'message' => __('Connection successful'),
                ];
            }
            $result = $this->jsonResultFactory->create();
            $result->setData($data);

            return $result;
        }
    }
}
