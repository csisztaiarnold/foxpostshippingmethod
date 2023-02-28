<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

/**
 *
 */
class MagentoShipmentManager
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ShipmentLoader
     */
    private ShipmentLoader $shipmentLoader;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentLoader           $shipmentLoader
     * @param Registry                 $registry
     */
    public function __construct(
        OrderRepositoryInterface   $orderRepository,
        ShipmentLoader             $shipmentLoader,
        Registry                   $registry
    ) {
        $this->orderRepository = $orderRepository;
        $this->shipmentLoader = $shipmentLoader;
        $this->registry = $registry;
    }

    /**
     * @throws NoSuchEntityException
     * @throws DocumentValidationException
     * @throws LocalizedException
     */
    public function createShipment(int $orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getShipmentsCollection()->count()) {
            return $order->getShipmentsCollection()->getFirstItem();
        }
        $this->shipmentLoader->setOrderId($orderId);
        $this->shipmentLoader->setShipment([]);
        $this->registry->unregister('current_shipment');
        $shipment = $this->shipmentLoader->load();
        if (!$shipment) {
            throw new LocalizedException(__('Cannot create shipment for order %1', $orderId));
        }

        $shipment->register();

        $shipment->getOrder()->setIsInProcess(true);
        $transaction = ObjectManager::getInstance()->create(
            \Magento\Framework\DB\Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $shipment;
    }
}
