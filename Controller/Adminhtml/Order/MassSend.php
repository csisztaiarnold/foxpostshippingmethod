<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Oander\FoxPostShippingMethod\Model\Flag\MassShippingFlag;
use Oander\FoxPostShippingMethod\Model\ShipmentManager;

class MassSend extends Action
{
    /**
     * @var MassShippingFlag
     */
    private MassShippingFlag $massShippingFlag;

    /**
     * @var ShipmentManager
     */
    private ShipmentManager $shipmentManager;

    /**
     * @param Context          $context
     * @param MassShippingFlag $massShippingFlag
     * @param ShipmentManager  $shipmentManager
     */
    public function __construct(
        Context          $context,
        MassShippingFlag $massShippingFlag,
        ShipmentManager  $shipmentManager
    ) {
        parent::__construct($context);
        $this->massShippingFlag = $massShippingFlag;
        $this->shipmentManager = $shipmentManager;
    }

    /**
     * Creates an array with the orders and sends it to the API endpoint. No need for a foreach iteration
     * of the shipments right here, shipments could be sent in a multidimensional array, the createShipmentArray
     * method does that.
     */
    public function execute()
    {
        $this->massShippingFlag->setFlag(true);
        $orderIds = $this->getRequest()->getParam('selected', []);
        try {
            $shipment_array = $this->shipmentManager->createShipmentArray($orderIds);
            $shipment_response = $this->shipmentManager->sendFoxpostShipment($shipment_array);
            if (isset($shipment_response)) {
                foreach ($shipment_response as $shipment_data) {
                    $shipment_id = $this->shipmentManager->getShipmentIdByOrderId((int)$shipment_data['orderId']);
                    $shipment = $this->shipmentManager->getShipmentById((int)$shipment_id);
                    $this->shipmentManager->saveTrackCode($shipment, $shipment_response[0]['clFoxId']);
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('sales/order');
    }
}
