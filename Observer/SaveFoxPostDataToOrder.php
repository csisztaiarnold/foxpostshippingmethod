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
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Address as OrderAddressModel;
use Oander\FoxPostShippingMethod\Enum\Attributes;
use Oander\FoxPostShippingMethod\Helper\Data as FoxPostData;

class SaveFoxPostDataToOrder implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

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
     * @param CartRepositoryInterface $quoteRepository
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        FoxPostData             $foxPostHelperData,
        CartRepositoryInterface $quoteRepository,
        MessageManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->quoteRepository = $quoteRepository;
        $this->foxPostHelperData = $foxPostHelperData;
    }

    /**
     * @param  EventObserver $observer
     * @return $this|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        if ($order instanceof OrderModel && $order->getShippingMethod() === Attributes::FOX_POST_SHIPPING_METHOD_CODE) {
            /* @var QuoteModel $quote */
            $quote = $this->quoteRepository->get($order->getQuoteId());

            /* @var OrderAddressModel $orderAddress */
            $orderAddress = $order->getShippingAddress();

            $foxPostData = $quote->getFoxpostData();
            if (empty($foxPostData)) {
                $message = __('Missing foxpost information. Please select a parcelshop before you try to place your order again.');
                $this->messageManager->addError($message);
                throw new LocalizedException($message);
            }

            $orderAddress->setFoxpostData($foxPostData);
            $order->setFoxpostData($foxPostData);
        }
        return $this;
    }
}
