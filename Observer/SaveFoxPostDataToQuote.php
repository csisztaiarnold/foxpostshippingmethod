<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Model\Order\Address as OrderAddressModel;
use Oander\FoxPostShippingMethod\Enum\Attributes;
use Oander\FoxPostShippingMethod\Helper\Data as FoxPostData;

class SaveFoxPostDataToQuote implements ObserverInterface
{
    /**
     * @var FoxPostData
     */
    private FoxPostData $foxPostHelperData;

    /**
     * @var MessageManagerInterface
     */
    protected MessageManagerInterface $messageManager;

    /**
     * @param FoxPostData             $foxPostHelperData
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        FoxPostData             $foxPostHelperData,
        MessageManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->foxPostHelperData = $foxPostHelperData;
    }

    /**
     * @param  EventObserver $observer
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        if ($order->getShippingMethod() === Attributes::FOX_POST_SHIPPING_METHOD_CODE) {
            $foxPostData = $order->getFoxpostData();

            if (empty($foxPostData)) {
                $message = __('Missing foxpost information. Please select a parcelshop before you try to place your order again.');
                $this->messageManager->addError($message);
                throw new LocalizedException($message);
            }

            /* @var OrderAddressModel $orderAddress */
            $orderAddress = $quote->getShippingAddress();
            $orderAddress->setFoxpostData($foxPostData);

            $quote->setFoxpostData($foxPostData);
        }
        return $this;
    }
}
