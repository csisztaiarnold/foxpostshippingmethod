<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;

class ParcelInformation extends AbstractHelper
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepsitory;

    /**
     * @var null
     */
    private $currentOrder = null;

    /**
     * @var null|array
     */
    private ?array $foxPostData = null;

    /**
     * @var null|int
     */
    private ?int $orderId = null;

    /**
     * @var null|array
     */
    private ?array $jsonArray = null;

    /**
     * @var JsonSerializer
     */
    private JsonSerializer $jsonSerializer;

    public function __construct(
        Context                  $context,
        JsonSerializer           $jsonSerializer,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->jsonSerializer = $jsonSerializer;
        $this->orderRepsitory = $orderRepository;
    }

    /**
     * @param  int $orderIdparamter
     * @return void
     */
    public function setOrderId(int $orderIdparamter)
    {
        $this->orderId = $orderIdparamter;
    }

    /**
     * @return OrderModel
     * @throws LocalizedException
     */
    private function getOrder(): OrderModel
    {
        if (!$this->orderId) {
            throw new LocalizedException(__('Please set the orderID'));
        }
        if (null === $this->currentOrder) {
            try {
                $order = $this->orderRepsitory->get($this->orderId);
                if ($order instanceof OrderModel) {
                    $this->currentOrder = $order;
                }
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('This order no longer exists.'));
            }
        }
        return $this->currentOrder;
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    protected function getFoxPostData(): ?array
    {
        if (null === $this->foxPostData) {
            $this->foxPostData = [];
            $order = $this->getOrder();
            if ($order instanceof OrderModel) {
                $jsonData = $order->getFoxpostData();
                $this->foxPostData = $this->createFromJson($jsonData);
            }
        }
        return $this->foxPostData;
    }

    /**
     * @param  $jsonString
     * @return array
     */
    private function createFromJson($jsonString): array
    {
        if (null === $this->jsonArray) {
            $this->jsonArray = $this->jsonSerializer->unserialize($jsonString);
        }
        return $this->jsonArray;
    }

    /**
     * @return float|DataObject|string|null
     * @throws LocalizedException
     */
    public function getShippingMethod()
    {
        $order = $this->getOrder();
        if ($order instanceof OrderModel) {
            return $order->getShippingMethod();
        }
        return '';
    }

    /**
     * @param  $key
     * @param  string $defaultValue
     * @return string
     * @throws LocalizedException
     */
    public function getFoxPostItemData($key, string $defaultValue = ''): string
    {
        $data = $this->getFoxPostData();
        if (array_key_exists($key, $data)) {
            return (string)$data[$key];
        }
        return $defaultValue;
    }

}
