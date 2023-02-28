<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Model;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Oander\FoxPostShippingMethod\Helper\Api;
use Oander\FoxPostShippingMethod\Helper\Config;
use Zend_Http_Client_Exception;

/**
 *
 */
class ShipmentManager
{
    /**
     * @var MagentoShipmentManager
     */
    private MagentoShipmentManager $magentoShipmentManager;

    /**
     * @var Api
     */
    private Api $apiHelper;

    /**
     * @var Config
     */
    private Config $configHelper;

    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $shipmentRepository;

    /**
     * @var Order
     */
    private Order $order;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var TrackFactory
     */
    private TrackFactory $trackFactory;

    /**
     * @param Api                         $apiHelper
     * @param Config                      $configHelper
     * @param MagentoShipmentManager      $magentoShipmentManager
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Order                       $order
     * @param OrderRepositoryInterface    $orderRepository
     * @param ManagerInterface            $messageManager
     * @param TrackFactory                $trackFactory
     */
    public function __construct(
        Api                         $apiHelper,
        Config                      $configHelper,
        MagentoShipmentManager      $magentoShipmentManager,
        ShipmentRepositoryInterface $shipmentRepository,
        Order                       $order,
        OrderRepositoryInterface    $orderRepository,
        ManagerInterface            $messageManager,
        TrackFactory                $trackFactory
    ) {
        $this->apiHelper = $apiHelper;
        $this->configHelper = $configHelper;
        $this->magentoShipmentManager = $magentoShipmentManager;
        $this->shipmentRepository = $shipmentRepository;
        $this->order = $order;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->trackFactory = $trackFactory;
    }

    /**
     * Creates a shipment array with the data to send to the API.
     *
     * @param  array $orderIds The array containing the order IDs.
     * @throws NoSuchEntityException
     * @throws DocumentValidationException
     * @throws LocalizedException
     */
    public function createShipmentArray(array $orderIds): array
    {
        $shipments = [];
        $i = 0;
        foreach ($orderIds as $orderId) {
            $shipment = $this->magentoShipmentManager->createShipment((int)$orderId);
            $order_data = $shipment->getOrder()->getStoredData();
            $foxpost_data = json_decode($order_data['foxpost_data']);
            $shipments[$i] = [
                "label" => true,
                "recipientEmail" => $shipment->getOrder()->getCustomerEmail(),
                "recipientName" => $shipment->getOrder()->getCustomerName(),
                "recipientPhone" => $shipment->getBillingAddress()->getTelephone(),
                "refCode" => 'FOX' . $orderId,
                "size" => "l",
                "destination" => isset($foxpost_data->code) ? $foxpost_data->code : '',
            ];

            if (!isset($foxpost_data->code)) {
                $shipments[$i]['recipientZip'] = $shipment->getShippingAddress()->getPostcode();
                $shipments[$i]['recipientCountry'] = $shipment->getShippingAddress()->getCountryId();
                $shipments[$i]['recipientCity'] = $shipment->getShippingAddress()->getCity();
                $shipments[$i]['recipientAddress'] = $shipment->getShippingAddress()->getStreet()[0];
            }
            $payment_method = $shipment->getOrder()->getPayment()->getMethodInstance()->getCode();
            // Cash on delivery is only sent when the payment method exist, this is optional.
            if ($payment_method === 'cashondelivery') {
                $shipments[$i]['cod'] = (int)$shipment->getOrder()->getGrandTotal();
            }
            $i++;
        }
        return $shipments;
    }

    /**
     * Sends the shipment to the API based on the array created by the createShipmentArray method.
     *
     * @param  array $shipment_array The shipment array.
     * @throws Zend_Http_Client_Exception
     */
    public function sendFoxpostShipment(array $shipment_array)
    {
        if ($this->configHelper->isFoxpostApiEnabled() === true) {
            $request = $this->apiHelper->request('parcel', 'POST', $shipment_array, 'data');
            // Store the sendCodes of the packages.
            $shipments = [];
            if (isset($request['data']['parcels'])) {
                foreach ($request['data']['parcels'] as $parcel) {
                    $orderId = explode('FOX', $parcel->refCode);
                    $shipments[] = [
                        'orderId' => $orderId[1],
                        'clFoxId' => $parcel->clFoxId,
                    ];
                    // Generates the label file and saves it to the media folder
                    $filename = $this->configHelper->getFoxpostLabelFolder() . '/' . $orderId[1] . '.pdf';
                    $this->apiHelper->setFileName($filename);
                    $this->apiHelper->request('label/' . $this->configHelper->getLabelSize(), 'POST', [$parcel->clFoxId], 'file');
                }
                return $shipments;
            }
        }
    }

    /**
     * Get shipment data by shipment ID.
     *
     * @param  $id
     * @return ShipmentInterface|null
     */
    public function getShipmentById($id): ?ShipmentInterface
    {
        try {
            $shipment = $this->shipmentRepository->get((int)$id);
        } catch (Exception $exception) {
            $shipment = null;
        }
        return $shipment;
    }

    /**
     * Get shipment data by order ID.
     *
     * @param  $order_id
     * @return string
     */
    public function getShipmentIdByOrderId($order_id)
    {
        $order = $this->order->load($order_id);
        $shipmentCollection = $order->getShipmentsCollection();
        foreach ($shipmentCollection as $shipment) {
            $shipmentId = $shipment->getIncrementId();
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->save();
        }
        return $shipmentId;
    }

    /**
     * Saves the tracking code of the shipment.
     *
     * @param  $shipment
     * @param  string $track_code
     * @return void
     * @throws LocalizedException
     */
    public function saveTrackCode($shipment, string $track_code): void
    {
        try {
            $data = [
                'carrier_label' => 'Foxpost',
                'carrier_code' => 'Foxpost',
                'title' => 'Foxpost',
                'number' => $track_code,
            ];
            $shipment->addTrack($this->trackFactory->create()->addData($data))->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
