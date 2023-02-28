<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Oander\FoxPostShippingMethod\Model\ShipmentManager;

/**
 * Class Send
 *
 * @package Oander\Gls\Controller\Adminhtml\Shipment
 */
class Send extends Action
{
    /**
     * @var ShipmentManager
     */
    private ShipmentManager $shipmentManager;

    /**
     * @param Context         $context
     * @param ShipmentManager $shipmentManager
     */
    public function __construct(
        Context         $context,
        ShipmentManager $shipmentManager
    ) {
        parent::__construct($context);
        $this->shipmentManager = $shipmentManager;
    }

    /**
     * Sends the Foxpost shipment.
     *
     * @return ResponseInterface|Redirect|ResultInterface|void
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id', 0);
        try {
            $shipment = $this->shipmentManager->getShipmentById((int)$shipmentId);
            // Since only one shipment is always present here, pass it as an array, the method will handle it.
            $shipment_array = $this->shipmentManager->createShipmentArray([$shipment->getOrderId()]);
            $shipment_response = $this->shipmentManager->sendFoxpostShipment($shipment_array);
            if (isset($shipment_response)) {
                $shipment->getOrder()->setIsInProcess(true);
                $this->shipmentManager->saveTrackCode($shipment, $shipment_response[0]['clFoxId']);
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath(
            'sales/shipment/view',
            ['shipment_id' => $shipmentId]
        );
    }
}
